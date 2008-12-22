<?

class ResourceContact extends CachedModel
{
    public function sql($params)
    {
        $where = "";
        if(isset($params["resource_id"])) {
            $where .= " and sc.resource_id = ".$params["resource_id"];
        }
        $sql = "SELECT sc.person_id, p.first_name, p.last_name, t.description as contact_type, r.description as rank_type from oim.resource_contact sc ".
                "join oim.person p on sc.person_id = p.person_id ".
                "join oim.contact_type t on sc.type_id = t.type_id ".
                "join oim.contact_rank r on sc.rank_id = r.rank_id ".
                "where sc.active = 1 $where order by sc.type_id"; 
        return $sql;
    }
}
