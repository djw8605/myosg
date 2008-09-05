<?

require_once("model_base.php");

class VO
{
    public function __construct()
    {
        if(!Zend_Registry::isRegistered("db")) {
            $this->db = connectdb();
        } else {
            $this->db = Zend_Registry::get("db");
        }
    }

    public function fetchAll()
    {
        $schema = config()->db_oim_schema;
        $sql = "select * from $schema.virtualorganization where active = 1 and disable = 0 order by short_name";
        $vos = $this->db->fetchAll($sql);

        //let's make sure we have vo image cached
        foreach($vos as $vo) {
            $cache_name = config()->getCacheDir()."/vo.".$vo->vo_id.".png";
            if(!file_exists($cache_name)) {
                $this->createVOImage($vo->short_name, $cache_name);
            }
        }

        return $vos;
    }

    private function createVOImage($text, $filename) 
    { 
        $angle = 90;
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

        imagePNG($image, $filename); 
    } 

    public function pullMemberVOs($resource_id = null)
    {
        $schema = config()->db_oim_schema;
        $sql = "SELECT VOM.*, R.name, VO.long_name, VO.short_name FROM vo_matrix VOM
                  LEFT JOIN $schema.resource R ON (VOM.resource_id = R.resource_id )
                  JOIN $schema.virtualorganization VO on VOM.vo_id = VO.vo_id";
        if($resource_id !== null) $sql .= " where VOM.resource_id = $resource_id";
        return $this->db->fetchAll($sql);
    }
}

?>
