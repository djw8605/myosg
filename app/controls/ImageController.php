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
        $angle = (int)$dirty_angle;

        $fontSize = 8;
        $font = $_SERVER["DOCUMENT_ROOT"].base()."/lib/zf-1.5.2/tests/Zend/Pdf/_fonts/Vera.ttf";

        $size = imagettfbbox($fontSize, $angle, $font, $text);
        $image = imageCreateTrueColor(-$size[6]+4, $size[1]-$size[5]);

        imageSaveAlpha($image, true);
        ImageAlphaBlending($image, false);

        $transparentColor = imagecolorallocatealpha($image, 200, 200, 200, 127);
        imagefill($image, 0, 0, $transparentColor);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        imagefttext($image, $fontSize, $angle, -$size[6], $size[1]-$size[5], $textColor, $font, $text);

        //output image
        header('Content-type: image/png');
        imagePNG($image); 
        $this->render("none");
    } 
} 
