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
        $this->selectmenu($this->pagename());
        $this->setpagetitle("Untitled Page");
    }


    public function setpagetitle($title)
    {
        $this->view->page_title = $title;
    }

    public function indexAction()
    {
        $this->load();

    }
    public function selectmenu($menu)
    {
        $this->view->menu_selected = $menu;
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

    public function adduwaAction()
    {
        $url = fullbase()."/".pagename()."/uwa?".$_SERVER["QUERY_STRING"];
        $target = "http://www.netvibes.com/subscribe.php?module=UWA&moduleUrl=".urlencode($url);
        header("Location: $target");
        exit;
    }

    public function addigoogleAction()
    {
        $url = urlencode(fullbase()."/".pagename()."/uwa?".$_SERVER["QUERY_STRING"]);
        $url = urlencode("www.netvibes.com/api/uwa/compile/google.php?moduleUrl=".$url);
        header("Location: http://www.google.com/ig/add?moduleurl=$url");
        exit;
    }

    public function xmlAction()
    {
        $this->load();
        header("Content-type: text/xml");
        //recreate the original non-xml url
?><!-- This XML was generated with a query in following MyOSG page
<?=fullbase()."/".pagename()."/?".$_SERVER["QUERY_STRING"]?>
--><?

    }

    public function pagename() {
        //use controller name
        return Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    } 

    protected function load_daterangequery()
    {
        $today_begin = (int)(time() / (3600*24));
        $today_begin *= 3600*24;

        //set some defaults
        if(!isset($_REQUEST["start_type"])) {
            $_REQUEST["start_type"] = "7daysago";
        }
        if(!isset($_REQUEST["end_type"])) {
            $_REQUEST["end_type"] = "now";
        }

        switch($_REQUEST["start_type"]) {
        case "yesterday":
            $this->view->start_time = $today_begin - 3600*24;
            break;
        case "7daysago":
            $this->view->start_time = $today_begin - 3600*24*7;
            break;
        case "30daysago":
            $this->view->start_time = $today_begin - 3600*24*30;
            break;
        case "specific":
            $str = $_REQUEST["start_date"];
            $this->view->start_time = strtotime($str);
            break;
        }

        switch($_REQUEST["end_type"]) {
        case "today":
            $this->view->end_time = $today_begin;
            break;
        case "now":
            $this->view->end_time = time();
            break;
        case "specific":
            $str = $_REQUEST["end_date"];
            $this->view->end_time = strtotime($str);
            break;
        }
    }


    //abstract public function breads(); //return array containing pagename leading to this page
    abstract public function load();

}
