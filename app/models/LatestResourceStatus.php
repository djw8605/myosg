<?
class LatestResourceStatus extends Model
{
    public function sql($params) {
        $sql = "SELECT * FROM statuschange_resource s group by resource_id having max(timestamp)";
        return $sql;
    }
}
