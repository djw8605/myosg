<?

class DN extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "select * from dn";
    }
    public function key() { return "id"; }
}

?>
