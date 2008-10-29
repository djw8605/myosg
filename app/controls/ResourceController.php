<?

class ResourceController extends ControllerBase
{ 
    public function pagename() { return "resource"; }

    public function load()
    {
        $params = array();
        $dirty_resource_id = $_REQUEST["resource_id"];
        $resource_id = (int)$dirty_resource_id;
        $params["resource_id"] = $resource_id;
        $resource_model = new Resource();
        $resources = $resource_model->get($params);
        $resource = $resources[0];
        $this->view->resource = $resource;

        ///////////////////////////////////////////////////////////////////////
        //load vo cache
        $cache_filename = config()->vomatrix_xml_cache;
        $cache_xml = file_get_contents($cache_filename);
        $this->view->xml = $cache_xml;
        $this->vos = new SimpleXMLElement($cache_xml);
        $this->view->vos = array();
        foreach($this->vos->ResourceGrouped[0] as $resource_vo) {
            $attributes = $resource_vo->attributes();
            $this->view->vos[(int)$attributes->id[0]] = $resource_vo->Members[0]->VO; 
        }

        $servicetype_model = new ServiceTypes();
        $this->view->servicetypes = $servicetype_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $this->view->resource_services = $resourceservice_model->getindex();

        $this->view->page_title = "Resource: " .$resource->name;
    }
} 
