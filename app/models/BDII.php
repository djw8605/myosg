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

class BDII
{
    //return array of resource groups with bdii (if available) information grouped by resource group ID
    public function get()
    {
        $voms_info = array();

        $bdii_xml = file_get_contents(config()->bdii_xml);
        $bdiis = new SimpleXMLElement($bdii_xml);

        //match RG names with OIM RG name
        $model = new ResourceGroup();
        $rgs = $model->getindex();

        $ret = array();
        foreach($rgs as $rg) {
            $rg = $rg[0];
            $name = $rg->name;
            $id = $rg->id;
            foreach($bdiis as $bdii) {
                //var_dump((string)$bdii->GroupName);
                $group_name = (string)$bdii->GroupName;
                if($name == $group_name) {
                    $rg->bdii = $bdii;
                }
            }
            $ret[$id] = $rg;
        }

        return $ret;
    }
}