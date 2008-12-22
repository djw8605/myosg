<?
class Metric extends CachedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["service_id"])) {
            $where .= " and service_id = ".$params["service_id"];
        }
        if(isset($params["critical"])) {
            $where .= " and critical = ".$params["critical"];
        }
        $sql = "select m.*, s.critical from oim.metric_service s join oim.metric m on s.metric_id = m.metric_id $where ";
        dlog($sql);
        return $sql;
    }
}
