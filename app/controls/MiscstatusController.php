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

    private $downtimes;

    public function load()
    {
        parent::load();

        $downtime_model = new Downtime();
        $this->downtimes = $downtime_model->getindex(array("start_time"=>time(), "end_time"=>time()));

        $this->view->statuses = array(
            "Critical"=>array(
                $this->getStatus(247, "https://twiki.grid.iu.edu/bin/view/Operations/BDIIServiceLevelAgreement"), //BDII
                $this->getStatus(246, "https://twiki.grid.iu.edu/bin/view/Operations/MyOSGServiceLevelAgreement")  //MyOSG
            ),
            "High"=>array(

                $this->getStatus(270, "https://twiki.grid.iu.edu/bin/view/Operations/SoftwareCacheServiceLevelAgreement"), //GOC_Software Cache
                $this->getStatus(250, "https://twiki.grid.iu.edu/bin/view/Operations/RSVServiceLevelAgreement"), //RSV collector
                $this->getStatus(233, "https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements"), //RESS
            ),
            "Normal"=>array(
                $this->getStatus(266, "https://twiki.grid.iu.edu/bin/view/Operations/OIMServiceLevelAgreement"), //OIM
                $this->getStatus(261, "https://twiki.grid.iu.edu/bin/view/Operations/OSGDisplayServiceLevelAgreement"), //display.grid
                $this->getStatus(255, "https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements"), //Gratia collector
                $this->getStatus(265, "https://twiki.grid.iu.edu/bin/view/Operations/GOCTicketServiceLevelAgreement"), //GOC Ticket
                //array("name"=>"WLCG Comparison Reports", "status"=>"UNKNOWN", "url"=>"https://twiki.grid.iu.edu/bin/view/Operations/RSVReportsServiceLevelAgreement"),
                $this->getStatus(256, "https://twiki.grid.iu.edu/bin/view/Operations/ServiceLevelAgreements"), //BDII ITB
                $this->getStatus(197, "https://twiki.grid.iu.edu/bin/view/Operations/TWikiServiceLevelAgreement"), //Twiki
                $this->getStatus(280, "https://twiki.grid.iu.edu/bin/view/Operations/OSGDocRepoServiceLevelAgreement"), //DOCDB
                $this->getStatus(281, "https://twiki.grid.iu.edu/bin/view/Operations/OSGWebPageServiceLevelAgreement"), //OSG Homepage
                $this->getStatus(308, "https://twiki.grid.iu.edu/bin/view/Operations/GlideInWMSServiceLevelAgreement") //GOC Glidein
                $this->getStatus(325, "https://twiki.grid.iu.edu/bin/view/Operations/SoftwareRepoServiceLevelAgreement") //RPM repo
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
        $downtime = 0;
        $resources = $this->resources_by_resource_group[$gid];

        $count = 0;
        foreach($resources as $resource) {
            if($resource->active == 0) continue; //filter by deactivated resource
            $count++;

            //is this resource under downtime?
            if(isset($this->downtimes[$resource->id])) {
                $downtime++;
                continue;
            }

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

        $rgstatus = "UNKNOWN";
        if($count > 0) {
            if($critical > 0) {
                $rgstatus = "CRITICAL";
            } else if($warning > 0) {
                $rgstatus = "WARNING";
            } else if($unknown > 0) {
                $rgstatus = "UNKNOWN";
            } else if($downtime > 0) {
                $rgstatus = "DOWNTIME";
            } else {
                $rgstatus = "OK";
            }
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
