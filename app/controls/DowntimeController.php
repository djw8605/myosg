<?

class DowntimeController extends ControllerBase
{ 
    public function breads() { return array(); }
    public static function default_title() { return "Downtime"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $params = array();
        if(isset($_REQUEST["resource_id"])){
            $dirty_resource_id = $_REQUEST["resource_id"];
            $resource_id = (int)$dirty_resource_id;
            $params["resource_id"] = $resource_id;
        }
        $resource_model = new Resource();
        $this->resources = $resource_model->get($params);

        $downtime_model = new Downtime();
        $this->downtimes = $downtime_model->get();

        $downtime_service_model = new DowntimeService();
        $this->downtime_service = $downtime_service_model->get();
    }
    public function icalAction()
    {
        $this->load();
        $this->view->resources = $this->resources;
        $this->view->downtimes = $this->downtimes;
        $this->view->downtime_service = $this->downtime_service;
    }
} 
