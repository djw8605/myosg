<?php
/*#################################################################################################

Copyright 2011 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class RgeventController extends RgController
{
    public static function default_title() { return "Realtime Resource Events"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs;

        $model = new ResourceGroup();
        $this->view->resource_groups = $model->getindex();

        //load current status cache for all requested resources
        $this->view->cache = array();
        /*
        foreach($this->view->rgs as $rgid=>$rg) {
            foreach($rg as $rid=>$resource) {
                $cache_filename_template = config()->current_resource_status_xml_cache;
                $cache_filename = str_replace("<ResourceID>", $rid, $cache_filename_template); 
                if(file_exists($cache_filename)) {
                    $cache_xml = file_get_contents($cache_filename);
                    $this->view->cache[$rid] = new SimpleXMLElement($cache_xml);
                }
            }
        }
        */
        $this->view->cache[383] = array("event1"=>array("key1"=>"value1", "key2"=>"value2"));
    }
}
