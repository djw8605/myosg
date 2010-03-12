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

class RgbdiitreeController extends RgController
{
    public static function default_title() { return "BDII Treemap"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $dirty_type = @$_REQUEST["bdiitree_type"];
        switch($dirty_type)
        {
        default:
        case "total_jobs":
            $sub_title = "Number of Jobs";
            $key = "TotalJobs";
            break;
        case "free_cpus":
            $sub_title = "Number of Free CPUs";
            $key = "FreeCPUs";
            break;
        case "estimated_response_time":
            $sub_title = "Estimated Response Time";
            $key = "EstimatedResponseTime";
            break;
        case "waiting_jobs":
            $sub_title = "Number of Waiting Jobs";
            $key = "WaitingJobs";
            break;
        case "running_jobs":
            $sub_title = "Number of Running Jobs";
            $key = "RunningJobs";
            break;
        case "free_job_slots":
            $sub_title = "Number of Free Job Slots";
            $key = "FreeJobSlots";
            break;
        }

        $model = new BDII();
        $rgs = $model->get();

        $model = new ResourceGroup();
        $oim_rgs = $model->getindex();

        $this->view->total_area = 0;
        $this->view->key = $key;
        $this->view->sub_title = $sub_title;
        $this->view->rgs = array();
        foreach($this->rgs as $rgid=>$rg) {
            //filter ones that passed mysql query for resource group
            if(isset($rgs[$rgid])) {
                $bdii_rg = $rgs[$rgid];
                $rg_view = array();

                //has resources information?
                if(isset($bdii_rg->resources)) {
                    //for each resource..
                    foreach($bdii_rg->resources as $rid=>$resource) {
                        //filter ones that passed mysql query for resource
                        if(isset($rg[$rid])) {
                            //aggregate data for each services
                            $agg = new Aggregator();
                            foreach($resource["services"] as $service) {
                                $service = $service->Service;
                                $servicename = $service->ServiceName;
                                //for each job manager entries...
                                foreach($service->JobManagers as $jobmanager) {
                                    $jobmanager = $jobmanager->JobManager;
                                    $serviceuri = $jobmanager->ServiceUri;
                                    $glueinfo = $jobmanager->GlueInfo;
                                    $agg->sum("TotalJobs", $glueinfo->GlueCEStateTotalJobs);
                                    $agg->sum("FreeCPUs", $glueinfo->GlueCEStateFreeCPUs);
                                    $agg->sum("EstimatedResponseTime", $glueinfo->GlueCEStateEstimatedResponseTime);
                                    $agg->sum("WaitingJobs", $glueinfo->GlueCEStateWaitingJobs);
                                    $agg->sum("RunningJobs", $glueinfo->GlueCEStateRunningJobs);
                                    $agg->sum("FreeJobSlots", $glueinfo->GlueCEStateFreeJobSlots);
                                }
                            }
                            $rg_view[$rid] = array("info"=>$rg[$rid], "agg"=>$agg);
                            $this->view->total_area += $agg->get($key);
                        } 
                    }
                }
                $this->view->rgs[$rgid] = array("info"=>$oim_rgs[$rgid][0], "resources"=>$rg_view);

/*
                $bdii = $rg->bdii;
                $this->view->rgs[$rgid] = $bdii;
                foreach($bdii->aggregates as $aggregate) {
                }
*/
            } else {
                elog("Can't find information for resource group $rgid");
            }
        }

        $this->setpagetitle($this->default_title()." - ".$sub_title);
    }
}

class Aggregator {
    var $info = array();
    public function sum($key, $value) {
        $value = (int)$value;
        if(isset($this->info[$key])) {
            $this->info[$key] = $this->info[$key] + $value;
        } else {
            $this->info[$key] = $value;
        }
    }
    public function get($key) { return $this->info[$key]; }
}



