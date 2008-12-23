<?

class Admin extends Model
{
    public function sql($params) { return ""; }    

    public function optimize()
    {
        $sql = "OPTIMIZE TABLE `statuschange_resource`";
        $this->db->query($sql);
        
        $sql = "OPTIMIZE TABLE `statuschange_service`";
        $this->db->query($sql);

        $sql = "OPTIMIZE TABLE `metricdetail`";
        $this->db->query($sql);
   }
}

?>
