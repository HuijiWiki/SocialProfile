<?php   
use \MediaWiki as mw;
/**
* DynamicLess
*/
class SpecialDynamicLess extends SpecialPage{
    
    function __construct(){
        parent::__construct( 'DynamicLess' );
    }

    public function execute( $params ) {
        global $wgHuijiPrefix;
        $default = Huiji::getInstance()->getSiteDefaultColor();
        $defaultRes = array();
        foreach ($default as $key => $value) {
            $defaultRes['@'.$key] = $value;
        }
        $cssContent = CommonStyle::getCurrentCssStyle(1);
        if ( $cssContent['cssContent'] == null ) {
            $lessCon = array();
        }else{
            $lessCon = (array)json_decode( $cssContent['cssContent'] );
        }
        $result = array_merge( $defaultRes, $lessCon );
        $lessStr = '';
        foreach ($result as $key => $value) {
            $lessStr .= $key.":".$value.";";
        }
        $lessPath = "/var/www/virtual/".$wgHuijiPrefix."/skins/bootstrap-mediawiki/less/custom.less";
        $lessStr .= file_get_contents($lessPath);
        mw\suppressWarnings();
        $this->getOutput()->setArticleBodyOnly(true);
        echo $lessStr;
        $this->getOutput()->output();
    }
}