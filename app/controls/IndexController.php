<?

class IndexController extends Zend_Controller_Action 
{ 
    public function indexAction() 
    { 
    } 

    public function jsAction() { 
        header("Content-Type: text/javascript");
    } 

    public function menuAction()
    {
        header("Content-Type: text/plain");
    }

    public function gmaptestAction() { } 
} 
