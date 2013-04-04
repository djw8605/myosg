<?php
/*#################################################################################################

Copyright 2013 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class RgpfmatrixController extends RgController
{
    public static function default_title() { return "Perfsonar Matrix"; }
    public static function default_url($query) { return ""; }

    public function indexAction() {
        parent::indexAction();
        message("warning", "This is an experimental feature");        
    }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs;

        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getgroupby("id");

        //load latency matrix (TODO - cache every minute?)
        $pfmodel = new Perfsonar();

        if(isset($_REQUEST["summary_attrs_showpflatematrix"])) {
            $matrix = $pfmodel->getMatrix(1);
            $this->view->late_service_names = $matrix->serviceNames;
            $this->view->late_status_labels = $matrix->statusLabels; //massage detail a bit
            $this->view->late_matrix = $this->process_matrix(1, $matrix, config()->perfsonar_late_service_id);
        } 
        if(isset($_REQUEST["summary_attrs_showpfbandmatrix"])) {
            $matrix = $pfmodel->getMatrix(2);
            $this->view->band_service_names = $matrix->serviceNames;
            $this->view->band_status_labels = $matrix->statusLabels; //massage detail a bit
            $this->view->band_matrix = $this->process_matrix(2, $matrix, config()->perfsonar_band_service_id);
        } 

        //load service details (all of them for now..) and attach it to resource_services
        //$servicetype_model = new Service();
        //$this->view->servicetypes = $servicetype_model->getindex();
        //$resourceservice_model = new ServiceByResourceID();
        //$this->view->resource_services = $resourceservice_model->getindex();

        //var_dump($this->view->late_matrix);
        //exit;
        
    }

    function process_matrix($matrix_id, $matrix, $pf_service_id) {
        $detail_model = new ResourceServiceDetail();
        $resource_service_details = $detail_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $resource_services = $resourceservice_model->getindex();

        $matrix_view = array();
        foreach($this->view->rgs as $rgid=>$resources) {
            foreach($resources as $rid=>$resource) {
                //get fqdn info
                $resource_fqdn = $resource->fqdn;

                //find resource services
                $services = $resource_services[$rid];
                $found = false;
                foreach($services as $service) {
                    if($service->service_id == $pf_service_id) {
                        $found = true;
                        //override with service detail (if given)
                        if(isset($resource_service_details[$rid][$pf_service_id])) {
                            $details = $resource_service_details[$rid][$pf_service_id];
                            if($details["endpoint"] != "") {
                                $resource_fqdn = $details["endpoint"];
                            }
                            $service->details = $details;
                        }
                        $resource->service_detail[$pf_service_id] = $service;
                        break;
                    }
                }
                if(!$found) continue;//no such service for this resource

                //clean up the fqdn (strip https:// and /toolkit people adds.)
                $pos = strpos($resource_fqdn, "//");
                if($pos !== false) {
                    $resource_fqdn = substr($resource_fqdn, $pos+2);
                }
                $pos = strpos($resource_fqdn, "/");
                if($pos !== false) {
                    $resource_fqdn = substr($resource_fqdn, 0, $pos);
                }

                //lookup matrix row index
                $row_idx = array_search($resource_fqdn, $matrix->rows);
                if($row_idx !== false) {
                    //pull colunmn from matrix
                    $cols = array();
                    foreach($matrix->matrix[$row_idx] as $col_idx=>$col) {
                        $col_fqdn = $matrix->columns[$col_idx];
            
                        $cols[$col_fqdn] = array();
                        foreach($col as $service_id => $detail) {
                            if(!isset($detail->result)) continue;
                            $status = $detail->result->status;
                            $label = $matrix->statusLabels[$status];
                            $detail->result->status_label = $label;
                            $detail->cellid = "$matrix_id:$row_idx:$col_idx";

                            $cols[$col_fqdn][] = $detail;
                        }
                    }
                    $matrix_view[$resource->id] = $cols;
                } else {
                    //no perfsonar info for this resource
                    error_log("no perfsonar data found in matrix for service: $pf_service_id on resource:".$resource->id." [".$resource_fqdn."]");
                    error_log(print_r($matrix->rows, true));
                }
            }
        }
        return $matrix_view;
    }

    public function popoverAction() {
        $cellid = $_REQUEST["cid"];
        list($mid, $rid, $cid, $sid) = explode(":", $cellid);
        $mid = (int)$mid;
        $rid = (int)$rid;
        $cid = (int)$cid;
        $sid = (int)$sid;

        $pfmodel = new Perfsonar();
        $matrix = $pfmodel->getMatrix($mid);
        $this->view->detail = $matrix->matrix[$rid][$cid][$sid]; 

        $this->view->status_id = $this->view->detail->result->status;
        $this->view->status = $matrix->statusLabels[$this->view->status_id];
    }
}
