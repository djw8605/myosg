<?

class AboutController extends ControllerBase
{
    //public function breads() { return array(); }
    public static function default_title() { return "About MyOSG"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle(self::default_title());
    }
} 
