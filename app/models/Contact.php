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

class Contact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1 = 1";

        //filter by personal contact
        if(isset($params["person"])) {
            $where .= " and person = ".$params["person"];
        }
        if(isset($params["ids"])) {
            if(count($params["ids"]) > 0) {
                $where .= " and id in (";
                $first = true;
                foreach($params["ids"] as $id) {
                    if($first) {
                        $first = false;
                    } else {
                        $where .= ", ";
                    }
                    $where .= $id;
                }
                $where .= ")";
            } else {
                $where .= " and 1 = 0"; //hide everything..
            }
        }
        return "select * from contact $where ORDER BY name";
    }
    public function key() { return "id"; }
}

?>
