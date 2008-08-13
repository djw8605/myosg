<?

//This holds a specific probe metric and probe information together.
//CurrentStatus holds a list of MetricAndProbeInfo
class MetricAndProbeInfo
{
    public function __construct($info, $critical_services) {
        $this->info = $info;
        $this->critical_services = $critical_services;
        $this->metric = null;
    }

    public function setMetric($metric) {
        $this->metric = $metric;
    }

    ///////////////////////////////////////////////////////////////////////////
    //probe information
    public function getName()
    {   
        return $this->info->name;
    }
    public function getCommonName()
    {   
        return $this->info->common_name;
    }

    ///////////////////////////////////////////////////////////////////////////
    //metric information
    public function hasData()
    {
        return $this->metric === null ? false : true;
    }

    //returns probe_id (not metric ID)
    public function getProbeID()
    {   
        return $this->info->id;
    }
    public function getMetricID()
    {
        if(!$this->hasData()) return null;
        return $this->metric->dbid;
    }

    public function getStatus()
    {   
        if(!$this->hasData()) return "NA";
        return $this->metric->status;
    }
    public function isOld()
    {
        return ((time() - $this->metric->timestamp) > config()->metric_considered_old);
    }
    public function getUNIXTimestamp()
    {
        if(!$this->hasData()) return 0;
        return $this->metric->timestamp;
    }
    public function getDetailsData()
    {
        if(!$this->hasData()) return "NA";
        return $this->metric->detail;
    }
    public function isCriticalFor($service_id)
    {
        return in_array($service_id, $this->critical_services);
    }
    public function hasNoServiceTypeAssociation()
    {
        return count($this->critical_services) == 0;
    }
}


