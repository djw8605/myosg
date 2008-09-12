<?

function connectdb()
{
    $db = Zend_Db::factory(config()->db_type, array(
        'host'     => config()->db_host,
        'username' => config()->db_username,
        'password' => config()->db_password,
        'dbname'   => config()->db_schema,
        'port'     => config()->db_port
    ));

    $db->setFetchMode(Zend_Db::FETCH_OBJ);

    if(config()->profile_db) {
        $db->getProfiler()->setEnabled(true);
    }

    Zend_Registry::set('db', $db);

    return $db;
}

function log_db_profile()
{
    if(Zend_Registry::isRegistered('db')) {
        $db = Zend_Registry::get('db');
        $profiler = $db->getProfiler();

        $totalTime    = $profiler->getTotalElapsedSecs();
        $queryCount   = $profiler->getTotalNumQueries();
        $longestTime  = 0;
        $longestQuery = null;

        dlog('----------------------------------------------------------------------');
        dlog('DB PROFILE');
        dlog('Executed ' . $queryCount . ' queries in ' . $totalTime . ' seconds');

        foreach ($profiler->getQueryProfiles() as $query) {
            if ($query->getElapsedSecs() > $longestTime) {
                $longestTime  = $query->getElapsedSecs();
                $longestQuery = $query->getQuery();
            }
            dlog("Executed Query: (in ".$query->getElapsedSecs().")");
            dlog($query->getQuery());
        }

        dlog('----------------------------------------------------------------------');
        //dlog('Longest query length: ' . $longestTime);
        //dlog("Longest query: \n" . $longestQuery);
    }
}
