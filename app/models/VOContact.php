<?php
/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class VOContact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["vo_id"])) {
            $where = "AND vo_id = ".$params["vo_id"];
        }

        $sql = "SELECT dn.dn_string as dn, rc.*, c.*, t.name as contact_type, r.name as rank_type from vo_contact rc ".
                "join contact c on rc.contact_id = c.id ".
                "LEFT join dn on dn.contact_id = c.id ".
                "join contact_type t on rc.contact_type_id = t.id ".
                "join contact_rank r on rc.contact_rank_id = r.id WHERE dn.disable = 0 $where";
        return $sql;
    }
    public function key() { return "vo_id"; }
}
