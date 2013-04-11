<?php

class Zend_View_Helper_Perfmatrix extends Zend_View_Helper_Abstract {
    public $view;
    public function setView(Zend_View_Interface $view) { $this->view = $view; }

    public function perfmatrix($header, $services, $cols) {
        ob_start();
        echo "<table class=\"table table-bordered table-condensed\">";
        
        echo "<thead>";
        echo "<tr><th colspan='".(count($cols)+1)."'>$header</th></tr>";
        echo "<tr class=\"matrix_header\"><th>Destination</th>";
        foreach($services as $service) {
            echo "<th>$service</th>";
        }
        echo "</tr>";
        echo "</thead>";

        foreach($cols as $fqdn=>$col) {
            if(count($col) == 0) continue;
            echo "<tr>";
            echo "<td width=\"20%\">$fqdn</td>";
            foreach($col as $service_idx => $detail) {
                $cellid = $detail->cellid;
                echo "<td width=\"40%\" class=\"cell\" data-cellid=\"$cellid:$service_idx\">";

                echo "<div class=\"row-fluid\">";

                switch($detail->result->status) {
                case 0: 
                    $type = "success"; 
                    $label = "OK";
                    $msg = $detail->result->message; 
                    break;//OK
                case 1: 
                    $type = "warning";
                    $label = "Warning";
                    $msg = $detail->result->message; 
                    break;//OK
                case 2: 
                    $type = "important"; 
                    $label = "Critical";
                    $msg = $detail->result->message; 
                    break;//OK
                default: 
                    $type = ""; //default - gray
                    $label = "N/A";
                    $msg = $detail->result->status_label;
                    //TODO - what should I do with message?
                }
                echo "<div class=\"span2\"><span class=\"label label-$type\">$label</span></div>";
                echo "<div class=\"span6\">$msg</div>";

                //time
                $time = $detail->result->time;
                echo "<div class=\"span4\">";
                echo "<time datetime=\"$time\" class=\"muted pull-right\" title=\"$time\"></time>";
                echo "</div>";

                echo "</div>";//row-fluid

                echo "</td>";//end service
            }
            echo "</tr>";
            
            /*
            //debug detail
            echo "<div class=\"debug\"><span class=\"debug-toggle\">Debug</span>";
            echo "<pre class=\"debug-detail\">";
            var_dump($col);
            echo "</pre>";
            */
        }
        echo "</table>";
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }
}
