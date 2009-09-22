<?php

class OIMLog
{
    public function ds() { return "oim"; }
    public function getByModel($model) 
    {
        $sql = "select *, UNIX_TIMESTAMP(timestamp) as unix_timestamp from log where model = '$model' order by timestamp desc";
        return db($this->ds())->fetchAll($sql);
    }
}
