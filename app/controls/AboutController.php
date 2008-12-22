<?

class AboutController extends ControllerBase
{
    public function breads() { return array(); }
    public static function default_title() { return "About"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle(AboutController::default_title());

    }
} 
