<?

require_once("model_base.php");

class VO
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
        $sql = "select * from $schema.virtualorganization";
        return $this->db->fetchAll($sql);
    }

    public function pullMemberVOs($resource_id = null)
    {
        $schema = config()->db_oim_schema;
        $sql = "select m.*, r.name, v.long_name from (vo_matrix m left join $schema.resource r on m.resource_id = r.resource_id ) join $schema.virtualorganization v on m.vo_id = v.vo_id";
        if($resource_id !== null) $sql .= " where m.resource_id = $resource_id";
        return $this->db->fetchAll($sql);
    }
}

?>
