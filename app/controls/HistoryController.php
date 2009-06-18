<?

require_once("app/timerange.php");

class HistoryController extends ControllerBase
{
    public function breads() { return array("rsv", "resources"); }
    public static function default_title() { return "History Status"; }
    public static function default_url($query) { 
        $id = $_REQUEST["resource_id"];
        return "resource_id=$id"; 
    }
    public function indexAction()
    {
        echo "Please use Resource / RSV Status History page.";
        $this->render("none", null, true);
    }

    public function processPeriodQuery()
    {
        if(!isset($_REQUEST["period"])) {
            //this causes the graph period combo box to use following as default, as well as telling graph 
            //the default length
            $_REQUEST["period"] = "";
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
        case "30days":
            $this->history_days = 30;
            break;
        case "year":
            $this->history_days = 365;
            break;
        case "week":
        default:
            $this->history_days = 7;
            break;
         }
    }

    //why do we have both graph and service detail page together?
    //bacause having them together greatly increases the usability.. 
    //I know this is against the design policy of having separate pages for diffent
    //kind of information... but I think we sometime have to make 
    //compromises..
    public function load()
    {
        $this->processPeriodQuery();

        ///////////////////////////////////////////////////////////////////////
        // Load graph inforamtion
        $dirty_resource_id = $_REQUEST["resource_id"];
        if(Zend_Validate::is($dirty_resource_id, 'Int')) {
            $resource_id = $dirty_resource_id;
        }

        list($start_time, $end_time) = getLastNDayRange($this->history_days);
        if(isset($_REQUEST["start_time"])&& isset($_REQUEST["end_time"])) {
            $dirty_start_time = $_REQUEST["start_time"];
            if(Zend_Validate::is($dirty_start_time, 'Int')) {
                $start_time = $dirty_start_time;
            }
            $dirty_end_time = $_REQUEST["end_time"];
            if(Zend_Validate::is($dirty_end_time, 'Int')) {
                $end_time = $dirty_end_time;
            }
        }

        $this->view->start_time = $start_time;
        $this->view->end_time = $end_time;
        $this->view->ruler = $this->generateRuler($start_time, $end_time);

        //get resource info
        $resource_model = new Resource();
        $params = array();
        $params["resource_id"] = $resource_id;
        $resources = $resource_model->get($params);
        $resource = $resources[0];

        $resource_service_model = new ResourceServices();
        $params = array("resource_id" => $resource_id);
        $this->view->services = $resource_service_model->get($params);

        $this->view->resource_id = $resource_id;
        $this->view->resource_name = $resource->name;

        $this->setpagetitle("Status History for ".$resource->name);

        ///////////////////////////////////////////////////////////////////////
        // Optionally, load service detail
        $this->view->detail_show = false;
        if(isset($_REQUEST["service_id"])) {
            $this->view->detail_show = true;
        
            $dirty_service_id = $_REQUEST["service_id"];
            $service_id = (int)$dirty_service_id;
            $this->view->detail_service_id = $service_id;

            $dirty_time = $_REQUEST["time"];
            $time = (int)$dirty_time;
            $this->view->detail_time = $time;

            //get service information
            $params = array("resource_id" => $resource_id, "service_id" => $service_id);
            $this->view->detail_service = $resource_service_model->get($params);
            $this->view->detail_page_title = "Metric Details for ".$this->view->detail_service[0]->description.
                " at ".date(config()->date_format_full, $time);

            //get statuses at specified timestamp
            $metricdata_model = new MetricData();
            $params = array("resource_id" => $resource_id, "time" => $time);
            $metrics = $metricdata_model->get($params); 

            //load cache (for template use.)
            $cache_filename_template = config()->current_resource_status_xml_cache;
            $cache_filename = str_replace("<ResourceID>", $resource_id, $cache_filename_template); 
            $cache_xml = file_get_contents($cache_filename);
            $cache = new SimpleXMLElement($cache_xml);
            foreach($cache->Services[0] as $service) {
                if($service->ServiceID[0] == $service_id) {
                    $critical_metrics = $service->CriticalMetrics[0];
                    $noncritical_metrics = $service->NonCriticalMetrics[0];
                    break;
                }
            }
            foreach($critical_metrics as $metric) {
                $this->metric_overwrite($metric, $metrics);
            }
            foreach($noncritical_metrics as $metric) {
                $this->metric_overwrite($metric, $metrics);
            }
            $this->view->detail_critical_metrics = $critical_metrics;
            $this->view->detail_noncritical_metrics = $noncritical_metrics;

            //load service status
            $service_status_model = new ServiceStatusChange();
            $params = array();
            $params["resource_id"] = $resource_id;
            $params["service_id"] = $service_id;
            $params["start_time"] = $time;
            $params["end_time"] = $time;
            $service_statuses = $service_status_model->get($params);
            $this->view->detail_service_status = null;
            if(isset($service_statuses[0])) {
                $this->view->detail_service_status = $service_statuses[0];
            }

            //load downtime
            $downtime_model = new Downtime();
            $params = array("resource_id" => $resource_id, "start_time"=>$time, "end_time"=>$time);
            $downtimes = $downtime_model->get($params);
            $downtimes_forservice = $this->getDowntimesForService($downtimes, $service_id);
            if(count($downtimes_forservice) > 0) {
                $this->view->downtime = $downtimes_forservice[0];//grab first one for this service
            }
        }
    }

