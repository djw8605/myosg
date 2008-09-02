<?

class Metrics
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
        $this->metrics[] = array();
    }

    public function getDetail($dbid) 
    {
        return $this->db->fetchOne("select detail from metric where dbid = $dbid");
    }

    public function getStatus($dbid) 
    {
        return $this->db->fetchOne("select status from metric where dbid = $dbid");
    }

    public function fetchNewGratiaRecords($limit = 1000)
    {
        //$sql = "select *, UNIX_TIMESTAMP(Timestamp) as unix_timestamp from gratia.MetricRecord where UNIX_TIMESTAMP(Timestamp) > ifnull((select max(timestamp) from rsvextra.metric), 0) order by Timestamp limit $limit;";
        //why am I sorting by timestamp? Because Gratia receives metric in out-of-oder timestamp.
        //TODO - now... what this "doesn't" prevent is that if the first record in the batch happens 
        //to be out-of-order, then we will still have out-of-order issue.. processnew action
        //may need to artificially update the timestamp by ignoring the reported timestamp
        //to not cause invalid data entry..
        $sql = "select dbid,ServiceUri,MetricName,MetricStatus,DetailsData, UNIX_TIMESTAMP(Timestamp) as unix_timestamp from gratia.MetricRecord where dbid > ifnull((select max(dbid) from rsvextra.metric), 0) order by Timestamp limit $limit;";
        dlog("Fetching new gratia recores. $sql");
        return $this->db->fetchAll($sql);
    }

    //pull metrics for a particular resource at particular time frame
    public function getLatest($resource_id, $before = null)
    {
        $this->fetchAllLatest($before);
        if(isset($this->metrics[$before][$resource_id])) {
            return $this->metrics[$before][$resource_id];
        }
        //key not found - but this is perfectly ok since some resource has no metrics
        return array();
    }

    //pulls metric records between certain time period
    public function getMetrics($resource_id, $from, $to = null)
    {
        if($to === null) $to = time();
        $sql = "select * from metric where timestamp > $from and timestamp <= $to and resource_id = $resource_id order by timestamp";
        dlog("getting metric records. $sql");
        return $this->db->fetchAll($sql);
    }

    //Take an array of metric records (ordered by resource_id), and group them by resource_id
    //This function simply tries to make it easier to pull set of metric records for a
    //particular resource_id
    private function group($records)
    {
        $metrics[] = array();

        //process for each bucket
        if(count($records) == 0) {
            return; //nothing to group
        }
        $resource_rec = array();
        $cur_rid = $records[0]->resource_id;
        foreach($records as $rec) {
            if($cur_rid == $rec->resource_id) {
                $resource_rec[$rec->metric_id] = $rec;
            } else {
                //store the group
                $metrics[$cur_rid] = $resource_rec;

                //reset bucket
                $resource_rec = array();
                $cur_rid = $rec->resource_id;

                //insert the first record to the new bucket
                $resource_rec[$rec->metric_id] = $rec;
            }
        }

        //store the last group
        $metrics[$cur_rid] = $resource_rec;
        return $metrics;
    }

    public function fetchOneMetric($mid)
    {
        $sql = "select * from metric where dbid = $mid";
        return $this->db->fetchRow($sql); 
    }

    private function fetchAllLatest($before = null)
    {
        if(Zend_Registry::isRegistered("latestmetrics_$before")) {
            //use cached
            $this->metrics[$before] = Zend_Registry::get("latestmetrics_$before");
        } else {
            $before_sql = "";
            if($before !== null) {
                $before_sql = "where timestamp <= $before";
            }
            $sql = "select a.* from 
                        metric a, 
                        (select resource_id, metric_id, max(timestamp) as timestamp 
                            from metric
                            $before_sql
                            group by resource_id, metric_id) l
                    where 
                        a.resource_id = l.resource_id and
                        a.metric_id = l.metric_id and
                        a.timestamp = l.timestamp
                    order by
                        a.resource_id";
            dlog("Fetching latest metrics. $sql");
            $latest_records = $this->db->fetchAll($sql);
            dlog("done.. grouping.");
            $this->metrics[$before] = $this->group($latest_records);
            dlog("all done..");
            Zend_Registry::set("latestmetrics_$before", $this->metrics[$before]);
        }
    }

    //return true for successful insertion
    public function insert($dbid, $resource_id, $metric_id, $status, $timestamp, $detail, $effective_dbid, $effective_timestamp)
    {
        //truncate the detail (too long detail will cause PDO_MYSQL to segfault)
        $detail = substr($detail, 0, 5000);
        $detail = addslashes($detail);

        if($effective_dbid === null) $effective_dbid = "NULL";
        if($effective_timestamp === null) $effective_timestamp = "NULL";

        $sql = "insert into metric (dbid, resource_id, metric_id, status, timestamp, detail, effective_dbid, effective_timestamp) 
            values ($dbid, $resource_id, $metric_id, \"$status\", \"$timestamp\", \"$detail\", $effective_dbid, $effective_timestamp)";
        $this->db->query($sql);
        return true;
    }
}
