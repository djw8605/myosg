<?
include("WizardGratiaController.php");

class WizardseController extends WizardGratiaController
{
    public function breads() { return array("rsv", "wizard"); }
    public static function default_title() { return "SE Specific Gratia Accounting"; }
    public static function default_url($query) { return ""; }

    public function map() {
        $dirty_type = @$_REQUEST["se_account_type"];
        switch($dirty_type) 
        {
        case "vo_transfer_volume":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/gip_vo";
            $sub_title = "Transfer volume (Grouped by VO)";
            $ylabel = "Transfer Volume (GB)";
            break;
        case "user_transfer_volume":
            $urlbase = "http://t2.unl.edu/gratia/transfer_graphs/user_transfer_volume";
            $sub_title = "Transfer Volumn (Grouped by Username)";
            $ylabel = "Transfer Volume (GB)";
            break;
        case "se_space":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/se_space";
            $sub_title = "Total Space";
            $ylabel = "GB";
            break;
        case "se_space_free":
            $urlbase = "http://t2.unl.edu/gratia/gip_graphs/se_space_free";
            $sub_title = "Total Free Space";
            $ylabel = "GB";
            break;
        default:
            elog("unknown account_type - maybe a bot");
        }
        return array($urlbase, $sub_title, $ylabel);
    }
}
