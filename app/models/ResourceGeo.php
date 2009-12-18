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

class ResourceGeo
{
    public function fetchAll()
    {
        $schema = config()->db_oim_schema;
        $sql = "SELECT r.resource_id, rrg.resource_group_id, s.* from (($schema.resource r join $schema.resource_resource_group rrg on r.resource_id = rrg.resource_id) join $schema.resource_group rg on rrg.resource_group_id = rg.resource_group_id) join $schema.site s on rg.site_id = s.site_id order by site_id";

        return db()->fetchAll($sql);
    }
}
