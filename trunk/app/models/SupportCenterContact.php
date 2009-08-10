<?

class SupportCenterContact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "where 1 = 1 ";
        if(isset($params["sc_id"])) {
            $where .= " and sc_id = ".$params["sc_id"];
        }
        if(isset($params["contact_type_id"])) {
            $where .= " and sc.contact_type_id = ".$params["contact_type_id"];
        }
        if(isset($params["contact_rank_id"])) {
            $where .= " and sc.contact_rank_id = ".$params["contact_rank_id"];
        }
        return "SELECT sc.sc_id, sc.contact_id, p.*, t.name as contact_type, r.name as contact_rank from sc_contact sc ".
                "join contact p on sc.contact_id = p.id ".
                "join contact_type t on sc.contact_type_id = t.id ".
                "join contact_rank r on sc.contact_rank_id = r.id ".
                "$where order by sc.contact_type_id";
    }
    public function key() { return "sc_id"; }
}
