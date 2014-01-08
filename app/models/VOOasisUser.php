<?php
/*#################################################################################################

Copyright 2012 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class VOOasisUser extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["vo_ids"])) {
            $where = "AND vo_id IN (".implode(",",$params["vo_ids"]).")";
        }

        $sql = "SELECT dn.dn_string AS dn, rc.*, c.* FROM vo_oasis_user rc ".
                "JOIN contact c ON rc.contact_id = c.id ".
                "LEFT join dn ON dn.contact_id = c.id WHERE dn.disable = 0 and c.disable = 0 $where";
        return $sql;
    }
    public function key() { return "vo_id"; }
}
