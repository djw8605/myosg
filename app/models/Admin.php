<?

class Admin extends Model
{
    public function sql($params) { return ""; }    
    public function ds() { return "rsv"; }

    public function optimize()
    {
        $sql = "OPTIMIZE TABLE `statuschange_resource`";
        db("rsv")->query($sql);
        
        $sql = "OPTIMIZE TABLE `statuschange_service`";
        db("rsv")->query($sql);

        $sql = "OPTIMIZE TABLE `metricdetail`";
        db("rsv")->query($sql);
   }
}

?>
