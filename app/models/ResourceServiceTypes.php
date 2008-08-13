<?

class ResourceServiceTypes
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }

        //load the resource service types tables and register
        if(Zend_Registry::isRegistered("rst_resource_service")) {
            $this->resource_service = Zend_Registry::get("rst_resource_service");
        } else {
            $this->resource_service = $this->loadResourceService();
            Zend_Registry::set("rst_resource_service", $this->resource_service);
        }
    }

    public function getServiceTypes($resource_id)
    {
        $servicetypes = array();
        foreach($this->resource_service as $service) {
            if($service->resource_id == $resource_id) {
                $servicetypes[] = $service;
            }
        }
        return $servicetypes;
    }

    private function loadResourceService()
    {
        $sql = "select r.resource_id, s.service_id, s.name, s.description
                from oim.resource_service r
                left join oim.service s on r.service_id = s.service_id";
// where r.active = 1";
        return $this->db->fetchAll($sql);
    }   
}
