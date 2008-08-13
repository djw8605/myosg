<?

class ResourceGeo
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
    }

    public function fetchAll()
    {
        $schema = config()->db_oim_schema;
        $sql = "SELECT r.resource_id, rrg.resource_group_id, s.* from (($schema.resource r join $schema.resource_resource_group rrg on r.resource_id = rrg.resource_id) join $schema.resource_group rg on rrg.resource_group_id = rg.resource_group_id) join $schema.site s on rg.site_id = s.site_id order by site_id";

        return $this->db->fetchAll($sql);
    }
}
