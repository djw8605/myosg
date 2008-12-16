<?

class GratiaController extends ControllerBase
{ 
    public function breads() { return array(); }
    public static function default_title() { return "Gratia"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle("Gratia");
    }
} 
