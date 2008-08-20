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

    public function pullMemberVOs($resource_id)
    {
        $sql = "select * from vo_matrix where resource_id = $resource_id";
        return $this->db->fetchAll($sql);
    }
}

?>
