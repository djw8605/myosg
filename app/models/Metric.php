<?

class Metric extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "SELECT * from metric order by name";
    }
    public function key() { return "id"; }
}
