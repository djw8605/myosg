<?

require_once("app/timerange.php");

//since this ain't python, I have to do this stupid thing to mimic
//metric record datastructure - TODO if I change the metric table
//layout, I have to update this too..
class MetricRecord
{
    public function __construct()
    {
        $this->dbid = null;
        $this->status = null;
        $this->timestamp = null;
        $this->detail = null;
        $this->effective_dbid = null;
        $this->effective_timestamp = null;
    }
}

//This class takes care of all cron-ish jobs that are initiated from cron on localhost
//wget -O latestmetric_log http://rsv-itb.grid.iu.edu/trunk/cron/latestmetric
class CronController extends Zend_Controller_Action 
{ 
    public function init()
    {
        //make sure the request originated from localhost
        if(!config()->debug) {
            if($_SERVER["REMOTE_ADDR"] != $_SERVER["SERVER_ADDR"]) {
                //pretend that this page doesn't exist
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                echo "404";
                exit;
            }
        }
    }

    //load new record from Gratia db and load it to our DB tables
    public function processnewAction()
    {
        dlog("processing new records from gratia table.");

        //lock the whole thing...
        /*
        $mutex = new Mutex();
        $mutex_key = ftok($_SERVER["SCRIPT_FILENAME"], "m");
        $mutex->init($mutex_key);
        */

        $fp_lock = fopen("/tmp/dashboard.processnew", "w+");
        dlog("acquiring flock.");
        //if($mutex->acquire()) {
        if (flock($fp_lock, LOCK_EX)) {
            dlog("acquiring flock success...");

            $resource_model = new Resource();
            $probe_model = new ProbeInfo();
            $metric_model = new Metrics();

            //grab sets of latest metrics for each resources for status caluculations
            $current_metrics = array();
            $overall_status_model = array();
            $overall_status = array();

            //number of records rejected
            $rejected = 0;
            $inserted = 0;
            $validation_error = 0;
            $newstatus_inserted = 0;

            //grab some new records (not all of them..)
            $newrecords = $metric_model->fetchNewGratiaRecords(3000);
            dlog("Records grabbed from gratia: ".count($newrecords));

            //we are going to make repeated inserts (x thousands times). Let's disable profiling for now
            Zend_Registry::get('db')->getProfiler()->setEnabled(false);

            foreach($newrecords as $record) {
                //lookup resource_id
                $dbid = $record->dbid;
                $resource_id = $resource_model->lookupID($record->ServiceUri);
                $metric_id = $probe_model->lookupID($record->MetricName);
                $status = $record->MetricStatus;
                $timestamp = $record->unix_timestamp;
                $detail = $record->DetailsData;
                $effective_dbid = null;
                $effective_timestamp = null;

                //validate 
                if($resource_id === null) {
                    $validation_error++;
                    continue;
                }
                if($metric_id === null) {
                    $validation_error++;
                    continue;
                }

                //pull current metrics for this resource (for initial metric set)
                $current = array();
                if(isset($current_metrics[$resource_id])) {
                    $current = $current_metrics[$resource_id];
                } else {
                    $current = $metric_model->getLatest($resource_id);
                    $current_metrics[$resource_id] = $current;
                }

                //if status is unknown, set effective_dbic to last known metric if it is 
                //within metric_considered_old time.
                if($status == "UNKNOWN") {
                    if(isset($current[$metric_id])) {
                        $previous = $current[$metric_id];
                        //previous not too old?
                        if($previous->status == "UNKNOWN") {
                            if(($previous->effective_timestamp + config()->metric_considered_old) > $timestamp) {
                                $effective_dbid = $previous->effective_dbid;
                                $effective_timestamp = $previous->effective_timestamp;
                            }
                        } else {
                            if(($previous->timestamp + config()->metric_considered_old) > $timestamp) {
                                $effective_dbid = $previous->dbid;
                                $effective_timestamp = $previous->timestamp;
                            }
                        }
                        if($effective_dbid !== null) {
                            dlog("setting to $dbid effective_dbid to $effective_dbid");
                        }
                    }
                }

                //update the current set with the new record from gratia
                if(!isset($current[$metric_id])) {
                    $current[$metric_id] = new MetricRecord();
                }
                $current[$metric_id]->dbid = $dbid;
                $current[$metric_id]->status = $status;
                $current[$metric_id]->timestamp = $timestamp;
                $current[$metric_id]->detail = $detail;
                $current[$metric_id]->effective_dbid = $effective_dbid;
                $current[$metric_id]->effective_timestamp = $effective_timestamp;

                //insert to our metrics table
                try {
                    $metric_model->insert($dbid, $resource_id, $metric_id, $status, $timestamp, $detail, $effective_dbid, $effective_timestamp);
                    $inserted++;
                    //$inserted_ids[] = $dbid;
                } catch (Exception $e) {
                    $rejected++;
                    dlog("Caught Exception while running query: ".print_r($e, true));
                }

                //re-calculate overall status
                if(!isset($overall_status[$resource_id])) {
                    //we have not yet seen this resource id appears initialize aux. classes.
                    dlog("initializing for $resource_id");
                    $ostatus_model = new OverallStatus($resource_id);
                    $overall_status_model[$resource_id] = $ostatus_model;
                    $lastinfo = $ostatus_model->getLastInfo();
                    if(isset($lastinfo->overall_status)) {
                        $overall_status[$resource_id] = $lastinfo->overall_status;
                    } else {
                        //below if ($new_status != overall_status) will cause this record to be inserted as
                        //initial status.
                        $overall_status[$resource_id] = "no-last-info"; 
                    }
                } else {
                    $ostatus_model = $overall_status_model[$resource_id];
                }
                $ostatus_model->calculateStatus($current, $timestamp);
                $new_status = $ostatus_model->getOverallStatus();

                //insert to status change table if ostatus changed
                if($overall_status[$resource_id] != $new_status) {

                    //if status changed due to metric being expired, then use the expiration time
                    //and its metric id for this status change.
                    //I can then ignore the metric itself that triggered the status to change to UNKNOWN
                    //since the status will remain unknown.
                    if($ostatus_model->isExpired()) {
                        $timestamp = $ostatus_model->getExpiredTimestamp();
                        $dbid = $ostatus_model->getExpiredResponsibleID();

                        //update current set as well..
                        $current[$metric_id]->dbid = $dbid;
                        $current[$metric_id]->timestamp = $timestamp;
                        dlog("status change due to metric expiration.. resetting responsible dbid to $dbid");
                    }

                    //NA means the status is UNKNONW(for now..) due to critical metrics
                    //not even reported..
                    if($ostatus_model->isNA()) {
                        $dbid = null;
                    }

                    $counts = trim(addslashes(serialize($ostatus_model->getStatusCounts())));
                    if($ostatus_model->insertNewOverallStatus(
                            $new_status, 
                            $timestamp, 
                            $ostatus_model->getOverallDetail(), 
                            $resource_id, 
                            $dbid, 
                            $counts)) {
                        $newstatus_inserted++;
                        dlog("inserted status change for $resource_id, at $timestamp caused by $dbid");
                    }
                    $overall_status[$resource_id] = $new_status;
                }
            }

            //now, we have the latest info in our $current array. let's update our current cache
            dlog("updating latest information cache");
            foreach($current_metrics as $resource_id => $current) {
                //latest metrics
                $ostatus_model = $overall_status_model[$resource_id];
                $out = serialize($current);
                $fp = fopen(config()->cache_filename_latest_metrics.".".$resource_id, "w");
                fwrite($fp, $out);
                fclose($fp);

                //latest overall status
                $ostatus_model = $overall_status_model[$resource_id];
                $ostatus_model->calculateStatus($current);
                $info = array(
                    "status"=>$ostatus_model->getOverallStatus(),
                    "detail"=>$ostatus_model->getOverallDetail(),
                    "counts"=>$ostatus_model->getStatusCounts()
                );
                $out = serialize($info);
                $fp = fopen(config()->cache_filename_latest_overall.".".$resource_id, "w");
                fwrite($fp, $out);
                fclose($fp);
            } 

            dlog("$validation_error records has failed on validation.");
            dlog("$rejected records has been rejected.");
            dlog("$inserted records has been inserted.");
            dlog("$newstatus_inserted records has been inserted to overall_status table.");

            flock($fp_lock, LOCK_UN);
            //$mutex->release();
        } else {
            dlog("Failed to obtain mutex for processnewAction");
        }
        fclose($fp_lock);
    }

