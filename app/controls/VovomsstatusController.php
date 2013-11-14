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

class VovomsstatusController extends VoController
{
    public static function default_title() { return "VOMS Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new VirtualOrganization();
        $vos = $model->getindex();

        $model = new VOMS();
        $voms = $model->get();
        $this->view->timestamp = $model->getTimestamp();
        $this->view->voms_status = array();
        foreach($this->vo_ids as $vo_id) {
            $info = $vos[$vo_id][0]; 

            //capitaliza to increase chance of VO name match (until we use OIM based vomses)
            $name = strtoupper($info->name);

            $this->view->voms_status[$vo_id] = array(
                "info"=>$info,
                "voms"=>@$voms[$name]
            );
        }

        $this->setpagetitle(self::default_title());
    }
}
