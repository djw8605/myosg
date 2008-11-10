<?

//*pagename* is just a name of controller. so pageid might be a better naming..
$g_pagename = "please_reset_me";
function pagename() { global $g_pagename; return $g_pagename; }
function setpagename($name) { 
    global $g_pagename; 
    $g_pagename = $name; 
}

abstract class ControllerBase extends Zend_Controller_Action
{
    public function init()
    {
        setpagename($this->pagename());
        $this->initbread();
    }

    protected function initbread()
    {
        $pages = $this->breads();
        $this->view->breadcrumbs = array();
        foreach($pages as $page) {
            if(isset($_SESSION["crumbs"][$page])) {
                $crumb = $_SESSION["crumbs"][$page]; 
                $url = $crumb[0];
                $title = $crumb[1];
            } else {
                $controllername = strtoupper(substr($page, 0, 1)).substr($page, 1)."Controller";
                include("$controllername.php");
                $title = eval("return $controllername::default_title();");
                $url = eval("return $controllername::default_url(\$_REQUEST);");
            }
            $this->view->breadcrumbs[] = array($title, $url);
        }
    }

    public function setpagetitle($title)
    {
        if(!isset($_SESSION["crumbs"])) {
            $_SESSION["crumbs"] = array();
        }

        $_SESSION["crumbs"][$this->pagename()] = array($this->pagename()."?".$_SERVER["QUERY_STRING"], $title);
        $this->view->page_title = $title;
    }

    public function indexAction()
    {
        $this->load();
    }
    public function htmlAction()
    {
        $this->load();
    }
    public function uwaAction()
    {
        $this->load();
        $this->render("uwa", null, true);
    }
    public function xmlAction()
    {
        $this->load();
        header("Content-type: text/xml");
    }

    public function pagename() {
        //use controller name
        return Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    } 

    abstract public function breads(); //return array containing pagename leading to this page
    /* php doesn't allow abstract static functions, but all controller must override these
    public static function default_title() {}
    public static function default_url($query) {}
    */
    public function load() {}

}
