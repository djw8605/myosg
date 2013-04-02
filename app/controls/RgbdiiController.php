<?php
/*#################################################################################################

Copyright 2011 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/


class RgbdiiController extends RgController
{
    public static function default_title() { return "BDII Information Browser"; }
    public static function default_url($query) { return ""; }

    public function processQuery() {
        if(!isset($_REQUEST["bdii_server"]) || !isset($_REQUEST["bdii_object"])) {
            echo "parameters not set";
            die();
        }
        
        $server_dirty = $_REQUEST["bdii_server"];
        $object_dirty = $_REQUEST["bdii_object"];

        switch($server_dirty) {
        case "is-osg":
            $host = "is.grid.iu.edu";
            $port = 2170;
            $base = "mds-vo-name=local,o=grid";
            break;
        case "is-wlcg":
            $host = "is.grid.iu.edu";
            $port = 2180;
            $base = "o=grid";
            break;
        case "lcg":
            $host = "lcg-bdii.cern.ch";
            $port = 2170;
            $base = "mds-vo-name=local,o=grid";
            break;
        }
        switch($object_dirty) {
        //case "site": $aggregator = new site_aggregator(); break;
        case "service": $aggregator = new service_aggregator(); break;
        case "ce": $aggregator = new ce_aggregator(); break;
        case "se": $aggregator = new se_aggregator(); break;
        case "sa": $aggregator = new sa_aggregator(); break;
        case "cluster": $aggregator = new cluster_aggregator(); break;
        }
        return array($host, $port, $base, $aggregator);
    }

    public function load()
    {
        parent::load();
        $this->view->rgs = $this->rgs;
        $this->view->error = null;

        $resource_group_model = new ResourceGroup();
        $resource_groups = $resource_group_model->getgroupby("name"); 

        list($host, $port, $base, $aggregator) = $this->processQuery();
        if($host == null || $port == null || $base == null || $aggregator == null) {
            $this->view->error = "Invalid parameters.";
        } else {
            $conn = ldap_connect($host, $port);
            $results = ldap_search($conn, $base, $aggregator->filter());
            $entries = ldap_get_entries($conn, $results);
            $detail_groups = array(); //group by resource group id and contatins aggregated object
            $first = true;
            foreach($entries as $entry) {
                if($first) {
                    $first = false;
                    $count = $entry; //TODO - should I do something with count object?
                    continue;
                }
                $dn = $entry["dn"];

                //parse out sitename
                $token = "Mds-Vo-name=";
                $token_len = strlen($token);
                $begin = strpos($dn,$token);
                $len = strpos($dn,",", $begin) - $begin;
                $sitename = substr($dn, $begin+$token_len, $len-$token_len);

                if(isset($resource_groups[$sitename])) {
                    $rgid = (int)$resource_groups[$sitename][0]->id;
                    $rg_info = $resource_groups[$sitename][0];
                    $entry_id = substr($dn, 0, $begin-1);
                    if(!isset($detail_groups[$rgid])) {
                        //new resource group
                        $detail_groups[$rgid] = $aggregator->newobject($sitename);
                    }
                    $detail_groups[$rgid]->process($entry, $entry_id);
                } else {
                    //echo "not osg site name: $sitename";
                }
            }
            $this->view->detail_groups = $detail_groups;
            $this->view->headers = $aggregator->headers();
            $this->view->resource_groups = $resource_group_model->getgroupby("id");
        }

        $this->setpagetitle($this->default_title());
        $this->view->has_subscribe_igoogle = false;
        $this->view->has_subscribe_uwa = false;
        $this->view->has_subscribe_mobile = true;
        $this->view->has_subscribe_xml = false;
        $this->view->has_subscribe_csv = false;
    }

    public function detailAction() {
        $this->render("none", null, true);
        list($host, $port, $base, $aggregator) = $this->processQuery();
        require_once("app/json.php");
        $json = new Services_JSON();
        if($host == null || $port == null || $base == null || $aggregator == null) {
            echo $json->encode(array("status"=>"ERROR", "reason"=>"Invalid parameters"));
        } else {
            $rec_id = $_REQUEST["rec_id"];
            //TODO - how should I validate?
            echo $json->encode($aggregator->detail($rec_id, $host, $port, $base));
        }
    }
}

class site_object {
    var $entry = null;
    function __construct($rgname) {
        $this->rgname = $rgname;
    }
    function process($entry, $id) {
        //$id = substr($id, strlen("GlueSiteUniqueID="));
        $this->entry = $entry;
    }

    function records() {
        $recs = array();
        
        $sponsor = "";
        for($i = 0;$i < $this->entry["gluesitesponsor"]["count"];$i++) {
            if($i != 0) $sponsor .= " ";
            $sponsor .= $this->entry["gluesitesponsor"][$i];
        }
        
        $recs[$this->rgname] = array(
            $this->rgname,
            //substr($this->entry["gluesiteemailcontact"][0], 7),
            $sponsor,
            $this->entry["gluesitelatitude"][0],
            $this->entry["gluesitelongitude"][0],
            $this->entry["gluesitelocation"][0],

            $this->rgname //last column is record id
        );
        return $recs;
    }
}

class service_aggregator {
    function filter() { return "(objectClass=GlueService)"; }
    function newobject($rgname) { return new service_object($rgname); }
    function headers() {
        return array(
            array("name"=>"Resource Group Name", "type"=>"string", "width"=>150),
            array("name"=>"ID", "type"=>"string", "width"=>280),
            array("name"=>"Type", "type"=>"string", "width"=>150),
            array("name"=>"Owner", "type"=>"string", "width"=>50),
            array("name"=>"Start Time", "type"=>"string", "width"=>140),
            array("name"=>"Status", "type"=>"string"),
            array("name"=>"Version", "type"=>"string"),
            array("name"=>"VOs supported", "type"=>"numeric")
            );
    }

    function detail($rec_id, $host, $port, $base) {
        $conn = ldap_connect($host, $port);
        $rec_id = explode(",", $rec_id);
        $results = ldap_search($conn, "GlueServiceUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueService)");
        $entries = ldap_get_entries($conn, $results);
        $entry = $entries[0];

        $access = "";
        if(isset($entry["glueserviceaccesscontrolrule"])) {
            for($i = 0;$i < $entry["glueserviceaccesscontrolrule"]["count"]; $i++) {   
                $vo = $entry["glueserviceaccesscontrolrule"][$i];
                if(strpos($vo, "VO:") === 0) continue;
                $access .= "<div class=\"dot\">$vo</div>";
            }
        } else {
            $access .= "(no access)";
        }

/*
        $subrecord = "";
        if($entry["glueservicetype"][0] == "SRM") {
            //load all SE associated with this service
            $subresults = ldap_search($conn, "GlueServiceUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueService)");
            $subentries = ldap_get_entries($conn, $subresults);
            $subrecord .= print_r($subentries, true);
        }
*/
        $urls = "";
        $urls .= "<b>Service Name</b> ".$entry["glueservicename"][0]."<br>"; 
        $urls .= "<b>Access Point URL</b> ".$entry["glueserviceaccesspointurl"][0]."<br>"; 
        $urls .= "<b>Service Endpoint</b> ".$entry["glueserviceendpoint"][0]."<br>"; 
        if(isset($entry["glueserviceuri"][0])) {
            $urls .= "<b>Service URI</b> ".$entry["glueserviceuri"][0]."<br>"; 
        } else {
            $urls .= "<b>Service URI</b> (no data)<br>"; 
        }
        $urls .= "<b>Service WSDL</b> ".$entry["glueservicewsdl"][0]."<br>"; 
        $urls .= "<b>Semantics</b> ".$entry["glueservicesemantics"][0]."<br>"; 
        $urls .= "<b>Status Info</b> ".$entry["glueservicestatusinfo"][0]."<br>"; 

        $out = "<table class=\"subtable\"><tr>";
        $out .= "<td width=\"60%\"><h3>Details</h3>".$urls."</td>";
        $out .= "<td width=\"40%\"><h3>VO Support</h3><div class=\"div300\">".$access."</div></td>";
        $out .= "</tr></table>";

