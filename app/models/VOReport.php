<?
class VOReport extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["vo_id"])) {
            $where = " where vo_id = ".$params["vo_id"];
        }
        return "SELECT * FROM vo_report_name $where";
    }
    public function key() { return "vo_id"; }
}
