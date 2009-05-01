<?

class Status extends CachedIndexedModel
{
    public function ds() { return "oim"; }
    public function sql($param)
    {
        return "select * from metric_status";
    }
    public function key() { return "id"; }

    static public function getStatus($status_id) {
        switch($status_id) {
        //TODO -- pull from MetricStatus model
        case 1: return "OK";
        case 2: return "WARNING";
        case 3: return "CRITICAL";
        case 4: return "UNKNOWN";

        case 99: return "DOWNTIME";
        }
        return "(unknow:$status_id)";
    }
}


?>