/*
        if($subrecord != "") {
            $out .= $subrecord;
        }
*/
        return array("status"=>"OK", "subrecord"=>null, "info"=>$out);
    }

}

class service_object {
    //aggregate records
    var $services = array();
    function __construct($rgname) {
        $this->rgname = $rgname;
    }
    function process($entry, $id) {
        $id = substr($id, strlen("GlueServiceUniqueID="));
        $this->services[$id] = $entry; //just store everything in array...
    }

    function records() {
        $recs = array();
        foreach($this->services as $id=>$service) {

            $vo_counts = 0;
            if(isset($service["glueserviceaccesscontrolrule"])) {
                for($i = 0;$i < $service["glueserviceaccesscontrolrule"]["count"]; $i++) {   
                    $vo = $service["glueserviceaccesscontrolrule"][$i];
                    if(strpos($vo, "VO:") === 0) continue;
                    $vo_counts++;
                }
            }

            $recs[$id] = array(
                $this->rgname,
                $service["glueserviceuniqueid"][0],
                $service["glueservicetype"][0],
                $service["glueserviceowner"][0],
                $service["glueservicestarttime"][0],
                $service["glueservicestatus"][0],
                $service["glueserviceversion"][0],
                $vo_counts,

                $id.",".$this->rgname //last column is record id
            );
        }
        return $recs;
    }
}

