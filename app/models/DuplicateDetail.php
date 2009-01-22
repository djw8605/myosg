<?
class DuplicateDetail extends CachedModel
{
    public function sql($params)
    {
/*
        $where = "";
        if(isset($params["critical"])) {
            $where .= " and critical = ".$params["critical"];
        }
*/
        $sql = "SELECT detail, count(*) count , GROUP_CONCAT(id order by id separator ' ') ids FROM metricdetail m where id < 1000000 group by detail having count(*) > 1 ";
        return $sql;
    }

    public function dedup($ids)
    {
        $newid = array_shift($ids);
        $ids_str = implode(",", $ids);

        db()->beginTransaction();
        try {
            $sql = "update metricdata set detail_id = $newid where id in ($ids_str);<br/>";
            db()->query($sql);
            echo $sql;

            $sql = "delete from metricdetail where id in ($ids_str);<br/>";
            db()->query($sql);
            echo $sql;

            db()->commit();
        } catch (Exception $e) {
            $db->rollBack();
            //rethrow
            throw $e;
        }
    }
}
