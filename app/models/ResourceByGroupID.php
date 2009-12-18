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

class ResourceByGroupID extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $service_condition = "where 1 = 1";
        if(isset($params["servicetype"])) {
            $service_condition .= " and service_id = ".$params["servicetype"];
        }

        $where = "";
        if(isset($params["resource_group_id"])) {
            $where .= " and r.resource_group_id = ".$params["resource_group_id"];
        }

        //return resource that has at least one resource_service
        $sql = "SELECT * FROM resource r where 1 = 1 $where and r.id in (select resource_id from resource_service $service_condition)";
        return $sql;
    }
    public function key() { return "resource_group_id"; }
}