class ce_aggregator {
    function filter() { return "(&(objectClass=GlueCE)(GlueInformationServiceURL=ldap://is.grid.iu.edu:2170))"; }
    function newobject($rgname) { return new ce_object($rgname); }
    function headers() {
        return array(
            array("name"=>"Resource Group Name", "type"=>"string", "width"=>140),
            array("name"=>"Gatekeeper", "type"=>"string", "width"=>140),
            array("name"=>"CE Name", "type"=>"string", "width"=>130),
            //array("name"=>"Status", "type"=>"string", "width"=>50),
            array("name"=>"Running Jobs", "type"=>"numeric", "width"=>50),
            array("name"=>"Waiting Jobs", "type"=>"numeric", "width"=>50),
            array("name"=>"Free Job Slots", "type"=>"numeric", "width"=>35),
            array("name"=>"Max Total Jobs", "type"=>"numeric"),
            array("name"=>"Max Waiting Jobs", "type"=>"numeric"),
            array("name"=>"Total CPUs", "type"=>"numeric"),
            array("name"=>"Max Obtainable CPU Time", "type"=>"numeric"),
            array("name"=>"Max Obtainable Wallclock Time", "type"=>"numeric"),
            array("name"=>"Estimated Response Time", "type"=>"numeric"),
            array("name"=>"Worst Response Time", "type"=>"numeric"),
            array("name"=>"LRMS Type", "type"=>"string")
            );
    }
    function detail($rec_id, $host, $port, $base) {
        $conn = ldap_connect($host, $port);
        $rec_id = explode(",", $rec_id);
        $results = ldap_search($conn, "GlueCEUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueCE)");
        $entries = ldap_get_entries($conn, $results);
        $entry = $entries[0];

        //buckets
        $info = array();
        $policy = array();

        $info["Data Directory"] = $entry["glueceinfodatadir"];
        $info["Application Directory"] = $entry["glueceinfoapplicationdir"];
        $info["GRAM Version"] = $entry["glueceinfogramversion"];
        $default_se = $entry["glueceinfodefaultse"][0];
        $info["LRMS Version"] = $entry["glueceinfolrmsversion"];
        $info["Gate Keeper Port"] = $entry["glueceinfogatekeeperport"];
        $info["Contact String"] = $entry["glueceinfocontactstring"];

        $info["Hosting Cluster"] = $entry["gluecehostingcluster"];
        $info["Glue Capability"] = $entry["gluececapability"];

        $policy["Max Running Jobs"] = $entry["gluecepolicymaxrunningjobs"];
        $policy["Assigned Job Slots"] = $entry["gluecepolicyassignedjobslots"];
        $policy["Max CPU Time"] = $entry["gluecepolicymaxcputime"];
        $policy["Max Wall Clock Time"] = $entry["gluecepolicymaxwallclocktime"];
        $policy["Preemption"] = $entry["gluecepolicypreemption"];
        $policy["Max Slots Per Jobs"] = $entry["gluecepolicymaxslotsperjob"];

        $policy["Total Jobs"] = $entry["gluecestatetotaljobs"];
        $policy["Status"] = $entry["gluecestatestatus"];
        $policy["Free CPUs"] = $entry["gluecestatefreecpus"];

        //$other["Cluster ID"] = $entry["glueforeignkey"];

        $vos = "";
        $count = $entry["glueceaccesscontrolbaserule"]["count"];
        for($i=0;$i < $count; $i++) {
            $vo = $entry["glueceaccesscontrolbaserule"][$i];
            if(strpos($vo, "VO:") === 0) {
                $vo = substr($vo, 3);
            }
            $vos .= "<div class=\"dot\">$vo</div>";
        }

        $ses = "";
        $results = ldap_search($conn, "GlueCESEBindGroupCEUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueCESEBindGroup)");
        $entries = ldap_get_entries($conn, $results);
        if(isset($entries[0]["gluecesebindgroupseuniqueid"])) {
            $se_ids = $entries[0]["gluecesebindgroupseuniqueid"];
            $count = $se_ids["count"];
            for($i=0;$i < $count; $i++) {
                $ses .= "<div class=\"dot\">".$se_ids[$i];
                if($se_ids[$i] == $default_se) {
                    $ses .= " (default)";
                }
                $ses .= "</div>";
            }
        } else {
            $ses = "none";
        }

        //create table
        $out = "<table class=\"subtable\"><tr>";
        $out .= "<td width=\"40%\"><h3>Information</h3>".$this->output_bucket($info)."</td>";
        $out .= "<td width=\"25%\"><h3>Policy/State</h3>".$this->output_bucket($policy)."</td>";
        $out .= "<td width=\"20%\"><h3>Storage Element</h3>".$ses."</td>";
        $out .= "<td width=\"15%\"><h3>VO Support</h3><div class=\"div300\">".$vos."</div></td>";
        $out .= "</tr></table>";

        return array("status"=>"OK", "subrecord"=>null, "info"=>$out);
    }