    private function getDowntimesForService($downtimes, $service_id)
    {
        $downtime_service_model = new DowntimeService();
        $downtime_service = $downtime_service_model->get();
        $downtime_forservice = array();

        foreach($downtimes as $downtime) {
            $id = $downtime->id;
            foreach($downtime_service as $service) {
                if($service->resource_downtime_id == $id) {
                    $downtime_forservice[] = $downtime;
                }
            }
        }
        return $downtime_forservice;
    }

    private function generateRuler($start_time, $end_time)
    {
        //$start_marker = date("M t g:i A", $end_time); 
        //$end_marker = date("M t g:i A", $end_time); 
        if($end_time == time()) {
            $end_marker = "now";
        } else {
            $end_marker = date(config()->date_format_full, $end_time);
        }

        $total = $end_time - $start_time;
        $q25 = $start_time + $total / 4;
        $mark_25th = date(config()->date_format_full, $q25);
        $q50 = $start_time + $total / 4 * 2;
        $mark_50th = date(config()->date_format_full, $q50);
        $q75 = $start_time + $total / 4 * 3;
        $mark_75th = date(config()->date_format_full, $q75);
        $out = "";
        $out .= "<table align=\"center\" width=\"100%\" class=\"ruler\"><tr>";
        $out .= "<td width=\"25%\">$mark_25th |</td>";
        $out .= "<td width=\"25%\">$mark_50th |</td>";
        $out .= "<td width=\"25%\">$mark_75th |</td>";
        $out .= "<td width=\"25%\">$end_marker |</td>";
        $out .= "</tr></table>";

        return $out;
    }

    public function graphAction()
    {
        list($status_changes, $start_time, $end_time, $downtimes)  = $this->loadStatusChanges();
        $this->drawGraph($status_changes, $start_time, $end_time, $downtimes);
    }
        
    public function loadStatusChanges()
    {
        /////////////////////////////////////////////////////////////////////////////////
        //get paramters
        $dirty_resource_id = $_REQUEST["resource_id"];
        if(Zend_Validate::is($dirty_resource_id, 'Int')) {
            $resource_id = $dirty_resource_id;
        }
        list($start_time, $end_time) = getLastNDayRange(7);//this default shouldn't be used..
        if(isset($_REQUEST["start"])&& isset($_REQUEST["end"])) {
            $dirty_start_time = $_REQUEST["start"];
            if(Zend_Validate::is($dirty_start_time, 'Int')) {
                $start_time = $dirty_start_time;
            }
            $dirty_end_time = $_REQUEST["end"];
            if(Zend_Validate::is($dirty_end_time, 'Int')) {
                $end_time = $dirty_end_time;
            }
        }
        $service_id = null;
        if(isset($_REQUEST["service_id"])) {
            $service_id = (int)$_REQUEST["service_id"];
        }

        /////////////////////////////////////////////////////////////////////////////////
        //pull status changes
        if($service_id === null) {
            //resource status
            $resource_statuschange_model = new ResourceStatusChange();
            $params = array();
            $params["resource_id"] = $resource_id;
            $params["start_time"] = $start_time;
            $params["end_time"] = $end_time;
            $status_changes = $resource_statuschange_model->get($params);
        } else {
            $service_statuschange_model = new ServiceStatusChange();
            $params = array();
            $params["service_id"] = $service_id;
            $params["resource_id"] = $resource_id;
            $params["start_time"] = $start_time;
            $params["end_time"] = $end_time;
            $status_changes = $service_statuschange_model->get($params);
        }
    
        /////////////////////////////////////////////////////////////////////////////////
        //pull downtime info
        $downtime_model = new Downtime();
        $params = array("resource_id" => $resource_id, "start_time"=>$start_time, "end_time"=>$end_time);
        $downtimes = $downtime_model->get($params);
        $downtimes_forservice = $this->getDowntimesForService($downtimes, $service_id);
        return array($status_changes, $start_time, $end_time, $downtimes_forservice);
    }

