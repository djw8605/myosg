<?

class MapController extends ControllerBase
{
    public function breads() { return array("rsv"); }
    public static function default_title() { return "RSV Status Map"; }
    public static function default_url($query) { return ""; }

    public function load()
    {

        //pull sites
        $site_model = new Site();
        $sites = $site_model->get();
        $this->view->sites = $sites;

        //pull resource groups
        $rgroup_model = new ResourceGroup();
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
        $rgroups = $rgroup_model->get($params);
        $this->view->rgs = array();
        foreach($sites as $site) {
            foreach($rgroups as $rgroup) {
                //elog(print_r($rgroup, true));
                if($rgroup->site_id == $site->site_id) {
                    if(!isset($this->view->rgs[$site->site_id])) {
                        $this->view->rgs[$site->site_id] = array();
                    }
                    $this->view->rgs[$site->site_id][] = $rgroup;
/*
                    if($site->site_id == 10064) {
                        slog("adding groupp ".print_r($group, true));
                    }
*/
                }
            }
        }

        //pull resources (grouped by resource group id)
        $rgrouped_model = new ResourceByGroupID();
        $this->view->resources_bygid = $rgrouped_model->getindex();

        //get resource statuses
        $model = new LatestResourceStatus();
        $this->view->resource_status = $model->getgroupby("resource_id");

        $this->setpagetitle(self::default_title());
    }
    public function uwaAction()
    {
        $this->load();
    }
    public function iframeAction()
    {
        $this->load();
    }
}