    function output_bucket($bucket) {
        $out = "";
        foreach($bucket as $key=>$data) {
            $values = "";
            $count = $data["count"];
            for($i=0;$i < $count; $i++) {
                if($i != 0) $values .= ", ";
                $values .= $data[$i];
            }
            //$out .= "<b>$key</b><br>".print_r($data, true)."<br>";
            $out .= "<b>$key</b> $values<br>";
        }
        return $out;
    }

}

class ce_object {
    //aggregate records
    var $ces = array();
    function __construct($rgname) {
        $this->rgname = $rgname;
    }
    function process($entry, $id) {
        $id = substr($id, strlen("GlueCEUniqueID="));
        $this->ces[$id] = $entry; //just store everything in array...
    }

    function records() {
        $recs = array();
        foreach($this->ces as $id=>$ce) {
            $recs[$id] = array(
                $this->rgname,
                $ce["glueceinfohostname"][0],//.":"..$ce["glueceinfogatekeeperport"][0],
                $ce["gluecename"][0],
                //$ce["gluecestatestatus"][0],
                (int)$ce["gluecestaterunningjobs"][0],
                (int)$ce["gluecestatewaitingjobs"][0],
                //(int)$ce["gluecestatetotaljobs"][0],
                (int)$ce["gluecestatefreejobslots"][0],
                (int)$ce["gluecepolicymaxwaitingjobs"][0],
                (int)$ce["gluecepolicymaxtotaljobs"][0],
                (int)$ce["glueceinfototalcpus"][0],
                (int)$ce["gluecepolicymaxobtainablecputime"][0],
                (int)$ce["gluecepolicymaxobtainablewallclocktime"][0],
                (int)$ce["gluecestateestimatedresponsetime"][0],
                (int)$ce["gluecestateworstresponsetime"][0],
                $ce["glueceinfolrmstype"][0],

                $id.",".$this->rgname //last column is record id
            );
        }
        return $recs;
    }
}

class sa_aggregator {
    function filter() { return "(objectClass=GlueSA)"; }
    function newobject($rgname) { return new sa_object($rgname); }
    function headers() {
        return array(
            array("name"=>"Resource Group Name", "type"=>"string", "width"=>120),
            array("name"=>"SE Name", "type"=>"string", "width"=>170),
            array("name"=>"SA Name", "type"=>"string", "width"=>160),
            array("name"=>"Access Latency", "type"=>"string"),
            array("name"=>"Expiration Mode", "type"=>"string"),
            array("name"=>"Free Online Size", "type"=>"numeric"),
            array("name"=>"Reservded Online Size", "type"=>"numeric"),
            array("name"=>"Retention Policy", "type"=>"string"),
            array("name"=>"Available Space", "type"=>"numeric"),
            array("name"=>"Used Space", "type"=>"numeric"),
            array("name"=>"Total Online Size", "type"=>"numeric"),
            array("name"=>"Used Online Size", "type"=>"numeric")
            );
    }

