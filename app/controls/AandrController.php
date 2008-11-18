<?

require_once("app/timerange.php");

class AandrController extends ControllerBase
{
    public function breads() { return array("rsv"); }
    public static function default_title() { return "Availability and Reliability"; }
    public static function default_url($query) { return ""; }

    public function processPeriodQuery()
    {
        $start_time = null;
        $end_time = null;

        if(!isset($_REQUEST["period"])) {
            //this causes the graph period combo box to use following as default, as well as telling graph 
            //the default length
            $_REQUEST["period"] = "1day";
        }
        $dirty_period = $_REQUEST["period"];
        $this->view->period = $dirty_period;
        switch($dirty_period) {
        case "1day":
            $end_time = (int)(time() / 86400) * 86400;
            $start_time = $end_time - 86400;
            break;
        case "week":
            $end_time = (int)(time() / 86400) * 86400;
            $start_time = $end_time - 86400*7;
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
        if(isset($_REQUEST["resource_id"])) {
            $dirty_resource_id = $_REQUEST["resource_id"];
            if(Zend_Validate::is($dirty_resource_id, 'Int')) {
                $resource_id = $dirty_resource_id;
            }
        }

        //use the same param for both resource / resource service model
        $params = array();
        if($resource_id !== null) {
            $params["resource_id"] = $resource_id;
        }

        //load resource list
        $resource_model = new Resource();
        $this->view->resources = $resource_model->getindex($params);

        //load resource services
        $service_type_model = new ServiceTypes();
        $this->view->services = $service_type_model->getindex($params);

        //load A&R cache
        $cache_filename = config()->aandr_cache;
        $cache_filename = str_replace("<start_time>", $start_time, $cache_filename);
        $cache_filename = str_replace("<end_time>", $end_time, $cache_filename);
        if(file_exists($cache_filename)) {
            $cache_xml = file_get_contents($cache_filename);
            $aandr = new SimpleXMLElement($cache_xml);
            $this->view->aandr = array();

            $this->view->calc_time = (int)$aandr->CalculateTimestamp[0];

            //pass A&R Info
            foreach($aandr->Resources[0] as $resource) {
                //filter by resource_id
                if($resource_id !== null) {
                    if($resource_id != (int)$resource->ResourceID) continue;
                }
                $this->view->aandr[(int)$resource->ResourceID] = $resource->Services[0];
            }

        }
        $this->setpagetitle(AandrController::default_title());
    }

}
