<?

class IframeController extends ControllerBase
{ 
    public function pagename() { return "iframe"; }
    public function indexAction() {
        $this->view->page_title = "Other Contents";
    }
    public function oimAction() {
        $this->view->page_title = "OIM";
        $this->view->url = "https://rsv.grid.iu.edu";
       // $this->view->url = "http://oim-dev.grid.iu.edu";
        $this->render("frame");
    }
} 
