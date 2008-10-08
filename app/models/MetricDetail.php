<?

class MetricDetail extends Model
{
    public function sql($param)
    {
        return "SELECT * from metricdetail where metricdata_id = $param";
    }
}
