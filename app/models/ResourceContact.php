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

class ResourceContact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $where = "WHERE resource_id = ".$params["resource_id"];
        }
        
        //WARNING - if there are multiple DNs for a contact_id, it will list duplicate records for each DN
        $sql = "SELECT dn.dn_string as dn,  rc.*, c.*, t.name as contact_type, r.name as rank_type ".
                "FROM resource_contact rc ".
                "JOIN contact c ON rc.contact_id = c.id ".
                "JOIN contact_type t ON rc.contact_type_id = t.id ".
                "JOIN contact_rank r ON rc.contact_rank_id = r.id ".
                "LEFT JOIN dn ON dn.contact_id = c.id ".
                $where;
        slog($sql);
        return $sql;
    }
    public function key() { return "resource_id"; }
}
