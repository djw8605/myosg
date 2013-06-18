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

//
// Common Configuration
//

class authtype
{
    //auth_type_id - must match oim.authorization_type
    public static $auth_guest = 0;
    public static $auth_end_user = 1;
    public static $auth_osg_staff = 2;
    public static $auth_osg_security = 3;
    public static $auth_osg_goc = 4;
}

class MetricRecord
{
    public function __construct()
    {
        $this->dbid = null;
        $this->status = null;
        $this->timestamp = null;
        $this->detail = null;
        $this->effective_dbid = null;
        $this->effective_timestamp = null;
    }
}

class role
{
    //role to debug authorization issues
    public static $test = 0;

    //view admin email address and able to open OS email client.
    public static $view_admin_email = 1;
    public static $see_oim_tree = 2;
}

class common_config
{
    function __construct() {
        ///////////////////////////////////////////////////////////////////////
        //
        // Main configuration (Mostly common stuff)
        //

        //application version to use for version specific data structures, etc.
        $this->version = "2.13";

        //application to display or used in email
        $this->app_name = "MyOSG";
        $this->app_subname = "by Grid Operations Center";
        $this->copyright = "Copyright 2011 The Trustees of Indiana University - Developed for Open Science Grid";
        //application name used in places such as session name
        $this->app_id = "rsv_viewer";

        //banner to show on all pages
        $this->banner = null;

        //google map v3 doesn't need api key
        //http://code.google.com/apis/maps/signup.html
        //$this->gmap_key = "get your own key";
        $this->google_analytics_id = "UA-69012-10";

        //if metric age is longer than this value, it is considred stale
        $this->metric_considered_old = 3600*24;

        $this->db_type = "Pdo_Mysql";

        //first page to load when user first visit the page
        //$this->initial_page = "home_start"; //needs to be a menu ID
        $this->initial_page = "current_detail"; //needs to be a menu ID

        //executes debuging code
        $this->debug = false;
        $this->logfile = "app/logs/log.txt";
        $this->error_logfile = "app/logs/error.txt";
        $this->audit_logfile = "app/logs/audit.txt";

        $this->email_from = "goc@opensciencegrid.org";

        //elog email
        $this->elog_email = false;
        $this->elog_email_address = "overrideme";

        //forward http request to https
        $this->force_https = true;

        ///////////////////////////////////////////////////////////////////////////////////////////
        //cache filenames
        
        $this->vomatrix_xml_cache = $this->getCacheDir()."/cache.vomatrix.xml";
        $this->current_resource_status_xml_cache = $this->getCacheDir()."/cache.current_resource_<ResourceID>.xml";
        
        //gip validation
        $this->gip_summary = "/usr/local/gip-validator/var/xml/result.xml"; 
        //$this->gip_summary_itb = "/usr/local/gip-validator/var/xml/result-itb.xml"; 
        $this->gip_wlcg_status = "/usr/local/gip-validator/var/xml/top_level_wlcg_bdii_monitor.xml";

        //bdii raw files
        $this->cemonbdii_url = "http://is.grid.iu.edu/cgi-bin/status.cgi?format=xml&grid_type=OSG-ITB"; //somehow, I need to pass OSG-ITB to pull both prod&itb data
        //$this->cemonbdii_itb_url = "http://is-itb.grid.iu.edu/cgi-bin/status.cgi?format=xml&grid_type=OSG-ITB";

        //VOMS stauts XML
        $this->voms_xml = "/usr/local/voms-monitor/var/xml/voms-monitor.xml";

        //BDII Dyanmic Information XML
        $this->bdii_xml = "/usr/local/bdii-information-gatherer/var/xml/rg_dynamic_information.xml";

        //GOC Ticket Open ticket XML
        $this->gocticket_open_cache = "/tmp/gocticket.open.cache.xml";
        $this->gocticket_open_url = "http://ticket.grid.iu.edu/goc/rest/listopen";

        //locale
        $this->date_format_full = "M j, Y H:i:s e";
        $this->date_format = "M j, Y";

        //number of records to show in one page
        //$this->page_rowcount = 20;

        //number of seconds that gratia input records can be out-of-order.
        //if the delay is less than this, the timestamp will be reset to last reported timestamp. 
        //if it is more than this, the record will be ignored.
        $this->gratia_max_outoforder = 120;

        //number of recrord to pull per each procesew request
        $this->gratia_recordcount = 3500;

        $this->history_graph_image_width = 1000; //this just defines the precision of the graph..
        $this->graph_color = array(
                -1=>"#444", //n/a (not unknown)
                1=>"#4c4", //ok
                2=>"#cc4", //warning
                3=>"#c44", //critical
                4=>"#888", //unknown
                99=>"#66f", //downtime
            );

        ///////////////////////////////////////////////////////////////////////
        //
        // Authorization Configuration
        //
        //list roles for each auth types to authorize
        $this->auth_metrics = array(
            authtype::$auth_guest => array(
                role::$test
            ),
            authtype::$auth_end_user => array(
                role::$test,
                role::$view_admin_email
            ),
            authtype::$auth_osg_staff => array(
                role::$test,
                role::$view_admin_email
            ),
            authtype::$auth_osg_security => array(
                role::$test,
                role::$view_admin_email
            ),
            authtype::$auth_osg_goc => array(
                role::$test,
                role::$view_admin_email,
                role::$see_oim_tree
            )
        );

        ///////////////////////////////////////////////////////////////////////
        // RSV Probe help  (metric_id => URL)
        $this->rsvforum = array(
            //1=>"http://groups.google.com/group/goc-issues/browse_thread/thread/4ef0f6297d80c39c#",
            //2=>"http://groups.google.com/group/goc-issues/browse_thread/thread/b3d918719d51a367",
            //3=>"http://groups.google.com/group/goc-issues/browse_thread/thread/a4840f319768c5f2#",
        );
        //if specific metric_id is not found, use following
        $this->default_rsvforum = "https://twiki.grid.iu.edu/bin/view/MonitoringInformation/RsvProbeList";

        $this->gocticket_url = "https://ticket.grid.iu.edu/goc";

        $this->status_expires_days = 30;

        $this->gratiaweb = "http://gratiaweb.grid.iu.edu/gratia";
        //$this->gratiaweb = "http://t2.unl.edu/gratia";

        $this->cometd_url = "http://comet1.grid.iu.edu:8080/cometd";
        $this->dn_override = array(); //use this in site config to temporarily override dn for testing

        //override this in site config to point to production if necessary
        $this->perfsonar_matrix_url = "http://perfsonar-itb.grid.iu.edu:8080/dashboard2-1.0-SNAPSHOT/matrices";
        $this->perfsonar_host_url = "http://perfsonar-itb.grid.iu.edu:8080/dashboard2-1.0-SNAPSHOT/hosts";
        $this->perfsonar_band_service_id = 130;
        $this->perfsonar_late_service_id = 131;
    }
    function getCacheDir() {
        return "/tmp";
    }
    function default_url($page) {
        switch($page) {
        case 'wizard':
            return "?all_resources=on&gridtype=on&gridtype_1=on&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showfqdn=on&gip_status_attrs_showtestresults=on";
        case 'rg':
            return "?facility=on&facility_10009=on&gridtype=on&gridtype_1=on&summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showfqdn=on&gip_status_attrs_showtestresults=on";
        case "rgpfmatrix":
            return "rgpfmatrix/index?summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showtestresults=on&downtime_attrs_showpast=&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&bdiitree_type=total_jobs&bdii_object=service&bdii_server=is-osg&summary_attrs_showpflatematrix=on&summary_attrs_showpfbandmatrix=on&start_type=7daysago&start_date=04%2F03%2F2013&end_type=now&end_date=04%2F03%2F2013&all_resources=on&facility_10009=on&gridtype=on&gridtype_1=on&service=on&service_130=on&service_131=on&active=on&active_value=1&disable_value=1";
        case "rgpfstatus":
            return "rgpfstatus/index?summary_attrs_showservice=on&summary_attrs_showrsvstatus=on&summary_attrs_showfqdn=on&gip_status_attrs_showtestresults=on&downtime_attrs_showpast=&account_type=cumulative_hours&ce_account_type=gip_vo&se_account_type=vo_transfer_volume&bdiitree_type=total_jobs&bdii_object=service&bdii_server=is-osg&start_type=7daysago&start_date=04%2F03%2F2013&end_type=now&end_date=04%2F03%2F2013&all_resources=on&facility_10009=on&gridtype=on&gridtype_1=on&service=on&service_130=on&service_131=on&active=on&active_value=1&disable_value=1";
        case 'sc':
            return "?all_scs=on&active=on&active_value=1";
        case 'vo':
            return "?all_vos=on&active=on&active_value=1";
        case 'scsummary':
            return "?datasource=summary&summary_attrs_showdesc=on&all_scs=on&active=on&active_value=1";
        case 'vosummary':
            return "?datasource=summary&all_vos=on&active_value=1";
        case 'map':
            return "?map_attrs_showcampusgrid=on&map_attrs_shownr=on&all_sites=on&active=on&active_value=1&disable_value=1&gridtype=on&gridtype_1=on";
        case 'misc':
            return "?count_sg_1&count_active=on&count_enabled=on";
        }
        
        //return htmlspecialchars(base()."/".$page.$param);
    }
}

function base() {
    $last_pos = strrpos($_SERVER["SCRIPT_NAME"], "/");
    return substr($_SERVER["SCRIPT_NAME"], 0, $last_pos);
}
function fullbase($p = "http") {
    if($p == "http") {
        if(isset($_SERVER["HTTPS"])) $p = "https";
    }
    if(isset($_SERVER["HOST_NAME"])) {
        $hostname=$_SERVER["HOST_NAME"];
    } else {
        $hostname=$_SERVER["SERVER_NAME"];
    }
    return $p."://$hostname".base();
}
function fullurl() {
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
}
function strleft($s1, $s2) {
    return substr($s1, 0, strpos($s1, $s2));
}

