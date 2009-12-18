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

class ServiceStatusChange extends Model
{
    public function ds() { return "rsv"; }
    public function sql($params) {

        $resource_id = $params["resource_id"];
        $service_id = $params["service_id"];
        $start_time = $params["start_time"];
        $end_time = $params["end_time"];
        
        $sql = "select * from statuschange_service where service_id = $service_id and resource_id = $resource_id and timestamp >= coalesce((select max(timestamp) from statuschange_service where service_id = $service_id and resource_id = $resource_id and timestamp <= $start_time), 0) and timestamp <= $end_time order by timestamp;"; 
        return $sql;
    }
}
