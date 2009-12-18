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


class Status extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        return "select * from metric_status";
    }
    public function key() { return "id"; }

    static public function getStatus($status_id) {
        switch($status_id) {
        //TODO -- pull from MetricStatus model
        case 1: return "OK";
        case 2: return "WARNING";
        case 3: return "CRITICAL";
        case 4: return "UNKNOWN";

        case 99: return "DOWNTIME";
        }
        return "(unknow:$status_id)";
    }
}


?>
