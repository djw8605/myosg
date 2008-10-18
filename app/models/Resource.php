<?

class Resource extends CachedModel
{
    public function sql($param)
    {
        $where = "where active = 1 and disable = 0";
        if(isset($params["resource_id"])) {
            $resource_id = $params["resource_id"];
            $where .= " and resource_id = $resource_id";
        }
        return "SELECT resource_id id, name, fqdn FROM oim.resource $where";
    }
}
