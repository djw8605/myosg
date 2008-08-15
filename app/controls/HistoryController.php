<?

require_once("app/timerange.php");

class HistoryController extends Zend_Controller_Action 
{ 
    public function metricAction()
    {
        if(isset($_REQUEST["id"])) {
            $dirty_metric_id = $_REQUEST["id"];
            if(Zend_Validate::is($dirty_metric_id, 'Int')) {
                $metric_id = $dirty_metric_id;
            }

            $metric_model = new Metrics();
            $metric = $metric_model->fetchOneMetric($metric_id);

            $probeinfo_model = new ProbeInfo();
            $metric_info = $probeinfo_model->getProbeInfo($metric->metric_id);

            echo "Metric Name: ".$metric_info->name."<br/>";
            echo "Metric Status: ".$metric->status."<br/>";
            
            $critical = "No";
            if($probeinfo_model->isCriticalProbe($metric->resource_id, $metric->metric_id)) {
                $critical = "Yes";
            }
            echo "Critical Metric for this resource: ".$critical."<br/>";
            echo "<br/>";

            if($metric->detail == "") {
                echo "(No Detail)";
            } else {
                echo $metric->detail;
            }
        }
    }
    public function proxyAction()
    {
        if(isset($_REQUEST["id"])) {
            $dirty_resource_id = $_REQUEST["id"];
            if(Zend_Validate::is($dirty_resource_id, 'Int')) {
                $resource_id = $dirty_resource_id;
            }

            //figure out the range of time to display
            list($start_time, $end_time) = getLast24HourRange();
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

            $overall_status = new OverallStatus($resource_id);

            $this->view->start_time = $start_time;
            $this->view->end_time = $end_time;
            $this->view->status_changes = $overall_status->fetchStatusChanges($start_time, $end_time);
            $this->render("detail");
        }
    }

    //output json containing status history graph (wrapped in json)
    public function resourceAction()
    {
        header('Content-type: text/json');

        $servicetype = null;
        if(isset($_REQUEST["servicetype"])) {
            $dirty_servicetype = $_REQUEST["servicetype"];
            if(Zend_Validate::is($dirty_servicetype, 'Int')) {
                $servicetype = $dirty_servicetype;
            }
        }

        //figure out the range of time to display
        list($start_time, $end_time) = getLast24HourRange();
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

        $sstr = date(config()->date_format_full, $start_time);
        $estr = date(config()->date_format_full, $end_time);

        dlog("graph Action called for $start_time($sstr) to $end_time($estr)");

        //pull resource info 
        $resource_model = new Resource();
        $resource_records_all = $resource_model->fetchAll($servicetype);
        $resource_records = array();
        foreach($resource_records_all as $resource_record) {
            if(file_exists(config()->cache_filename_latest_overall.".".$resource_record->id)) {
                $resource_records[] = $resource_record;
            }
        }
        $total_count = count($resource_records);

        echo "{";
            echo "\"totalCount\":\"$total_count\",";
            echo "\"graphs\":[";
                $first = true;
                foreach($resource_records as $resource_record) { 
                    $resource_id = $resource_record->id;
                    //$overall_status = new OverallStatus($resource_id);
                    //$status_changes = $overall_status->fetchStatusChanges($start_time, $end_time);
                    //add comma if not first
                    if(!$first) echo ",";
                    $first = false;

                    //pull data
                    $resource_name = $resource_record->name;
                    $resource_fqdn = $resource_record->uri;
                    //$graph = addslashes($this->generateGraph($status_changes, $start_time, $end_time));
                    $graph = addslashes("<img src=\"history/graph?rid=$resource_id&start=$start_time&end=$end_time\" width=\"100%\" height=\"14px\"/>");
                    $graph .= addslashes($this->generateRuler($start_time, $end_time));
                    $url = $resource_record->url;

                    //service type
                    $resource_service_types = new ResourceServiceTypes();
                    $service_types = $resource_service_types->getServiceTypes($resource_id);
                    $s_first = true;
                    $str = "";
                    foreach($service_types as $service_type) {
                        if(!$s_first) $str .= " / ";
                        $s_first = false;
                        $str .= $service_type->description;
                    }

                    //output graph data
                    echo "{";
                        echo "\"resource_id\":\"$resource_id\",";
                        echo "\"name\":\"$resource_name\",";
                        echo "\"fqdn\":\"$resource_fqdn\",";
                        echo "\"graph\":\"$graph\",";
                        echo "\"url\":\"$url\",";
                        echo "\"service_types\":\"$str\"";
                    echo "}";
                }
            echo "]";
        echo "}";

        $this->render("none");
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
        $out .= "<table align=\"center\" width=\"100%\" class=\"graph ruler\"><tr>";
        $out .= "<td width=\"25%\">$mark_25th |</td>";
        $out .= "<td width=\"25%\">$mark_50th |</td>";
        $out .= "<td width=\"25%\">$mark_75th |</td>";
        $out .= "<td width=\"25%\">$end_marker |</td>";
        $out .= "</tr></table>";

        return $out;
    }

/*
    private function generateGraph($changes, $start_time, $end_time) 
    {
        $total_time = $end_time - $start_time;

        $out = "";
        $js = "";

        $status = "NA";//if no changes are available, it will use this

        $out .= "<table width=\"100%\" class=\"graph\" align=\"center\"><tr>";

        $first = true;
        $decile_out = 0; //used to calculate the reminder area
        foreach($changes as $change) {
            $time = $change->timestamp;
            if($first) {
                //assume first element is always a initial status element at $start_time
                $decile1 = 0;
                $status = $change->overall_status;
                $detail = $change->detail;
                $first = false;
            } else {
                $next_status = $change->overall_status;
                $next_detail = $change->detail;
                $decile2 = (float)($time-$start_time)/$total_time*100;
                $size = ceil($decile2 - $decile1);
                $decile_out += $size;
                $out .= "<td width=\"$size%\" class=\"color_$status\"/>";

                $status = $next_status;
                $decile1 = $decile2;
            }
        }

        //fill leftover
        $size = max(ceil(100 - $decile_out), 1);
        $out .= "<td width=\"$size%\" class=\"color_$status\">&nbsp;</td>";

        //close table and output js
        $out .= "</td></table>";

        return $out;
    }
*/

