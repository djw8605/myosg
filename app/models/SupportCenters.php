<?
class SupportCenters extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        return "select * from sc order by name";
    }
    public function key() { return "id"; }
}
