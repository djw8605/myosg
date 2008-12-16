<?

class GratiajobController extends ControllerBase
{
    public function breads() { return array("gratia"); } //these must be controller names. don't include this page itself
    public static function default_title() { return "Job Activity"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle("Job Activity");

        if(!isset($_REQUEST["type"])) {
            $_REQUEST["type"] = "wall";
        }
        $dirty_type = $_REQUEST["type"];
        $this->view->type = $dirty_type;
        switch($dirty_type) {
        case "wall":
            $this->view->graph_type = "osg_wall_hours";
            break;
        case "cum_hours":
            $this->view->graph_type = "facility_success_cumulative_smry";
            break;
        case "hours":
            $this->view->graph_type = "facility_hours_bar_smry";
            break;
        case "cum_wall":
            $this->view->graph_type = "osg_wall_cumulative";
            break;
        default:
            throw new exception("bad type: $dirty_type");
        }
    }
}
