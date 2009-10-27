<?

class RgController extends ControllerBase
{
    public static function default_title() { return "OSG Resource Group"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle($this->default_title());
        $this->selectmenu("rg");

        if(isset($_REQUEST["datasource"])) {
            $this->rgs = $this->process_rglist();
            if(count($this->rgs) == 0) {
                $this->view->info = "No resource group or resource matches your current criteria. Please adjust your criteria in order to display any data.";
            }
        }
        $this->load_daterangequery();
    }

    public function xmlAction()
    {
        //find if xml.phtml exists for this control
        $name = $this->getRequest()->getControllerName();
        $path = $this->view->getScriptPath("").$name."/xml.phtml";
        if(file_exists($path)) {
            //if so, then we support xml
            parent::xmlAction();
        } else {
            $this->setpagetitle($this->default_title());
            $this->render("noxml", null, true);
        }
    }

    public function csvAction()
    {
        //find if xml.phtml exists for this control
        $name = $this->getRequest()->getControllerName();
        $path = $this->view->getScriptPath("").$name."/xml.phtml";
        if(file_exists($path)) {
            //if so, then we support xml
            parent::csvAction();
        } else {
            $this->setpagetitle($this->default_title());
            $this->render("nocsv", null, true);
        }
    }

    //from user query, find the list of resources gropu to display information
    protected function process_rglist()
    {
        $rg_ids = array();

        if(isset($_REQUEST["all_resources"])) {
            $model = new ResourceGroup();
            $rgs = $model->get();
            foreach($rgs as $rg) {
                $rg_ids[] = (int)$rg->id;
            }
        } else {
            foreach($_REQUEST as $key=>$value) {
                if(isset($_REQUEST["sc"])) {
                    if(preg_match("/^sc_(\d+)/", $key, $matches)) {
                        $this->process_rglist_addsc($rg_ids, $matches[1]);
                    }
                }
                if(isset($_REQUEST["facility"])) {
                    if(preg_match("/^facility_(\d+)/", $key, $matches)) {
                        $this->process_rglist_addfacility($rg_ids, $matches[1]);
                    }
                }
                if(isset($_REQUEST["site"])) {
                    if(preg_match("/^site_(\d+)/", $key, $matches)) {
                        $this->process_rglist_addsite($rg_ids, $matches[1]);
                    }
                }
                if(isset($_REQUEST["rg"])) {
                    if(preg_match("/^rg_(\d+)/", $key, $matches)) {
                        $this->process_rglist_addrg($rg_ids, $matches[1]);
                    }
                }
            }
        }

        //apply filter for resource group
        $rg_ids = $this->process_rg_filter($rg_ids);

        //pull all resources and apply resource filter
        $model = new Resource();
        $resources = $model->getindex();
        $resource_ids = $this->process_resource_filter(array_keys($resources));

        $rgs = array();
        //place resource info under resource group array
        foreach($rg_ids as $rg_id) {
            foreach($resource_ids as $resource_id) {
                $resource = $resources[$resource_id][0];
                if($resource->resource_group_id == $rg_id) {
                    if(!isset($rgs[$rg_id])) {
                        $rgs[$rg_id] = array();
                    }
                    $rgs[$rg_id][$resource_id] = $resource;
                }
            }
        }

        return $rgs;
    }

    private function process_rglist_addfacility(&$rg_ids, $facility_id) 
    {
        $site_ids = array();

        //load all site id under the requested sc
        $model = new Site();
        $sites = $model->get(array("facility_id"=>$facility_id));
        foreach($sites as $site) {
            if(!in_array($site->id, $site_ids)) {
                $site_ids[] = $site->id;
            }
        }

        foreach($site_ids as $site_id) {
            $this->process_rglist_addsite($rg_ids, $site_id);
        }
    }

    private function process_rglist_addsc(&$rg_ids, $sc_id) 
    {
        $site_ids = array();

        //load all site id under the requested sc
        $model = new Site();
        $sites = $model->get(array("sc_id"=>$sc_id));
        foreach($sites as $site) {
            if(!in_array($site->id, $site_ids)) {
                $site_ids[] = $site->id;
            }
        }
        foreach($site_ids as $site_id) {
            $this->process_rglist_addsite($rg_ids, $site_id);
        }
    }

    private function process_rglist_addsite(&$rg_ids, $site_id)
    {
        //load all resource groups under the requested site_id
        $model = new ResourceGroup();
        $rgs = $model->get(array("site_id"=>$site_id));
        foreach($rgs as $rg) {
            $this->process_rglist_addrg($rg_ids, $rg->id);
        }
    }

