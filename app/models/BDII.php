<?php
/**************************************************************************************************

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

class BDII
{
    //return array of resource groups with bdii (if available) information grouped by resource group ID
    public function get()
    {
        $voms_info = array();

        $bdii_xml = file_get_contents(config()->bdii_xml);
        $bdiis = new SimpleXMLElement($bdii_xml);

        //match RG names with OIM RG name
        $model = new ResourceGroup();
        $rgs = $model->getindex();

        $ret = array();
        foreach($rgs as $rg) {
            $rg = $rg[0];
            $name = $rg->name;
            $id = $rg->id;
            foreach($bdiis as $bdii) {
                $group_name = (string)$bdii->GroupName;
                if($name == $group_name) {
                    $rg->bdii = $this->processXML($bdii);
                    $rg->bdii->basic = $rg;
                }
            }
            $ret[$id] = $rg;
        }

        return $ret;
    }

    //process raw bdii XML information so that MyOSG can understand
    public function processXML($bdii) {
        $info = new BDII_structure();

/*
        echo "<pre>";
        var_dump($bdii);
        echo "</pre>";
*/

        foreach($bdii->Resources as $resource) {
            $resource = $resource->Resource;
            $fqdn = $resource->FQDN;

            //aggregate data for each resource
            $agg = new Aggregator();
            foreach($resource->Services as $service) {
                $service = $service->Service;
                $servicename = $service->ServiceName;
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
            $info->aggregates[(string)$resource->FQDN] = $agg;
        }

        return $info;
    }
}

class BDII_structure {
    var $aggregates = array();
    var $basic = null;
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
