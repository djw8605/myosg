<?

class MetricDetail extends Model
{
    public function sql($params)
    {
        $id = $params["id"];
        return "SELECT * from metricdetail where id = $id";
    }
}
