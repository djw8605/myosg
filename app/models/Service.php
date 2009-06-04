<?

class Service extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        $where = "";
        if(isset($params["service_group_id"])) {
            $service_group_id = $params["service_group_id"];
            $where = "WHERE service_group_id=$service_group_id";
        }
        return "SELECT * from service $where order by name";
    }
    public function key() { return "id"; }
}
