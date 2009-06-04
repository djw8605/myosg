<?

class WizardarhistoryController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Availability History"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        //$this->load_daterangequery();

        $resource_model = new Resource();
        $this->view->resource_info = $resource_model->getindex();
        $service_type_model = new Service();
        $this->view->service_info = $service_type_model->getindex();

        ///////////////////////////////////////////////////////////////////////
        // Load graph inforamtion
        $this->view->services = array();
        foreach($this->resource_ids as $rid) {
            //pull A&R history
            $model = new ServiceAR();
            $params["start_time"] = $this->view->start_time;
            $params["end_time"] = $this->view->end_time;
            $params["resource_id"] = $rid;
            $this->view->services[$rid] = $model->getgroupby("service_id", $params);
        }

        $this->setpagetitle(self::default_title());
    }
}
