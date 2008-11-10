<?

class Resource extends CachedModel
{
    public function sql($params)
    {
        $where = "where active = 1 and disable = 0";
        if(isset($params["resource_id"])) {
            $resource_id = $params["resource_id"];
            $where .= " and resource_id = $resource_id";
        }
        $sql = "SELECT resource_id id, name, fqdn FROM oim.resource $where";
        return $sql;
    }
}
