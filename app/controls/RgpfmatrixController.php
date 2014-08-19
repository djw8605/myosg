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

class RgpfmatrixController extends RgpfController
{
    public static function default_title() { return "Perfsonar Full OSG Matrix"; }
    public static function default_url($query) { return ""; }

    public function indexAction() {
        parent::indexAction();
        message("warning", "This page will soon be deprecated by maddash");        

        $this->view->rgs = $this->rgs;

        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getgroupby("id");

        $pfmodel = new Perfsonar();
        if(isset($_REQUEST["summary_attrs_showpflatematrix"])) {
            $matrix = $pfmodel->getMatrix("vo_all/perfsonarbuoy/owamp");
            $this->view->late_service_names = $matrix->serviceNames;
            $this->view->late_status_labels = $matrix->statusLabels; //massage detail a bit
            $this->view->late_matrix = $this->process_matrix($matrix, config()->perfsonar_late_service_id);
        } 
        if(isset($_REQUEST["summary_attrs_showpfbandmatrix"])) {
            $matrix = $pfmodel->getMatrix("vo_all/perfsonarbuoy/bwctl");
            $this->view->band_service_names = $matrix->serviceNames;
            $this->view->band_status_labels = $matrix->statusLabels; //massage detail a bit
            $this->view->band_matrix = $this->process_matrix($matrix, config()->perfsonar_band_service_id);
        } 
    }

    function process_matrix($matrix, $pf_service_id) {
        //$detail_model = new ResourceServiceDetail();
        //$resource_service_details = $detail_model->getindex();
        //$resourceservice_model = new ServiceByResourceID();
        //$resource_services = $resourceservice_model->getindex();

        //construct rows
        $matrix_rows = array();
        foreach($matrix->rows as $item) {
            $matrix_rows[] = $item->hostname;
        }

        $matrix_view = array();
        $this->load_perfsonar_fqdn($this->view->rgs, array($pf_service_id));
        foreach($this->view->rgs as $rgid=>$resources) {
            foreach($resources as $rid=>$resource) {
                foreach($resource->services as $service) {
                    if(isset($service->perfsonar_fqdn)) {
                        $pffqdn = $service->perfsonar_fqdn;

                        //lookup matrix row index
                        $row_idx = array_search($pffqdn, $matrix_rows);
                        if($row_idx !== false) {
                            //pull colunmn from matrix
                            $cols = array();
                            foreach($matrix->matrix[$row_idx] as $col_idx=>$col) {
                                $col_fqdn = $matrix_rows[$col_idx];//TODO - should I create matrix_column?
                                $cols[$col_fqdn] = array();
                                foreach($col as $service_id => $detail) {
                                    //error_log(print_r($detail, true)); exit;
                                    if(!isset($detail->result)) continue;
                                    $status = $detail->result->status;
                                    $label = $matrix->statusLabels[$status];
                                    $detail->result->status_label = $label;
                                    $detail->cellid = $matrix->id.":$row_idx:$col_idx";

                                    $cols[$col_fqdn][] = $detail;
                                }
                            }
                            $matrix_view[$resource->id] = $cols; //I am guessing this won't override other service info since we create different matrix_view for each service?
                        } else {
                            //no perfsonar info for this resource
                            error_log("no perfsonar data found in matrix:$matrix->id for service: $pf_service_id on resource:".$resource->id." [".$pffqdn."]");
                        }
                    }
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
        $matrix = $pfmodel->getMatrixByDatastoreID($mid);
        $this->view->detail = $matrix->matrix[$rid][$cid][$sid]; 

        $this->view->status_id = $this->view->detail->result->status;
        $this->view->status = $matrix->statusLabels[$this->view->status_id];
    }
}
