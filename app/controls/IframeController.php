<?

class IframeController extends ControllerBase
{ 
    public function breads() { return array(); }
    public static function default_title() { return "Other Contents"; }
    public static function default_url($query) { return ""; }

    public function indexAction() {
        $this->setpagetitle(ResourcesController::default_title());
    }
    public function oimAction() {
        $this->view->url = "https://rsv.grid.iu.edu";
        $this->setpagetitle("OIM");
        $this->render("frame");
    }
} 
