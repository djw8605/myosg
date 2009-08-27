<?

$g_db = null;
function connect_db()
{
    global $g_db;
    $g_db = array();

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // For RSVExtra
    ///////////////////////////////////////////////////////////////////////////////////////////////
    $db = Zend_Db::factory(config()->db_type, config()->rsvextra_dbparam);
    $db->setFetchMode(Zend_Db::FETCH_OBJ);
    $g_db["rsv"] = $db;

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // For OIM
    ///////////////////////////////////////////////////////////////////////////////////////////////
    $db = Zend_Db::factory(config()->db_type, config()->oim_dbparam);
    $db->setFetchMode(Zend_Db::FETCH_OBJ);
    $g_db["oim"] = $db;

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // For MyOSG
    ///////////////////////////////////////////////////////////////////////////////////////////////
    $db = Zend_Db::factory(config()->db_type, config()->myosg_dbparam);
    $db->setFetchMode(Zend_Db::FETCH_OBJ);
    $g_db["myosg"] = $db;

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // For Gratia MetricDetail
    ///////////////////////////////////////////////////////////////////////////////////////////////
    $db = Zend_Db::factory(config()->db_type, config()->gratia_dbparam);
    $db->setFetchMode(Zend_Db::FETCH_OBJ);
    $g_db["gratia"] = $db;

    //profile db via firebug
    if(config()->debug) {
        $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
        $profiler->setEnabled(true);
        $g_db["rsv"]->setProfiler($profiler);
        $g_db["oim"]->setProfiler($profiler);
        $g_db["myosg"]->setProfiler($profiler);
        $g_db["gratia"]->setProfiler($profiler);
    }

}

function db($db) {
    global $g_db;
    if($g_db == null) {
        connect_db();
    }
    return $g_db[$db];
}

