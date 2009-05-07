<?
class VOReportFQAN extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["vo_report_name_id"])) {
            $where = " where vo_report_name_id = ".$params["vo_report_name_id"];
        }
        return "SELECT * FROM vo_report_name_fqan $where";
    }
    public function key() { return "vo_report_name_id"; }
}
