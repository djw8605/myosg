<?

class RsvController extends ControllerBase
{ 
    public function breads() { return array(); }
    public static function default_title() { return "RSV"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle("RSV");
    }
} 
