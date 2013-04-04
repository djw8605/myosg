<?php

//zend viewer http://devzone.zend.com/article/3412
class Zend_View_Helper_Alerts extends Zend_View_Helper_Abstract {

    //flush all messages pending to be displayed
    public function alerts()
    {
        $out = "";

        $message = new Zend_Session_Namespace('message');
        if(isset($message->alerts)) {
            foreach($message->alerts as $alert) {
                $type = $alert["type"];

                $out .= "<div class=\"alert alert-$type\">";
                $out .= "<a class=\"close\" href=\"#\" data-dismiss=\"alert\">x</a>";
                $out .= $alert["html"]."</div>";
            }
        }
        $message->alerts = array();
        return $out;
    }
}

