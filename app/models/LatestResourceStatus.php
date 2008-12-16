<?
class LatestResourceStatus extends Model
{
    public function sql($params) {
        $time = time();
        $sql = "select a.* from statuschange_resource a, (SELECT resource_id, max(timestamp) timestamp FROM statuschange_resource where timestamp < $time group by resource_id) b where a.resource_id = b.resource_id and a.timestamp = b.timestamp";
        dlog($sql);
        return $sql;
    }
}
