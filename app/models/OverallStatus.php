<?

//calculate overall status based on different metrics for a specific resource
class OverallStatus
{
    public function __construct($resource_id)
    {  
        $this->resource_id = $resource_id;
        $this->infos = new ProbeInfo();
    }

    private function isCriticalProbe($metric_id)
    {
        return $this->infos->isCriticalProbe($this->resource_id, $metric_id);
    }
    private function isNonCriticalProbe($metric_id)
    {
        return $this->infos->isNonCriticalProbe($this->resource_id, $metric_id);
    }


    public function getLastInfo()
    {
        $info = db()->fetchRow("select * from overall_status o where timestamp = (select max(timestamp) from overall_status where resource_id = ".$this->resource_id.")");
        //dlog("pulling latest overall_Status info for ".$this->resource_id);
        return $info;
    }

    public function insertNewOverallStatus($overall_status, $timestamp, $detail, $resource_id, $responsible_metric_id)
    {
        if($responsible_metric_id === null) $responsible_metric_id = "NULL";
        $sql = "insert into overall_status (overall_status, timestamp, detail, resource_id, responsible_metric_id) values (\"$overall_status\", $timestamp, \"$detail\", $resource_id, $responsible_metric_id)";
        try {
            db()->query($sql);
        } catch(Exception $e) {
            dlog("Caught Exception with following query: ".$sql);
            return false;
        }
        return true; 
    }

/*
    //could return null
    public function getOldestProbeTimestamp()
    {
        return $this->oldest_criticalprobe_timestamp;
    }
*/

    public function fetchStatusChanges($start_time, $end_time)
    {
        return db()->fetchAll("select * from overall_status where resource_id = ".
            $this->resource_id." and timestamp >= coalesce((select max(timestamp) from overall_status where resource_id = ".
            $this->resource_id." and timestamp < $start_time), 0) and timestamp <= $end_time order by timestamp;"); 
    }

