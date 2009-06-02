<?

class Service extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "SELECT * from service order by name";
    }
    public function key() { return "id"; }
}
