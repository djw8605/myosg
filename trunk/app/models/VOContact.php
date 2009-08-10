<?

class VOContact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $sql = "SELECT rc.*, c.*, t.name as contact_type, r.name as rank_type from vo_contact rc ".
                "join contact c on rc.contact_id = c.id ".
                "join contact_type t on rc.contact_type_id = t.id ".
                "join contact_rank r on rc.contact_rank_id = r.id ";
        return $sql;
    }
    public function key() { return "vo_id"; }
}
