<?php
/*#################################################################################################

Copyright 2014 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class RgsummaryController extends RgController
{
    public static function default_title() { return "Resource Group Summary"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs;

        //pull commonly needed information
        $gridtype_model = new GridTypes();
        $this->view->gridtypes = $gridtype_model->getindex();
        $model = new ResourceGroup();
        $this->view->resourcegroups = $model->getgroupby("id");

        ///////////////////////////////////////////////////////////////////////////////////////////
        //pull other optional stuff
        if(isset($_REQUEST["summary_attrs_showservice"])) {
            $servicetype_model = new Service();
            $this->view->servicetypes = $servicetype_model->getindex();
            $resourceservice_model = new ServiceByResourceID();
            $this->view->resource_services = $resourceservice_model->getindex();

            //load details (all of them for now..) and attach it to resource_services
            $detail_model = new ResourceServiceDetail();
            $resource_service_details = $detail_model->get();
            foreach($this->view->resource_services as $rid=>$services) {
                foreach($services as $service) {
                    $service->details = array();
                    //search for details for this service
                    foreach($resource_service_details as $detail) {
                        if($detail->resource_id == $rid && $detail->service_id == $service->service_id) {
                            $service->details[$detail->key] = $detail->value;
                        }
                    }
                }
            }
        }
        if(isset($_REQUEST["summary_attrs_showrsvstatus"])) {
            $model = new LatestResourceStatus();
            $this->view->resource_status = $model->getgroupby("resource_id");
            $downtime_model = new Downtime();
            $this->view->downtime = $downtime_model->getindex(array("start_time"=>time(), "end_time"=>time()));
        }
        if(isset($_REQUEST["summary_attrs_showgipstatus"])) {
            $model = new LDIF();
            $this->view->gipstatus = $model->getValidationSummary();
        }
        if(isset($_REQUEST["summary_attrs_showvomembership"])) {
            $cache_filename = config()->vomatrix_xml_cache;
            $cache_xml = file_get_contents($cache_filename);
            $vocache = new SimpleXMLElement($cache_xml);
            $resourcegrouped = $vocache->ResourceGrouped[0];

            $this->view->vos_supported = array();
            $this->view->vos_errors = array();
            foreach($resourcegrouped as $resource) {
                $attr = $resource->attributes();
                $resource_id = (int)$attr->id;
                $this->view->vos_supported[$resource_id] = $resource->Members[0];
                $this->view->vos_errors[$resource_id] = $resource->ErrorMessage[0];
            }
        }
        if(isset($_REQUEST["summary_attrs_showvoownership"])) {
            $model = new ResourceOwnership();
            $this->view->resource_ownerships = $model->getindex();
        }
        if(isset($_REQUEST["summary_attrs_showwlcg"])) {
            $model = new ResourceWLCG();
            $this->view->resource_wlcg = $model->getindex();

            //append bdii link
            foreach($this->rgs as $rg_id=>$rg) {
                foreach($rg as $resource_id=>$resource) {

                    //get resource group name
                    $rgroup = $this->view->resourcegroups[$rg_id][0];
                    $rgname = $rgroup->name;

                    if(isset($this->view->resource_wlcg[$resource_id][0])) { 
                        $this->view->resource_wlcg[$resource_id][0]->ldap_url = "ldap://is.grid.iu.edu:2180/mds-vo-name=$rgname,o=grid";
                    }
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showenv"])) {
            $model = new ResourceEnv();
            $details = $model->getindex(array("metric_id"=>0));

            $this->view->envs = array();
            //convert to XML String to SimpleXMLElement object
            foreach($this->rgs as $rg) {
                foreach($rg as $resource_id=>$resource) {
                    if(isset($details[$resource_id])) {
                        $rec = $details[$resource_id][0];
                        $env = null;
                        if($rec !== null) {
                            try {
                                $env = new SimpleXMLElement($rec->xml);
                            } catch (exception $e) {
                                elog((string)$e);
                                elog($rec->xml);
                            }
                        }
                        $this->view->envs[$resource_id] = $env;
                    }
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showcontact"])) {

            $model = new ResourceContact();
            $contacts = $model->getindex();

            //group by contact_type_id
            $this->view->contacts = array();
            foreach($this->rgs as $rg) {
                foreach($rg as $resource_id => $resource) {
                    $types = array();
                    if(isset($contacts[$resource_id])) {
                        foreach($contacts[$resource_id] as $contact) {
                            if(!isset($types[$contact->contact_type])) {
                                $types[$contact->contact_type] = array();
                            }
                            //group by contact name
                            $types[$contact->contact_type][] = $contact;
                        }
                        $this->view->contacts[$resource_id] = $types;
                    }
                }
            }
        }

        if(isset($_REQUEST["summary_attrs_showfqdn"])) {
            $amodel = new ResourceAlias();
            $this->view->aliases = $amodel->getindex();
        }

        if(isset($_REQUEST["summary_attrs_showhierarchy"])) {
            $this->view->hierarchy = array();
            $model = new Facilities();
            $facilities = $model->getgroupby("id", array("filter_disabled"=>false));
            $model = new Site();
            $sites = $model->getgroupby("id", array("filter_disabled"=>false));
            $this->view->sites = $sites;
            $model = new SupportCenters();
            $scs = $model->getgroupby("id");
            foreach($this->rgs as $rgid=>$rg) {
                $rginfo = $this->view->resourcegroups[$rgid][0];
                $siteinfo = $sites[$rginfo->site_id][0];
                $facilityinfo = $facilities[$siteinfo->facility_id][0];
                $scinfo = $scs[$siteinfo->sc_id][0];
                $this->view->hierarchy[$rgid] = array("facility"=>$facilityinfo, "site"=>$siteinfo, "sc"=>$scinfo);
            }
        }

        if(isset($_REQUEST["summary_attrs_showticket"])) {
            $ticketmodel = new OpenTickets();
            $this->view->tickets = $ticketmodel->getGroupByRID();
        }

        $this->setpagetitle(self::default_title());
    }
}
