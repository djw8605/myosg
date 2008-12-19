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
        $this->selectmenu();
    }

    private function composeControllerName($page)
    {   
        return strtoupper(substr($page, 0, 1)).substr($page, 1)."Controller";
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
                $controllername = $this->composeControllerName($page);
                include_once("$controllername.php");
                $title = eval("return $controllername::default_title();");
                $url = $page."?".eval("return $controllername::default_url(\$_REQUEST);");
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
    private function selectmenu()
    {
        //based on the breadcrumb select correct menu item
        $breads = $this->breads();
        if(isset($breads[0])) {
            $this->view->menu_selected = $breads[0];
        } else {
            $this->view->menu_selected = $this->pagename();
        }
        if(isset($breads[1])) {
            $this->view->submenu_selected = $breads[1];
        } else {
            $this->view->submenu_selected = $this->pagename();
        }
    }

    public function htmlAction()
    {
        $this->load();
        if(isset($_REQUEST["uwa"])) setuwa();
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
    public function load() {
        $this->setpagetitle("Untitled Page");
    }

}
