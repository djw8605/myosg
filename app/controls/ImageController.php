<?

class ImageController extends Zend_Controller_Action 
{ 
    public function rotateAction() 
    { 
        $dirty_text = $_REQUEST["text"];
        $dirty_angle = $_REQUEST["angle"];
        //TODO - validate $text
        $text = $dirty_text;
        //TODO - validate $angle
        $angle = $dirty_angle;

        //do rendering
        $im = imageCreate(70,16);
        $bg = imagecolorallocatealpha($im, 255,255,255,127); //transparent
        $textcolor = imagecolorallocate($im, 0, 0, 0);
        imagestring($im, 4, 0, 0, $dirty_text, $textcolor);
        $im = imagerotate($im, $angle, 0); 

        //output image
        header('Content-type: image/png');
        imagePNG($im); 
        $this->render("none");

    } 
} 
