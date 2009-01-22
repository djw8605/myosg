<?

interface wizard_controller
{
    public function __construct($view, $resource_ids);
    public function pagetitle();
}

class WizardController extends ControllerBase
{
    public function breads() { return array("rsv"); }
    public static function default_title() { return "RSV Information Wizard"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->view->datasource = "wizard/default.phtml";
        $this->setpagetitle(self::default_title());

        $resources = null;
        if(isset($_REQUEST["datasource"])) {
            $resource_ids = $this->process_resourcelist();
            $this->select_datasource($resource_ids);
        }
    }

    private function select_datasource($resource_ids)
    {
        $contoller = null;
        $datasource = @$_REQUEST["datasource"];
        switch($datasource) {
        case "summary":
        case "current_status":
            require_once("wizard_$datasource.php");
            $class_name = "wizard_$datasource";
            $controller = new $class_name($this->view, $resource_ids);
            $this->view->datasource = "wizard_".$datasource."/html.phtml";
            $this->view->resource_counts = count($resource_ids);
            $this->setpagetitle($controller->pagetitle());
            break;
        }
    }

    //from user query, find the list of resources to display information
    private function process_resourcelist()
    {
        $resource_ids = array();

        if(isset($_REQUEST["all_resources"])) {
            $model = new Resource();
            $resources = $model->get();
            foreach($resources as $resource) {
                $resource_ids[] = (int)$resource->id;
            }
        } else {
            foreach($_REQUEST as $key=>$value) {
                if(isset($_REQUEST["sc"])) {
                    if(preg_match("/^sc_(?<id>\d+)/", $key, $matches)) {
                        $this->process_resourcelist_addsc($resource_ids, $matches["id"]);
                    }
                }
                if(isset($_REQUEST["site"])) {
                    if(preg_match("/^site_(?<id>\d+)/", $key, $matches)) {
                        $this->process_resourcelist_addsite($resource_ids, $matches["id"]);
                    }
                }
                if(isset($_REQUEST["rg"])) {
                    if(preg_match("/^rg_(?<id>\d+)/", $key, $matches)) {
                        $this->process_resourcelist_addrg($resource_ids, $matches["id"]);
                    }
                }
                if(isset($_REQUEST["r"])) {
                    if(preg_match("/^r_(?<id>\d+)/", $key, $matches)) {
                        $this->process_resourcelist_addr($resource_ids, $matches["id"]);
                    }
                }
            }
        }
        //filter the resource list based on user query
        $resource_ids = $this->process_resource_filter($resource_ids);

        return $resource_ids;
    }

    private function process_resourcelist_addsc(&$resource_ids, $sc_id) 
    {
        $site_ids = array();

        //load all site id under the requested sc
        $model = new Site();
        $sites = $model->get(array("sc_id"=>$sc_id));
        foreach($sites as $site) {
            if(!in_array($site->site_id, $site_ids)) {
                $site_ids[] = $site->site_id;
            }
        }

        foreach($site_ids as $site_id) {
            $this->process_resourcelist_addsite($resource_ids, $site_id);
        }
    }

    private function process_resourcelist_addsite(&$resource_ids, $site_id)
    {
        $rg_ids = array();

        //load all resource groups under the requested site_id
        $model = new ResourceGroup();
        $rgs = $model->get(array("site_id"=>$site_id));
        foreach($rgs as $rg) {
            if(!in_array($rg->resource_group_id, $rg_ids)) {
                $rg_ids[] = $rg->resource_group_id;
            }
        }

        foreach($rg_ids as $rg_id) {
            $this->process_resourcelist_addrg($resource_ids, $rg_id);
        }
    }

    private function process_resourcelist_addrg(&$resource_ids, $rg_id)
    {
        //load all resource under the requested resource_group_id
        $model = new ResourceByGroupID();
        $rs = $model->get(array("resource_group_id"=>$rg_id));
        foreach($rs as $r) {
            if(!in_array($r->resource_id, $resource_ids)) {
                $resource_ids[] = (int)$r->resource_id;
            }
        }
    }
    private function process_resourcelist_addr(&$resource_ids, $resource_id)
    {
        if(!in_array($resource_id, $resource_ids)) {
            $resource_ids[] = (int)$resource_id;
        }
    }

    private function process_resource_filter($resources)
    {
        //setup filter
        if(isset($_REQUEST["gt"])) {
            $keep = $this->process_resource_filter_gt();
            $resources = array_intersect($keep, $resources);
        }
        if(isset($_REQUEST["service"])) {
            $keep = $this->process_resource_filter_service();
            $resources = array_intersect($keep, $resources);
        }
        if(isset($_REQUEST["vosup"])) {
            $keep = $this->process_resource_filter_vosup();
            $resources = array_intersect($keep, $resources);
        }
        if(isset($_REQUEST["voown"])) {
            $keep = $this->process_resource_filter_voown();
            $resources = array_intersect($keep, $resources);
        }
        if(isset($_REQUEST["status"])) {
            $keep = $this->process_resource_filter_status();
            $resources = array_intersect($keep, $resources);
        }
        if(isset($_REQUEST["has_status"])) {
            $keep = $this->process_resource_filter_hasstatus();
            $resources = array_intersect($keep, $resources);
        }

        return $resources;
    }

    private function process_resource_filter_service()
    {
        $resources_to_keep = array();
        $model = new ServiceTypes();
        $list = $model->get(array("service_group_id"=>1));
        foreach($list as $item) {
            if(isset($_REQUEST["service_".$item->service_id])) {
                $model = new ResourceServices();
                $rs = $model->get(array("service_id"=>$item->service_id));
                foreach($rs as $r) {
                    if(!isset($resources_to_keep[$r->resource_id])) {
                        $resources_to_keep[] = $r->resource_id;
                    }
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
            $downtime = $downtimes[(int)$rid];
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

        foreach($list as $item) {
            if(isset($_REQUEST["voown_".$item->vo_id])) {
                $model = new VOOwnedResources();
                $rs = $model->get(array("vo_id"=>$item->vo_id));
                foreach($rs as $r) {
                    if(!in_array($r->resource_id, $resources_to_keep)) {
                        $resources_to_keep[] = $r->resource_id;
                    }
                }
            }
        }
        return $resources_to_keep;
    }

    private function process_resource_filter_gt()
    { 
        $resources_to_keep = array();
        $model = new GridTypes();
        $list = $model->get();
        foreach($list as $item) {
            if(isset($_REQUEST["gt_".$item->grid_type_id])) {
                //pull resource groups
                $model = new ResourceGroup();
                $rgs = $model->get(array("osg_grid_type_id"=>$item->grid_type_id));
                foreach($rgs as $rg) {
                    //pull resources
                    $model = new ResourceByGroupID();
                    $rs = $model->get(array("resource_group_id"=>$rg->resource_group_id));
                    foreach($rs as $r) {
                        if(!in_array($r->resource_id, $resources_to_keep)) {
                            $resources_to_keep[] = $r->resource_id;
                        }
                    }
                }
            }
        }
        return $resources_to_keep;
    }
}
