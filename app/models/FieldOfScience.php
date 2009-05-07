<?

class FieldOfScience extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "SELECT * FROM field_of_science";
    }
    public function key() { return "id"; }
}
