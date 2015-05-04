<?php
/**************************************************************************************************

Copyright 2014 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

**************************************************************************************************/

class MeshConfig 
{
    public function getConfigs()
    {
        $oim = db("oim");
        $sql = "SELECT * FROM mesh_config WHERE disable = 0";
        return $oim->fetchAll($sql);
    }
    public function getConfigByName($name)
    {
        $oim = db("oim");
        $sql = "SELECT * FROM mesh_config WHERE name = ".$oim->quote($name)." AND disable = 0";
        return $oim->fetchRow($sql);
    }
    public function getTestsByConfigID($config_id)
    {
        $oim = db("oim");
        $sql = "SELECT * FROM mesh_config_test WHERE mesh_config_id = $config_id AND disable = 0";
        return $oim->fetchAll($sql);
    }
    public function getContacts($config_id)
    {
        $oim = db("oim");
        $sql = "SELECT * FROM mesh_config_contact WHERE mesh_config_id = $config_id";
        $contacts = array();
        foreach($oim->fetchAll($sql) as $contact) {
            $sql = "SELECT name,primary_email FROM contact WHERE id = ".$contact->contact_id;
            $contact->detail = $oim->fetchRow($sql);
            $contacts[$contact->contact_id] = $contact;
        }
        return $contacts;
    }

    public function getGroupMembers($group_ids) {
        $ids = explode(",", $group_ids);
        $all = null;
        foreach($ids as $id) {
            if($id == "") continue;
            if(is_null($all)) {
                $all = $this->getAGroupMembers($id);
            } else {
                $new = $this->getAGroupMembers($id);
                $all = array(
                    "oim"=>array_merge($all["oim"], $new["oim"]),
                    "wlcg"=>array_merge($all["wlcg"], $new["wlcg"])
                );
            }
        }
        return $all;
    }

    public function getAGroupMembers($group_id) {
        if(is_null($group_id)) return null;
        static $cache = array();
        if(!isset($cache[$group_id])) {
            $oim = db("oim");

            /*
            $sql = "SELECT * FROM mesh_config_oim_member WHERE group_id = $group_id";
            $resource_ids = array();
            foreach($oim->fetchAll($sql) as $member) {
                $resource_ids[] = $member->resource_id;
            }

            //load resource_services from view_oim_hostname (override preapplied)
            $resource_services = array();
            if(!empty($resource_ids)) {
                $sql = "SELECT id, service_id, hostname FROM view_oim_hostname WHERE id IN (".implode($resource_ids, ",").")";
                foreach($oim->fetchAll($sql) as $rs) {
                    $resource_services[] = array("rid"=>$rs->id, "sid"=>$rs->service_id, "fqdn"=>$rs->hostname);
                }
            }
            */
            //load oim members (use join because I want to key by both rid and sid)
            $sql = "select id,m.service_id,hostname,name from mesh_config_oim_member m join view_oim_hostname v on resource_id = v.id and m.service_id = v.service_id where group_id = $group_id";
            $resource_services = array();
            foreach($oim->fetchAll($sql) as $rs) {
                $resource_services[] = array("rid"=>$rs->id, "sid"=>$rs->service_id, "fqdn"=>$rs->hostname);
            }

            //load wlcg members (use join like oim?)
            $sql = "SELECT * FROM mesh_config_wlcg_member WHERE group_id = $group_id";
            $wlcg_ids = array();
            foreach($oim->fetchAll($sql) as $member) {
                $id = $oim->quote($member->primary_key);
                $wlcg_ids[] = $id;
            }
            $wlcg = array();
            if(!empty($wlcg_ids)) {
                $sql = "SELECT primary_key,hostname FROM wlcg_endpoint WHERE primary_key IN (".implode($wlcg_ids, ",").")";
                foreach($oim->fetchAll($sql) as $resource) {
                    $key = $oim->quote($resource->primary_key);//why did I do this?
                    $wlcg[] = array("primary_key"=>$key, "fqdn"=>$resource->hostname);
                }
            }

            $cache[$group_id] = array("oim"=>$resource_services, "wlcg"=>$wlcg);
        }
        return $cache[$group_id];
    }

