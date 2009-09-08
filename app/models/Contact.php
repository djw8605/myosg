<?

class Contact extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "select * from contact";
    }
    public function key() { return "id"; }
}

?>
