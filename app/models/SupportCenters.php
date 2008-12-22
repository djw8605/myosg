<?
class SupportCenters extends CachedModel
{
    public function sql($param)
    {
        return "select * from oim.supportcenter where active = 1 and disable = 0";
    }
}
