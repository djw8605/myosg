<?

class ProbeInfo
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }

        if(!Zend_Registry::isRegistered("probeinfo_init")) {
            //pull critical metric information
            $sql = "select * from ".config()->db_oim_schema.".metric_service";
            $metricinfo_critical_servicetype = $this->db->fetchAll($sql); 

            //group by metric_id
            $this->servicetype = array();
            $this->critical_servicetype = array();
            foreach($metricinfo_critical_servicetype as $info) {
                $mid = $info->metric_id;

                //store it to generic servicetype catalog - for looking up which metric belongs to which service
                if(!isset($this->servicetype[$mid])) {
                    $this->servicetype[$mid] = array();
                }
                $list = $this->servicetype[$mid];
                $list[] = $info->service_id;
                $this->servicetype[$mid] = $list;

                if($info->critical == 1) {
                    //critical services
                    if(!isset($this->critical_servicetype[$mid])) {
                        $this->critical_servicetype[$mid] = array();
                    }
                    $list = $this->critical_servicetype[$mid];
                    $list[] = $info->service_id;
                    $this->critical_servicetype[$mid] = $list;
                }
            }

            //group by critical_servicetype
            $this->critical_probetype = array();
            foreach($metricinfo_critical_servicetype as $info) {
                $sid = $info->service_id;
                if($info->critical == 1) {
                    if(!isset($this->critical_probetype[$sid])) {
                        $this->critical_probetype[$sid] = array();
                    }
                    $list = $this->critical_probetype[$sid];
                    $list[] = $info->metric_id;
                    $this->critical_probetype[$sid] = $list;
                }
            }

            //fetch all metricinfo records
            $sql = "select m.metric_id as id, m.name, m.common_name from ".config()->db_oim_schema.".metric m order by name";
            $this->probe_infos = $this->db->fetchAll($sql);

            //store it to cache
            Zend_Registry::set("probeinfo_critical_servicetype", $this->critical_servicetype);
            Zend_Registry::set("probeinfo_critical_probetype", $this->critical_probetype);
            Zend_Registry::set("probeinfo_probeinfo", $this->probe_infos);
            Zend_Registry::set("probeinfo_init", true);
        } else {
            //use cache
            $this->critical_servicetype = Zend_Registry::get("probeinfo_critical_servicetype");
            $this->critical_probetype = Zend_Registry::get("probeinfo_critical_probetype");
            $this->probe_infos = Zend_Registry::get("probeinfo_probeinfo");
        }

        $this->critical_probes = array();
    }

    //lookup metric id from metric name
    public function lookupID($name)
    {
         if(!isset($this->name2id)) {
            $this->name2id = array();
            foreach($this->probe_infos as $probe) {
                $this->name2id[$probe->name] = $probe->id;
            }
        }
        if(!isset($this->name2id[$name])) return null;
        return $this->name2id[$name]; 
    }

    public function getProbeInfo($id)
    {
        foreach($this->probe_infos as $probe_info)
        {
            if($probe_info->id == $id) return $probe_info;
        }
        return null;
    }

    public function getAllProbeInfo()
    {
        return $this->probe_infos;
    }

    //used to pull service types that this metric is designed for (not necessary critical)
    public function getServices($metric_id)
    {
        if(!isset($this->servicetype[$metric_id])) return array();
        return $this->servicetype[$metric_id];
    }

    public function getCriticalServices($metric_id)
    {
        if(!isset($this->critical_servicetype[$metric_id])) return array();
        return $this->critical_servicetype[$metric_id];
    }
    public function getCriticalProbes($service_id)
    {
        if(!isset($this->critical_probetype[$service_id])) return array();
        return $this->critical_probetype[$service_id];
    }

    public function isCriticalProbe($resource_id, $metric_id)
    {
        if(!isset($this->critical_probes[$resource_id])) {
            $this->critical_probes[$resource_id] = $this->loadCriticalProbes($resource_id); 
        }
        return in_array($metric_id, $this->critical_probes[$resource_id]);
    }
    public function loadCriticalProbes($resource_id)
    {
        //find critical_servicetypes that this resource is associated with.
        $resource_service = new ResourceServiceTypes();
        $service_types = $resource_service->getServiceTypes($resource_id);
        $critical_probes = array();
        foreach($service_types as $service_type) {
            $ps = $this->getCriticalProbes($service_type->service_id);
            foreach($ps as $p) {
                if(!in_array($p, $this->critical_probes)) {
                    $critical_probes[] = $p;
                }
            }
        }
        return $critical_probes;
    }
}
