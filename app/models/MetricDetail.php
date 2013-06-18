<?

class MetricDetail extends Model
{
    public function ds() { return "gratia"; }
    public function sql($params)
    {
        $id = $params["id"];
        $sql = "SELECT dbid as id, DetailsData as detail, GatheredAt as gathered_at, ServiceUri as service_uri from MetricRecord where dbid = $id";
        return $sql;
    }

    public function getXmlDetail($id) {
        return db($this->ds())->fetchOne("SELECT extraxml AS detail from MetricRecord_Xml WHERE dbid = $id");
    }
}
