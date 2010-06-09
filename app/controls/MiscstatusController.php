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

class MiscstatusController extends MiscController
{
    public static function default_title() { return "Operations Status Overview"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();

        $this->view->statuses = array(
            "Critical"=>array(
                $this->getStatus(247, "https://twiki.grid.iu.edu/bin/view/Operations/BDIIServiceLevelAgreement"), //BDII
                $this->getStatus(246, "https://twiki.grid.iu.edu/bin/view/Operations/MyOSGServiceLevelAgreement")  //MyOSG
            ),
            "High"=>array(
                array("name"=>"CA Distribution", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/SoftwareCacheServiceLevelAgreement"),
                $this->getStatus(250, "https://twiki.grid.iu.edu/bin/view/Operations/RSVServiceLevelAgreement"), //RSV collector
                array("name"=>"Resource Selection Service", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements")
            ),
            "Normal"=>array(
                $this->getStatus(266, "https://twiki.grid.iu.edu/bin/view/Operations/OIMServiceLevelAgreement"), //OIM
                $this->getStatus(261, "https://twiki.grid.iu.edu/bin/view/Operations/OSGDisplayServiceLevelAgreement"), //display.grid
                $this->getStatus(255, "https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements"), //Gratia collector
                $this->getStatus(265, "https://twiki.grid.iu.edu/bin/view/Operations/GOCTicketServiceLevelAgreement"), //GOC Ticket
                array("name"=>"WLCG Comparison Reports", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/RSVReportsServiceLevelAgreement"),
                array("name"=>"Integration BDII", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements"),
                array("name"=>"GOC Footprints Ticketing", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements"),
                $this->getStatus(197, "https://twiki.grid.iu.edu/bin/view/Operations/TWikiServiceLevelAgreement"), //Twiki
                array("name"=>"Document Repository", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/OSGDocRepoServiceLevelAgreement"),
                array("name"=>"OSG Web Pages", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/OSGWebPageServiceLevelAgreement"),
            )
        );
        $this->setpagetitle(self::default_title());
    }

    private function getStatus($gid, $url) {
        if(!isset($this->resource_groups)) {
            $model = new ResourceGroup();
            $this->resource_groups = $model->getgroupby("id");
        }
        $rginfo = $this->resource_groups[$gid][0];

        if(!isset($this->latest_resource_status)) {
            $model = new LatestResourceStatus();
            $this->latest_resource_status = $model->getgroupby("resource_id");
            $model = new Resource();
            $this->resources_by_resource_group = $model->getgroupby("resource_group_id");
        } 

        //calculate service status (the same standard algorithm)
        $warning = 0;
        $critical = 0;
        $unknown = 0;
        $resources = $this->resources_by_resource_group[$gid];
        foreach($resources as $resource) {
            if(isset($this->latest_resource_status[$resource->id])) {
                $status = $this->latest_resource_status[$resource->id][0];
                switch((int)$status->status_id) {
                case 2: $warning++; break;
                case 3: $critical++; break;
                case 4: $unknown++; break;
                }
            } else {
                $unknown++;
            }
        }
        $rgstatus = "OK";
        if($critical > 1) {
            $rgstatus = "CRITICAL";
        } else if($warning > 1) {
            $rgstatus = "WARNING";
        } else if($unknown > 1) {
            $rgstatus = "UNKNOWN";
        }

        return array(
            "name"=>$rginfo->name,
            "description"=>$rginfo->description,
            "status"=>$rgstatus,
            "resource_group_id"=>$rginfo->id,
            "url"=>$url
        );
    }
}
