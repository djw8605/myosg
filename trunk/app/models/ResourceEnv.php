<?

class ResourceEnv extends CachedIndexedModel
{
    public function ds() { return "rsv"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["metric_id"])) {
            $where = "where metric_id = ".$params["metric_id"];
        }
        $sql = "SELECT * FROM resource_detail $where";
        return $sql;
    }
    public function key() { return "resource_id"; }
}
