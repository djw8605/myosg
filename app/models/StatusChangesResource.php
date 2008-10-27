<?

class StatusChangesResource extends CachedModel
{
    public function sql($params)
    {
        $sql = "select * from statuschange_resource s order by timestamp desc limit 30";
        return $sql;
    }
}

?>
