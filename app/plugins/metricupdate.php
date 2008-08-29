<?

class Plugin_MetricUpdate
{
    public function dispatch($resource_id, $metric_id, $metric)
    {
//        dlog("metric id $metric_id ".$metric->status);
        if($metric_id == 19) {
            if($metric->status == "OK") {
                $this->processVoSupported($resource_id, $metric_id, $metric);
            }
        }
//        dlog("dispatch end..");
    }
    private function processVoSupported($resource_id, $metric_id, $metric)
    {
        $detail = $metric->detail;

        $token = "# List of VOs this site claims to support ";
        $ret = strstr($detail, $token);

        if($ret !== false) {
            //found the correct token.. let's parse it..
            $lstr = substr($detail, strlen($token));
            $vos = split(" ", $lstr);
            //dlog(print_r($vos, true));

            $db = Zend_Registry::get("db");

            //remove old data
            $db->query("delete from vo_matrix where resource_id = $resource_id");

            //insert new data
            foreach($vos as $vo) {
                //lookup vo_id
                $schema = config()->db_oim_schema;
                $lvo = strtolower($vo);
                $sql = "select vo_id from $schema.virtualorganization where lower(short_name) = '$lvo'";
                //dlog("looking up id for $lvo");
                $vo_id = $db->fetchOne($sql);
                if($vo_id != "") {
                    $sql = "insert into vo_matrix (resource_id, vo_id) values ($resource_id, $vo_id)";
                    //dlog("inserting $lvo($vo_id) to vo_matrix for resource ID: $resource_id");
                    $db->query("insert into vo_matrix (resource_id, vo_id) values ($resource_id, $vo_id)");
                } else {
                    //elog("couldn't find '$lvo' in virtualorganization table while processing metric ".$metric->dbid);
                }
            }
        } else {
            //elog("received vosupported update with OK status, but the detail format was not what was expteded. metric ID:".$metric->dbid." Resource ID: ".$resource_id." [$detail]");
        }
    }
}
