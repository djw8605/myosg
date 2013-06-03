<?php
/*#################################################################################################

Copyright 2013 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class PfmeshController extends RgpfController
{
    public static function default_title() { return "Perfsonar Mesh Config"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle($this->default_title());
        $this->selectmenu("misc");
    }

    public function jsonAction()
    {
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Step1. Load various information needed from various DBs
        //

        if(isset($_REQUEST["vo_id"])) {
            $vo_id = (int)$_REQUEST["vo_id"];

            //load vo detail
            $model = new VirtualOrganization();
            $vo = $model->get(array("vo_id"=>$vo_id));
            $vo = $vo[0];

            $mesh_desc = $vo->name;
            $mesh_id = "vo_$vo_id";

            //load all resources owned by specified vo
            $model = new VOOwnedResources();
            $vo_ros = $model->get(array("vo_id"=>$vo_id));
            $rids = array();
            foreach($vo_ros as $vo_ro) {
                $rids[] = $vo_ro->resource_id;
            } 
        } else {
            $mesh_desc = "All OSG";
            $rids = null;
            $mesh_id = "vo_all";
        }
        //error_log(print_r($rids, true));

        $mesh_admins = array(   
            array("email"=>"hayashis@iu.edu", "name"=>"Soichi Hayashi"), 
            array("email"=>"goc@opensciencegrid.org", "name"=>"OSG Grid Operations")
        );

        //for each resource, look for perfsonar services
        $model = new ServiceByResourceID();
        $rss = $model->getIndex(array("resource_ids"=>$rids));
        //error_log(print_r($rss, true));
        
        $pf_services =  array(config()->perfsonar_late_service_id, config()->perfsonar_band_service_id);
        $all_rids = array();
        $late_rids = array();//stores all resource ids that we should pull site information
        $band_rids = array();//stores all resource ids that we should pull site information
        foreach($rss as $resource_id=>$services) {
            foreach($services as $service) {
                if(in_array($resource_id, $all_rids)) continue;
                if($service->service_id == config()->perfsonar_late_service_id) {
                    $all_rids[] = $resource_id;
                    $late_rids[] = $resource_id;
                } else if($service->service_id == config()->perfsonar_band_service_id) {
                    $all_rids[] = $resource_id;
                    $band_rids[] = $resource_id;
                }
            }
        }

        //load all resource details
        $model = new ResourceServiceDetail();
        $resource_details = $model->getIndex(array("resource_ids"=>$all_rids)); 

        //lookup all relevant resources
        $model = new Resource();
        $rs = $model->getIndex(array("resource_ids"=>$all_rids));

        //lookup all relevant resource groups
        $rgids = array();
        foreach($rs as $rid=>$resource) {
            $resource = $resource[0];
            //use base class function
            $rgids[] = $resource->resource_group_id;
        }

        $model = new ResourceGroup();
        $rgs = $model->getIndex(array("resource_group_ids"=>$rgids));

        //lookup all relevant sites
        $siteids = array(); 
        foreach($rgs as $rgid=>$rg) {
            $rg = $rg[0];
            $siteids[] = $rg->site_id;
        } 
        $model = new Site();
        $sites = $model->getIndex(array("site_ids"=>$siteids));

        //lookup all relevant facilities
        $facilityids = array(); 
        $scids = array(); 
        foreach($sites as $site_id=>$site) {
            $site = $site[0];
            $facilityids[] = $site->facility_id;
            $scids[] = $site->sc_id;
        } 
        $model = new Facilities();
        $facilities = $model->getIndex(array("facility_ids"=>$facilityids));
        $model = new SupportCenterContact();
        $sc_contacts = $model->getIndex(array("sc_ids"=>$scids));
        $model = new ResourceContact();
        $r_contacts = $model->getIndex(array("resource_ids"=>$all_rids));

        $model = new ResourceAlias();
        $resource_aliases = $model->getIndex();

        //mapping between oim and mesh config
        //oim/facility == organizations
        //oim/site == sites
        //oim/sc admin == site admin
        //oim/resource_groups == hosts
        //oim/resource/resource_service == measurement_archives
        //oim/resource admin == host/administrator

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Step2. Create skelton structure and put some key components
        //
        $data = array();
        foreach($all_rids as $rid) {
            $resource = $rs[$rid][0];
            $resource_group = $rgs[$resource->resource_group_id][0];
            $site = $sites[$resource_group->site_id][0];
            $facility = $facilities[$site->facility_id][0];
            if(!isset($data[$facility->id])) {
                //we don't store admin for facility, so let' use GOC
                $org_admins = array(   
                    array("email"=>"goc@opensciencegrid.org", "name"=>"OSG Grid Operations")
                );
                $data[$facility->id] = array("administrators"=>$org_admins, "sites"=>array(), "description"=>$facility->name);
            }
            if(!isset($data[$facility->id]["sites"][$site->id])) {
                $data[$facility->id]["sites"][$site->id] = array();
            }
            if(!isset($data[$facility->id]["sites"][$site->id][$resource_group->id])) {
                $data[$facility->id]["sites"][$site->id][$resource_group->id] = array();
            }
            $resource->services = array();
            foreach($rss[$rid] as $service) {
                if(isset($resource_details[$rid])) {
                    $details = $resource_details[$rid];
                } else {
                    $details = null; //some resource has no detail
                }
                $resource->services[$service->service_id] = array("service"=>$service, "details"=>$details);
            }
            $data[$facility->id]["sites"][$site->id][$resource_group->id][$rid] = $resource;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Step3.  Finally construct the json
        //
        $late_hostnames = array();
        $band_hostnames = array();
        $orgs = array();
        foreach($data as $facility_id=>$facility) {
            $org = array("administrators"=>$facility["administrators"]);
            $_sites = array();
            foreach($facility["sites"] as $site_id=>$rg) {
                $site = $sites[$site_id][0];
                $contacts = $sc_contacts[$site->sc_id];
                $site_admins = array(); 
                foreach($contacts as $c) {
                    $site_admins[$c->primary_email] = array("email"=>$c->primary_email, "name"=>$c->name);
                }

                $_hosts = array();
                foreach($rg as $rg_id=>$resources) {
                    foreach($resources as $rid=>$resource) {
                        $services = array();
                        $addresses = array($resource->fqdn);
                        if(isset($resource_aliases[$rid])) {
                            foreach($resource_aliases[$rid] as $alias) {
                                if(!in_array($hostname, $addresses)) {
                                    $addresses[] = $alias;
                                }
                            }
                        }
                        foreach($resource->services as $service_id=>$service) {
                            $hostname = $resource->fqdn;
                            if($service["details"][$service_id]["endpoint"] != "") {
                                $resource_fqdn = $service["details"][$service_id]["endpoint"];
                            }
                            $hostname = $this->clean_perfsonar_fqdn($hostname);
                            if(!in_array($hostname, $addresses)) {
                                $addresses[] = $hostname;
                            }

                            switch($service_id) {
                            case config()->perfsonar_late_service_id:
                                $services[] = array(
                                    "read_url"=>"http://$hostname:8086/perfSONAR_PS/services/traceroute_ma",
                                    "write_url"=>"http://$hostname:8086/perfSONAR_PS/services/tracerouteCollector",
                                    "type"=>"traceroute"
                                );
                                $services[] = array(
                                    "read_url"=>"http://$hostname:8085/perfSONAR_PS/services/services/pSB",
                                    "write_url"=>"$hostname:8569",
                                    "type"=>"perfsonarbuoy/owamp"
                                );
                            
                                if(!in_array($hostname, $late_hostnames)) {
                                    $late_hostnames[] = $hostname;
                                }
                                break;
                            case config()->perfsonar_band_service_id:
                                $services[] = array(
                                    "read_url"=>"http://$hostname:8085/perfSONAR_PS/services/services/pSB",
                                    "write_url"=>"$hostname:8570",
                                    "type"=>"perfsonarbuoy/bwctl"
                                );
                                if(!in_array($hostname, $band_hostnames)) {
                                    $band_hostnames[] = $hostname;
                                }
                                break;
                            default:
                                error_log("unexpected service_id $service_id while generating json content for rid:$rid");
                            }
                        }
                        $host_admins = array();
                        foreach($r_contacts[$rid] as $rc) {
                            if($rc->contact_type_id == 3) {
                                $host_admins[$rc->primary_email] = array("email"=>$rc->primary_email, "name"=>$rc->name);
                            }
                        }
                        $_hosts[] = array(
                            "administrators"=>array_values($host_admins),
                            "measurement_archives"=>$services, 
                            //"_debug"=>$service, 
                            "addresses"=>$addresses, 
                            "description"=>$resource->name);
                    }
                }

                $_sites[] = array("hosts"=>$_hosts,
                    "administrators"=>array_values($site_admins),
                    "location"=>array(
                        "longitude"=>trim($site->longitude), "latitude"=>$site->latitude, 
                        "city"=>$site->city, "state"=>$site->state, "country"=>$site->country
                    ),
                    "description"=>$site->name);
            }
            $org["sites"] = $_sites;
            $org["description"] = $facilities[$facility_id][0]->name;
            $orgs[] = $org;
        }

        //debug
        //$debug = array("vo"=>$vo, "data"=>$data, "info"=>$this->view->info);

        $tests = array();

        //latency Traceroute Test
        $tests[] = array("parameters"=>array( //TODO - move this config
                "protocol"=>"udp",
                "max_ttl"=>"64",
                "waittime"=>"5",
                "pause"=>"0",
                "first_ttl"=>"0",
                "timeout"=>"30",
                "type"=>"traceroute",
                "packet_size"=>"40",
                "test_interval"=>"600",
            ),
            "members"=>array("members"=>$late_hostnames, "type"=>"mesh"),
            "description"=>"Traceroute Test Between $mesh_desc Latency Hosts#$mesh_id/traceroute"
        );

        //tcp bwctl 
        $tests[] = array("parameters"=>array( //TODO - move this config
                "protocol"=>"tcp",
                "duration"=>"20",
                "interval"=>"14400",
                "force_bidirectional"=>"1",
                "tool"=>"bwctl/iperf",
                "type"=>"perfsonarbuoy/bwctl",
            ),
            "members"=>array("members"=>$band_hostnames, "type"=>"mesh"),
            "description"=>"TCP BWCTL Test Between $mesh_desc Bandwidth Hosts#$mesh_id/perfsonarbuoy/bwctl"
        );
    
        //latency Traceroute Test
        $tests[] = array("parameters"=>array( //TODO - move this config
                "loss_threshold"=>"10",
                "bucket_width"=>"0.001",
                "packet_interval"=>"0.1",
                "sample_count"=>"300",
                "force_bidirectional"=>"1",
                "packet_padding"=>"0",
                "type"=>"perfsonarbuoy/owamp",
                "session_count"=>"18000",
            ),
            "members"=>array("members"=>$late_hostnames, "type"=>"mesh"),
            "description"=>"OWAMP Test Between $mesh_desc Latency Hosts#$mesh_id/perfsonarbuoy/owamp"
        );


        $this->view->data = array(
            "administrators"=>$mesh_admins, 
            "organizations"=>$orgs, 
            "tests"=>$tests, 
            "description"=>$mesh_desc." Mesh"
        );
    }
}
