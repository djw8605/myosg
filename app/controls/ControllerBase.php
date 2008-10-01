<?

class ControllerBase extends Zend_Controller_Action
{
    public function rememberQuery($page)
    {
        session()->query[$page] = $_SERVER["QUERY_STRING"];
    }
    public function indexAction()
    {
    }
    public function htmlAction()
    {
    }
    public function uwaAction()
    {
    }
    public function xmlAction()
    {
        header("Content-type: text/xml");
    }
}
