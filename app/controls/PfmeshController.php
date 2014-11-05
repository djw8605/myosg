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

class PfmeshController extends RgpfController
{
    public static function default_title() { return "Perfsonar Mesh Configurations"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle($this->default_title());
        $this->selectmenu("misc");
    }

    private function pullfqdns($as) {
        if($as == null) return null;
        $fqdns = array();
        foreach($as["oim"] as $a) {
            $fqdns[] = $a["fqdn"];
        }
        foreach($as["wlcg"] as $a) {
            $fqdns[] = $a["fqdn"];
        }
        return $fqdns;
    }

    public function indexAction() {
        parent::indexAction();
        $model = new MeshConfig();
        $this->view->configs = $model->getConfigs();
    }

    public function jsonAction() {

        //like "us-atas", "us-cms"
        $name = $this->getParam("name");
        if(is_null($name) && isset($_REQUEST["name"])) {
            $name = $_REQUEST["name"];
        }
        if(is_null($name)) {
            message('warning', 'please specify name parameter');
            $this->_helper->redirector('', 'pfmesh');
            return;
        }

        //either v33 or v34 (default to v34)
        $version = $this->getParam("version");
        if(is_null($version) && isset($_REQUEST["version"])) {
            $version = $_REQUEST["version"];
        }
        if(is_null($version)) {
            $version = "v34";
        }

        /////////////////////////////////////////////////////////////////////////////////
        // temporary redirection to various cern hosted json until 
        if(!isset($_REQUEST["new"])) { //this is for testing *new* auto generated host instead
            if(!isset(config()->mesh_redirects[$version])) {
                message('warning', 'unknown version!');
                $this->_helper->redirector('', 'pfmesh');
                return;
            }
            $redirects = config()->mesh_redirects[$version];
            if(!isset($redirects[$name])) {
                message('warning', 'unknown mesh config name for wlcg redirection.');
                $this->_helper->redirector('', 'pfmesh');
                return;
            }
            $url = $redirects[$name];
            header("HTTP/1.1 307 Temporary Redirect");
            header("Location: $url");
            return;
        }

        $model = new MeshConfig();
        $mc = $model->getConfigByName($name);
        if(!$mc) { 
            message('warning', 'no such mesh config');
            $this->_helper->redirector('', 'pfmesh');
            return;
        }

        //find mesh config admins
        $mesh_admins = array();
        $contacts = $model->getContacts($mc->id);
        foreach($contacts as $contact) {
            if($contact->contact_type_id == 3) {//admin
                $mesh_admins[] = array("email"=>$contact->detail->primary_email, "name"=>$contact->detail->name);
            }
        }

        $tests = $model->getTestsByConfigID($mc->id);
        $data = $this->generateMeshConfig($tests);
        $data["administrators"] = $mesh_admins;
        $data["description"] = $mc->desc;
        $this->view->data = $data;
        $this->render("json");
    }

    //some endpoint doesn't allow us to crawl (firewall?) so we need to guess what their endpoints are.
    function getDefaultMAUrls($fqdn, $sids) {
        $mas = array();
        foreach($sids as $sid) {
            switch($sid) {
            case 130:
                $mas[] = array("read_url"=>"http://$fqdn/esmond/perfsonar/archive/", "type"=>"perfsonarbuoy/bwctl");
                break;
            case 131:
                $mas[] = array("read_url"=>"http://$fqdn/esmond/perfsonar/archive/", "type"=>"perfsonarbuoy/owamp");
                $mas[] = array("read_url"=>"http://$fqdn/esmond/perfsonar/archive/", "type"=>"perfsonarbuoy/traceroute");
                break;
            default:   
                elog("unknown sid:$sid while constructing default ma for $fqdn");
            }
        }
        return $mas;
    }

