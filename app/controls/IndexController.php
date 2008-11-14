<?

class IndexController extends Zend_Controller_Action 
{ 
    public function indexAction() 
    { 
        $this->_redirect("resources");
    }

/*
    private function setInitialPage()
    {
        $page = config()->initial_page;
        if(isset($_REQUEST["page"])) {
            $dirty_page = $_REQUEST["page"];
            $validator = new Zend_Validate_Regex("/^[a-z_-]+$/");
            if($validator->isValid($dirty_page)) {
                $page = $dirty_page;
            }
        } 
        
        $this->view->initial_page = $page;
    } 

    public function jsAction() { 
        header("Content-Type: text/javascript");
        $this->setInitialPage();
    } 

    public function menuAction()
    {
        header("Content-Type: text/plain");
    }

    public function gmaptestAction() { } 
*/
} 
