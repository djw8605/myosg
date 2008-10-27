<?

class StatusChangesService extends CachedModel
{
    public function sql($params)
    {
        $sql = "select * from statuschange_service s order by timestamp desc limit 30";
        return $sql;
    }
}

?>