    function generateMeshConfig($tests) {

        $model = new MeshConfig();

        $oim_all = array();
        $wlcg_all = array();
        $mesh_tests = array();
        $all_hostnames = array();

        //pull group details / parameters for all tests
        foreach($tests as $test) {
            $a = $model->getGroupMembers($test->groupa_ids);
            $b = $model->getGroupMembers($test->groupb_ids);

            $a_fqdns = $this->pullfqdns($a);
            $b_fqdns = $this->pullfqdns($b);
            $type = strtolower($test->type);

            $oim_all = array_merge($oim_all, $a["oim"]);
            $wlcg_all = array_merge($wlcg_all, $a["wlcg"]);
            if($b == null) {
                $mesh_members = array("members"=>$a_fqdns, "type"=>$type);
                $all_hostnames = array_merge($all_hostnames, $a_fqdns);
            } else {
                $mesh_members = array("a_members"=>$a_fqdns, "b_members"=>$b_fqdns, "type"=>$type);
                $all_hostnames = array_merge($all_hostnames, $b_fqdns);
                $oim_all = array_merge($oim_all, $b["oim"]);
                $wlcg_all = array_merge($wlcg_all, $b["wlcg"]);
            }
            $mesh_parameters = $model->getParameters($test->param_id);
            $mesh_tests[] = array("members"=>$mesh_members, "parameters"=>$mesh_parameters, "description"=>$test->name);
        }

        ///////////////////////////////////////////////////////////
        // load meshconfig mas
        $mas = $model->getMAs($all_hostnames);

        ///////////////////////////////////////////////////////////
        // generate meshconfig (site groups) for osg sites
        $mesh_orgs = array();
        $oim_sites = $model->getOIMSites($oim_all);
        foreach($oim_sites as $oimsite) {
            $services = array();
            $rgnames = array();
            foreach($oimsite["resources"] as $resource) {
                //keep all resource group names
                if(!in_array($resource["group_name"], $rgnames)) {
                    $rgnames[] = $resource["group_name"];
                }

                $resource_admins = array();
                foreach($resource["admins"] as $admin) {
                    $resource_admins[] = array("name"=>$admin->name, "email"=>$admin->primary_email);
                }

                /*
                $ma = array();
                if(isset($mas[$resource["fqdn"]])) {
                    $ma = $mas[$resource["fqdn"]];
                } else {
                    $ma = $this->getDefaultMAUrls($resource["fqdn"], $resource["sid"]);
                }
                */

                //always use template for now..
                $ma = $this->getDefaultMAUrls($resource["fqdn"], $resource["sids"]);

                $services[] = array(
                    "administrators"=>$resource_admins, 
                    "addresses"=>array($resource["fqdn"]),
                    "measurement_archives"=>$ma,
                    "description"=>$resource["name"]);
            }
            $mesh_orgs[] = array(
                "sites"=>array( //we always have 1 hosts group under each site
                    array(
                        "hosts"=>$services, 
                        "administrators"=>array(), //left empty for now (no site admins)
                        "location"=>array(
                            "longitude"=>$oimsite["detail"]->longitude,
                            "latitude"=>$oimsite["detail"]->latitude,
                            "city"=>$oimsite["detail"]->city,
                            "state"=>$oimsite["detail"]->state
                        ),
                        "description"=>$oimsite["detail"]->name
                    )
                ), 
                "administrators"=>array(), //we don't have this stored anywhere?
                "description"=>implode(" / ", $rgnames) //no such thing as site groups
            );
        }

        ///////////////////////////////////////////////////////////
        // generate meshconfig (site groups) for WLCG sites
        $wlcg_sites = $model->getWLCGSites($wlcg_all);
        foreach($wlcg_sites as $wlcgsite) {
            $endpoints = array();
            foreach($wlcgsite["endpoints"] as $end) {
 
                /*
                $ma = array();
                if(isset($mas[$end->hostname])) {
                    $ma = $mas[$end->hostname];
                } else {
                    $sid = null;
                    switch($end->service_type) {
                    case "net.perfSONAR.Bandwidth": $sid = 130; break;
                    case "net.perfSONAR.Latency": $sid = 131; break;
                    default:
                        elog("unknown wlcg service type:".$end->service_type." for ".$end->hostname);
                    }
                    $ma = $this->getDefaultMAUrls($end->hostname, $sid);
                }
                */
                //always use template for now..
                /*
                $sid = null;
                switch($end->service_type) {
                case "net.perfSONAR.Bandwidth": $sid = 130; break;
                case "net.perfSONAR.Latency": $sid = 131; break;
                default:
                    elog("unknown wlcg service type:".$end->service_type." for ".$end->hostname);
                }
                */
                $ma = $this->getDefaultMAUrls($end->hostname, $end->sids);

                $endpoints[] = array(
                    "administrators"=>array(), //no contact for endpoint
                    "addresses"=>array($end->hostname),
                    "measurement_archives"=>$ma,
                    "description"=>$wlcgsite["detail"]->short_name." ".$end->service_type
                );
            }
            $mesh_orgs[] = array(
                "sites"=>array( //we always have 1 hosts group under each site
                    array(
                        "hosts"=>$endpoints,
                        "administrators"=>array($wlcgsite["admin"]), 
                        "location"=>array(
                            "longitude"=>$wlcgsite["detail"]->longitude,
                            "latitude"=>$wlcgsite["detail"]->latitude,
                            "city"=>$wlcgsite["detail"]->timezone,
                            "state"=>$wlcgsite["detail"]->country
                        ),
                        "description"=>$wlcgsite["detail"]->short_name 
                    )
                ), 
                "administrators"=>array(), //we don't have this stored anywhere?
                "description"=>$wlcgsite["detail"]->short_name." Site Group" //$wlcgsite["detail"]->short_name
            );
        }

        return array(
            "organizations"=>$mesh_orgs, 
            "tests"=>$mesh_tests
        );
    }

    public function mineAction() {
        $hostname = $this->getParam("hostname");
        if(is_null($hostname)) {
            //REMOTE_HOST doesn't get set for somereason..
            //$hostname = $_SERVER["REMOTE_HOST"];

            message('warning', 'please specify hostname /pfmesh/mine/hostname/<yourhostname>');
            $this->_helper->redirector('', 'pfmesh');
            return;
        }

        //find all host groups that specified hostname is in
        $model = new MeshConfig();
        $tests = $model->getTestsByHost($hostname);
        $data = $this->generateMeshConfig($tests);
        //$data["administrators"] = $mesh_admins;
        $data["description"] = "Mesh config with all test pertaining to $hostname";
        $this->view->data = $data;
        $this->render("json");
    }

    public function allAction() {
        $includes = array();
        $model = new MeshConfig();

        //either v33 or v34 (default to v34)
        $version = $this->getParam("version");
        if(is_null($version) && isset($_REQUEST["version"])) {
            $version = $_REQUEST["version"];
        }

        $configs = $model->getConfigs();
        foreach($configs as $config) {
            $url = fullbase()."/pfmesh/json/name/".$config->name;
            if(!is_null($version)) {
                $url.="/version/$version";
            }
            if(isset($_REQUEST["new"])) {
                $url.="?new";
            }
            $includes[] = $url;
        }
        $this->view->data = array("include"=>$includes);
        $this->render("json");
    }

}
