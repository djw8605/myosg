<?
class VOReportContact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["vo_report_name_id"])) {
            $where = " where vo_report_name_id = ".$params["vo_report_name_id"];
        }
        return "SELECT * FROM vo_report_contact v join contact c on v.contact_id = c.id $where";
    }
    public function key() { return "vo_report_name_id"; }
}
