<?

class AboutController extends ControllerBase
{
    public static function default_title() { return "About MyOSG"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->parseUCV();
        $this->setpagetitle(self::default_title());
    }

    public function parseUCV()
    {
        $this->view->ucv = array();


        system("wget --no-check-certificate -O /tmp/myosg.ucv.html https://twiki.grid.iu.edu/bin/view/Operations/UserContributedViews?raw=on&cover=plain");
        $html = file_get_contents("/tmp/myosg.ucv.html");
        $lines = explode("\n", $html);

        //load User Contributed Links list and pull the table content
        $in = false;
        foreach($lines as $line) {
            if(!$in) {
                //detect beginning
                if(strpos($line, "<textarea")) {
                    $in = true;
                    continue;
                }
            }
            if($in) {
                //detect end
                if(strpos($line, "</textarea")) {
                    $in = false;
                    break;
                }
                $tokens  = explode("|", $line);
                if(count($tokens) == 7) {
                    $this->view->ucv[] = $tokens;
                }
            }
        }
    } 
} 
