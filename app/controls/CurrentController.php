<?

class CurrentController extends ControllerBase
{
    public function init()
    {
        $this->rememberQuery("current");

        $dirty_resource_id = $_REQUEST["resource_id"];
        $resource_id = (int)$dirty_resource_id;

        //get resource info
        $resource_model = new Resource();
        $resources = $resource_model->get("where resource_id = $resource_id");
        $resource = $resources[0];

        $this->view->resource_id = $resource->id;
        $this->view->resource_name = $resource->name;
        $this->view->resource_fqdn = $resource->fqdn;

        //load cache
        $cache_filename_template = config()->current_resource_status_xml_cache;
        $cache_filename = str_replace("<ResourceID>", $resource_id, $cache_filename_template); 
        $cache_xml = file_get_contents($cache_filename);
        $this->view->xml = $cache_xml;

        $cache = new SimpleXMLElement($cache_xml);
        $this->view->timestamp = (int)$cache->Timestamp[0];
        $this->view->status = $cache->Status[0];
        $this->view->note = $cache->Note[0];
        $this->view->services = $cache->Services[0];
    
        $this->view->page_title = "Current Status for ".$resource->name;
    }

}
