<?
///////////////////////////////////////////////////////////////////////////////
//
// Common Configuration
//
///////////////////////////////////////////////////////////////////////////////

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
        $this->version = "1.5";
        //application to display or used in email
        $this->app_name = "MyOSG";
        $this->app_subname = "by Grid Operations Center";
        $this->copyright = "Copyright &copy; 2009 Indiana University - Developed for Open Science Grid";
        //application name used in places such as session name
        $this->app_id = "rsv_viewer";

        //banner to show on all pages
        $this->banner = null;

        //http://code.google.com/apis/maps/signup.html
        $this->gmap_key = "get your own key";

        //if metric age is longer than this value, it is considred stale
        $this->metric_considered_old = 3600*24;

        $this->db_type = "Pdo_Mysql";

        //rsvextra database info
        $this->rsvextra_dbparam = array(
            'unix_socket'     => '/usr/local/rsv-gratia-collector-1.0/vdt-app-data/mysql5/var/mysql.sock',
            'host'     => "localhost",
            'username' => "myosg",
            'password' => "somepass",
            'dbname'   => "rsvextra",
            'port'     => 49152
        );

        $this->oim_dbparam = array(
            'unix_socket'     => '/usr/local/rsv-gratia-collector-1.0/vdt-app-data/mysql5/var/mysql.sock',
            'host'     => "localhost",
            'username' => "oim",
            'password' => "somepass",
            'dbname'   => "oim",
            'port'     => 49152
        );

        //first page to load when user first visit the page
        //$this->initial_page = "home_start"; //needs to be a menu ID
        $this->initial_page = "current_detail"; //needs to be a menu ID

        //executes debuging code
        $this->debug = false;
        $this->logfile = "app/logs/log.txt";
        $this->error_logfile = "app/logs/error.txt";
        $this->audit_logfile = "app/logs/audit.txt";

        //elog email
        $this->elog_email = false;
        $this->elog_email_address = "overrideme";

        //log db profile (only available in debug mode)
        $this->profile_db = false;

        //forward http request to https
        $this->force_https = true;

        ///////////////////////////////////////////////////////////////////////////////////////////
        //cache filenames
        
        //vomatrix
        $this->vomatrix_xml_cache = $this->getCacheDir()."/cache.vomatrix.xml";
        $this->current_resource_status_xml_cache = $this->getCacheDir()."/cache.current_resource_<ResourceID>.xml";
        $this->aandr_cache = $this->getCacheDir()."/cache.aandr_<start_time>_to_<end_time>.xml";
        
        //gip validation
        $this->gip_summary = "/usr/local/gip-validator/var/results/xml_osg/gipvalidate_summary.xml"; 
        $this->gip_summary_itb = "/usr/local/gip-validator/var/results/xml_osg_itb/gipvalidate_summary.xml"; 
        $this->gip_detail = "/usr/local/gip-validator/var/results/xml_osg/gipvalidate_<resource_name>_detail.xml"; 
        $this->gip_detail_itb = "/usr/local/gip-validator/var/results/xml_osg_itb/gipvalidate_<resource_name>_detail.xml"; 

        //bdii raw files
        $this->cemonbdii_url = "http://is.grid.iu.edu/cgi-bin/status.cgi?format=xml";
        $this->cemonbdii_itb_url = "http://is-itb.grid.iu.edu/cgi-bin/status.cgi?format=xml";

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
                1=>"#4f4", //ok
                2=>"#ff4", //warning
                3=>"#f44", //critical
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
        $this->default_rsvforum = "http://groups.google.com/group/rsv-issues/topics";
    }
    function getCacheDir() {
        return "/tmp";
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

    return $p."://".$_SERVER["HTTP_HOST"].base();
}

