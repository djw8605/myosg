<?

$g_db = null;
function connect_db()
{
    global $g_db;
    $g_db = array();

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // For RSVExtra
    ///////////////////////////////////////////////////////////////////////////////////////////////
    $rsv_db = Zend_Db::factory(config()->db_type, config()->rsvextra_dbparam);
    $rsv_db->setFetchMode(Zend_Db::FETCH_OBJ);
    $g_db["rsv"] = $rsv_db;

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // For OIM
    ///////////////////////////////////////////////////////////////////////////////////////////////
    $oim_db = Zend_Db::factory(config()->db_type, config()->oim_dbparam);
    $oim_db->setFetchMode(Zend_Db::FETCH_OBJ);
    $g_db["oim"] = $oim_db;

    //profile db via firebug
    if(config()->debug) {
        $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        $profiler->setEnabled(true);
        $rsv_db->setProfiler($profiler);
        $oim_db->setProfiler($profiler);
    }

}

function db($db) {
    global $g_db;
    if($g_db == null) {
        connect_db();
    }
    return $g_db[$db];
}

