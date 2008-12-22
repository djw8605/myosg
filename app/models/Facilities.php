<?
class Facilities extends CachedModel
{
    public function sql($param)
    {
        return "select * from oim.facility where active = 1 and disable = 0";
    }
}
