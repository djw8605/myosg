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
    public static function default_title() { return "Perfsonar Mesh Config"; }
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
        $model = new MeshConfig();
        $mc = null;
        if(isset($_REQUEST["name"])) {
            $mc = $model->getConfigByName($_REQUEST["name"]);
        } else {
            message('warning', 'please specify name parameter');
            $this->_helper->redirector('', 'miscpfmesh');
            return;
        }
        if(!$mc) { 
            message('warning', 'no such mesh config');
            $this->_helper->redirector('', 'miscpfmesh');
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

        $oim_all = array();
        $wlcg_all = array();
        $mesh_tests = array();
        $all_hostnames = array();

        //pull group details / parameters for all tests
        $tests = $model->getTestsByConfigID($mc->id);
        foreach($tests as $test) {
            $a = $model->getGroupMembers($test->groupa_ids);
            $b = $model->getGroupMembers($test->groupb_ids);

            $a_fqdns = $this->pullfqdns($a);
            $b_fqdns = $this->pullfqdns($b);

            $oim_all = array_merge($oim_all, $a["oim"]);
            $wlcg_all = array_merge($wlcg_all, $a["wlcg"]);
            if($b == null) {
                $mesh_members = array("members"=>$a_fqdns, "type"=>$test->type);
                $all_hostnames = array_merge($all_hostnames, $a_fqdns);
            } else {
                $mesh_members = array("a_members"=>$a_fqdns, "b_members"=>$b_fqdns, "type"=>$test->type);
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

                //$ma = $this->guessMAs($resource["fqdn"], $resource["sids"]);
                $ma = array();
                if(isset($mas[$resource["fqdn"]])) {
                    $ma = $mas[$resource["fqdn"]];
                }

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
                        "administrators"=>array(),//not admin at the site level
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

                //$mas = $this->guessMAs($end->hostname, array($end->service_id));
                $ma = array();
                if(isset($mas[$end->hostname])) {
                    $ma = $mas[$end->hostname];
                }

                $endpoints[] = array(
                    "administrators"=>array(), //no contact for endpoint
                    "addresses"=>array($end->hostname),
                    "measurement_archive"=>$ma,
                    "description"=>$wlcgsite["detail"]->short_name." ".$end->service_type
                );
            }
            //slog(print_r($endpoints, true));
            $mesh_orgs[] = array(
                "sites"=>array( //we always have 1 hosts group under each site
                    array(
                        "hosts"=>$endpoints,
                        "administrators"=>$wlcgsite["admin"], 
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

        $this->view->data = array(
            "administrators"=>$mesh_admins, 
            "organizations"=>$mesh_orgs, 
            "tests"=>$mesh_tests,
            "description"=>$mc->desc
        );
        $this->render("json");
    }

}
