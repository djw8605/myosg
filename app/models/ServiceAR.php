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

class ServiceAR extends CachedModel
{
    public function ds() { return "rsv"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $where .=  " and resource_id = ".$params["resource_id"];
        }
        /* -- use resource_ids
        if(isset($params["service_id"])) {
            $where .=  " and service_id = ".$params["service_id"];
        }
        */
        if(isset($params["start_time"])) {
            $where .=  " and timestamp >= ".$params["start_time"];
        }
        if(isset($params["end_time"])) {
            $where .=  " and timestamp <= ".$params["end_time"];
        }
	//specifying empty resource_ids will select *all*
        if(isset($params["resource_ids"]) && !empty($params["resource_ids"])) {
            $where .=  " and resource_id in (".implode(",", $params["resource_ids"]).")";
        }
        $sql = "select * from service_ar where 1 = 1 $where order by timestamp";
        return $sql;
    }
}

?>
