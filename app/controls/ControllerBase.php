<?

//*pagename* is just a name of controller. so pageid might be a better naming..
$g_pagename = "please_reset_me";
function pagename() { global $g_pagename; return $g_pagename; }
function setpagename($name) { 
    global $g_pagename; 
    $g_pagename = $name; 
}

class path
{
    public $colid;

    public $parent;
    public $name;
    public $children;

    public $branch;
    public $counters;
    public $hastext;

    public $rows;
    public $row; //current row to set values

    function __construct($parent, $name) {
        $this->colid = null;
        $this->branch = false;
        $this->hastext = false;
        $this->counter = 0;
        $this->rows = array();
        $this->row = array();

        $this->parent = $parent;
        $this->name = $name;

        //register myself to parent
        $this->children = array();
        if($parent !== null) {
            $parent->children[] = $this;
        }
    }
/*
    function newRow()
    {
        $this->row = array();
        $this->rows[] = $this->row;
    }
*/
    function getFullPath()
    {
        $fullname = "";
        if($this->parent !== null) {
            $fullname .= $this->parent->getFullPath()."/";
        }
        $fullname .= $this->name;
        return $fullname;
    }
    function findChild($name) {
        foreach($this->children as $child) {
            if($child->name == $name) return $child;
        }
        return null;
    }
    function analyzeColumn(&$cols) {
        if($this->hastext) {
            $this->colid = sizeof($cols);
            $cols[] = $this;
        }
        foreach($this->children as $child) {
            $child->analyzeColumn($cols);
        }
    }
    function analyzeBranch() {
        foreach($this->children as $child) {
            if($child->counter > 1) {
                $child->branch = true;
            }
            $child->counter = 0;
        }
    }
    function getBranch()
    {
        if($this->branch) return $this;
        if($this->parent !== null) {
            return $this->parent->getBranch();
        }
        return null;
    }
    function closeBranch()
    {
        $parent_branch = $this->parent->getBranch();
        if($parent_branch !== null) {
            //merge my row and sub-rows to parent rows.
            if(sizeof($this->rows) == 0) {
                $parent_branch->rows[] = merge_row($parent_branch->row, $this->row);
            } else {
                foreach($this->rows as $row) {
                    $parent_branch->rows[] = merge_row($parent_branch->row, merge_row($this->row, $row));
                }
            }
            $this->row = array();
            $this->rows = array();
        }
    }
    function output($colnum)
    {
        //output all rows collected
        foreach($this->rows as $row) {
            for($i = 0;$i < $colnum;$i++) {
                if(isset($row[$i])) {
                    echo $row[$i];
                }
                echo ",";
            }
            echo "\n";
        }
    }
}

function merge_row($row1, $row2)
{
    foreach($row2 as $key=>$col) {
        $row1[$key] = $col;
    }
    return $row1;
}

/*
class branch
{
    public $parent;
    public $row;
    function __construct($parent)
    {
        $this->parent = $parent;
    }
}
*/

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

    public function csvAction()
    {
        $this->load();
        $xml_content = $this->view->render(pagename()."/xml.phtml");

        $xml = new XMLReader();
        $xml->XML($xml_content);

        $this->render("none", null, true);
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=\"".pagename().".csv\"");

        //First pass - discover all path and branch points
        $cols = array();
        $root = new path(null, "root");
        $current = $root;
        while($xml->read()) {
            if (in_array($xml->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE))) {
                if(trim($xml->value) == "") continue;
                $current->hastext = true;
            }
            if ($xml->nodeType == XMLReader::ELEMENT) {
                $child = $current->findChild($xml->name);
                if($child !== null) {
                    $current = $child;
                    $current->counter++;
                } else {
                    //brand new path
                    $current = new path($current, $xml->name);
                }
            }
            if ($xml->nodeType == XMLReader::END_ELEMENT) {
                $current->analyzeBranch();
                $current = $current->parent;
            }
        }

        //output column headder
        $cols = array();
        $root->analyzeColumn($cols);
        foreach($cols as $path) {
            //append parent's path name to be more descriptive
            if($path->parent !== null) {
                echo $path->parent->name."/";
            }
            echo $path->name;
            echo ",";
        }
        echo "\n";

        //Second pass - map values to current branch points
        $xml->XML($xml_content);
        $current = $root;
        $branch = null;
        while($xml->read()) {
            if (in_array($xml->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE))) {
                $value = trim($xml->value);
                if(trim($xml->value) == "") continue;
                $branch->row[$current->colid] = $value;
            }
            if ($xml->nodeType == XMLReader::ELEMENT) {
                $current = $current->findChild($xml->name);
                $branch = $current->getBranch();
            }
            if ($xml->nodeType == XMLReader::END_ELEMENT) {
                if($current == $branch) {
                    $branch->closeBranch();
                }
                $current = $current->parent;
                $branch_new = $current->getBranch();
                if($branch_new !== null) {
                    $branch = $branch_new;
                }
            }
        }

        //dump the content
        $branch->output(sizeof($cols));
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
