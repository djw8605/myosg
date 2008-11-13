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
        $this->view->resources = $resource_model->get($params);

        $downtime_model = new Downtime();
        $this->view->downtimes = $downtime_model->get($params);

        $downtime_service_model = new DowntimeService();
        $this->view->downtime_services = $downtime_service_model->get();
    }
    public function icalAction()
    {
        $this->load();
    }
} 
