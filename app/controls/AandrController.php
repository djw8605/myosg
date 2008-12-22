<?

require_once("app/timerange.php");

function cmp_availability($a, $b)
{
    $a_sum = 0;
    foreach($a as $service) {
        $val = $service["availability"];
        $a_sum += $val;
    }
    $a_av = $a_sum / count($a);

    $b_sum = 0;
    foreach($b as $service) {
        $val = $service["availability"];
        $b_sum += $val;
    }
    $b_av = $b_sum / count($b);

    if($a_av == $b_av) return 0;
    return ($a_av > $b_av) ? -1 : 1;
}

function cmp_reliability($a, $b)
{
    $a_sum = 0;
    foreach($a as $service) {
        $val = $service["reliability"];
        $a_sum += $val;
    }
    $a_av = $a_sum / count($a);

    $b_sum = 0;
    foreach($b as $service) {
        $val = $service["reliability"];
        $b_sum += $val;
    }
    $b_av = $b_sum / count($b);

    if($a_av == $b_av) return 0;
    return ($a_av > $b_av) ? -1 : 1;
}

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
            $_REQUEST["period"] = "week";
        }
        $dirty_period = $_REQUEST["period"];
        $this->view->period = $dirty_period;
        return computePeriodStartEnd($dirty_period);
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

        //load AR history
        $model = new ServiceAR();
        $params["start_time"] = $start_time;
        $params["end_time"] = $end_time;
        $ar = $model->get($params);
        
        //group by resource/service_id
        $ar_resource_service = array();
        foreach($ar as $a) {
            $r_id = (int)$a->resource_id;
            if(!isset($ar_resource_service[$r_id])) {
                $ar_resource_service[$r_id] = array();
            }
            $service_id = (int)$a->service_id;
            if(!isset($ar_resource_service[$r_id][$service_id])) {
                $ar_resource_service[$r_id][$service_id] = array();
            }
            $ar_resource_service[$r_id][$service_id][] = $a;
        }

        //create aveladge
        $data = array();
        foreach($ar_resource_service as $rid => $resource) {
            //filter by resource_id
            if($resource_id !== null) {
                if($resource_id != (int)$rid) continue;
            }
            $data[$rid] = array();
            foreach($resource as $service_id=>$service) {
                $count = 0;
                $a_total = 0;
                $r_total = 0;
                foreach($service as $rec) {
                    $count++;
                    $a_total += (double)$rec->availability;
                    $r_total += (double)$rec->reliability;
                }
                //store data
                if($count != 0) {
                    $data[$rid][$service_id] = array(
                        "availability"=>($a_total/$count),
                        "reliability"=>($r_total/$count)
                    );
                }
            }
        }

        //sort data
        if(isset($_REQUEST["sort"])) {
            $dirty_sort = $_REQUEST["sort"];
            switch($dirty_sort) {
            case "resource_name":
                break;
            case "a":
                uasort($data, "cmp_availability");
                break;
            case "r":
                uasort($data, "cmp_reliability");
                break;
            }
        }

        $this->view->data = $data;
        $this->setpagetitle(AandrController::default_title());
    }

}
