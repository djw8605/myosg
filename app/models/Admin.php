<?

class Admin extends Model
{
    public function sql($params) { return "" }    
    public optimize()
    {
        $sql = "OPTIMIZE TABLE `statuschange_resource`";
        $sql = "OPTIMIZE TABLE `statuschange_service`";
   }
}

?>
