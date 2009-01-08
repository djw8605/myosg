<?

class OimController extends ControllerBase
{ 
    public function breads() { return array(); }
    public static function default_title() { return "OIM"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle(self::default_title());
    }
} 
