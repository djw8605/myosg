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

class SupportCenterContact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1 = 1 ";
        if(isset($params["sc_id"])) {
            $where .= " and sc_id = ".$params["sc_id"];
        }
        if(isset($params["contact_type_id"])) {
            $where .= " and sc.contact_type_id = ".$params["contact_type_id"];
        }
        if(isset($params["contact_rank_id"])) {
            $where .= " and sc.contact_rank_id = ".$params["contact_rank_id"];
        }
        return "SELECT dn.dn_string as dn, sc.sc_id, sc.contact_id, p.*, t.name as contact_type, r.name as contact_rank from sc_contact sc ".
                "join contact p on sc.contact_id = p.id ".
                "LEFT join dn on dn.contact_id = p.id ".
                "join contact_type t on sc.contact_type_id = t.id ".
                "join contact_rank r on sc.contact_rank_id = r.id ".
                "$where order by sc.contact_type_id";
    }
    public function key() { return "sc_id"; }
}
