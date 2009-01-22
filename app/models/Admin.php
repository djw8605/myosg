<?

class Admin extends Model
{
    public function sql($params) { return ""; }    

    public function optimize()
    {
        $sql = "OPTIMIZE TABLE `statuschange_resource`";
        db()->query($sql);
        
        $sql = "OPTIMIZE TABLE `statuschange_service`";
        db()->query($sql);

        $sql = "OPTIMIZE TABLE `metricdetail`";
        db()->query($sql);
   }
}

?>
