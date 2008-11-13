<?

class Downtime extends CachedModel
{
    public function sql($params)
    {
        $where = " where disable = 0";
        if(isset($params["resource_id"])) {
            $where .= " and resource_id = ".$params["resource_id"];
        }

        if(isset($params["start_time"]) && isset($params["end_time"])) {
            $start_time = $params["start_time"];
            $end_time = $params["end_time"];

            $where .= " and ( ";

            //end overwrap
            $where .= "(UNIX_TIMESTAMP(end_time) > $start_time and UNIX_TIMESTAMP(end_time) < $end_time) or";
            //start overwarp
            $where .= "(UNIX_TIMESTAMP(start_time) > $start_time and UNIX_TIMESTAMP(start_time) < $end_time) or";
            //contained
            $where .= "(UNIX_TIMESTAMP(start_time) > $start_time and UNIX_TIMESTAMP(end_time) < $end_time) or";
            //outside
            $where .= "(UNIX_TIMESTAMP(start_time) < $start_time and UNIX_TIMESTAMP(end_time) > $end_time)";

            $where .= " )"; 
        }

        $sql = "select rd.*, UNIX_Timestamp(start_time) as unix_start_time, UNIX_Timestamp(end_time) as unix_end_time ".
                " from oim.resource_downtime rd ". 
                $where.
                " order by start_time";
        return $sql;
    }
}

?>
