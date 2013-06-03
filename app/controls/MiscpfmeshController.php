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

class MiscpfmeshController extends MiscController
{
    public static function default_title() { return "Perfsonar Mesh Configurations"; }
    public static function default_url($query) { return ""; }

    public function indexAction() {
        parent::indexAction();
        $model = new VirtualOrganization();
        $vos = $model->getindex();

        $model = new VOOwnedResources();
        $voowners = $model->getindex();

        $resourceservice_model = new ServiceByResourceID();
        $resource_services = $resourceservice_model->getindex();

        $perfsonar_service_ids = array(
            config()->perfsonar_band_service_id,
            config()->perfsonar_late_service_id);

        //pass list of VOs that owns at least 1 resource with pf toolkit
        $this->view->vos = array();
        foreach($voowners as $void=>$voowner) {
            $found = false;
            foreach($voowner as $voorec) {
                if(isset($resource_services[$voorec->resource_id])) {
                    $services = $resource_services[$voorec->resource_id];
                    foreach($services as $service) {
                        if(in_array($service->service_id, $perfsonar_service_ids)) {
                            $found = true;
                            break;
                        }
                    }
                    if($found) break;
                }
            }
            if($found) {
                $this->view->vos[$void] = $vos[$void][0];
            }
        }
    }
}
