<?
class Metric extends CachedModel
{
    public function sql($param)
    {
        return "select * from oim.metric where active = 1 and disable = 0";
    }
}