    function html2rgb($color)
    {
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0].$color[1],
                                     $color[2].$color[3],
                                     $color[4].$color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
        else
            return false;

        $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

        return array($r, $g, $b);
    }
    function get_graphcolor($id, $im)
    {
        $html = config()->graph_color[$id];
        $rgb = $this->html2rgb($html);
        return imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
    }

    function drawGraph($status_changes, $start_time, $end_time, $downtimes)
    {

        $image_width = config()->history_graph_image_width;
        $im = imageCreate($image_width,2);

        //should I have this configurable via OIM DB?
        $color = array();
        $color[1] = $this->get_graphcolor(1, $im);
        $color[2] = $this->get_graphcolor(2, $im);
        $color[3] = $this->get_graphcolor(3, $im);
        $color[4] = $this->get_graphcolor(4, $im);
        $color_downtime = $this->get_graphcolor(99, $im);
        $back = $this->get_graphcolor(-1, $im);

        $total_time = $end_time - $start_time;
        $decile_out = 0; //used to calculate the reminder area
        $first = true;
        if($total_time > 0) {
            foreach($status_changes as $change) {
                $time = $change->timestamp;
                if($first) {
                    if($time < $start_time) $time = $start_time;
                    $decile1 = (float)($time-$start_time)/$total_time*$image_width;
                    imageline($im, 0, 0, $decile1, 0, $back);
                    imageline($im, 0, 1, $decile1, 1, $back);
                    $decile_out = $decile1;
                    $status = (int)$change->status_id;
                    $first = false;
                } else {
                    $next_status = (int)$change->status_id;
                    $decile2 = (float)($time-$start_time)/$total_time*$image_width;
                    imageline($im, $decile1, 0, $decile2, 0, $color[$status]);
                    imageline($im, $decile1, 1, $decile2, 1, $color[$status]);
                    $size = ($decile2 - $decile1);
                    $decile_out += $size;

                    $status = $next_status;
                    $decile1 = $decile2;
                }
            }
            if(count($status_changes) > 0) {
                //fill leftover
                imageline($im, $decile_out, 0, $image_width, 0, $color[$status]);
                imageline($im, $decile_out, 1, $image_width, 1, $color[$status]);
            } else {
                //no data?
                imageline($im, 0, 0, $image_width, 0, $back);
                imageline($im, 0, 1, $image_width, 1, $back);
            }
        }

        //now draw downtimes
        foreach($downtimes as $downtime) {
            $start_p = (float)($downtime->unix_start_time-$start_time)/$total_time*$image_width;
            $end_p = (float)($downtime->unix_end_time-$start_time)/$total_time*$image_width;
            imageline($im, $start_p, 0, $end_p, 0, $color_downtime);
        }

        //output the image
        header('Content-type: image/png');
        imagePNG($im); 

        $this->render("none");
    }

    private function metric_overwrite($metric, $latest)
    {
        //find the update from $latest and apply change to $metric
        foreach($latest as $latest_metric) {
            if($latest_metric->metric_id == $metric->MetricID[0]) {
                $metric->MetricDataID = $latest_metric->id;
                $metric->Timestamp = $latest_metric->timestamp;
                $metric->Detail = $this->fetchMetricDetail($latest_metric->detail_id);
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