    private function process_rglist_addrg(&$rg_ids, $rg_id)
    {
        if(!in_array($rg_id, $rg_ids)) {
            $rg_ids[] = (int)$rg_id;
        }
    }
    private function process_rg_filter($rgs)
    {
        if(isset($_REQUEST["gridtype"])) {
            $keep = $this->process_rg_filter_gt();
            $rgs = array_intersect($rgs, $keep);
        }
        if(isset($_REQUEST["gipstatus"])) {
            $keep = $this->process_rg_filter_gipstatus();
            $rgs = array_intersect($rgs, $keep);
        }
        return $rgs;
    }
    private function process_resource_filter($resources)
    {
        if(isset($_REQUEST["service"])) {
            $keep = $this->process_resource_filter_service();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["service_central"])) {
            $keep = $this->process_resource_filter_service_central();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["service_hidden"])) {
            $keep = $this->process_resource_filter_service_hidden();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["service_hidden"])) {
            $keep = $this->process_resource_filter_service_hidden();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["vosup"])) {
            $keep = $this->process_resource_filter_vosup();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["voown"])) {
            $keep = $this->process_resource_filter_voown();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["status"])) {
            $keep = $this->process_resource_filter_status();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["has_status"])) {
            $keep = $this->process_resource_filter_hasstatus();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["active"])) {
            $keep = $this->process_resource_filter_active();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["disable"])) {
            $keep = $this->process_resource_filter_disable();
            $resources = array_intersect($resources, $keep);
        }
        if(isset($_REQUEST["has_wlcg"])) {
            $keep = $this->process_resource_filter_haswlcg();
            $resources = array_intersect($resources, $keep);
        }
        return $resources;
    }

    private function process_resource_filter_service()
    {
        $resources_to_keep = array();
        $model = new Service();
        $list = $model->get();
        foreach($list as $item) {
            if(isset($_REQUEST["service_".$item->id])) {
                $model = new ResourceServices();
                $rs = $model->get(array("service_id"=>$item->id));
                foreach($rs as $r) {
                    /*
                    if($_REQUEST["service_central"]) {
                        if(!isset($_REQUEST["service_central_".$r->central])) {
                            continue;
                        } 
                    }
                    if($_REQUEST["service_hidden"]) {
                        if(!isset($_REQUEST["service_hidden".$r->hidden])) {
                            continue;
                        } 
                    }
                    */
                    if(isset($_REQUEST["service_hidden"])) {
                        if($r->hidden != 1) continue;
                    }
                    if(!in_array($r->resource_id, $resources_to_keep)) {
                        $resources_to_keep[] = $r->resource_id;
                    }
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_service_central()
    {
        $resources_to_keep = array();
        $model = new ResourceServices();
        $rs = $model->get();
        foreach($rs as $r) {
            if($_REQUEST["service_central_value"] == $r->central) {
                if(!in_array($r->resource_id, $resources_to_keep)) {
                    $resources_to_keep[] = $r->resource_id;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_service_hidden()
    {
        $resources_to_keep = array();
        $model = new ResourceServices();
        $rs = $model->get();
        foreach($rs as $r) {
            if($_REQUEST["service_hidden_value"] == $r->hidden) {
                if(!in_array($r->resource_id, $resources_to_keep)) {
                    $resources_to_keep[] = $r->resource_id;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_vosup()
    {
        $resources_to_keep = array();
        $cache_filename = config()->vomatrix_xml_cache;
        $cache_xml = file_get_contents($cache_filename);
        $vos = new SimpleXMLElement($cache_xml);
        $vogrouped = $vos->VOGrouped[0];

        //find supported vos
        foreach($vogrouped as $vo) {
            $attr = $vo->attributes();
            if(isset($_REQUEST["vosup_".$attr->id])) {
                $rs = $vo->Members[0];
                foreach($rs as $r) {
                    if(!in_array((string)$r->ResourceID, $resources_to_keep)) {
                        $resources_to_keep[] = (string)$r->ResourceID;
                    }
                }
            }
        }

        return $resources_to_keep;
    }

    private function process_resource_filter_hasstatus()
    {
        $resources_to_keep = array();
        $model = new LatestResourceStatus();
        $resource_status = $model->getgroupby("resource_id");
        $model = new Resource();
        $resources = $model->getindex();
        foreach($resources as $rid=>$r) {
            if(isset($resource_status[$rid])) {
                if(!in_array($rid, $resources_to_keep)) {
                    $resources_to_keep[] = (string)$rid;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_haswlcg()
    {
        $resources_to_keep = array();
        $model = new ResourceWLCG();
        $wlcgs = $model->getindex();
        $model = new Resource();
        $resources = $model->getindex();
        foreach($resources as $rid=>$r) {
            if(isset($wlcgs[$rid][0]) and ( 
                    ($wlcgs[$rid][0]->interop_bdii == 1)
                        or
                    ($wlcgs[$rid][0]->interop_monitoring == 1)
                        or
                    ($wlcgs[$rid][0]->interop_accounting == 1)
                )
            ) {
                if(!in_array($rid, $resources_to_keep)) {
                    $resources_to_keep[] = (string)$rid;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_active()
    {
        $resources_to_keep = array();
        $model = new Resource();
        $resources = $model->getindex();
        $active_value = $_REQUEST["active_value"];
        foreach($resources as $rid=>$r) {
            if($r[0]->active == $active_value) {
                if(!in_array($rid, $resources_to_keep)) {
                    $resources_to_keep[] = (string)$rid;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_disable()
    {
        $resources_to_keep = array();
        $model = new Resource();
        $resources = $model->getindex();
        $disable_value = $_REQUEST["disable_value"];
        foreach($resources as $rid=>$r) {
            if($r[0]->disable == $disable_value) {
                if(!in_array($rid, $resources_to_keep)) {
                    $resources_to_keep[] = (string)$rid;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_status()
    {
        $resources_to_keep = array();
        $model = new LatestResourceStatus();
        $resource_status = $model->getgroupby("resource_id");

        //load downtime
        $downtime_model = new Downtime();
        $params = array("start_time"=>time(), "end_time"=>time());
        $downtimes = $downtime_model->getindex($params);

        $model = new Resource();
        $resources = $model->getindex();
        foreach($resources as $rid=>$r) {
            if(!isset($resource_status[$rid])) {
                //if status not found, then treat it as UNKNOWN
                $status_id = 4;//unknown
            } else {
                $rs = $resource_status[$rid];
                $status_id = $rs[0]->status_id;
            }

            //consider status to be down
            $downtime = @$downtimes[(int)$rid];
            if($downtime !== null) {
                $status_id = 99;
            }

            if(isset($_REQUEST["status_".$status_id])) {
                if(!in_array($rid, $resources_to_keep)) {
                    $resources_to_keep[] = $rid;
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_voown()
    {
        $resources_to_keep = array();
        $model = new VirtualOrganization();
        $list = $model->get();

        foreach($list as $vo_id=>$item) {
            if(isset($_REQUEST["voown_".$vo_id])) {
                $model = new VOOwnedResources();
                $rs = $model->get(array("vo_id"=>$vo_id));
                foreach($rs as $r) {
                    if(!in_array($r->resource_id, $resources_to_keep)) {
                        $resources_to_keep[] = $r->resource_id;
                    }
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_rg_filter_gipstatus()
    {
        $rgs_to_keep = array();

        $model = new LDIF();
        $summary = $model->getValidationSummary();

        $model = new ResourceGroup();
        $rgs = $model->getindex();
        foreach($rgs as $rg_id=>$rg) {
            //search for the gip status for this resource group
            $found = false;
            $overallstatus = "UNKNOWN"; //if not found, treat it as unknown
            foreach($summary->Resource as $gip) {
                if($rg[0]->name == (string)$gip->Name) {
                    $overallstatus = (string)$gip->OverAllStatus;
                    break;
                }
            }
            //has user selected this resource status?
            if(isset($_REQUEST["gipstatus_".$overallstatus])) {
                if(!in_array($rg_id, $rgs_to_keep)) {
                    $rgs_to_keep[] = $rg_id;
                }
            }
        }
        return $rgs_to_keep;
    }

    private function process_rg_filter_gt()
    {
        $rg_to_keep = array();
        $model = new GridTypes();
        $list = $model->get();
        foreach($list as $item) {
            if(isset($_REQUEST["gridtype_".$item->id])) {
                //pull resource groups
                $model = new ResourceGroup();
                $rgs = $model->get(array("osg_grid_type_id"=>$item->id));
                foreach($rgs as $rg) {
                    if(!in_array($rg->id, $rg_to_keep)) {
                        $rg_to_keep[] = $rg->id;
                    }
                }
            }
        }
        return $rg_to_keep;
    }
}
