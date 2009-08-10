<?

class MetricService extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($params)
    {
        return "SELECT * from metric_service";
    }
    public function key() { return "metric_id"; }
}
