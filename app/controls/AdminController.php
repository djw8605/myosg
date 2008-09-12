<?

require_once("app/timerange.php");

//This class takes care of all cron-ish jobs that are initiated from cron on localhost
//wget -O latestmetric_log http://rsv-itb.grid.iu.edu/trunk/cron/latestmetric
class AdminController extends Zend_Controller_Action 
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

        //why flock? sometimes processnew takes very long time to complete, and if the jobs is kicking off 
        //every minute, the overwrap could happen. If it does, skip it.
        $fp_lock = fopen("/tmp/dashboard.processnew", "w+");
        dlog("acquiring flock.");
        if (flock($fp_lock, LOCK_EX | LOCK_NB)) {
            dlog("acquiring flock success...");

            $resource_model = new Resource();
            $probe_model = new ProbeInfo();
            $metric_model = new Metrics();

            //grab sets of latest metrics for each resources for status caluculations
            $current_metrics = array();
            $overall_status_model = array();
            $overall_status = array();

            //for some status..
            $rejected = 0;
            $inserted = 0;
            $validation_error = 0;
            $newstatus_inserted = 0;

            //grab "some" new records
            $newrecords = $metric_model->fetchNewGratiaRecords(config()->gratia_recordcount);
            dlog("Records grabbed from gratia: ".count($newrecords));

            //we are going to make repeated inserts (x thousands times). Let's disable profiling for now
            Zend_Registry::get('db')->getProfiler()->setEnabled(false);

            require_once("app/plugins/metricupdate.php");
            $plugin = new Plugin_MetricUpdate();

            foreach($newrecords as $record) {
                //pull & gather information about this metric data
                $dbid = $record->dbid;
                $resource_id = $resource_model->lookupID($record->ServiceUri);
                $metric_id = $probe_model->lookupID($record->MetricName);
                $status = $record->MetricStatus;
                $timestamp = $record->unix_timestamp;
                $detail = $record->DetailsData;
                $effective_dbid = null;
                $effective_timestamp = null;

                //make sure it belongs to correct resource and metric
                if($resource_id === null) {
                    $validation_error++;
                    continue;
                }
                if($metric_id === null) {
                    $validation_error++;
                    continue;
                }

                //pull current metrics for this resource (for initial metric set)
                //also initialize the overstatus model for status calculation
                $current = array();
                if(!isset($current_metrics[$resource_id])) {
                    dlog("initializing current_metrics array for $resource_id");
                    $current = $metric_model->getLatest($resource_id);

                    //find the last metric entered and use it as resource timestamp (to detect out-of-order metric data)
                    $last = 0;
                    foreach($current as $met) {
                        if($met->timestamp > $last) $last = $met->timestamp;
                    }
                    $current["lasttime"] = $last;
                    //dlog("last metric entry for $resource_id happend at ".date(config()->date_format_full." s", $last));

                    $current_metrics[$resource_id] = $current;

                    $ostatus_model = new OverallStatus($resource_id);
                    $overall_status_model[$resource_id] = $ostatus_model;
                    $lastinfo = $ostatus_model->getLastInfo();
                    if(isset($lastinfo->overall_status)) {
                        $overall_status[$resource_id] = $lastinfo->overall_status;
                    } else {
                        //below if ($new_status != overall_status) will cause this record to be inserted as initial status.
                        $overall_status[$resource_id] = "no-last-info"; 
                    }
                }

                //some shorthands..
                $ostatus_model = $overall_status_model[$resource_id];
                $current = $current_metrics[$resource_id];
                
                //if the metric status is unknown, try to use previous non-unknown metric
                //if it it's within certain time frame.
                if($status == "UNKNOWN") {
                    if(isset($current[$metric_id])) {
                        $previous = $current[$metric_id];
                        if($previous->status == "UNKNOWN") {
                            //previous is also UNKNOWN, test with it's effective_ metric info
                            if(($previous->effective_timestamp + config()->metric_considered_old) > $timestamp) {
                                $effective_dbid = $previous->effective_dbid;
                                $effective_timestamp = $previous->effective_timestamp;
                            }
                        } else {
                            //previous is not UNNOWN, test with it's status
                            if(($previous->timestamp + config()->metric_considered_old) > $timestamp) {
                                $effective_dbid = $previous->dbid;
                                $effective_timestamp = $previous->timestamp;
                            }
                        }
                        if($effective_dbid !== null) {
                            dlog("setting effective status on metric ID: $dbid with that of metric ID: $effective_dbid");
                        }
                    }
                }

                //Finaly, update the current set with the new record from gratia
                if(!isset($current[$metric_id])) {
                    //first ever metric type for this resource!
                    //MetricRecord is defined in config.php because we serialize current array
                    $current[$metric_id] = new MetricRecord(); 
                }
                $current[$metric_id]->dbid = $dbid;
                $current[$metric_id]->status = $status;
                $current[$metric_id]->timestamp = $timestamp;
                $current[$metric_id]->detail = $detail;
                $current[$metric_id]->effective_dbid = $effective_dbid;
                $current[$metric_id]->effective_timestamp = $effective_timestamp;

                //check the lasttimestamp to detect the out-of-order metric -- 
                //(TODO: Arvind to contact Gratia to see if this could be prevented upstream)
                if($current["lasttime"] > $timestamp) {
                    $lag = $current["lasttime"] - $timestamp;
                    if($lag < config()->gratia_max_outoforder) {
                        dlog("out-of-order metric detected. ID:$dbid ".$lag. " seconds - resetting timestamp to last reported timestamp");
                        $timestamp = $current["lasttime"];
                    } else {
                        //ignore this metric.. for now.
                        dlog("out-of-order metric detected. ID:$dbid ".$lag. " seconds (more than ".config()->gratia_max_outoforder." seconds) - ignoring");
                        continue;
                    }
                }
                $current["lasttime"] = $timestamp;

                //insert new record to our metrics table
                try {
                    $metric_model->insert($dbid, $resource_id, $metric_id, $status, $timestamp, $detail, $effective_dbid, $effective_timestamp);
                    //dlog("inserting new metric $dbid for resource: $resource_id at ".date(config()->date_format_full." s", $timestamp));
                    $inserted++;
                } catch (Exception $e) {
                    $rejected++;
                    elog("Caught Exception while running query: ".print_r($e, true));
                }

                //call metric update plugins for any additional processing for this metric update
                $plugin->dispatch($resource_id, $metric_id, $current[$metric_id]);

                //Re-calculate overall status with current metric set
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

                    //NA means the status is UNKNOWN(for now..) due to critical metrics
                    //not even reported..
                    if($ostatus_model->isNA()) {
                        $dbid = null; //no particular metric should be responsible for not having enough data
                    }

                    if($ostatus_model->insertNewOverallStatus(
                            $new_status, 
                            $timestamp, 
                            $ostatus_model->getOverallDetail(), 
                            $resource_id, 
                            $dbid)) {
                        $newstatus_inserted++;
                        dlog("inserted status change for $resource_id, at $timestamp caused by $dbid");
                    }
                    $overall_status[$resource_id] = $new_status;
                }

                $current_metrics[$resource_id] = $current;
            }

            //TODO - following algorithm doesn't work, because I don't know when to set timestamp
            //for calculation time of calculate()
