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
        $hArr = array(
                    '@detail-h1'=>'h1',
                    '@detail-h2'=>'h2',
                    '@detail-h3'=>'h3',
                    '@detail-h4'=>'h4',
                    '@detail-h5'=>'h5',
                );
        $valueName = array(
                        '@detail-bg' => 'wiki-outer-body背景',
                        '@detail-inner-bg' => 'wiki-body背景',
                        '@detail-color' => '文字',
                        '@detail-h1'=>'h1',
                        '@detail-h2'=>'h2',
                        '@detail-h3'=>'h3',
                        '@detail-h4'=>'h4',
                        '@detail-h5'=>'h5',
                        '@detail-contentsub' => '副标题文字',
                        '@detail-a' => '有效链接',
                        '@detail-new' => '无效链接',
                        '@detail-border' => '边框',
                        '@detail-toc-a' => '目录链接',
                        '@detail-toc-a-hover' => '目录链接-hover',
                        '@detail-sub-bg' => '导航背景',
                        '@detail-sub-a' => '导航文字',
                        '@detail-sub-a-hover-bg' => '导航悬浮背景',
                        '@detail-sub-site-count' => '导航统计数字',
                        '@detail-bottom-bg' => '页面底部背景',
                        '@detail-bottom-color' => '底部文字',
                        '@detail-quote-bg' => 'quote背景',
                        '@detail-quote-color' => 'quote文字',
                        '@detail-quote-a' => 'quote链接',
                        '@detail-quote-border' => 'quote边框',
                        '@detail-wikitable-bg' => 'wikitable背景',
                        '@detail-wikitable-color' => 'wikitable文字',
                        '@detail-wikitable-a' => 'wikitable链接',
                        '@detail-wikitable-border' => 'wikitable边框',
                        '@detail-infobox-bg' => 'infobox背景',
                        '@detail-infobox-color' => 'infobox文字',
                        '@detail-infobox-a' => 'infobox链接',
                        '@detail-infobox-border' => 'infobox边框',
                        '@detail-infobox-title-bg' => 'infobox title背景',
                        '@detail-infobox-title-color' => 'infobox title文字',
                        '@detail-infobox-item-title-bg' => 'infobox header背景',
                        '@detail-infobox-item-title-color' => 'infobox header文字',
                        '@detail-infobox-item-label-bg' => 'infobox label背景',
                        '@detail-infobox-item-label-color' => 'infobox label文字',
                        '@detail-infobox-item-label-a' => 'infobox label链接',
                        '@detail-infobox-item-label-border' => 'infobox label边框',
                        '@detail-infobox-item-detail-bg' => 'infobox data背景',
                        '@detail-infobox-item-detail-color' => 'infobox data字体',
                        '@detail-infobox-item-detail-a' => 'infobox data链接',
                        '@detail-infobox-item-detail-border' => 'infobox data边框',
                        '@detail-navbox-bg' => 'navbox背景',
                        '@detail-navbox-color' => 'navbox文字',
                        '@detail-navbox-a' => 'navbox链接',
                        '@detail-navbox-title-bg' => 'navbox title背景',
                        '@detail-navbox-title-color' => 'navbox title文字',
                        '@detail-navbox-title-a' => 'navbox title链接',
                        '@detail-navbox-group-bg' => 'navbox group背景',
                        '@detail-navbox-group-color' => 'navbox group文字',
                        '@detail-navbox-group-a' => 'navbox group链接',
                        '@detail-navbox-list-bg' => 'navbox list背景',
                        '@detail-navbox-list-color' => 'navbox list文字',
                        '@detail-navbox-list-a' => 'navbox list链接',
                        '@detail-navbox-list-new' => 'navbox list无效链接',
                        '@detail-navbox-list-odd-bg' => 'navbox list奇数背景',
                        '@detail-navbox-list-even-bg' => 'navbox list偶数背景',
                        '@detail-navbox-abovebelow-bg' => 'navbox above/below背景',
                        '@detail-navbox-abovebelow-color' => 'navbox above/below文字',
                        '@detail-navbox-abovebelow-a' => 'navbox above/below链接',
                    );
        $styleArr = array();
        foreach ($valueName as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key1 => $value1) {
                    $res1[] = array(
                                'name2' => $value1,
                                'variable2' => $key1,
                                'value2' => !isset( $lessCon[$key] ) ? 'false' : $lessCon[$key],
                            );
                }
                $styleArr['h'] = $res1;
            }else{
                $styleArr[] = array(
                                'name' => $value,
                                'variable' => $key,
                                'value' => !isset( $lessCon[$key1] ) ? 'false' : $lessCon[$key1],
                            ); 
            }
            
        }
        // print_r($styleArr);die();
        $mainBase = !isset( $lessCon['@main-base'] ) ? "#333" : $lessCon['@main-base'];
        $bg = !isset( $lessCon['@bg'] ) ? "#fff" : $lessCon['@bg'];
        $bgInner = !isset( $lessCon['@bg-inner'] ) ? "#fff" : $lessCon['@bg-inner'];
        $a = !isset( $lessCon['@a'] ) ? "#428bca" : $lessCon['@a'];
        $subBg = !isset( $lessCon['@sub-bg'] ) ? "#f6f8f8" : $lessCon['@sub-bg'];
        $subA = !isset( $lessCon['@sub-a'] ) ? "#333" : $lessCon['@sub-a'];
        $modal = !isset( $lessCon['@modal'] ) ? "#222" : $lessCon['@modal'];
        $default = !isset( $lessCon['@default'] ) ? "#ffffff" : $lessCon['@default'];
        $primary = !isset( $lessCon['@primary'] ) ? "#337ab7" : $lessCon['@primary'];
        $success = !isset( $lessCon['@success'] ) ? "#5cb85c" : $lessCon['@success'];
        $info = !isset( $lessCon['@info'] ) ? "#5bc0de" : $lessCon['@info'];
        $warning = !isset( $lessCon['@warning'] ) ? "#f0ad4e" : $lessCon['@warning'];
        $danger = !isset( $lessCon['@danger'] ) ? "#d9534f" : $lessCon['@danger'];
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
                                'default' => $default,
                                'primary' => $primary,
                                'success' => $success,
                                'info' => $info,
                                'warning' => $warning,
                                'danger' => $danger,
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