    public function calculateStatus($metrics, $calctime = null)
    {
        //calctime is a time when OverallStatus is to be calculated against given set of metrics.
        //if not set, use current time
        if($calctime === null) {
            $calctime = time();
        }
        $calctime_str = date(config()->date_format_full, $calctime);

        //various counter to use for making status decisions
        $critical_ok = 0;
        $critical_warning = 0;
        $critical_critical = 0;
        $critical_unknown = 0;
        $critical_na = 0;
        
        $old_critical = 0;
 
        $noncritical_ok = 0;
        $noncritical_warning = 0;
        $noncritical_critical = 0;
        $noncritical_unknown = 0;
        $noncritical_na = 0;

        //counter for some statistics
        $critical_total = 0;

        $this->oldest_criticalprobe_timestamp = null;
        $this->oldest_criticalmetricdata_id = null;
        $this->expired = false;
        $this->nad = false;

        $note = "";

        //count occurence of each status in the metric set - iterate based on the probe info
        foreach($this->infos->getAllProbeInfo() as $info) {
            $critical = $this->isCriticalProbe($info->id);
            $non_critical = $this->isNonCriticalProbe($info->id);

            //ignore irelevant metrics (aka 'others' in old context)
            if(!$critical and !$non_critical) {
                continue;
            }

            //count critical metrics
            if($critical) {
                $critical_total++;
            }

            if(!isset($metrics[$info->id])) {
                //current set doesn't have metric this probe
                if($critical) $critical_na++;
                else $noncritical_na++;
            } else {


                //current has metric for this probe
                $metric = $metrics[$info->id];
                $status = $metric->status;
                $timestamp = $metric->timestamp;
                $mid = $metric->dbid;

                //if status is unknown, check for effective metric
                if($status == "UNKNOWN") {
                    if($metric->effective_dbid !== null) {
                        //use status of effective metric instead
                        $sql = "select * from metric where dbid = ".$metric->effective_dbid;
                        $metric = db()->fetchRow($sql);
                        $status = $metric->status;
                        $timestamp = $metric->timestamp;
                    }
                }

                //check oldest timestamp for critical probes
                if($this->isCriticalProbe($info->id)) {
                    if($this->oldest_criticalprobe_timestamp == null or $this->oldest_criticalprobe_timestamp > $timestamp) {
                        $this->oldest_criticalprobe_timestamp = $timestamp;
                        $this->oldest_criticalmetricdata_id = $mid;
                    }
                }

                //old critical?
                $expiration_time = $timestamp + config()->metric_considered_old;
                if($calctime > $expiration_time) {
                    if($this->isCriticalProbe($info->id)) {
                        $old_critical++;
                        continue;
                    }
                }

                //count as usual
                if($status == "CRITICAL") {
                    if($this->isCriticalProbe($info->id)) {
                        $critical_critical++;
                        //$note .= "$mid is critical.";
                    } else {
                        $noncritical_critical++;
                    }
                }
                if($status == "WARNING") {
                    if($this->isCriticalProbe($info->id)) {
                        $critical_warning++;
                    } else {
                        $noncritical_warning++;
                    }
                }
                if($status == "OK") {
                    if($this->isCriticalProbe($info->id)) {
                        $critical_ok++;
                    } else {
                        $noncritical_ok++;
                    }
                }
                if($status == "UNKNOWN") {
                    if($this->isCriticalProbe($info->id)) {
                        $critical_unknown++;
                    } else {
                        $noncritical_unknown++;
                    }
                }
            }
        }
/*
        
        //store it to stats counts
        $this->statuscounts = array(
            "WARNING" => $critical_warning + $noncritical_warning,
            "CRITICAL" => $critical_critical + $noncritical_critical,
            "UNKNOWN" => $critical_unknown + $noncritical_unknown + $critical_na + $noncritical_na,
            "OK" => $critical_ok + $noncritical_ok
        );

        $critical_total = $critical_warning + $critical_critical + $critical_unknown + $critical_ok + $critical_na;
*/

        //check for expiration (this must takes precedance, or processnew algorithm will not work
        if($this->oldest_criticalprobe_timestamp !== null) {
            $expiration_time = $this->oldest_criticalprobe_timestamp + config()->metric_considered_old;
            if($calctime > $expiration_time) {
                $this->expired = true;
                $this->expired_time = $expiration_time;
                $this->expired_responsible_id = $this->oldest_criticalmetricdata_id;

                $this->overall_status = "UNKNOWN";
                $this->overall_detail = "$old_critical of $critical_total critical metrics is too old. $note";
                return;
            }
        }

        //now figure out the overal status
        if($critical_critical > 0) {
            $this->overall_status = "CRITICAL";
            $this->overall_detail = "$critical_critical of $critical_total critical metrics reported CRITICAL status. $note";
            return;
        }
        if($critical_warning > 0) {
            $this->overall_status = "WARNING";
            $this->overall_detail = "$critical_warning of $critical_total critical metrics reported WARNING status. $note";
            return;
        }
        if($critical_unknown > 0) {
            $this->overall_status = "UNKNOWN";
            $this->overall_detail = "$critical_unknown of $critical_total critical metrics reported UNKNOWN status. $note";
            return;
        }
        if($critical_na > 0) {
            $this->overall_status = "UNKNOWN";
            $this->overall_detail = "$critical_na of $critical_total critical metrics is not available. $note";
            $this->nad = true;
            return;
        }

        $this->overall_status = "OK";

        if($noncritical_critical > 0) {
            $this->overall_detail = "No issue reported for critical metrics, however, $noncritical_critical noncritical probes reported CRITICAL status.";
            return;
        }
        if($noncritical_warning > 0) {
            $this->overall_detail = "No issue reported for critical metrics, however, $noncritical_warning noncritical probes reported WARNING status.";
            return;
        }
        if($noncritical_unknown > 0) {
            $this->overall_detail = "No issue reported for critical metrics, however, $noncritical_unknown noncritical probes reported UNKNOWN status.";
            return;
        }

        $this->overall_detail = "No issue reported.";
    }

    public function getOverallStatus()
    {   
        return $this->overall_status;
    }
    public function getOverallDetail()
    {   
        return $this->overall_detail;
    }
/*
    public function getStatusCounts()
    {   
        return $this->statuscounts;
    }
*/
    public function isNA()
    {
        return $this->nad;
    }
    public function isExpired()
    {
        return $this->expired;
    }
    public function getExpiredResponsibleID()
    {
        if($this->expired) {
            return $this->expired_responsible_id;
        }
        throw new Exception("getExpiredResponsibleID called for not expired status");
    }
    public function getExpiredTimestamp()
    {
        if($this->expired) {
            return $this->expired_time;
        }
        throw new Exception("getExpiredTimestamp called for not expired status");
    }
}
