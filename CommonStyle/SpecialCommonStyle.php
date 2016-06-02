<?php   
/**
* CommonStyle
*/
class SpecialCommonStyle extends SpecialPage{
    
    function __construct(){
        parent::__construct( 'CommonStyle' );
    }

    public function execute( $params ) {
        global $wgHuijiPrefix, $wgUser;
        $templateParser = new TemplateParser(  __DIR__ . '/pages' );
        $cssPath = "/var/www/virtual/".$wgHuijiPrefix."skins/bootstrap-mediawik/css";
        $this->setHeaders();
        $out = $this->getOutput();
        $output = '';
        if ( !$wgUser->isAllowed('editinterface')){
            $out->permissionRequired( 'editinterface' );
            return;
        }
        $cssCon_1 = CommonStyle::getCurrentCssStyle(1);
        if ($cssCon_1 == false) {
            $isNew = 0;
        }else{
            $isNew = 1;
        }
        if ( $cssCon_1['cssContent'] == null ) {
            $lessCon = array();
            $show = 'none';
        }else{
            $lessCon = (array)json_decode( $cssCon_1['cssContent'] );
            $show = '';
        }
        $valueName = array(
                        '@detail-bg' => 'wiki-outer-body背景颜色',
                        '@detail-inner-bg' => 'wiki-body背景颜色',
                        '@detail-color' => '字体颜色',
                        '@detail-a' => '链接颜色',
                        '@detail-border' => '边框颜色',
                        '@detail-toc-a' => '目录链接颜色',
                        '@detail-toc-hover' => '目录链接hover颜色',
                        '@detail-sub-bg' => '次级导航背景',
                        '@detail-sub-a' => '次级导航字体颜色',
                        '@detail-sub-a-hover-bg' => '次级导航hover背景',
                        '@detail-sub-site-count' => '次级导航统计数字颜色',
                        '@detail-contentsub' => 'contentsub字体颜色',
                        '@detail-bottom-bg' => '页面底部背景颜色',
                        '@detail-detail-bottom-color' => '底部字体颜色',
                        '@detail-detail-quote-bg' => 'quote背景颜色',
                        '@detail-detail-quote-color' => 'quote字体颜色',
                        '@detail-quote-a' => 'quote链接颜色',
                        '@detail-quote-border' => 'quote边框颜色',
                        '@detail-wikitable-bg' => 'wikitable背景颜色',
                        '@detail-wikitable-color' => 'wikitable字体颜色',
                        '@detail-wikitable-a' => 'wikitable链接颜色',
                        '@detail-wikitable-border' => 'wikitable边框颜色',
                        '@detail-infobox-bg' => 'infobox背景颜色',
                        '@detail-infobox-color' => 'infobox字体颜色',
                        '@detail-infobox-a' => 'infobox链接颜色',
                        '@detail-infobox-border' => 'infobox边框颜色',
                        '@detail-infobox-title-bg' => 'infobox主标题背景颜色',
                        '@detail-infobox-title-color' => 'infobox主标题字体颜色',
                        '@detail-infobox-item-title-bg' => 'infobox次级标题背景颜色',
                        '@detail-infobox-item-title-color' => 'infobox次级标题字体颜色',
                        '@detail-infobox-item-label-bg' => 'infobox label背景颜色',
                        '@detail-infobox-item-label-color' => 'infobox label字体颜色',
                        '@detail-infobox-item-label-a' => 'infobox label链接颜色',
                        '@detail-infobox-item-label-border' => 'infobox label边框颜色',
                        '@detail-infobox-item-detail-bg' => 'infobox detail背景颜色',
                        '@detail-infobox-item-detail-color' => 'infobox detail字体颜色',
                        '@detail-infobox-item-detail-a' => 'infobox detail链接颜色',
                        '@detail-infobox-item-detail-border' => 'infobox detail边框颜色',
                        '@detail-navbox-bg' => 'navbox背景颜色',
                        '@detail-navbox-color' => 'navbox字体颜色',
                        '@detail-navbox-a' => 'navbox链接颜色',
                        '@detail-navbox-border' => 'navbox边框颜色',
                        '@detail-navbox-title-bg' => 'navbox标题背景颜色',
                        '@detail-navbox-title-color' => 'navbox标题字体颜色',
                        '@detail-navbox-title-a' => 'navbox标题链接颜色',
                        '@detail-navbox-group-bg' => 'navbox group背景颜色',
                        '@detail-navbox-group-color' => 'navbox group字体颜色',
                        '@detail-navbox-group-a' => 'navbox group链接颜色',
                        '@detail-navbox-abovebelow-bg' => 'navbox abovebelow背景颜色',
                        '@detail-navbox-abovebelow-color' => 'navbox abovebelow字体颜色',
                        '@detail-navbox-abovebelow-a' => 'navbox abovebelow链接颜色',
                    );
        $styleArr = array();
        foreach ($valueName as $key => $value) {
            $styleArr[] = array(
                            'name' => $value,
                            'variable' => $key,
                            'value' => !isset( $lessCon[$key] ) ? 'false' : $lessCon[$key],
                        ); 
        }
        // print_r($styleArr);die();
        $mainBase = !isset( $lessCon['@main-base'] ) ? "#333" : $lessCon['@main-base'];
        $bg = !isset( $lessCon['@bg'] ) ? "#fff" : $lessCon['@bg'];
        $bgInner = !isset( $lessCon['@bg-inner'] ) ? "#fff" : $lessCon['@bg-inner'];
        $a = !isset( $lessCon['@a'] ) ? "#428bca" : $lessCon['@a'];
        $subBg = !isset( $lessCon['@sub-bg'] ) ? "#f6f8f8" : $lessCon['@sub-bg'];
        $subA = !isset( $lessCon['@sub-a'] ) ? "#333" : $lessCon['@sub-a'];
        $modal = !isset( $lessCon['@modal'] ) ? "#222" : $lessCon['@modal'];
        $output .= $templateParser->processTemplate(
                            'view',
                            array(
                                'mainBase' => $mainBase,
                                'bg' => $bg,
                                'bgInner' => $bgInner,
                                'a' => $a,
                                'subBg' => $subBg,
                                'subA' => $subA,
                                'modal' => $modal,
                                'styleArr' => $styleArr,
                                'isNew' => $isNew,
                            )
                    );
        $out->addHTML( $output );
        $out->addModulestyles('socialprofile.commonstyle.css');
        $out->addModules( 'ext.socialprofile.commonstyle.js' );
    }

}

?>