    function detail($rec_id, $host, $port, $base) {
        $conn = ldap_connect($host, $port);
        $rec_id = explode(",", $rec_id);
        //slog("GlueSALocalID=".$rec_id[0].","."GlueSEUniqueID=".$rec_id[1].","."Mds-Vo-name=".$rec_id[2].",".$base);
        $results = ldap_search($conn, "GlueSALocalID=".$rec_id[0].",".$rec_id[1].","."Mds-Vo-name=".$rec_id[2].",".$base, "(objectClass=GlueSA)");
        $entries = ldap_get_entries($conn, $results);
        $entry = $entries[0];

        //buckets
        $voms = "";
        $capabilities = "";
        $others = array();

        //sort values
        foreach($entry as $key=>$data) {
            if(!is_array($data)) continue;
            if(strpos($key, "gluesaaccesscontrolbaserule") !== false) {
                $key = substr($key, strlen("gluesaaccesscontrolbaserule"));
                $count = $data["count"];
                for($i=0;$i < $count; $i++) {
                    $vo = $data[$i];
                    if(strpos($vo, "VO:") === 0) continue;
                    $voms .= "<div class=\"dot\">$vo</div>";
                }
            } else if(strpos($key, "gluesacapability") !== false) {
                $key = substr($key, strlen("gluesacapability"));
                $count = $data["count"];
                for($i=0;$i < $count; $i++) {
                    $capabilities .= "<b>$key</b>".$data[$i]."<br>";
                }
            }
        }
        if($voms == "") $voms = "(No access)";

        //pull misc stuff
        $others["Policy File Lifetime"] = $entry["gluesapolicyfilelifetime"];
        $others["Storage Area Path"] = $entry["gluesapath"];
        $others["Storage Area LocaL ID"] = $entry["gluesapath"];

        //create table
        $out = "<table class=\"subtable\"><tr>";
        $out .= "<td width=\"30%\"><h3>Access Control</h3><div class=\"div300\">$voms</div></td>";
        $out .= "<td width=\"30%\"><h3>Capabilities</h3>$capabilities</td>";
        $out .= "<td width=\"40%\"><h3>Misc.</h3>".$this->output_bucket($others)."</td>";
        $out .= "</tr></table>";

        return array("status"=>"OK", "subrecord"=>null, "info"=>$out);
    }

    function output_bucket($bucket) {
        $out = "";
        foreach($bucket as $key=>$data) {
            $values = "";
            $count = $data["count"];
            for($i=0;$i < $count; $i++) {
                if($i != 0) $values .= ", ";
                $values .= $data[$i];
            }
            //$out .= "<b>$key</b><br>".print_r($data, true)."<br>";
            $out .= "<b>$key</b> $values<br>";
        }
        return $out;
    }

}

class sa_object {
    //aggregate records
    var $ces = array();
    function __construct($rgname) {
        $this->rgname = $rgname;
    }
    function process($entry, $id) {
        $id = substr($id, strlen("GlueSALocalID="));
        $this->ces[$id] = $entry; //just store everything in array...
    }

    function records() {
        $recs = array();
        foreach($this->ces as $id=>$ce) {
            $recs[$id] = array(
                $this->rgname,
                $ce["gluechunkkey"][0],
                $ce["gluesaname"][0],
                $ce["gluesaaccesslatency"][0],
                $ce["gluesaexpirationmode"][0],
                (int)$ce["gluesafreeonlinesize"][0],
                (int)$ce["gluesareservedonlinesize"][0],
                $ce["gluesaretentionpolicy"][0],
                (int)$ce["gluesastateavailablespace"][0],
                (int)$ce["gluesastateusedspace"][0],
                (int)$ce["gluesatotalonlinesize"][0],
                (int)$ce["gluesausedonlinesize"][0],

                $id.",".$this->rgname //last column is record id
            );
        }
        return $recs;
    }
}