    public function getParameters($param_id) {
        if(is_null($param_id)) return null;
        static $cache = array();
        if(!isset($cache[$param_id])) {
            $oim = db("oim");
            $sql = "SELECT params FROM mesh_config_param WHERE id = $param_id";
            $params  = $oim->fetchRow($sql)->params;
            $cache[$param_id] = json_decode($params);
        }
        return $cache[$param_id];
    }

    //from list of resource/service records, construct a data structure that can then 
    //easily turned into mesh config
    public function getOIMSites($resource_services) {
        if(empty($resource_services)) return array();
        $oim = db("oim");

        //first load all resources..
        $rids = array();
        foreach($resource_services as $resource_service) {
            $rid = $resource_service["rid"];
            if(!in_array($rid, $rids)) {
                $rids[] = $rid;
            }
        }
        //$sql = "SELECT id, name, resource_group_id FROM resource WHERE id in (".implode($rids, ",").")";
        $sql = "SELECT * FROM view_oim_hostname WHERE id in (".implode($rids, ",").")";
        $resources = $oim->fetchAll($sql);
        if(empty($resources)) return array();

        //load all resource admins
        $resource_admins = array();
        $sql = "SELECT r.resource_id, c.id, c.name, c.primary_email FROM resource_contact r JOIN contact c ON r.contact_id = c.id ".
            " WHERE r.contact_type_id = 3";//select admins
        foreach($oim->fetchAll($sql) as $admin) {
            $resource_admins[$admin->resource_id][$admin->id] = $admin;
        }

        //get all resource groups we need
        $rg_ids = array();
        foreach($resources as $resource) {
            $rg_ids[] = $resource->resource_group_id;
        }
        $sql = "SELECT id, name, site_id FROM resource_group WHERE id in (".implode($rg_ids, ",").")";
        $rgs = $oim->fetchAll($sql);
        if(empty($rgs)) return array();

        //get all sites we need
        $site_ids = array();
        foreach($rgs as $rg) {
            $site_ids[] = $rg->site_id;
        }
        $sql = "SELECT id,longitude,latitude,city,state,name FROM site WHERE id IN (".implode($site_ids, ",").")";
        $sites = $oim->fetchAll($sql);
        if(empty($sites)) return array();

        //now, put everything together site/resource
        $org = array();
        foreach($sites as $site) {
            //look for all resources under this site
            $site_resources = array();
            foreach($rgs as $rg) {
                if($rg->site_id == $site->id) {
                    //look for all resource under this rg
                    foreach($resources as $resource) {
                        if($resource->resource_group_id == $rg->id) {
                            //don't include if client didn't request this rid/sid pair
                            foreach($resource_services as $resource_service) {
                                if($resource_service["rid"] == $resource->id && $resource_service["sid"] == $resource->service_id) {
                                    //found..
                                    if(isset($site_resources[$resource->hostname])) {
                                        //this happens if there are 2 separate service registration for identical hostname.. 
                                        //merging sid, but keep the name/group_name the same (whichever comes the first?)
                                        //(TODO) we should also merge admin if it's from different resource
                                        $current = &$site_resources[$resource->hostname];
                                        if(!in_array($resource->service_id, $current["sids"])) {
                                            $current["sids"][] = $resource->service_id;
                                        }
                                    } else {
                                        $site_resources[$resource->hostname] = array(
                                            "name"=>$resource->name, 
                                            "fqdn"=>$resource->hostname, 
                                            "sids"=>array($resource->service_id),
                                            "admins"=>$resource_admins[$resource->id],
                                            "group_name"=>$rg->name 
                                        );
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $org[$site->id] = array("detail"=>$site, "resources"=>$site_resources);
        }

        return $org;
    }
    
    public function getMAs($hostnames) {
        if(empty($hostnames)) return array();
        $oim = db("oim");

        $quoted_hostnames = array();
        foreach($hostnames as $hostname) {
            $quoted_hostnames[] = $oim->quote($hostname);
        }
        $mas = array();
        $sql = "SELECT * FROM perfsonar_mas WHERE hostname in (".implode($quoted_hostnames, ",").")";
        $recs = $oim->fetchAll($sql);
        foreach($recs as $rec) {
            $mas[$rec->hostname] = json_decode($rec->ma);
        }
        //dlog($mas, "mas");
        return $mas;

    }

    public function getWLCGSites($key_fqdns) {
        if(empty($key_fqdns)) return array();

        $oim = db("oim");
        $pkeys = array();
        //dlog($key_fqdns, "key");
        foreach($key_fqdns as $key_fqdn) {
            $pkeys[] = $key_fqdn["primary_key"];
        }
        //first load all endpoints
        $sql = "SELECT * FROM wlcg_endpoint WHERE primary_key in (".implode($pkeys, ",").")";
        $endpoints = $oim->fetchAll($sql);
        if(empty($endpoints)) return array();

        //get all sites we need
        $site_ids = array();
        foreach($endpoints as $endpoint) {
            $site_ids[] = $oim->quote($endpoint->site_id);
        }
        $sql = "SELECT * FROM wlcg_site WHERE primary_key IN (".implode($site_ids, ",").")";
        $sites = $oim->fetchAll($sql);
        if(empty($sites)) return array();
    
        //now, put everything together site/resource
        $org = array();
        foreach($sites as $site) {
            //look for all resources under this site
            $site_endpoints = array();
            foreach($endpoints as $endpoint) {
                if($endpoint->site_id == $site->primary_key) {

                    //convert service_type to array of sid
                    $sid = null;
                    switch($endpoint->service_type) {
                    case "net.perfSONAR.Bandwidth": 
                        $sid = 130; 
                        $endpoint->service_type = "BW";
                        break;
                    case "net.perfSONAR.Latency": 
                        $sid = 131; 
                        $endpoint->service_type = "LAT";
                        break;
                    default:    
                        elog("unknown wlcg endpoint service type :".$endpoint->service_type);
                    }
                    $endpoint->sids = array($sid);
                    
                    //even though there could be multiple endpoint (with different primary_key)
                    //with the same hostname with different service type, I need to dedupe by 
                    //hostname instead of primary key because mesh config are keyed by hostname
                    //and we can only list each hostname once under /site. 

                    if(isset($site_endpoints[$endpoint->hostname])) {
                        //merge sid
                        $current = $site_endpoints[$endpoint->hostname];
                        $current->sids = array_merge($current->sids, $endpoint->sids);
                        $current->service_type .= " & ".$endpoint->service_type;
                    } else {
                        $site_endpoints[$endpoint->hostname] = $endpoint;
                    }
                }
            }

            $site_admin = array("email"=>$site->contact_email);
            $org[$site->primary_key] = array("detail"=>$site, "endpoints"=>$site_endpoints, "admin"=>$site_admin);
        }
        return $org;
    }

    public function getTestsByHost($hostname) {
        $oim = db("oim");
        $sql = "select group_id from mesh_config_oim_member m join view_oim_hostname h on h.id = resource_id and h.service_id = m.service_id where hostname = ".$oim->quote($hostname);
        $recs = $oim->fetchAll($sql);
        $gids = array();
        foreach($recs as $rec) {
            $gids[] = $rec->group_id;
        }

        //load by wlcg hostname
        $sql = "select group_id from mesh_config_wlcg_member where primary_key in (select primary_key from wlcg_endpoint where hostname = ".$oim->quote($hostname).");";
        $recs = $oim->fetchAll($sql);
        foreach($recs as $rec) {
            $gid = $rec->group_id;
            if(!in_array($gid, $gids)) {
                $gids[] = $gid;
            }
        }

        //load tests for each groups
        if(count($gids) == 0) {
            return array();
        }
        $in = "";
        foreach($gids as $gid) {
            if($in != "") $in .= " or ";
            $pattern = "regexp ('(,$gid|^$gid),')";
            $in .= "groupa_ids $pattern or groupb_ids $pattern";
        }
        $sql = "select * from mesh_config_test where ($in) and disable = 0";
        return $oim->fetchAll($sql);
    }
}


