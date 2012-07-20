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

abstract class RgGratiaController extends RgController
{
    //maps between graph type and actual Gratia graph source
    abstract public function map();

    public function load()
    {
        parent::load();
        
        $model = new ResourceGroup();
        $resource_groups = $model->getindex();

        $legend = false;
        list($urlbase, $sub_title, $ylabel) = $this->map();

        //$this->load_daterangequery();

        $start_time = date("Y-m-d h:i:s", $this->view->start_time);
        $end_time = date("Y-m-d h:i:s", $this->view->end_time);

        $resource_names = array();
        foreach($this->rgs as $rgid=>$rg) {
            $resource_group = $resource_groups[$rgid][0];
        
            //add resource group name as resource name as well
            $resource_names[] = $resource_group->name;
            //add resource names (if it differes from the resource group name)
            foreach($rg as $rid=>$resource) {
                if($resource->name != $resource_group->name) {
                    $resource_names[] = $resource->name;
                }
            }
        }
        $this->view->url = $urlbase."?facility=".implode("|",$resource_names)."&title=&ylabel=$ylabel&starttime=$start_time&endtime=$end_time";
        if(!$legend) {
            $this->view->url .= "&legend=False";
        }
        $this->view->resource_names = implode(" / ", $resource_names);
        $this->setpagetitle($this->default_title()." <small>".$sub_title."</small>");
    }
}

