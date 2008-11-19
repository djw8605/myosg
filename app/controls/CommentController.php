<?

class CommentController extends ControllerBase
{ 
    public function breads() { return array(); }
    public static function default_title() { return "Comment"; }
    public static function default_url($query) { return ""; }

    public function load()
    {
        $this->setpagetitle("Feedback");
    }

    public function submitAction()
    {
        $this->load();

        $name = $_REQUEST["name"];
        $comment = $_REQUEST["comment"];
        $page = $_REQUEST["page"];
        
        //send email
        $Name = "Soichi Hayashi";
        $email = "hayashis@indiana.edu";
        $recipient = "hayashis@indiana.edu";
        $mail_body = "[META Comment on $page]\n";
        $mail_body .= $comment;
        $subject = "[myosg] comment from ".$name;
        $header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields
        mail($recipient, $subject, $mail_body, $header); //mail command :)

        $this->view->comment = $_REQUEST["comment"];
    }
} 
