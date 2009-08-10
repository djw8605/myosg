<?

class DowntimeClass extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "select * from downtime_class";
    }
    public function key() { return "id"; }
}

?>
