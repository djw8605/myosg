<?

class GadgetController extends Zend_Controller_Action 
{
    public function init()
    {
        //everything is xml
        header('Content-type: text/xml');
    }

    public function testAction()
    {
    }
}
