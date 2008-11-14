<?

require_once("app/timerange.php");

class AandrController extends ControllerBase
{
    public function breads() { return array("rsv"); }
    public static function default_title() { return "Availability and Reliability"; }
    public static function default_url($query) { return ""; }

    public function processPeriodQuery()
    {
        if(!isset($_REQUEST["period"])) {
            //this causes the graph period combo box to use following as default, as well as telling graph 
            //the default length
            $_REQUEST["period"] = config()->history_graph_default_period;
        }
        $dirty_period = $_REQUEST["period"];
        $this->view->period = $dirty_period;
        switch($dirty_period) {
        case "1day":
            $this->history_days = 1;
            break;
        case "3day":
            $this->history_days = 3;
            break;
        case "week":
            $this->history_days = 7;
            break;
        case "month":
            $this->history_days = 31;
            break;
        case "year":
            $this->history_days = 365;
            break;
        default:
            throw new exception("bad period: $dirty_period");
        }
    }

    public function load()
    {
        $this->processPeriodQuery();

        ///////////////////////////////////////////////////////////////////////
        // Load graph inforamtion
        $resource_id = null;
        if(isset($_REQUEST["resource_id"])) {
            $dirty_resource_id = $_REQUEST["resource_id"];
            if(Zend_Validate::is($dirty_resource_id, 'Int')) {
                $resource_id = $dirty_resource_id;
            }
        }

        //determine report period
        list($start_time, $end_time) = getLastNDayRangeProper($this->history_days);
        $this->view->start_time = $start_time;
        $this->view->end_time = $end_time;

        //use the same param for both resource / resource service model
        $params = array();
        if($resource_id !== null) {
            $params["resource_id"] = $resource_id;
        }

        //load resource list
        $resource_model = new Resource();
        $this->view->resources = $resource_model->get($params);

        //load resource services
        $resource_service_model = new ResourceServices();
        $this->view->services = $resource_service_model->get($params);

        //calculate a&r
        $ars = $this->calculateAR($this->view->resources, $this->view->services, $start_time, $end_time);
        var_dump($ars);

        $this->setpagetitle(AandrController::default_title());
    }

    private function getDowntimesForService($downtimes, $service_id)
    {
        $downtime_service_model = new DowntimeService();
        $downtime_service = $downtime_service_model->get();
        $downtime_forservice = array();

        foreach($downtimes as $downtime) {
            $id = $downtime->downtime_id;
            foreach($downtime_service as $service) {
                if($service->downtime_id == $id) {
                    $downtime_forservice[] = $downtime;
                }
            }
        }
        return $downtime_forservice;
    }

    function calculateAR($resources, $resource_services, $start_time, $end_time)
    {
        $ars = array();
        
        foreach($resources as $resource) {
            $ars[$resource->id] = array();

            foreach($resource_services as $service) {
                if($service->resource_id == $resource->id) {

                    /////////////////////////////////////////////////////////////////////////////////
                    //pull status changes
                    $params = array();
                    $params["resource_id"] = $resource->id;
                    $params["start_time"] = $start_time;
                    $params["end_time"] = $end_time;
                    $params["service_id"] = $service->service_id;
                    $service_statuschange_model = new ServiceStatusChange();
                    $status_changes = $service_statuschange_model->get($params);
                
            /*
                    /////////////////////////////////////////////////////////////////////////////////
                    //pull downtime info
                    $downtime_model = new Downtime();
                    $params = array("resource_id" => $resource_id, "start_time"=>$start_time, "end_time"=>$end_time);
                    $downtimes = $downtime_model->get($params);
                    $downtimes_forservice = $this->getDowntimesForService($downtimes, $service_id);
            */

                    ////////////////////////////////////////////////////////////////////////////////
                    //calculate AR
                    $total_time = $end_time - $start_time;
                    $up_time = 0;

                    $decile_out = 0; //used to calculate the reminder area
                    $first = true;
                    if($total_time > 0) {
                        foreach($status_changes as $change) {
                            $time = $change->timestamp;
                            if($first) {
                                if($time < $start_time) $time = $start_time;
                                $decile1 = $time - $start_time;
                                $decile_out = $decile1;
                                $status = (int)$change->status_id;
                                $first = false;
                            } else {
                                $next_status = (int)$change->status_id;
                                $decile2 = $time - $start_time;
                                $size = ($decile2 - $decile1);
                                if($status == "OK" or $status == "WARNING") {
                                    $up_time += $size;
                                }
                                $decile_out += $size;
                                $status = $next_status;
                                $decile1 = $decile2;
                            }
                        }
                        if(count($status_changes) > 0) {
                            //fill leftover
                            if($status == "OK" or $status == "WARNING") {
                                $up_time += $total_time - $decile_out;
                            }
                        }
                    }

                    $down_time = 0;
            /*
                    //now draw downtimes
                    foreach($downtimes as $downtime) {
                        $start_p = (float)($downtime->unix_start_time-$start_time)/$total_time*$image_width;
                        $end_p = (float)($downtime->unix_end_time-$start_time)/$total_time*$image_width;
                        imageline($im, $start_p, 0, $end_p, 0, $color_downtime);
                    }
            */
                    $ars[$resource->id][$service->service_id] = array($total_time, $up_time, $down_time);
                    break;
                }
            }
        }

        return $ars;
    }

    private function metric_overwrite($metric, $latest)
    {
        //find the update from $latest and apply change to $metric
        foreach($latest as $latest_metric) {
            if($latest_metric->metric_id == $metric->MetricID[0]) {
                $metric->MetricDataID = $latest_metric->id;
                $metric->Timestamp = $latest_metric->timestamp;
                $metric->Detail = $this->fetchMetricDetail($latest_metric->id);
                $metric->Status = Status::getStatus($latest_metric->metric_status_id);
                return;
            }
        }
        //didn't find the match - clear it
        $metric->MetricDataID = null;
        $metric->Timestamp = null;
        $metric->Detail = null;
        $metric->Status = null;
    }
    private function fetchMetricDetail($id)
    {
        static $metric_detail_model = null;
        if($metric_detail_model === null) $metric_detail_model = new MetricDetail();
        $detail = $metric_detail_model->get(array("id"=>$id)); 
        return $detail[0]->detail; 
    }
}
