<?php
/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class ErrorController extends Zend_Controller_Action 
{ 
    public function errorAction()
    { 
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                //$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found'); //looks like this gets overriden by render()
                $this->render('404');
                break;
            default:
                //application error !!
                $exception = $errors->exception;
                $log = "";
                $log .= "Error Message ---------------------------------------\n";
                $log .= $exception->getMessage()."\n\n";

                $log .= "Stack Trace -----------------------------------------\n";
                $log .= $exception->getTraceAsString()."\n\n";

                $log .= "Server Parameter ------------------------------------\n";
                $log .= print_r($_SERVER, true)."\n\n";

                if(config()->debug) {
                    $this->view->content = "<pre>".$log."</pre>";
                } else {
                    $this->view->content = "Encountered an application error.\n\n";
                    if(config()->elog_email) {
                        mail(config()->elog_email_address, "[myosg] Error: ".$exception->getMessage(), $log, "From: ".php_uname('n'));
                        $this->view->content .= "Detail of this error has been sent to the development team for further analysis.";
                    }
                }

                elog($log);
                break;
        }
    } 
} 
