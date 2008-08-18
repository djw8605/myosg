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

class role
{
    //role to debug authorization issues
    public static $test = 0;

    //view admin email address and able to open OS email client.
    public static $view_admin_email = 1;
}

class common_config
{
    function __construct() {
        ///////////////////////////////////////////////////////////////////////
        //
        // Main configuration (Mostly common stuff)
        //

        //application version to use for version specific data structures, etc.
        $this->version = "1.0";
        //application to display or used in email
        $this->app_name = "OSG Operations Dashboard";
        //application name used in places such as session name
        $this->app_id = "rsv_viewer";

        //http://code.google.com/apis/maps/signup.html
        $this->gmap_key = "get your own key";

        //if metric age is longer than this value, it is considred stale
        $this->metric_considered_old = 3600*24;

        //database info
        $this->db_type = "Pdo_Mysql";
        $this->db_host = "overrideme.example.com";
        $this->db_username = "overrideme";
        $this->db_password = "overrideme";
        $this->db_schema = "rsvextra";
        $this->db_port = 49152;

        $this->db_oim_schema = "oim";

        //first page to load when user first visit the page
        $this->initial_page = "current";

        //executes debuging code
        $this->debug = false;
        $this->logfile = "app/logs/log.txt";
        $this->error_logfile = "app/logs/error.txt";

        //elog email
        $this->elog_email = false;
        $this->elog_email_address = "overrideme";

        //log db profile (only available in debug mode)
        $this->profile_db = false;

        //forward http request to https
        $this->force_https = true;

        //cache filenames
        $this->cache_filename_latest_metrics = $this->getCacheDir()."/latest_detail";
        $this->cache_filename_latest_overall = $this->getCacheDir()."/latest_overall";

        //locale
        $this->date_format_full = "M j, Y h:i A";
        $this->date_format = "M j, Y";

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
                role::$view_admin_email
            )
        );
    }
    function getCacheDir() {
        return "app/cache";
    }
}

//TODO - I need to figure this out automatically from SERVER_ADDR, etc..
function base() { return "/trunk";}
