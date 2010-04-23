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

class RgbdiiController extends RgController
{
    public static function default_title() { return "BDII Information"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new BDII();
        $bdii_rgs = $model->get();

        $model = new ResourceGroup();
        $oim_rgs = $model->getindex();

        $this->view->rgs = array();
        foreach($this->rgs as $rgid=>$rg) {
            //filter ones that passed mysql query for resource group
            if(isset($bdii_rgs[$rgid])) {
                $bdii_rg = $bdii_rgs[$rgid];
                $rg_view = array();

                //has resources information?
                if(isset($bdii_rg->resources)) {
                    //for each resource..
                    foreach($bdii_rg->resources as $rid=>$bdii_r) {
                        //filter ones that passed mysql query for resource
                        if(isset($rg[$rid])) {
                            $rg_view[$rid] = $bdii_r;
                        } 
                    }
                }
                $this->view->rgs[$rgid] = array("info"=>$oim_rgs[$rgid][0], "resources"=>$rg_view);
            }
        }

        $this->setpagetitle($this->default_title());
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



