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

        //pull site groups
        $rgroup_model = new ResourceGroup();
        $rgroups = $rgroup_model->get();
        $this->view->rgs = array();

        $site_ids = $this->process_sitelist();
        foreach($site_ids as $site_id) {
            $rgs = array();
            foreach($rgroups as $rgroup) {
                //filter by gridtype
                if(isset($_REQUEST["gridtype"])) {
                    $gridtype = $rgroup->osg_grid_type_id;
                    if(!isset($_REQUEST["gridtype_".$gridtype])) {
                        continue;
                    }
                }
                //only pass active/non-disable resource group
                if($rgroup->active == 0 || $rgroup->disable == 1) {
                    continue;
                }
                if($rgroup->site_id == $site_id) {
                    $rgs[] = $rgroup;
                }
            }
            $this->view->rgs[$site_id] = $rgs;
        }

        //pull sites (grouped by site group id)
        $rgrouped_model = new ResourceByGroupID();
        $this->view->resources_bygid = $rgrouped_model->getindex();

        //get site statuses
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

    protected function process_sitelist()
    {
        $site_ids = array();

        if(isset($_REQUEST["all_sites"])) {
            $model = new Site();
            $sites = $model->get();
            foreach($sites as $site) {
                $site_ids[] = $site->id;
            }
        } else {
            foreach($_REQUEST as $key=>$value) {
                if(isset($_REQUEST["sc"])) {
                    if(preg_match("/^sc_(\d+)/", $key, $matches)) {
                        $this->process_sitelist_addsc($site_ids, $matches[1]);
                    }
                }
                if(isset($_REQUEST["facility"])) {
                    if(preg_match("/^facility_(\d+)/", $key, $matches)) {
                        $this->process_sitelist_addfacility($site_ids, $matches[1]);
                    }
                }
            }
        }

        //filter the site list based on user query
        $site_ids = $this->process_site_filter($site_ids);
        return $site_ids;
    }

    private function process_sitelist_addsc(&$site_ids, $sc_id)
    {
        //load all site under the requested site_group_id
        $model = new Site();
        $sites = $model->get(array("sc_id"=>$sc_id));
        foreach($sites as $site) {
            if(!in_array($site->id, $site_ids)) {
                $site_ids[] = $site->id;
            }
        }
    }

    private function process_sitelist_addfacility(&$site_ids, $facility_id)
    {
        //load all site under the requested site_group_id
        $model = new Site();
        $sites = $model->get(array("facility_id"=>$facility_id));
        foreach($sites as $site) {
            if(!in_array($site->id, $site_ids)) {
                $site_ids[] = $site->id;
            }
        }
    }

    private function process_site_filter($sites)
    {
        if(isset($_REQUEST["active"])) {
            $keep = $this->process_site_filter_active();
            $sites = array_intersect($sites, $keep);
        }
        if(isset($_REQUEST["disable"])) {
            $keep = $this->process_site_filter_disable();
            $sites = array_intersect($sites, $keep);
        }
        return $sites;
    }

    private function process_site_filter_active()
    {
        $sites_to_keep = array();
        $model = new Site();
        $sites = $model->get();
        $active_value = $_REQUEST["active_value"];
        foreach($sites as $site) {
            if($site->active == $active_value) {
                if(!in_array($site->id, $sites_to_keep)) {
                    $sites_to_keep[] = $site->id;
                }
            }
        }
        return $sites_to_keep;
    }

    private function process_site_filter_disable()
    {
        $sites_to_keep = array();
        $model = new Site();
        $sites = $model->get();
        $disable_value = $_REQUEST["disable_value"];
        foreach($sites as $site) {
            if($site->disable == $disable_value) {
                if(!in_array($site->id, $sites_to_keep)) {
                    $sites_to_keep[] = $site->id;
                }
            }
        }
        return $sites_to_keep;
    }
}
