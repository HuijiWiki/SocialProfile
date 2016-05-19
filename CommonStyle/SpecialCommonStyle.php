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
        $cssPath = "/var/www/virtual/".$wgHuijiPrefix."skins/bootstrap-mediawik/css";
        $this->setHeaders();
        $out = $this->getOutput();
        $output = '';
        if ( !$wgUser->isAllowed('editinterface')){
            $out->permissionRequired( 'editinterface' );
            return;
        }
//         $skinDirParts = explode( DIRECTORY_SEPARATOR, __DIR__ );
//         var_dump($IP);die();
// echo $skinDir = array_pop( $skinDirParts );die();
        $cssCon_1 = CommonStyle::getCurrentCssStyle(1);
        if ( count($cssCon_1) == 0 ) {
            $lessCon = array();
            $isNew = 0;
        }else{
            $lessCon = (array)json_decode( $cssCon_1['cssContent'] );
            $isNew = 1;
        }
        $mainBase = !isset( $lessCon['@main-base'] ) ? "#fff" : $lessCon['@main-base'];
        $bg = !isset( $lessCon['@bg'] ) ? "#000" : $lessCon['@bg'];
        $bgInner = !isset( $lessCon['@bg-inner'] ) ? "#000" : $lessCon['@bg-inner'];
        $a = !isset( $lessCon['@a'] ) ? "#428bca" : $lessCon['@a'];
        $subBg = !isset( $lessCon['@sub-bg'] ) ? "#f6f8f8" : $lessCon['@sub-bg'];
        $subA = !isset( $lessCon['@sub-a'] ) ? "#333" : $lessCon['@sub-a'];
        // print_r($lessCon);die();
        $output .= "<aside class='color-picker'>
        <ul class='picker-img'>
        <li>
        <input type='file' class='file-btn' data-selector='body'>
        <div class='color-box'></div>
        <div class='color-name'>body背景</div>
        </li>
        <li>
        <input type='file' class='file-btn' data-selector='#wiki-outer-body'>
        <div class='color-box'></div>
        <div class='color-name'>wiki-outer-body背景</div>
        </li>
        </ul>
        <ul class='picker-color'>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-variable='@main-base' value='".$mainBase."'>
        <div class='color-name'>字体主色调</div>
        </li>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-variable='@bg'  value='".$bg."'>
        <div class='color-name'>wiki-outer-body背景</div>
        </li>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-variable='@bg-inner'  value='".$bgInner."'>
        <div class='color-name'>wiki-body背景</div>
        </li>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-variable='@a'  value='".$a."'>
        <div class='color-name'>链接颜色</div>
        </li>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-variable='@sub-bg'  value='".$subBg."'>
        <div class='color-name'>次级导航背景</div>
        </li>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-variable='@sub-a'  value='".$subA."'>
        <div class='color-name'>次级导航字体色</div>
        </li>
        </ul>
        <input type='hidden' class='is-new' value=".$isNew.">
        <button class='btn btn-primary commonstyle-submit'>保存</button>
        </aside>";
        $output .='<div class="preview">
        <h2>预览说明</h2>
        <p>此页面方便您调整站点色彩搭配，一共六个主色彩。其余色彩根据主色彩计算进行生成。点击左边的颜色框选取颜色或者直接输入颜色值，如果您对某些生成色彩不满意，可以使用css进行覆盖，覆盖时您只需对选择器外层加上".huiji-css-hook"。颜色计算规则如下</p>
        <h4>字体颜色</h4>
        <p>指定字体颜色，同时根据颜色亮度生成页面中的边框颜色和侧边的导航颜色。如果想使用更深的边框，请对边框容器加上border-darker的class名。效果如下</p>
        <div class="exa-border">我的border颜色会随着字体颜色变化</div>
        <div class="exa-border border-darker">我拥有更深的border</div>
        <h4>wiki-outer-body背景</h4>
        <p>指定主体内容外部的背景颜色。</p>
        <h4>wiki-body背景</h4>
        <p>指定主体内容的背景颜色，同时生成nav-box,wiki-table,info-box等table的背景颜色以及quote的背景颜色。如果想要斑马纹的table背景颜色，请给table加上table-stripe的class名。效果如下</p>
        <blockquote class="quote blockquotequote" style="">“现在我才是<a href="/wiki/%E9%83%A8%E8%90%BD" title="部落">部落</a>的统治者，<a href="/wiki/%E5%8F%A4%E5%B0%94%E4%B8%B9" title="古尔丹">古尔丹</a>。
        不是你，也不是<a href="/wiki/%E6%9A%97%E5%BD%B1%E8%AE%AE%E4%BC%9A" title="暗影议会">你的那些术士</a>。只有我毁灭之锤。从今以后不会再有屈辱，不会再有背叛，也不会再有欺骗和谎言！”
        <p style="margin-bottom:0;text-align:right">—— <strong class="selflink">奥格瑞姆·毁灭之锤</strong></p></blockquote>
        <h4>链接颜色</h4>
        <p>指定主体内容的链接颜色，同时生成链接滑过等时的字体颜色。效果如下</p>
        <p><a href="/wiki/%E7%B4%A2%E7%91%9E%E6%A3%AE%E5%A4%A7%E5%B8%9D" title="">索瑞森大帝</a></p>
        <h4>次级导航背景</h4>
        <p>指定次级导航背景颜色，同时生成导航滑过背景色</p>
        <h4>次级导航字体色</h4>
        <p>指定次级导航字体颜色，同时生成反色的统计数目颜色</p>
        <h4>按钮组颜色定义</h4>
        <p>开发中......</p>
        <p>注：如果预览失效，请打开调试模式，链接后面加上?debug=1</p>
        </div>
        ';
        $out->addHTML( $output );
        $out->addModuleStyles('ext.socialprofile.commonstyle.css');
        $out->addModules( 'ext.socialprofile.commonstyle.js' );
    }

}

?>
