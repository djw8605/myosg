<?

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
    abstract public function pagename();
    public function load() {}
}
