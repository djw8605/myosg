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

        $model = new ResourceGroup();
        $resource_groups = $model->getindex();
        
        //merge those xmls
        $this->view->resource_groups = array();
        $this->view->resource_details = array();
        foreach($this->rgs as $rgid=>$resources) {
            $tests = array();
            $resource_group = $resource_groups[$rgid][0];

            //if not found use following defaults
            $testtime = null;
            $overallstatus = "NA";

            $found = false;
            //search for gip status for this resource name (by resource GROUP name!)
            foreach($gip->Resource as $resource) {
                if($resource_group->name == $resource->Name) {
                    foreach($resource->TestCase as $test) {
                        $tests[(string)$test->Name] = array("status"=>(string)$test->Status, "reason"=>(string)$test->Reason);
                    }
                    $found = true;
                    $testtime = strtotime((string)$gip->TestRunTime);
                    $overallstatus = $resource->OverAllStatus;
                    break;
                }
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
                "testtime"=>$testtime,
                "name"=>$resource_group->name, 
                "gridtype"=>$resource_group->grid_type_description,
                "overallstatus"=>$overallstatus,
                "resources"=>$resources,
                "tests"=>$tests
            );
        }
        //dlog($this->view->resource_details);
        $this->setpagetitle(self::default_title());
    }

    public function detailAction()
    {
        $rid = (int)$_REQUEST["rid"];
        $model = new ResourceGroup();
        $resources = $model->getindex();
        $resource_info = @$resources[$rid][0];

        if($resource_info === null) {
            echo "no such resource";
            $this->render("none", null, true);
        } else {
            $resource_name = $resource_info->name;
        
            //try to find the detail in prod directory first
            $xmlname = config()->gip_detail;
            $xmlname = str_replace("<resource_name>", $resource_name, $xmlname);
            if(!file_exists($xmlname)) {
                //try the itb directory
                $xmlname = config()->gip_detail_itb;
                $xmlname = str_replace("<resource_name>", $resource_name, $xmlname);
            }
            $cache_xml = file_get_contents($xmlname);
            $cache = new SimpleXMLElement($cache_xml);

            $dirty_name = $_REQUEST["name"];
            //validate dirty_name
            switch($dirty_name) {
            case "Validate_GIP_BDII":
            case "Missing_Sites":
            case "Interop_Reporting_Check":
            case "Validate_GIP_URL":
                $name = $dirty_name;
            }
            $case = $cache->xpath("//TestCase/Name[.='$name']/parent::*");
            $this->view->detail = $case[0];
        }
    }
}
