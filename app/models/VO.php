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
        //$sql = "select m.*, r.name, v.long_name from (vo_matrix m left join $schema.resource r on m.resource_id = r.resource_id ) join $schema.virtualorganization v on m.vo_id = v.vo_id";
        $sql = "SELECT VOM.*, R.name, VO.long_name
  FROM vo_matrix VOM
  LEFT JOIN $schema.resource R ON (VOM.resource_id = R.resource_id )
  JOIN $schema.resource_resource_group RRG ON (RRG.resource_id=R.resource_id)
  JOIN $schema.resource_group RG ON (RG.resource_group_id=RRG.resource_group_id)
  JOIN $schema.resource_service RS ON (RS.resource_id=R.resource_id)
  JOIN $schema.service S ON (S.service_id=RS.service_id)
  JOIN $schema.virtualorganization VO on VOM.vo_id = VO.vo_id
WHERE RG.osg_grid_type_id IN (SELECT grid_type_id FROM $schema.osg_grid_type OGT WHERE OGT.short_name = 'OSG-ITB')";
        if($resource_id !== null) $sql .= " and VOM.resource_id = $resource_id";
        return $this->db->fetchAll($sql);
    }
}

?>
