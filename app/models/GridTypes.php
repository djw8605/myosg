<?

require_once("model_base.php");

class GridTypes
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
        $sql = "select * from oim.osg_grid_type";
        return $this->db->fetchAll($sql);
    }
}


?>
