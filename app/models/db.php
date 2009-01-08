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

function dump_db_profile()
{
    $out = "";

    if(Zend_Registry::isRegistered('db')) {
        $db = Zend_Registry::get('db');
        $profiler = $db->getProfiler();

        $totalTime    = round($profiler->getTotalElapsedSecs(), 2);
        $queryCount   = $profiler->getTotalNumQueries();
        $longestTime  = 0;
        $longestQuery = null;

        if($profiler->getQueryProfiles()) {
            $out .= "DB PROFILE ----------------------------------------------------------------------\n";
            $out .= "Executed $queryCount queries in $totalTime seconds.\n";

            foreach ($profiler->getQueryProfiles() as $query) {
                if ($query->getElapsedSecs() > $longestTime) {
                    $longestTime  = $query->getElapsedSecs();
                    $longestQuery = $query->getQuery();
                }
                $out .= "[".round($query->getElapsedSecs(),2)." seconds]\n\t".$query->getQuery()."\n";
            }
        }
    }
    return $out;
}
