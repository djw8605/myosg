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

class RgpfController extends RgController
{
    //set perfsonar_fqdn on all resource that has perfsonar service
    function load_perfsonar_fqdn($rgs, $perfsonar_service_ids) {
        $detail_model = new ResourceServiceDetail();
        $resource_service_details = $detail_model->getindex();
        $resourceservice_model = new ServiceByResourceID();
        $resource_services = $resourceservice_model->getindex();
        //$service_ids = array(config()->perfsonar_band_service_id, config()->perfsonar_late_service_id));

        foreach($rgs as $rgid=>$resources) {
            foreach($resources as $rid=>$resource) {
                //get fqdn info
                $resource_fqdn = $resource->fqdn;

                //find resource services
                $services = $resource_services[$rid];
                $resource->services = $services;
                foreach($services as $service) {
                    if(in_array($service->service_id, $perfsonar_service_ids)) {
                        //found target service

                        //override with service detail (if given)
                        if(isset($resource_service_details[$rid][$service->service_id])) {
                            $details = $resource_service_details[$rid][$service->service_id];
                            if($details["endpoint"] != "") {
                                $resource_fqdn = $details["endpoint"];
                            }
                            $service->details = $details;
                        }
                        $resource->service_detail[$service->service_id] = $service;
                        $service->perfsonar_fqdn = $this->clean_perfsonar_fqdn($resource_fqdn);
                        //break;
                    }
                }
            }
        }
    }

    public function clean_perfsonar_fqdn($fqdn) {
        //clean up the fqdn (strip https:// and /toolkit people adds.)
        $pos = strpos($fqdn, "//");
        if($pos !== false) {
            $fqdn = substr($fqdn, $pos+2);
        }
        $pos = strpos($fqdn, "/");
        if($pos !== false) {
            $fqdn = substr($fqdn, 0, $pos);
        }
        return $fqdn;
    }
}
