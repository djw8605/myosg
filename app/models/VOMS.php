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

class VOMS
{
    var $timestamp = null;

    //returns array of VOs containing VOMSStatus
    public function get()
    {
        $voms_info = array();

        $cache_xml = file_get_contents(config()->voms_xml);
        $xml = new SimpleXMLElement($cache_xml);
        $vos = $xml->VOs[0];
        $this->timestamp = (int)$xml->Timestamp;
        foreach($vos as $vo) {
            $name = (string)$vo->Name[0];

            //workaround until vomses files will use OIM based name
            $name = strtoupper($name);
            $stat = $vo->VOMSStatus;
            $voms_info[$name] = $stat;
        }
        return $voms_info;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }
}