class se_aggregator {
    function filter() { return "(&(objectClass=GlueSE)(GlueInformationServiceURL=ldap://is.grid.iu.edu:2170))"; }
    function newobject($rgname) { return new se_object($rgname); }
    function headers() {
        return array(
            array("name"=>"Resource Group Name", "type"=>"string", "width"=>150),
            array("name"=>"SE Name", "type"=>"string", "width"=>170),
            array("name"=>"Arch.", "type"=>"string", "width"=>70),
            array("name"=>"Impl. Name", "type"=>"string", "width"=>60),
            array("name"=>"Impl. Version", "type"=>"string"),
            array("name"=>"Port", "type"=>"numeric"),
            array("name"=>"Free Size", "type"=>"numeric"),
            array("name"=>"Total Size", "type"=>"numeric"),
            array("name"=>"Total Online Size", "type"=>"numeric"),
            array("name"=>"Used Online Size", "type"=>"numeric")
            );
    }
    function detail($rec_id, $host, $port, $base) {
        $conn = ldap_connect($host, $port);
        $rec_id = explode(",", $rec_id);

        //access protocols
        $results = ldap_search($conn, "GlueSEUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueSEAccessProtocol)");
        $entries = ldap_get_entries($conn, $results);
        if($entries[0] > 0) {
            $access_protocols = "<table><tr>";
            $access_protocols .= "<td><b>Endpoint</b></td>";
            $access_protocols .= "<td><b>Version</b></td>";
            $access_protocols .= "<td><b>Capability</b></td>";
            $access_protocols .= "</tr>";
            foreach($entries as $entry) {
                if(is_array($entry)) {
                    $access_protocols .= "<tr>";
                    $access_protocols .= "<td>".$entry["glueseaccessprotocolendpoint"][0]."</td>";
                    $access_protocols .= "<td>".$entry["glueseaccessprotocolversion"][0]."</td>";
                    $access_protocols .= "<td>".$entry["glueseaccessprotocolcapability"][0]."</td>";
                    $access_protocols .= "</tr>";
                } else {
                    //why wouldn't it be an array?
                    //$protocols.="<pre>".print_r($entry, true)."</pre>";
                }
            }
            $access_protocols .= "</table>";
        } else {
            $access_protocols = "None";
        }

        //control protocols
        $results = ldap_search($conn, "GlueSEUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueSEControlProtocol)");
        $entries = ldap_get_entries($conn, $results);
        if(isset($entries[0]) && $entries[0] > 0) {
            $control_protocols = "<table><tr>";
            $control_protocols .= "<td><b>Endpoint</b></td>";
            $control_protocols .= "<td><b>Version</b></td>";
            $control_protocols .= "<td><b>Type</b></td>";
            $control_protocols .= "<td><b>Capability</b></td>";
            $control_protocols .= "</tr>";
            foreach($entries as $entry) {
                if(is_array($entry)) {
                    $control_protocols .= "<tr>";
                    $control_protocols .= "<td>".$entry["gluesecontrolprotocolendpoint"][0]."</td>";
                    $control_protocols .= "<td>".$entry["gluesecontrolprotocolversion"][0]."</td>";
                    $control_protocols .= "<td>".$entry["gluesecontrolprotocoltype"][0]."</td>";
                    $control_protocols .= "<td>".$entry["gluesecontrolprotocolcapability"][0]."</td>";
                    $control_protocols .= "</tr>";
                } else {
                    //why wouldn't it be an array?
                    //$protocols.="<pre>".print_r($entry, true)."</pre>";
                }
            }
            $control_protocols .= "</table>";
        } else {
            $control_protocols = "None";
        }

        $out = "<table class=\"subtable\"><tr>";
        $out .= "<td width=\"50%\"><h3>Access Protocols</h3><div class=\"div300\">".$access_protocols."</div></td>";
        $out .= "<td width=\"50%\"><h3>Control Protocols</h3><div class=\"div300\">".$control_protocols."</div></td>";
        $out .= "</tr></table>";

        return array("status"=>"OK", "subrecord"=>null, "info"=>$out);
    }

}

class se_object {
    //aggregate records
    var $ces = array();
    function __construct($rgname) {
        $this->rgname = $rgname;
    }
    function process($entry, $id) {
        $id = substr($id, strlen("GlueSEUniqueID="));
        $this->ces[$id] = $entry; //just store everything in array...
    }

