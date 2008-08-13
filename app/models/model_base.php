<?php


class model_base extends Zend_Db_Table_Abstract
{
    public function __construct()
    {
        //open connection to db if it isn't done yet
        if(!Zend_Registry::isRegistered("db")) {
            connectdb();
        }

        //tell it to grab db connection from the registry 
        parent::__construct(array("db" => "db"));
    }
}
