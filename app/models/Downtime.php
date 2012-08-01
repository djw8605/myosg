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

class Downtime extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = " where disable = 0";
        if(isset($params["resource_id"])) {
            $where .= " and resource_id = ".$params["resource_id"];
        }

        if(isset($params["start_time"]) && isset($params["end_time"])) {
            //find downtimes that overwraps with given start and end time

            $start_time = $params["start_time"];
            $end_time = $params["end_time"];
            $where .= " and ( ";
            $where .= "(UNIX_TIMESTAMP(end_time) > $start_time and UNIX_TIMESTAMP(end_time) < $end_time) or";
            $where .= "(UNIX_TIMESTAMP(start_time) > $start_time and UNIX_TIMESTAMP(start_time) < $end_time) or";
            $where .= "(UNIX_TIMESTAMP(start_time) > $start_time and UNIX_TIMESTAMP(end_time) < $end_time) or";
            $where .= "(UNIX_TIMESTAMP(start_time) < $start_time and UNIX_TIMESTAMP(end_time) > $end_time)";
            $where .= " )"; 
        }
        $sql = "SELECT *, UNIX_Timestamp(timestamp) AS unix_timestamp, UNIX_Timestamp(start_time) AS unix_start_time, UNIX_Timestamp(end_time) AS unix_end_time FROM resource_downtime $where ORDER BY start_time";
        return $sql;
    }
    public function key() { return "resource_id"; }
}