    private function outputData($filename, $changes)
    {
        //serialize 
        $out = serialize($changes);
        $fp = fopen($filename, "w");
        fwrite($fp, $out);
        fclose($fp);
    }

    public function geocodeAction()
    {

        $db = Zend_Registry::get('db');
        $rows = $db->fetchAll("SELECT site_id, zipcode FROM oim.site s where zipcode is not null and zipcode <> \"\"");
        foreach($rows as $row) {
            //this is very slow...
            $ret = system("wget -O - http://geocoder.us/service/csv/geocode?zip=".$row->zipcode);

            $a = split(",", $ret);
            $sql = "update oim.site set latitude='".$a[0]."', longitude='".$a[1]."' where site_id = ".$row->site_id;
            dlog($sql);
            $db->query($sql);
        }
        $this->render("none");
    }


    public function installAction()
    {
        $db = Zend_Registry::get('db');

        $db->query("DROP TABLE IF EXISTS `rsvextra`.`metric`;");
        $db->query("CREATE TABLE  `rsvextra`.`metric` (
  `dbid` int(10) unsigned NOT NULL default '0',
  `status` varchar(10) NOT NULL,
  `detail` text NOT NULL,
  `resource_id` int(10) unsigned NOT NULL default '0',
  `metric_id` int(10) unsigned NOT NULL default '0',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `effective_dbid` int(10) unsigned default NULL,
  `effective_timestamp` int(10) unsigned default NULL,
  PRIMARY KEY  USING BTREE (`dbid`),
  KEY `resource_id_index` USING BTREE (`resource_id`),
  KEY `timestamp` (`timestamp`),
  KEY `probe_id_index` USING BTREE (`metric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='this is a copy of metricrecord from gratia, but  this table ';");

        $db->query("DROP TABLE IF EXISTS `rsvextra`.`overall_status`;");
        $db->query("CREATE TABLE  `rsvextra`.`overall_status` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `overall_status` varchar(10) default NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `detail` text NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  `responsible_metric_id` int(10) unsigned default NULL,
  `count_info` varchar(128) default NULL,
  PRIMARY KEY  (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=472 DEFAULT CHARSET=latin1;");
        //clear cache
        passthru("rm ".config()->cache_dir."/*");

        $this->render("none");
        
    }


/*
    public function addressAction()
    {
        $resource_model = new Resource();
        $resources = $resource_model->fetchAll();
        foreach($resources as $resource) {
            $name = $resource->name; 
            echo $name."<br/>";
            passthru("wget -O - http://is.grid.iu.edu/cgi-bin/show_source_data?which=$name&source=served");
        }
    }
*/
} 
