<?

class WizarddowntimeController extends WizardController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "Downtime Information"; }
    public static function default_url($query) { return ""; }

    public function indexAction()
    {
        $this->load();
        
        $this->view->resource_ids = $this->resource_ids;

        $resource_model = new Resource();
        $this->view->resources = $resource_model->getindex();

        $downtime_model = new Downtime();
        $today_start = time()/(3600*24) * (3600*24);
        $today_end = time()/(3600*24) * (3600*24) + 3600*24;
        $this->view->downtimes = $downtime_model->getindex(array("start_time"=>$today_start, "end_time"=>$today_end));

        $model = new ServiceTypes();
        $this->view->service_info = $model->getindex();

        dlog($this->view->service_info);

        $downtime_service_model = new DowntimeService();
        $this->view->downtime_services = $downtime_service_model->get();

        $this->setpagetitle(self::default_title());
    }
}
