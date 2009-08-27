<?

class MetricDetail extends Model
{
    public function ds() { return "gratia"; }
    public function sql($params)
    {
        $id = $params["id"];
        return "SELECT dbid as id, DetailsData as detail from MetricRecord where dbid = $id";
    }
}
