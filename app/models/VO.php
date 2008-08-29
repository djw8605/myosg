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
        $sql = "select * from $schema.virtualorganization where active = 1 and disable = 0 order by short_name";
        return $this->db->fetchAll($sql);
    }

    public function pullMemberVOs($resource_id = null)
    {
        $schema = config()->db_oim_schema;
        $sql = "SELECT VOM.*, R.name, VO.long_name, VO.short_name FROM vo_matrix VOM
                  LEFT JOIN $schema.resource R ON (VOM.resource_id = R.resource_id )
                  JOIN $schema.virtualorganization VO on VOM.vo_id = VO.vo_id";
        if($resource_id !== null) $sql .= " where VOM.resource_id = $resource_id";
        return $this->db->fetchAll($sql);
    }
}

?>