    function records() {
        $recs = array();
        foreach($this->ces as $id=>$ce) {
            $recs[$id] = array(
                $this->rgname,
                $ce["gluesename"][0],
                $ce["gluesearchitecture"][0],
                $ce["glueseimplementationname"][0],
                $ce["glueseimplementationversion"][0],
                (int)$ce["glueseport"][0],
                (int)$ce["gluesesizefree"][0],
                (int)$ce["gluesesizetotal"][0],
                (int)$ce["gluesetotalonlinesize"][0],
                (int)$ce["glueseusedonlinesize"][0],

                $id.",".$this->rgname //last column is record id
            );
        }
        return $recs;
    }
}

class cluster_aggregator {
    function filter() { return "(&(|(objectClass=GlueCluster)(objectClass=GlueSubCluster))(GlueInformationServiceURL=ldap://is.grid.iu.edu:2170))"; }
    function newobject($rgname) { return new cluster_object($rgname); }
    function headers() {
        return array(
            array("name"=>"Resource Group Name", "type"=>"string", "width"=>150),
            array("name"=>"(Sub)Cluster Name", "type"=>"string", "width"=>250),
            array("name"=>"Logical CPUs", "type"=>"numeric", "width"=>70),
            array("name"=>"Physical CPUs", "type"=>"numeric", "width"=>70),
            array("name"=>"Memory Size", "type"=>"numeric", "width"=>70),
            array("name"=>"OS", "type"=>"string"),
            array("name"=>"Processor", "type"=>"string"),
            array("name"=>"Available Software", "type"=>"string", "width"=>200),
            //array("name"=>"Env.", "type"=>"list")
        );
    }
    //show more cluster info & sub cluster list
    function detail($rec_id, $host, $port, $base) {
        $conn = ldap_connect($host, $port);
        $rec_id = explode(",", $rec_id);

        if(!isset($rec_id[1])) {       
            elog("rec_id looks strange:".print_r($rec_id, true). " for $host $port $base");
        }

        //access protocols
        $results = ldap_search($conn, "GlueClusterUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueSubCluster)");
        $entries = ldap_get_entries($conn, $results);
        $subrecs = array();
        if($entries[0] > 0) {
            foreach($entries as $entry) {
                if(is_array($entry)) {
                    error_log(print_r($entry["gluehostapplicationsoftwareruntimeenvironment"], true));
                    //create env list
                    $env = "<div style=\"max-height: 150px; overflow-y: scroll;\"><ul>";
                    foreach($entry["gluehostapplicationsoftwareruntimeenvironment"] as $id=>$value) {
                        if($id == "count") continue;
                        $env .= "<li>$value</li>";
                    }
                    $env .= "</ul></div>";

                    $subrecs[] = array(
                        null,
                        "<div class=\"dot\">".$entry["gluesubclusteruniqueid"][0]."</div>",
                        //$entry["gluehostarchitectureplatformtype"][0],
                        //$entry["gluehostarchitecturesmpsize"][0],
                        (int)$entry["gluesubclusterlogicalcpus"][0],
                        (int)$entry["gluesubclusterphysicalcpus"][0],
                        (int)$entry["gluehostmainmemoryramsize"][0],
                        $entry["gluehostoperatingsystemname"][0]." ".$entry["gluehostoperatingsystemrelease"][0],
                        $entry["gluehostprocessormodel"][0],
                        $env
                        //$entry["gluehostapplicationsoftwareruntimeenvironment"],
                    );
                } else {
                    //why wouldn't it be an array?
                    //$protocols.="<pre>".print_r($entry, true)."</pre>";
                }
            }
        }

        //cluster detail
        $results = ldap_search($conn, "GlueClusterUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base, "(objectClass=GlueCluster)");
        error_log("ldap: GlueClusterUniqueID=".$rec_id[0].","."Mds-Vo-name=".$rec_id[1].",".$base);
        $entries = ldap_get_entries($conn, $results);
        $entry = $entries[0];
        $detail = "";
        $detail .= "<b>Cluster Temp Directory</b> ".$entry["glueclustertmpdir"][0]."<br>"; 
        $detail .= "<b>Cluster Worker Node Temp Directory</b> ".$entry["glueclusterwntmpdir"][0]."<br>"; 

        $assoc = "";
        $token = "GlueCEUniqueID=";
        for($id = 0; $id < $entry["glueforeignkey"]["count"]; ++$id) {
            $key = $entry["glueforeignkey"][$id];
            if(strpos($key, $token) === 0) {
                $assoc .= "<div class=\"dot\">".substr($key, strlen($token))."</div>";
            }
        }

        $out = "<div class=\"row-fluid\">";
        $out .= "<div class=\"span6\"><h3>Detail</h3>".$detail."</div>";
        $out .= "<div class=\"span6\"><h3>Associated CEs</h3>".$assoc."</div>";
        $out .= "</div>";
        return array("status"=>"OK", "subrecords"=>$subrecs, "info"=>$out);
    }
}

class cluster_object {
    var $clusters = array();
    function __construct($rgname) {
        $this->rgname = $rgname;
    }
    function process($entry, $id) {
        $ids = explode(",", $id);
        $cluster_token = "GlueClusterUniqueID";
        $subcluster_token = "GlueSubClusterUniqueID";
        if(strpos($ids[0], $cluster_token) === 0) {
            //cluster object
            $cluster_id = substr($ids[0], strlen($cluster_token)+1);
            if(!isset($this->clusters[$cluster_id])) {
                $this->clusters[$cluster_id] = array();
            }
            $this->clusters[$cluster_id]["cluster"] = $entry;
        } else {
            //sub cluster object
            $cluster_id = substr($ids[1], strlen($cluster_token)+1);
            if(!isset($this->clusters[$cluster_id])) {
                $this->clusters[$cluster_id] = array();
            }
            $subcluster_id = substr($ids[0], strlen($subcluster_token)+1);
            $this->clusters[$cluster_id][$subcluster_id] = $entry;
        }

/*
        $this->cluster_id = $entry["gluechunkkey"];//let's just override it for each recrod
        $this->logical_cpu_counts += $entry["gluesubclusterlogicalcpus"];
        $this->physical_cpu_counts += $entry["gluesubclusterphysicalcpus"];
*/
    }