    public function graphAction()
    {
        //get paramters and pull status changes for that period
        $dirty_resource_id = $_REQUEST["rid"];
        if(Zend_Validate::is($dirty_resource_id, 'Int')) {
            $resource_id = $dirty_resource_id;
        }
        list($start_time, $end_time) = getLast24HourRange();
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
        $overall_status = new OverallStatus($resource_id);
        $status_changes = $overall_status->fetchStatusChanges($start_time, $end_time);

        dlog("status_change count: ".count($status_changes));

        //let's draw the graph..

        $image_width = 300;
        $im = imageCreate($image_width,1);
        $back = imageColorAllocate($im, 255,255,255);//paint it white..

        $color = array();
        $color["OK"] = imagecolorallocate($im, 0,255,0);
        $color["WARNING"] = imagecolorallocate($im, 255,255,0);
        $color["CRITICAL"] = imagecolorallocate($im, 255,0,0);
        $color["UNKNOWN"] = imagecolorallocate($im, 100,100,100);

        $total_time = $end_time - $start_time;
        $decile_out = 0; //used to calculate the reminder area
        $first = true;
        foreach($status_changes as $change) {
            $time = $change->timestamp;
            if($first) {
                $decile1 = (float)($time-$start_time)/$total_time*$image_width;
                $decile_out = $decile1;
                $status = $change->overall_status;
                //$detail = $change->detail;
                $first = false;
            } else {
                $next_status = $change->overall_status;
                $decile2 = (float)($time-$start_time)/$total_time*$image_width;
                imageline($im, $decile1, 0, $decile2, 0, $color[$status]);
                dlog("coloring from $decile1 to $decile2 with $status");
                $size = ($decile2 - $decile1);
                $decile_out += $size;

                $status = $next_status;
                $decile1 = $decile2;
            }
        }
        if(count($status_changes) > 0) {
            //fill leftover
            imageline($im, $decile_out, 0, $image_width, 0, $color[$status]);
            dlog("(last)coloring from $decile_out to $image_width with $status");
        }
 
        //output the image
        header('Content-type: image/png');
        imagePNG($im); 

        $this->render("none");
    }
} 
