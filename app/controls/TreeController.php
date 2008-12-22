<?

class TreeController extends ControllerBase
{ 
    public function breads() { return array("oim"); }
    public static function default_title() { return "OIM Hirearchy Browser"; }
    public static function default_url($query) { return ""; }

    public function load() 
    { 
        $this->setpagetitle(TreeController::default_title());
        if(!in_array(role::$see_oim_tree, user()->roles)) {
            $this->render("error/noaccess", null, true);
            return;
        }
    }

    public function itemAction()
    {
        if(!in_array(role::$see_oim_tree, user()->roles)) {
            $this->render("none", null, true);
            return;
        }

        $dirty_type = $_REQUEST["type"];
        $dirty_id = @$_REQUEST["id"];
        $id = (int)$dirty_id;

        switch($dirty_type) {
        case "root":
            $model = new Facilities();
            $this->view->facility_count = $model->getcount();
            $model = new SupportCenters();
            $this->view->sc_count = $model->getcount();
            break;
        case "facility":
            $model = new Facilities();
            $this->view->facilities = $model->get();

            $model = new Site();
            $this->view->site_counts = array();
            foreach($this->view->facilities as $f) {
                $this->view->site_counts[$f->facility_id] = $model->getcount(array("facility_id"=>$f->facility_id));
            }
            break;
        case "sc":
            $model = new SupportCenters();
            $this->view->supportcenters = $model->get();

            $model = new Site();
            $this->view->site_counts = array();
            foreach($this->view->supportcenters as $sc) {
                $this->view->site_counts[$sc->sc_id] = $model->getcount(array("sc_id"=>$sc->sc_id));
            }

            $model = new SupportCenterContact();
            $this->view->contact_counts = array();
            foreach($this->view->supportcenters as $sc) {
                $this->view->contact_counts[$sc->sc_id] = $model->getcount(array("sc_id"=>$sc->sc_id));
            }

            $model = new VirtualOrganization();
            $this->view->vo_counts = array();
            foreach($this->view->supportcenters as $sc) {
                $this->view->vo_counts[$sc->sc_id] = $model->getcount(array("sc_id"=>$sc->sc_id));
            }
            break;
        case "facility_site":
            $model = new Site();
            $params = array("facility_id"=>$id);
            $this->view->sites = $model->get($params);

            $model = new ResourceGroup();
            $this->view->rg_counts = array();
            foreach($this->view->sites as $sc) {
                $this->view->rg_counts[$sc->site_id] = $model->getcount(array("site_id"=>$sc->site_id));
            }
            break;
        case "supportcenter_site":
            $model = new Site();
            $params = array("sc_id"=>$id);
            $this->view->sites = $model->get($params);

            $model = new ResourceGroup();
            $this->view->rg_counts = array();
            foreach($this->view->sites as $sc) {
                $this->view->rg_counts[$sc->site_id] = $model->getcount(array("site_id"=>$sc->site_id));
            }
            break;

        case "supportcenter_contact":
            $model = new SupportCenterContact();
            $params = array("sc_id"=>$id);
            $this->view->contact_types = $model->getgroupby("contact_type", $params);
            break;
        case "vo_contact":
            $model = new VOContact();
            $params = array("vo_id"=>$id);
            $this->view->contact_types = $model->getgroupby("contact_type", $params);
            break;
        case "resource_contact":
            $model = new ResourceContact();
            $params = array("resource_id"=>$id);
            $this->view->contact_types = $model->getgroupby("contact_type", $params);
            break;

        case "supportcenter_vo":
            $model = new VirtualOrganization();
            $params = array("sc_id"=>$id);
            $this->view->vos = $model->get($params);

            $model = new VOContact();
            $this->view->contact_counts = array();
            foreach($this->view->vos as $vo) {
                $this->view->contact_counts[$vo->vo_id] = $model->getcount(array("vo_id"=>$vo->vo_id));
            }
            break;
        case "site_resource_groups":
            $model = new ResourceGroup();
            $param = array("site_id"=>$id);
            $this->view->gridtypes = $model->getgroupby("grid_type", $param);

            $this->view->r_counts_arrays = array();

            $model = new ResourceByGroupID();
            foreach($this->view->gridtypes as $gridtype=>$rgs) {
                $this->view->r_counts_arrays[$gridtype] = array();
                foreach($rgs as $rg) {
                    $this->view->r_counts_arrays[$gridtype][$rg->resource_group_id] = 
                        $model->getcount(array("resource_group_id"=>$rg->resource_group_id));
                }
            }
            break;
        case "resource_group_resource":
            $model = new ResourceByGroupID();
            $param = array("resource_group_id"=>$id);
            $this->view->rs = $model->get($param);

            $model = new ResourceServices();
            $this->view->service_counts = array();
            foreach($this->view->rs as $r) {
                $this->view->service_counts[$r->resource_id] = $model->getcount(array("resource_id"=>$r->resource_id));
            }

            $model = new ResourceContact();
            $this->view->contact_counts = array();
            foreach($this->view->rs as $r) {
                $this->view->contact_counts[$r->resource_id] = $model->getcount(array("resource_id"=>$r->resource_id));
            }
            break;
        case "resource_service":
            $model = new ResourceServices();
            $param = array("resource_id"=>$id);
            $this->view->services  = $model->get($param);

            $model = new Metric();
            $this->view->critical_metric_counts = array();
            $this->view->noncritical_metric_counts = array();
            foreach($this->view->services as $s) {
                $this->view->critical_metric_counts[$s->service_id] = $model->getcount(array("service_id"=>$s->service_id, "critical"=>1));
                $this->view->noncritical_metric_counts[$s->service_id] = $model->getcount(array("service_id"=>$s->service_id, "critical"=>0));
            }

            break;
        case "service_criticalmetrics":
            $model = new Metric();
            $this->view->metrics = $model->get(array("service_id"=>$id, "critical"=>1));
            break;
        case "service_noncriticalmetrics":
            $model = new Metric();
            $this->view->metrics = $model->get(array("service_id"=>$id, "critical"=>0));
            break;
        default:
            $this->render("none", null, true);
            return;
        }
        $this->render($dirty_type);
    }
} 
