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

class Facilities extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $filter_disabled = true;
        if(isset($params["filter_disabled"])) {
            $filter_disabled = $params["filter_disabled"];
        }
        if($filter_disabled) {
            $where = "where active = 1 and disable = 0";
        } else {
            $where = "where 1 = 1";
        }
        return "select * from facility $where order by name";
    }
}
