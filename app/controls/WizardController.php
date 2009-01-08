<?

class WizardController extends ControllerBase
{
    public function breads() { return array("rsv"); }
    public static function default_title() { return "Information Wizard"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle(self::default_title());
    }
}
