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

class RggipstatusController extends RgController
{
    public static function default_title() { return "Current GIP Validation Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        
        $model = new LDIF();
        $gip = $model->getValidationSummary();
        $cemonbdii = $model->getBdii();

        $wlcgstatus = array();
        if(isset($_REQUEST["gip_status_attrs_showwlcgstatus"])) {
            $wlcgstatus_raw = $model->getWLCGStatus();
            $this->view->wlcgstatus_updatetime = $wlcgstatus_raw["updatetime"];
            $wlcgstatus = array();
            foreach($wlcgstatus_raw as $rgid=>$status_lists) {
                //don't process if it's not requested
                if(!isset($this->rgs[$rgid])) continue;

                $group = array();
                foreach($status_lists as $hostname=>$statuses) {
                    $group_status = "OK";
                    foreach($statuses as $status) {
                        if($status->Status != "OK") {
                            $group_status = "CRITICAL";
                        }
                    }
                    $group[$hostname] = array($group_status, $statuses);
                }
                $wlcgstatus[$rgid] = $group;
            }
        }

        $model = new ResourceGroup();
        $resource_groups = $model->getindex();
        
        //merge those xmls
        $this->view->resource_groups = array();
        $this->view->resource_details = array();
        foreach($this->rgs as $rgid=>$resources) {
            $tests = array();
            $resource_group = $resource_groups[$rgid][0];
            $gipstatus = null;
            if(isset($gip[$resource_group->name])) {
                $gipstatus = $gip[$resource_group->name];
            }

            if(isset($_REQUEST["gip_status_attrs_showresource"])) { 
                //gather resource details
                foreach($resources as $rid=>$resource) {
                    $details = array();
                    
                    if(isset($_REQUEST["gip_status_attrs_showcemondata"])) { 
                        //search cemon bdii data
                        $rawdata = array();
                        foreach($cemonbdii->resource as $cemon_resource) {
                            if($cemon_resource->name == $resource->name) {
                                $details["cemon_raw_data"] = $cemon_resource;
                                break;
                            }
                        }
                    }

                    //TODO - add code to gather more resource details here

                    $this->view->resource_details[$rid] = $details;
                }
            }

            //put everything together
            $this->view->resource_groups[$rgid] = array(
                "name"=>$resource_group->name, 
                "gridtype"=>$resource_group->grid_type,
                "resources"=>$resources,
                "gipstatus"=>$gipstatus,
                "wlcgstatus"=>@$wlcgstatus[$rgid]
            );
        }
        $this->setpagetitle(self::default_title());
    }
}
