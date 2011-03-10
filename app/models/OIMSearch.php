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

class OIMSearch
{
    public function ds() { return "oim"; }

    //make sure to valide the query before passing here!
    public function search_resource($query) {
        $recs = db("oim")->fetchAll("select *, name as v1, fqdn as v2 from resource where name like \"%$query%\" or fqdn like \"%$query%\" and disable = 0");
        return $recs; 
    }
    public function search_resourcegroup($query) {
        $recs = db("oim")->fetchAll("select *, name as v1 from resource_group where name like \"%$query%\" and disable = 0");
        return $recs; 
    }
    public function search_facility($query) {
        $recs = db("oim")->fetchAll("select *, name as v1 from facility where name like \"%$query%\" and disable = 0");
        return $recs; 
    }
    public function search_site($query) {
        $recs = db("oim")->fetchAll("select *, name as v1 from site where name like \"%$query%\" and disable = 0");
        return $recs; 
    }
    public function search_vo($query) {
        $recs = db("oim")->fetchAll("select *, name as v1, long_name as v2 from vo where (name like \"%$query%\" or long_name like \"%$query%\") and disable = 0");
        return $recs; 
    }
    public function search_sc($query) {
        $recs = db("oim")->fetchAll("select *, name as v1, long_name as v2 from sc where (name like \"%$query%\" or long_name like \"%$query%\") and disable = 0");
        return $recs; 
    }
    public function search_contact($query) {
        $recs = db("oim")->fetchAll("select *, name as v1, primary_email as v2 from contact where name like \"%$query%\" or primary_email like \"%$query%\" and disable = 0");
        return $recs; 
    }
}
