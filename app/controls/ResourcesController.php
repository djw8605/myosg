<?

class ResourcesController extends ControllerBase
{
    public function breads() { return array(); }
    public static function default_title() { return "Resource Groups"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->vocache = null;

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

        ///////////////////////////////////////////////////////////////////////
        //pull group requested (filter by gridtype)
        $resource_group_model = new ResourceGroup();
        $params = array();
        if(isset($_REQUEST["gridtype"])) {
            if(trim($_REQUEST["gridtype"]) != "") {
                $gridtype = (int)$_REQUEST["gridtype"];
                $params["osg_grid_type_id"] = $gridtype;
            }
        } else {
            //if gridtype is not set, then default it to 1. be sure to let (all) to be selected still
            $_REQUEST["gridtype"] = "1";
            $params["osg_grid_type_id"] = 1;
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
                //only add recrods if it passes resource filter
                if($this->filterResource($rec)) {
                    $newlist[] = $rec;
                }
            }
            $this->view->resources_index[$resource_group->id] = $newlist;
        }

        ///////////////////////////////////////////////////////////////////////
        //load vo supported
        if(isset($_REQUEST["detail_vomembers"])) {
            if($_REQUEST["detail_vomembers"] == "true") {
                $resourcegrouped = $this->getResourceGrouped();
                $this->view->vos_supported = array();
                foreach($resourcegrouped as $resource) {
                    $attr = $resource->attributes();
                    $id = (int)$attr->id[0];
                    $this->view->vos_supported[$id] = $resource->Members[0];
                }
            }
        }

        ///////////////////////////////////////////////////////////////////////
        //load vo ownership 
        if(isset($_REQUEST["detail_voowner"])) {
            if($_REQUEST["detail_voowner"] == "true") {
                $model = new ResourceOwnership();
                $this->view->resource_ownerships = $model->getindex();
            }
        }

        $this->setpagetitle(ResourcesController::default_title());
    }

    private function filterResource($rec) 
    {
        //filter on status
        if(isset($_REQUEST["status"])) {
            if(trim($_REQUEST["status"]) != "") {
                $status = $_REQUEST["status"];
                $resource_status = $this->view->resource_status[$rec->id];
                if($resource_status === null or $resource_status->Status[0] != $status) {
                    return false;
                }
            }
        }

        //filter on vo
        if(isset($_REQUEST["vo"])) {
            if(trim($_REQUEST["vo"]) != "") {
                $vogrouped = $this->getVOGrouped();
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

        //filter on voowner
        if(isset($_REQUEST["voowner"])) {
            if(trim($_REQUEST["voowner"]) != "") {
                $vo_id = $_REQUEST["voowner"];
                $owned_resources = $this->getVOOwnedResources($vo_id);
                $found = false;
                foreach($owned_resources as $owned_resource) {
                    if($rec->id == $owned_resource->resource_id) {
                        $found = true;
                        break;
                    }
                }
                if(!$found) return false;
            }
        }

        return true;
    }

    private function getVOGrouped()
    {
        if($this->vocache === null) {
            $this->loadvocache();
        }
        return $this->vocache->VOGrouped[0];
    }

    private function getResourceGrouped()
    {
        if($this->vocache === null) {
            $this->loadvocache();
        }
        return $this->vocache->ResourceGrouped[0];
    }

    private function loadvocache()
    {
        $cache_filename = config()->vomatrix_xml_cache;
        $cache_xml = file_get_contents($cache_filename);
        $this->view->xml = $cache_xml;
        $this->vocache = new SimpleXMLElement($cache_xml);
    }

    //$vo_id will be only used once when static variale is initialized.
    //once it's cached, $vo_id will be ignored..
    private function getVOOwnedResources($vo_id)
    {
        static $resources = null;
        if($resources === null) {
            $model = new VOOwnedResources();
            $params = array();
            $params["vo_id"] = $vo_id;
            $resources = $model->get($params);
        }
        return $resources;
    }
}
