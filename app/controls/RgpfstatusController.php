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

/*

Here is the problem...

In OIM, user can register resource and each resource can have FQDN. Each resource can only have
any number of services including bandwidth and latency services. Each service can have FQDN
override. 

In the mean time, perfsonar uses hostname as unique identifier (I believe), so, if 2 different
services in OIM happens to have the same FQDN, perfsonar can't tell which is which. 

So, for the time being, let's assume that this collision never happens and we can display
correct information via MyOSG


*/

class RgpfstatusController extends RgpfController
{
    public static function default_title() { return "Perfsonar Status"; }
    public static function default_url($query) { return ""; }

    public function indexAction() {
        parent::indexAction();
        message("warning", "This is an experimental feature");        

        $this->view->rgs = $this->rgs;

        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getgroupby("id");

        $pfmodel = new Perfsonar();
        $hosts = $pfmodel->getHosts();

        $this->load_perfsonar_fqdn($this->view->rgs, array(
            config()->perfsonar_late_service_id,
            config()->perfsonar_band_service_id
        ));

        foreach($this->view->rgs as $rgid=>$resources) {
            foreach($resources as $rid=>$resource) {
                foreach($resource->services as $service) {
                    if(isset($service->perfsonar_fqdn)) {
                        $pffqdn = $service->perfsonar_fqdn;
                        if(isset($hosts[$pffqdn])) {
                            $host_id = $hosts[$pffqdn];
                            $service->perfsonar = $pfmodel->getHost($host_id);
                        } else {
                            error_log("host:$pffqdn doesn't exist in /host");
                        }
                    }
                }
            }
        }
    }
}
