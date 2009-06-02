<?

class DowntimeSeverity extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "select * from downtime_severity";
    }
    public function key() { return "id"; }
}

?>