    function records() {
        $recs = array();
        foreach($this->clusters as $id=>$cluster) {
            //aggregate records
            $cluster_name = "";
            $os = "";
            $processors = "";
            $logical_cpu_count = 0;
            $physical_cpu_count = 0;
            $memory_size = 0;
            $count = 0;//number of subcluster - used to calcualte "average"
            foreach($cluster as $subid=>$entry) {
                if($subid == "cluster") {
                    $cluster_name = $entry["glueclustername"][0];
                } else {
                    //must be sub-cluster info
                    $count++;
                    $logical_cpu_count += (int)$entry["gluesubclusterlogicalcpus"][0];
                    $physical_cpu_count += (int)$entry["gluesubclusterphysicalcpus"][0];
                    $memory_size += (int)$entry["gluehostmainmemoryramsize"][0];

                    $aos = $entry["gluehostoperatingsystemname"][0]." ".$entry["gluehostoperatingsystemrelease"][0];
                    if(strpos($os, $aos) === false) {
                        if(strlen($os) != 0) $os .= " / ";
                        $os .= $aos;
                    }

                    $ap = $entry["gluehostprocessormodel"][0];
                    if(strpos($processors, $ap) === false) {
                        if(strlen($processors) != 0) { 
                            if(strpos($processors, "others") === false) {
                                $processors .= ", others"; 
                            } 
                        }
                        else $processors .= $ap;
                    }
                }
            }

            if($count > 0) {
                $recs[$id] = array(
                    $this->rgname,
                    $cluster_name,
                    $logical_cpu_count,
                    $physical_cpu_count,
                    $memory_size,
                    $os,
                    $processors,
                    "(see subcluster)",//subcluster only

                    $id.",".$this->rgname //last column is record id
                );
            } else {
                //no subcluster..
                $recs[$id] = array(
                    $this->rgname,
                    $cluster_name
                );
            }
/*j
            $recs[$id] = array(
                $this->rgname,
                $this->id,
                $this->logical_cpu_counts/($this->count),
                $this->physical_cpu_counts/($this->count),

                $id.",".$this->rgname //last column is record id
            );
*/
        }
        return $recs;
    }
}