/*
            //for resrouce that I didn't receive any data (and the status is not "UNKNOWN"), let's
            //check the metricdata timestamp to see if the status should now be expired (UNKNOWN)
            dlog("processing on resource that has stopped posting data");
            $resources = $resource_model->fetchAll();
            foreach($resources as $resource) {
                $found = false;
                foreach($current_metrics as $resource_id=>$current) {
                    if($resource->id == $resource_id) {
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    $cache_filename = config()->cache_filename_latest_overall.".".$resource->id;
                    if(file_exists($cache_filename)) {
                        //load the last status
                        $status = unserialize(file_get_contents($cache_filename));
                        if($status["status"] != "UNKNOWN") {
                            //dlog("status expiration candicate ".$resource->id);

                            //load latest metrics
                            $lastmetrics = unserialize(file_get_contents(config()->cache_filename_latest_metrics.".".$resource->id));
                            
                            //recalculate overallstatus
                            $omodel = new OverallStatus($resource->id, $when???);
                            $omodel->calculateStatus($lastmetrics);

                            if($omodel->isExpired()) {
                                $timestamp = $omodel->getExpiredTimestamp();
                                $detail = $omodel->getOverallDetail();
                                $dbid = $omodel->getExpiredResponsibleID();
                                if($omodel->insertNewOverallStatus(
                                        "UNKNOWN", 
                                        $timestamp, 
                                        $detail,
                                        $resource_id, 
                                        $dbid)) {
                                    $newstatus_inserted++;
                                    elog("Expired Previously: inserted status change for $resource_id, at $timestamp caused by $dbid");

                                    //these causes below caching saving code to update info
                                    $current_metrics[$resource->id] = $lastmetrics;
                                    $overall_status_model[$resource->id] = $omodel;
                                }
                            }
                        }
                    }
                }
            }
*/

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
                $ostatus_model->calculateStatus($current);//calculate on time()
                $info = array(
                    "status"=>$ostatus_model->getOverallStatus(),
                    "detail"=>$ostatus_model->getOverallDetail()
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
            if(count($newrecords) > 0) {
                $dstr = date(config()->date_format_full, $timestamp);
                dlog("data is now synced to $dstr");
            }

            flock($fp_lock, LOCK_UN);
        } else {
            elog("Failed to obtain flock for processnewAction -- maybe previous run is taking too long?");
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

    public function ordertestAction()
    {
        $resource_model = new Resource();
        $resources = $resource_model->fetchAll();
        $db = Zend_Registry::get('db');
        foreach($resources as $resource) {
            if($resource->name != "UC_ITB") continue;
            $rows = $db->fetchAll("SELECT *, UNIX_Timestamp(Timestamp) as timestamp  from gratia.MetricRecord where ServiceUri = 'uct3-edge7.uchicago.edu'");
            if(count($rows) == 0) continue;
            echo "Checking timestamp ordering on ".$resource->name."<br/>"; 
            $ctime = 0;
            $mis = 0;
            $lasttime = 0;
            foreach($rows as $row) {
                if($ctime > $row->timestamp) {
                    $mis++;
                    echo date(config()->date_format_full, $row->timestamp)." ".($ctime - $row->timestamp). "sec ".$row->dbid." ".$row->metric_id."<br/>";
                    $lasttime = $row->timestamp;
                } 
                $ctime = $row->timestamp;
            }
            echo "&nbsp;&nbsp;Misalignment: $mis our of ".count($rows)." metrics. <br/>";
        }
        $this->render("none");
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

    //clear history for last n-days
    public function clearAction()
    {
        if(!isset($_REQUEST["days"])) {
            $days = 7;
        } else {
            $days = $_REQUEST["days"];
        }
        $start = time() - $days*3600*24;

        $db = Zend_Registry::get('db');

        $db->query("delete from metric where timestamp > $start");
        $db->query("delete from overall_status where timestamp > $start");

        //clear cache
        passthru("rm ".config()->getCacheDir()."/*");
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
        passthru("rm ".config()->getCacheDir()."/*");
        $this->render("none");
    }

    public function clearlogAction()
    {
        clearlog();
        $this->render("none");
    }

    public function emailAction()
    {

        elog("elog will be sent to email");

/*
        $to = "hayashis@indiana.edu";
        $subject = "Hi!";
        $body = "Hi,\n\nHow are you?";
        if(mail($to, $subject, $body)) {
            echo("<p>Message successfully sent!</p>");
        } else {
            echo("<p>Message delivery failed...</p>");
        }
*/
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
