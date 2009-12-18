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

class LatestResourceStatus extends CachedModel
{
    public function ds() { return "rsv"; }
    public function sql($params) {

        $end_time = time();

        $sql = "select a.* from statuschange_resource a, (SELECT resource_id, max(timestamp) timestamp FROM statuschange_resource where timestamp < $end_time group by resource_id) b where a.resource_id = b.resource_id and a.timestamp = b.timestamp";
        return $sql;
    }

}
