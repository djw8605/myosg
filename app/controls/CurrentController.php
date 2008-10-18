<?

class CurrentController extends ControllerBase
{
    public function pagename() { return "current"; }
    public function load()
    {
        $params = array();
        $dirty_resource_id = $_REQUEST["resource_id"];
        $resource_id = (int)$dirty_resource_id;
        $params["resource_id"] = $resource_id;
        $resource_model = new Resource();
        $resources = $resource_model->get($params);
        $resource = $resources[0];

        $this->view->resource_id = $resource->id;
        $this->view->resource_name = $resource->name;
        $this->view->resource_fqdn = $resource->fqdn;

        //load cache
        $cache_filename_template = config()->current_resource_status_xml_cache;
        $cache_filename = str_replace("<ResourceID>", $resource_id, $cache_filename_template); 
        if(file_exists($cache_filename)) {
            $cache_xml = file_get_contents($cache_filename);
            $this->view->xml = $cache_xml;

            $cache = new SimpleXMLElement($cache_xml);
            $this->view->timestamp = (int)$cache->Timestamp[0];
            $this->view->status = $cache->Status[0];
            $this->view->note = $cache->Note[0];
            $this->view->services = $cache->Services[0];
        }

        $this->view->page_title = "Current Status for ".$resource->name;
    }

}
