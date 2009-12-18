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

class WizardcurrentstatusController extends WizardController
{
    public function breads() { return array("wizard"); }
    public static function default_title() { return "Current RSV Status"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $model = new Resource();

        $this->view->cache = array();
        $this->view->resources = array();

        //load current status cache for all requested resource_ids
        foreach($this->resource_ids as $rid) {
            $cache_filename_template = config()->current_resource_status_xml_cache;
            $cache_filename = str_replace("<ResourceID>", $rid, $cache_filename_template); 
            if(file_exists($cache_filename)) {
                $cache_xml = file_get_contents($cache_filename);
                $this->view->cache[$rid] = new SimpleXMLElement($cache_xml);
                $recs = $model->get(array("resource_id"=>$rid));
                $this->view->resources[$rid] = $recs[0];
            }
        }
    }
}
