<?
class ServiceStatusChange extends Model
{
    public function sql($params) {

        $resource_id = $params["resource_id"];
        $service_id = $params["service_id"];
        $start_time = $params["start_time"];
        $end_time = $params["end_time"];
        
        $sql = "select * from statuschange_service where service_id = $service_id and resource_id = $resource_id and timestamp >= coalesce((select max(timestamp) from statuschange_service where service_id = $service_id and resource_id = $resource_id and timestamp <= $start_time), 0) and timestamp <= $end_time order by timestamp;"; 
        return $sql;
    }
}
