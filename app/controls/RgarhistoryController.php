<?

class RgarhistoryController extends RgController
{
    public static function default_title() { return "Availability History"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs; 

        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getindex();
        $service_type_model = new Service();
        $this->view->service_info = $service_type_model->getindex();

        ///////////////////////////////////////////////////////////////////////
        // Load graph inforamtion
        $this->view->services = array();
        foreach($this->rgs as $rgid=>$rg) {
            foreach($rg as $rid=>$resource) {
                //pull A&R history
                $model = new ServiceAR();
                $params["start_time"] = $this->view->start_time;
                $params["end_time"] = $this->view->end_time;
                $params["resource_id"] = $rid;
                $this->view->services[$rid] = $model->getgroupby("service_id", $params);
            }
        }
        $this->setpagetitle(self::default_title());
    }
}
