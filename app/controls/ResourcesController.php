<?

class ResourcesController extends ControllerBase
{ 
    public function init() 
    { 
        $this->rememberQuery("resources"); 

        ///////////////////////////////////////////////////////////////////////
        //pull resource requested (filter by servicetype)
        $resource_model = new ResourceByGroupID();
        $where = "";
        if(isset($_REQUEST["servicetype"])) {
            if(trim($_REQUEST["servicetype"]) != "") {
                $servicetype = (int)$_REQUEST["servicetype"];
                $where = "where resource_id in (select resource_id from oim.resource_service where service_id = $servicetype and active = 1 and disable = 0)";
            }
        }
        $this->view->resources_index = $resource_model->getindex($where);

        ///////////////////////////////////////////////////////////////////////
        //pull group requested (filter by gridtype)
        $resource_group_model = new ResourceGroup();
        $where = "";
        if(isset($_REQUEST["gridtype"])) {
            if(trim($_REQUEST["gridtype"]) != "") {
                $gridtype = (int)$_REQUEST["gridtype"];
                $where = "where osg_grid_type_id = $gridtype"; 
            }
        }
        $this->view->resource_groups = $resource_group_model->get($where);

        ///////////////////////////////////////////////////////////////////////
        //pull other things that we'd like to display
        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $servicetype_model = new ServiceTypes();
        $this->view->servicetypes = $servicetype_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $this->view->resource_services = $resourceservice_model->getindex();

        ///////////////////////////////////////////////////////////////////////
        //get resource status cache
        $cache_filename_template = config()->current_resource_status_xml_cache;
        $cache_filename = str_replace("<ResourceID>", "all", $cache_filename_template); 
        $cache_xml = file_get_contents($cache_filename);
        $this->view->xml = $cache_xml;

        $cache = new SimpleXMLElement($cache_xml);
        //index resource status list by resource ID
        $this->view->resource_status = array();
        foreach($cache->ResourceStatus as $resource_status) {
            $id = (int)$resource_status->ResourceID[0];
            $this->view->resource_status[$id] = $resource_status;
        }
        $this->view->page_title = "Resource";
    }
} 
