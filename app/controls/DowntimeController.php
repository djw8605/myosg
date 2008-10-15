<?

class DowntimeController extends ControllerBase
{ 
    public function pagename() { return "ical"; }
    public function load()
    {
        $resource_model = new Resource();
        $this->resources = $resource_model->get();

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
