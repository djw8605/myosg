<?

class MetricDetail extends Model
{
    public function ds() { return "rsv"; }
    public function sql($params)
    {
        $id = $params["id"];
        return "SELECT * from metricdetail where id = $id";
    }
}
