<?php
/**************************************************************************************************

Copyright 2013 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

class Project extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "SELECT p.*, c.name as pi_name, c.primary_email as pi_email, vo.name as vo_name, campusgrid.name as cg_name, fos.name as fos_name ".
        "FROM project p JOIN contact c ON c.id = p.pi_contact_id ".
        "LEFT JOIN vo ON vo.id = p.vo_id ".
        "LEFT JOIN campusgrid ON campusgrid.id = p.cg_id ".
        "JOIN field_of_science fos ON fos.id = p.fos_id ".
        "WHERE p.disable = 0 ".
        "ORDER BY p.name";
    }
    public function key() { return "id"; }
}
