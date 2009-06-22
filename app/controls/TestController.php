<?
class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        header("Content-type: image/png");

        $im = imagecreate(200, 200);
        $green = imagecolorallocate($im, 0, 255, 0);
        $red = imagecolorallocate($im, 255, 0, 0);
        imagefill($im, 0, 0, $green);
        imageline($im, 10, 10, 190, 190, $red);
        imagePNG($im);
        imagedestroy($im);

        $this->render("none");
    }
}
