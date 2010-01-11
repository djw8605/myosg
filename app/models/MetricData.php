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

class MetricData extends Model
{
    public function ds() { return "rsv"; }
    public function sql($params)
    {
        $where_timestamp = "";
        if(isset($params["time"])) {
            $time = $params["time"];
            $where_timestamp = " and timestamp <= " . $time . " ";
        }
        $resource_id = $params["resource_id"];
        $sql = "select * from metricdata m, ".
            "(select max(timestamp) last_timestamp ,metric_id from metricdata ".
            "where resource_id = ". $resource_id . " " . $where_timestamp .
            "group by metric_id) last ".
            "where m.timestamp = last.last_timestamp and m.metric_id = last.metric_id and m.resource_id = $resource_id ".
            "order by timestamp";
        return $sql;
    }
    static public function isFresh($metric_timestamp, $metric_freshfor, $at) {
        if($at < ($metric_timestamp + $metric_freshfor)) {
            return true;
        }
        return false;
    }
}

