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

class RgarhistoryController extends RgController
{
    public static function default_title() { return "Availability History"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs; 

        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getindex();
        $service_type_model = new Service();
        $this->view->service_info = $service_type_model->getindex();

        ///////////////////////////////////////////////////////////////////////
        // Load graph inforamtion
        $this->view->services = array();
        foreach($this->rgs as $rgid=>$rg) {
            foreach($rg as $rid=>$resource) {
                //pull A&R history
                $model = new ServiceAR();
                $params["start_time"] = $this->view->start_time;
                $params["end_time"] = $this->view->end_time;
                $params["resource_id"] = $rid;
                $this->view->services[$rid] = $model->getgroupby("service_id", $params);
            }
        }
        $this->setpagetitle(self::default_title());
    }
}
