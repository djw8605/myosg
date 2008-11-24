<?

require_once("app/timerange.php");

class AandrhistoryController extends ControllerBase
{
    public function breads() { return array("rsv", "aandr"); }
    public static function default_title() { return "Availability and Reliability History"; }
    public static function default_url($query) { return ""; }

    public function processPeriodQuery()
    {
        $start_time = null;
        $end_time = null;

        if(!isset($_REQUEST["period"])) {
            //this causes the graph period combo box to use following as default, as well as telling graph 
            //the default length
            $_REQUEST["period"] = "30days";
        }
        $dirty_period = $_REQUEST["period"];
        $this->view->period = $dirty_period;
        switch($dirty_period) {
        case "week":
            $end_time = (int)(time() / 86400) * 86400;
            $start_time = $end_time - 86400*7;
            break;
        case "30days":
            $end_time = (int)(time() / 86400) * 86400;
            $start_time = $end_time - 86400*30;
            break;
        default:
            throw new exception("bad period: $dirty_period");
        }

        return array($start_time, $end_time);
    }

    public function load()
    {
        //determine report period
        list($start_time, $end_time) = $this->processPeriodQuery();
        $this->view->start_time = $start_time;
        $this->view->end_time = $end_time;

        ///////////////////////////////////////////////////////////////////////
        // Load graph inforamtion
        $resource_id = null;
        $dirty_resource_id = $_REQUEST["resource_id"];
        if(Zend_Validate::is($dirty_resource_id, 'Int')) {
            $resource_id = $dirty_resource_id;
        }

        //use the same param for both resource / resource service model
        $params = array();
        $params["resource_id"] = $resource_id;

        //load resource list
        $resource_model = new Resource();
        $resource_info = $resource_model->getindex($params);
        $r = $resource_info[$resource_id];
        $resource_name = $r[0]->name;

        //load resource services
        $service_type_model = new ServiceTypes();
        $this->view->service_info = $service_type_model->getindex($params);

        //pull A&R history
        $model = new ServiceAR();
        $params["start_time"] = $start_time;
        $params["end_time"] = $end_time;
        $this->view->services = $model->getgroupby("service_id", $params);
        
        $this->setpagetitle(AandrhistoryController::default_title(). " for ".$resource_name);
    }

}
