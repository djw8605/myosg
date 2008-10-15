<?

class ResourcesController extends ControllerBase
{ 
    public function pagename() { return "resources"; }
    public function load() 
    { 

        //the reason why this is so convoluted is because I have to sort by resource group name
        //well.. that's just one of reasons..

        ///////////////////////////////////////////////////////////////////////
        //pull resource requested (filter by servicetype)
        $resource_model = new ResourceByGroupID();
        $params = array();
        if(isset($_REQUEST["servicetype"])) {
            if(trim($_REQUEST["servicetype"]) != "") {
                $servicetype = (int)$_REQUEST["servicetype"];
                $params["servicetype"] = $servicetype;
            }
        }
        $this->view->resources_index = $resource_model->getindex($params);
        var_dump($this->view->resources_index);

        ///////////////////////////////////////////////////////////////////////
        //pull group requested (filter by gridtype)
        $resource_group_model = new ResourceGroup();
        $params = array();
        if(isset($_REQUEST["gridtype"])) {
            if(trim($_REQUEST["gridtype"]) != "") {
                $gridtype = (int)$_REQUEST["gridtype"];
                $params["osg_grid_type_id"] = $gridtype;
            }
        }
        if(isset($_REQUEST["resourcegroup"])) {
            if(trim($_REQUEST["resourcegroup"]) != "") {
                $resourcegroup = (int)$_REQUEST["resourcegroup"];
                $params["resourcegroup"] = $resourcegroup;
            }
        }
        $this->view->resource_groups = $resource_group_model->get($params);

        ///////////////////////////////////////////////////////////////////////
        //pull other things that we'd like to display
        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $servicetype_model = new ServiceTypes();
        $this->view->servicetypes = $servicetype_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $this->view->resource_services = $resourceservice_model->getindex();

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

        ///////////////////////////////////////////////////////////////////////
        //get resource status cache
        $cache_filename_template = config()->current_resource_status_xml_cache;
        $cache_filename = str_replace("<ResourceID>", "all", $cache_filename_template); 
        $cache_xml = file_get_contents($cache_filename);

        $cache = new SimpleXMLElement($cache_xml);
        //index resource status list by resource ID
        $this->view->resource_status = array();
        foreach($cache->ResourceStatus as $resource_status) {
            $id = (int)$resource_status->ResourceID[0];
            $this->view->resource_status[$id] = $resource_status;
        }

        ///////////////////////////////////////////////////////////////////////
        //filter resources based on status filter
        foreach($this->view->resource_groups as $resource_group) {
            if(!isset($this->view->resources_index[$resource_group->id])) {
                continue;
            }
            $list = $this->view->resources_index[$resource_group->id];
            $newlist = array();
            foreach($list as $rec) {
                /*
                $resource_status = $this->view->resource_status[$rec->id];
                if($resource_status->Status[0] == $status) {
                    $newlist[] = $rec;
                }
                */
                //only add recrods if it passes resource filter
                if($this->filterResource($rec)) {
                    $newlist[] = $rec;
                } 
            } 
            $this->view->resources_index[$resource_group->id] = $newlist;
        }
        $this->view->page_title = "Resource Groups";
    }

    private function filterResource($rec) 
    {
        //filter on status
        if(isset($_REQUEST["status"])) {
            if(trim($_REQUEST["status"]) != "") {
                $status = $_REQUEST["status"];
                $resource_status = $this->view->resource_status[$rec->id];
                if($resource_status->Status[0] != $status) {
                    return false;
                }
            }
        } 

        //filter on vo
        if(isset($_REQUEST["vo"])) {
            if(trim($_REQUEST["vo"]) != "") {
                $vogrouped = $this->vos->VOGrouped[0];
                $vo = (int)$_REQUEST["vo"];
                $found = false;
                foreach($vogrouped as $vogroup) {
                    $attr = $vogroup[0]->attributes();
                    if($attr->id[0] == $vo) {
                        $resources = $vogroup->Members[0]->Resource;
                        foreach($resources as $resource) {
                            if($resource->ResourceID[0] == $rec->id) {
                                $found = true;
                                break;
                            }
                        }
                        break;
                    }
                }
                if(!$found) return false;
            }
        }

        return true;
    }
} 
