<?

class DebugController extends Zend_Controller_Action 
{ 
    public function indexAction() 
    { 
    } 
    public function resourceAction() 
    { 
        //$r = new Resource();
        //var_dump($r->fetchAll());
        $r = new ResourceServiceTypes();
        $ret = $r->getServiceTypes(174);
        var_dump($ret);
    } 
    public function roleAction() 
    { 
        $r = new Person($_SERVER["SSL_CLIENT_S_DN"]);
        var_dump($r->roles);
    } 
} 
