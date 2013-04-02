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

class ResourceServiceDetail extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
/*
        $where = "where 1 = 1";
        if(isset($params["resource_id"])) {
            $where .= " and rs.resource_id = ".$params["resource_id"];
        }
        if(isset($params["service_id"])) {
            $where .= " and rs.service_id = ".$params["service_id"];
        }
        $sql = "SELECT rs.*, s.* FROM resource_service rs join service s on rs.service_id = s.id $where";
*/
        return "SELECT * from resource_service_detail";
    }

    //return multidimentional array containing rec[$rid][$sid]
    public function getindex() {
        $recs = array();
        foreach($this->get() as $rec) {
            $rid = $rec->resource_id;
            $sid = $rec->service_id;
            if(!isset($recs[$rid])) {    
                $recs[$rid] = array(); 
            }
            if(!isset($recs[$rid][$sid])) {    
                $recs[$rid][$sid] = array(); 
            }
            $recs[$rid][$sid][$rec->key] = $rec->value;
        }
        return $recs;
    }
}
