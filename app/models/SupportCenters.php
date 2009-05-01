<?
class SupportCenters extends CachedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        return "select * from sc where active = 1 and disable = 0 order by name";
    }
}
