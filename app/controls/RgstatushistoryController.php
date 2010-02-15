<?php
/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class RgstatushistoryController extends RgController
{
    public static function default_title() { return "RSV Status History"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $this->view->ruler = $this->generateRuler($this->view->start_time, $this->view->end_time);
        $this->view->rgs = $this->rgs;

        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getindex();

        $resource_model = new Resource();
        $resource_service_model = new ResourceServices();
        $this->view->resources = array();
        $this->view->services = array();
        foreach($this->rgs as $rg) {
            foreach($rg as $rid=>$resource) {
                //load resource detail
                $recs = $resource_model->get(array("resource_id"=>$rid));
                $this->view->resources[$rid] = $recs[0];

                //load service info
                $params = array("resource_id" => $rid);
                $this->view->services[$rid] = $resource_service_model->get($params);
            }
        }
    }

    public function detailAction()
    {
        if(isset($_REQUEST["resource_id"]) && isset($_REQUEST["service_id"]) && isset($_REQUEST["time"])) {
            $resource_id = (int)$_REQUEST["resource_id"];
            $service_id = (int)$_REQUEST["service_id"];
            $time = (int)$_REQUEST["time"];

            $this->view->detail_service_id = $service_id;
            $this->view->detail_time = $time;

            //get service information
            $model = new ResourceServices();
            $params = array("resource_id" => $resource_id, "service_id" => $service_id);
            $this->view->detail_service = $model->get($params);

            //get statuses at specified timestamp
            $metricdata_model = new MetricData();
            $params = array("resource_id" => $resource_id, "time" => $time);
            $latest_metrics = $metricdata_model->get($params); 

            //pull metric info
            $model = new Metric();
            $this->view->metric_details = $model->getindex();

            //pull all metric for this service
            $model = new MetricService();
            $this->view->detail_critical_metrics = array();
            $this->view->detail_noncritical_metrics = array();
            foreach($model->get(array("service_id"=>$service_id)) as $metric) {
                $info = array();
                $info["metric_id"] = $metric->metric_id;
                //find the reported detail (if not, leave it empty)
                foreach($latest_metrics as $latest_metric) {
                    if($latest_metric->metric_id == $metric->metric_id) {
                        $info["detail_id"] = $latest_metric->id;
                        $info["detail"] = $this->fetchMetricDetail($latest_metric->id);
                        $info["status"] = Status::getStatus($latest_metric->metric_status_id);
                        $info["timestamp"] = $latest_metric->timestamp;
                        break;
                    }
                }
                if($metric->critical == 1) {
                    $this->view->detail_critical_metrics[] = $info;
                } else {
                    $this->view->detail_noncritical_metrics[] = $info;
                }
            }

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
/*
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
        unset($metric->MetricDataID);
        unset($metric->Timestamp);
        unset($metric->Detail);
        unset($metric->Status);
    }
*/
    private function fetchMetricDetail($id)
    {
        static $metric_detail_model = null;
        if($metric_detail_model === null) $metric_detail_model = new MetricDetail();
        $detail = $metric_detail_model->get(array("id"=>$id)); 
        if($detail == null) return null;
        return $detail[0]->detail; 
    }

    private function generateRuler($start_time, $end_time)
    {
        $period = $end_time - $start_time;
        if($period < 3600*24) {
            $marker = "H:i";
        } else if($period < 3600*24*30) {
            $marker = "M j H:i";
        } else {
            $marker = "M j, Y";
        }

        if($end_time == time()) {
            $end_marker = "now";
        } else {
            $end_marker = date($marker, $end_time);
        }

        $total = $end_time - $start_time;
        $q25 = $start_time + $total / 4;
        $mark_25th = date($marker, $q25);
        $q50 = $start_time + $total / 4 * 2;
        $mark_50th = date($marker, $q50);
        $q75 = $start_time + $total / 4 * 3;
        $mark_75th = date($marker, $q75);
        $out = "";
        $out .= "<table align=\"center\" width=\"100%\" class=\"ruler\"><tr>";
        $out .= "<td width=\"25%\">$mark_25th |</td>";
        $out .= "<td width=\"25%\">$mark_50th |</td>";
        $out .= "<td width=\"25%\">$mark_75th |</td>";
        $out .= "<td width=\"25%\">$end_marker |</td>";
        $out .= "</tr></table>";

        return $out;
    }
}
