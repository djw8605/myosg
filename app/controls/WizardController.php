<?

class WizardController extends ControllerBase
{
    public function breads() { return array("rsv"); }
    public static function default_title() { return "OSG Resource"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle($this->default_title());
        $this->selectmenu("wizard");

        if(isset($_REQUEST["datasource"])) {
            $this->resource_ids = $this->process_resourcelist();
            if(count($this->resource_ids) == 0) {
                $this->view->info = "No resource matches your current criteria. Please adjust your criteria in order to display any data.";
            }
        }
        $this->load_daterangequery();
    }

    public function xmlAction()
    {
        $this->setpagetitle($this->default_title());

        //find if xml.phtml exists for this control
        $name = $this->getRequest()->getControllerName();
        $path = $this->view->getScriptPath("").$name."/xml.phtml";
        if(file_exists($path)) {
            //if so, then we support xml
            parent::xmlAction();
        } else {
            $this->render("noxml", null, true);
        }
    }

/*
    public function flashAction()
    {
        // some typical movie variables
        Ming_setScale(20.0000000);
        ming_useswfversion(6);
        $movie = new SWFMovie();
        $movie->setRate(60);
        $movie->setDimension(550, 400);
        $movie->setBackground(rand(0,0xFF),rand(0,0xFF),rand(0,0xFF));

        $f = new SWFFont('Arial');
        $t = new SWFTextField();
        $t->setFont($f);
        $t->setHeight(200);
        $t->addString('MyOSG');
        $movie->add($t);

        // create a centered square shape
        $sh=new SWFShape(); 
        $sh->setRightFill(255,155,0);
        $sh->movePenTo(-50,-50); 
        $sh->drawLine(100,0);  
        $sh->drawLine(0,100); 
        $sh->drawLine(-100,0); 
        $sh->drawLine(0,-100); 

        // add shape to holder sprite
        $holder= new SWFSprite();
        $f1 = $holder->add($sh);
        $holder->nextFrame();

        // add holder sprite to library and
        // give sprite linkage name 'sq'
        $movie->addExport($holder, 'sq');

        // export 'sq' in frame 1 of movie
        $movie->writeExports();

        // Object.registerClass actionscript
        $strAction = <<<EOT
        // create class
        function RotateClip() {};
        RotateClip.prototype = new MovieClip();
        RotateClip.prototype.onEnterFrame = function() {
            this._rotation+=this.rotationspeed;
        };

        // associate RotateClip class with sq
        Object.registerClass('sq', RotateClip);

        // attach sq and set sq1 rotationspeed to 15
        attachMovie('sq','sq1',1,{rotationspeed:15});
        sq1._x=150;
        sq1._y=100;

        // attach sq and set sq2 rotationspeed to -5
        attachMovie('sq','sq2',2,{rotationspeed:-5});
        sq2._x=350;
        sq2._y=100;

        // attach sq and set sq3 rotationspeed to 5
        attachMovie('sq','sq3',3,{rotationspeed:5});
        sq3._x=150;
        sq3._y=300;

        // attach sq and set sq4 rotationspeed to -15
        attachMovie('sq','sq4',4,{rotationspeed:-15});
        sq4._x=350;
        sq4._y=300;
EOT;

        // add actionscript to root timeline of movie
        $movie->add(new SWFAction($strAction));

        header('Content-type: application/x-shockwave-flash');
        $movie->output();
        $this->render("none", null, true);
    }
*/

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
                if(isset($_REQUEST["facility"])) {
                    if(preg_match("/^facility_(?<id>\d+)/", $key, $matches)) {
                        $this->process_resourcelist_addfacility($resource_ids, $matches["id"]);
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

    private function process_resourcelist_addfacility(&$resource_ids, $facility_id) 
    {
        $site_ids = array();

        //load all site id under the requested sc
        $model = new Site();
        $sites = $model->get(array("facility_id"=>$facility_id));
        foreach($sites as $site) {
            if(!in_array($site->site_id, $site_ids)) {
                $site_ids[] = $site->site_id;
            }
        }

        foreach($site_ids as $site_id) {
            $this->process_resourcelist_addsite($resource_ids, $site_id);
        }
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
        if(isset($_REQUEST["gridtype"])) {
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
            if(isset($_REQUEST["gridtype_".$item->grid_type_id])) {
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

    protected function load_daterangequery()
    {
        $today_begin = (int)(time() / (3600*24));
        $today_begin *= 3600*24;

        //set some defaults
        if(!isset($_REQUEST["start_type"])) {
            $_REQUEST["start_type"] = "7daysago";
        }
        if(!isset($_REQUEST["end_type"])) {
            $_REQUEST["end_type"] = "today";
        }

        switch($_REQUEST["start_type"]) {
        case "yesterday":
            $this->view->start_time = $today_begin - 3600*24;
            break;
        case "7daysago":
            $this->view->start_time = $today_begin - 3600*24*7;
            break;
        case "30daysago":
            $this->view->start_time = $today_begin - 3600*24*30;
            break;
        case "specific":
            $str = $_REQUEST["start_date"];
            $this->view->start_time = strtotime($str);
            break;
        }

        switch($_REQUEST["end_type"]) {
        case "today":
            $this->view->end_time = $today_begin;
            break;
        case "now":
            $this->view->end_time = time();
            break;
        case "specific":
            $str = $_REQUEST["end_date"];
            $this->view->end_time = strtotime($str);
            break;
        }
    }

}
