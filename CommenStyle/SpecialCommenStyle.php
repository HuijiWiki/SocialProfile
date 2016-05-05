<?php   
/**
* CommenStyle
*/
class SpecialCommenStyle extends SpecialPage{
    
    function __construct(){
        parent::__construct( 'CommenStyle' );
    }

    public function execute( $params ) {
        global $wgHuijiPrefix;
        $cssPath = "/var/www/virtual/".$wgHuijiPrefix."skins/bootstrap-mediawik/css";
        $this->setHeaders();
        $out = $this->getOutput();
        $output = '';
//         $skinDirParts = explode( DIRECTORY_SEPARATOR, __DIR__ );
//         var_dump($IP);die();
// echo $skinDir = array_pop( $skinDirParts );die();
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
        <ul class='picker-bg'>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-selector='body'>
        <div class='color-name'>body背景</div>
        </li>
        <li>
        <input class='color-box jscolor' style='background:#fff' data-selector='#wiki-body'>
        <div class='color-name'>wiki-outer-body背景</div>
        </li>
        </ul>
        <ul class='picker-font'>
        <li>
        <input class='color-box jscolor' data-selector='#wiki-body'>
        <div class='color-name'>内容字体颜色</div>
        </li>
        <li>
        <input class='color-box jscolor' data-selector='#wiki-body a'>
        <div class='color-name'>链接颜色</div>
        </li>
        </ul>
        <ul class='picker-border'>
        <li>
        <div class='color-box'></div>
        <div class='color-name'>body背景</div>
        </li>
        <li>
        <div class='color-box'></div>
        <div class='color-name'>wiki-outer-body背景</div>
        </li>
        </ul>
        <button class='btn btn-primary commenstyle-submit'>保存</button>
        </aside>";
        $output .='
        <h2>分类“重要角色”中的页面</h2>
        <p>以下74个页面属于本分类，共74个页面。
        </p><div lang="zh-CN" dir="ltr" class="mw-content-ltr"><div class="mw-category"><div class="mw-category-group"><h3>A</h3>
        <ul><li><a href="/wiki/%E9%98%BF%E5%B0%94%E7%8E%9F" title="阿尔玟">阿尔玟</a></li>
        <li><a href="/wiki/%E5%9F%83%E5%B0%94%E6%B1%B6" title="埃尔汶">埃尔汶</a></li>
        <li><a href="/wiki/%E5%9F%83%E5%85%8B%E5%A1%9E%E7%90%86%E5%AE%89" title="埃克塞理安">埃克塞理安</a></li>
        <li><a href="/wiki/%E5%9F%83%E5%85%B0%E8%BF%AA%E5%B0%94" title="埃兰迪尔">埃兰迪尔</a></li>
        <li><a href="/wiki/%E5%9F%83%E5%B0%94%E9%9A%86%E5%BE%B7" title="埃尔隆德">埃尔隆德</a></li>
        <li><a href="/wiki/%E5%9F%83%E9%9B%85%E4%BB%81%E8%BF%AA%E5%B0%94" title="埃雅仁迪尔">埃雅仁迪尔</a></li>
        <li><a href="/wiki/%E9%98%BF%E6%8B%89%E8%B4%A1" title="阿拉贡">阿拉贡</a></li>
        <li><a href="/wiki/%E5%AE%89%E6%A0%BC%E7%8E%9B%E5%B7%AB%E7%8E%8B" title="安格玛巫王">安格玛巫王</a></li>
        <li><a href="/wiki/%E5%A5%A5%E5%8A%9B" title="奥力">奥力</a></li>
        <li><a href="/wiki/%E9%98%BF%E7%91%9E%E8%92%82%E5%B0%94" title="阿瑞蒂尔">阿瑞蒂尔</a></li></ul></div><div class="mw-category-group"><h3>B</h3>
        <ul><li><a href="/wiki/%E5%B7%B4%E5%BE%B7" title="巴德">巴德</a></li>
        <li><a href="/wiki/%E8%B4%9D%E7%83%88%E6%A0%BC%C2%B7%E5%BA%93%E6%B2%99%E7%90%86%E5%AE%89" title="贝烈格·库沙理安">贝烈格·库沙理安</a></li>
        <li><a href="/wiki/%E8%B4%9D%E4%BC%A6" title="贝伦">贝伦</a></li>
        <li><a href="/wiki/%E6%AF%94%E5%B0%94%E5%8D%9A%C2%B7%E5%B7%B4%E9%87%91%E6%96%AF" title="比尔博·巴金斯">比尔博·巴金斯</a></li>
        <li><a href="/wiki/%E6%B3%A2%E6%B4%9B%E7%B1%B3%E5%B0%94" title="波洛米尔">波洛米尔</a></li></ul></div><div class="mw-category-group"><h3>D</h3>
        <ul><li><a href="/wiki/%E5%BE%B7%E5%86%85%E6%A2%AD%E5%B0%94%E4%BA%8C%E4%B8%96" title="德内梭尔二世">德内梭尔二世</a></li>
        <li><a href="/wiki/%E8%BF%AA%E5%A5%A5" title="迪奥">迪奥</a></li>
        <li><a href="/wiki/%E9%83%BD%E6%9E%97%E4%B8%80%E4%B8%96" title="都林一世">都林一世</a></li></ul></div><div class="mw-category-group"><h3>F</h3>
        <ul><li><a href="/wiki/%E6%B3%95%E6%8B%89%E7%B1%B3%E5%B0%94" title="法拉米尔">法拉米尔</a></li>
        <li><a href="/wiki/%E8%B4%B9%E8%89%BE%E8%AF%BA" title="费艾诺">费艾诺</a></li>
        <li><a href="/wiki/%E8%8F%B2%E7%BA%B3%E8%8A%AC" title="菲纳芬">菲纳芬</a></li>
        <li><a href="/wiki/%E8%8A%AC%E7%BD%97%E5%BE%B7" title="芬罗德">芬罗德</a></li>
        <li><a href="/wiki/%E8%8A%AC%E5%A8%81" title="芬威">芬威</a></li>
        <li><a href="/wiki/%E8%8A%AC%E5%B7%A9" title="芬巩">芬巩</a></li>
        <li><a href="/wiki/%E8%8A%AC%E5%9B%BD%E6%98%90" title="芬国昐">芬国昐</a></li>
        <li><a href="/wiki/%E5%BC%97%E7%BD%97%E5%A4%9A%C2%B7%E5%B7%B4%E9%87%91%E6%96%AF" title="弗罗多·巴金斯">弗罗多·巴金斯</a></li></ul></div><div class="mw-category-group"><h3>G</h3>
        <ul><li><a href="/wiki/%E7%94%98%E9%81%93%E5%A4%AB" title="甘道夫">甘道夫</a></li>
        <li><a href="/wiki/%E6%A0%BC%E5%8A%B3%E9%BE%99" title="格劳龙">格劳龙</a></li>
        <li><a href="/wiki/%E6%A0%BC%E7%BD%97%E8%8A%AC%E5%BE%B7%E5%B0%94" title="格罗芬德尔">格罗芬德尔</a></li>
        <li><a href="/wiki/%E5%8B%BE%E6%96%AF%E9%AD%94%E6%A0%BC" title="勾斯魔格">勾斯魔格</a></li>
        <li><a href="/wiki/%E5%92%95%E5%99%9C" title="咕噜">咕噜</a></li></ul></div><div class="mw-category-group"><h3>H</h3>
        <ul><li><a href="/wiki/%E8%83%A1%E6%9E%97%C2%B7%E6%B2%99%E7%90%86%E5%AE%89" title="胡林·沙理安">胡林·沙理安</a></li>
        <li><a href="/wiki/%E8%83%A1%E5%AE%89" title="胡安">胡安</a></li></ul></div><div class="mw-category-group"><h3>J</h3>
        <ul><li><a href="/wiki/%E5%90%89%E5%B0%94-%E5%8A%A0%E6%8B%89%E5%BE%B7" title="吉尔-加拉德">吉尔-加拉德</a></li>
        <li><a href="/wiki/%E5%90%89%E5%A7%86%E5%88%A9" title="吉姆利">吉姆利</a></li>
        <li><a href="/wiki/%E5%8A%A0%E6%8B%89%E5%BE%B7%E7%91%9E%E5%B0%94" title="加拉德瑞尔">加拉德瑞尔</a></li>
        <li><a href="/wiki/%E6%8D%B7%E5%BD%B1" title="捷影">捷影</a></li></ul></div><div class="mw-category-group"><h3>K</h3>
        <ul><li><a href="/wiki/%E5%87%AF%E5%8B%92%E5%B7%A9" title="凯勒巩">凯勒巩</a></li>
        <li><a href="/wiki/%E5%87%AF%E5%8B%92%E5%8D%9A%E6%81%A9" title="凯勒博恩">凯勒博恩</a></li>
        <li><a href="/wiki/%E5%87%AF%E5%8B%92%E5%B8%83%E6%9E%97%E5%8D%9A" title="凯勒布林博">凯勒布林博</a></li>
        <li><a href="/wiki/%E5%BA%93%E8%8C%B9%E8%8A%AC" title="库茹芬">库茹芬</a></li></ul></div><div class="mw-category-group"><h3>L</h3>
        <ul><li><a href="/wiki/%E8%8E%B1%E6%88%88%E6%8B%89%E6%96%AF" title="莱戈拉斯">莱戈拉斯</a></li>
        <li><a href="/wiki/%E9%9C%B2%E8%A5%BF%E6%81%A9" title="露西恩">露西恩</a></li></ul></div><div class="mw-category-group"><h3>M</h3>
        <ul><li><a href="/wiki/%E7%8E%9B%E6%A0%BC%E6%B4%9B%E5%B0%94" title="玛格洛尔">玛格洛尔</a></li>
        <li><a href="/wiki/%E8%BF%88%E6%A0%BC%E6%9E%97" title="迈格林">迈格林</a></li>
        <li><a href="/wiki/%E9%BA%A6%E6%9B%BC%C2%B7%E9%BB%84%E6%B2%B9%E8%8F%8A" title="麦曼·黄油菊">麦曼·黄油菊</a></li>
        <li><a href="/wiki/%E8%BF%88%E5%85%B9%E6%B4%9B%E6%96%AF" title="迈兹洛斯">迈兹洛斯</a></li>
        <li><a href="/wiki/%E6%9B%BC%E5%A8%81" title="曼威">曼威</a></li>
        <li><a href="/wiki/%E6%A2%85%E9%87%8C%E9%98%BF%E9%81%93%E5%85%8B%C2%B7%E7%99%BD%E5%85%B0%E5%9C%B0%E9%B9%BF" title="梅里阿道克·白兰地鹿">梅里阿道克·白兰地鹿</a></li>
        <li><a href="/wiki/%E7%BE%8E%E4%B8%BD%E5%AE%89" title="美丽安">美丽安</a></li>
        <li><a href="/wiki/%E9%AD%94%E8%8B%9F%E6%96%AF" title="魔苟斯">魔苟斯</a></li>
        <li><a href="/wiki/%E5%A2%A8%E7%8E%9F%C2%B7%E5%9F%83%E5%88%97%E5%85%B9%E7%8E%9F" title="墨玟·埃列兹玟">墨玟·埃列兹玟</a></li></ul></div><div class="mw-category-group"><h3>N</h3>
        <ul><li><a href="/wiki/%E6%B6%85%E8%AF%BA%E5%B0%94%C2%B7%E5%A6%AE%E6%B6%85%E5%B0%94" title="涅诺尔·妮涅尔">涅诺尔·妮涅尔</a></li></ul></div><div class="mw-category-group"><h3>O</h3>
        <ul><li><a href="/wiki/%E6%AC%A7%E6%B4%9B%E5%BE%B7%E7%91%9E%E6%96%AF" title="欧洛德瑞斯">欧洛德瑞斯</a></li></ul></div><div class="mw-category-group"><h3>P</h3>
        <ul><li><a href="/wiki/%E4%BD%A9%E9%87%8C%E6%A0%BC%E6%9E%97%C2%B7%E5%9B%BE%E5%85%8B" title="佩里格林·图克">佩里格林·图克</a></li></ul></div><div class="mw-category-group"><h3>Q</h3>
        <ul><li><a href="/wiki/%E5%A5%87%E5%B0%94%E4%B8%B9" title="奇尔丹">奇尔丹</a></li></ul></div><div class="mw-category-group"><h3>S</h3>
        <ul><li><a href="/wiki/%E8%90%A8%E8%8C%B9%E6%9B%BC" title="萨茹曼">萨茹曼</a></li>
        <li><a href="/wiki/%E7%91%9F%E5%85%B0%E6%9D%9C%E4%BC%8A" title="瑟兰杜伊">瑟兰杜伊</a></li>
        <li><a href="/wiki/%E6%A0%91%E9%A1%BB" title="树须">树须</a></li>
        <li><a href="/wiki/%E6%A2%AD%E6%9E%97%C2%B7%E6%A9%A1%E6%9C%A8%E7%9B%BE" title="梭林·橡木盾">梭林·橡木盾</a></li>
        <li><a href="/wiki/%E7%B4%A2%E9%9A%86" title="索隆">索隆</a></li></ul></div><div class="mw-category-group"><h3>T</h3>
        <ul><li><a href="/wiki/%E6%B1%A4%E5%A7%86%C2%B7%E9%82%A6%E5%B7%B4%E8%BF%AA%E5%B0%94" title="汤姆·邦巴迪尔">汤姆·邦巴迪尔</a></li>
        <li><a href="/wiki/%E9%93%81%E8%B6%B3%E6%88%B4%E5%9B%A0" title="铁足戴因">铁足戴因</a></li>
        <li><a href="/wiki/%E5%9B%BE%E5%B0%94%E5%B7%A9" title="图尔巩">图尔巩</a></li>
        <li><a href="/wiki/%E5%9B%BE%E5%A5%A5" title="图奥">图奥</a></li>
        <li><a href="/wiki/%E5%9B%BE%E6%9E%97%C2%B7%E5%9B%BE%E4%BC%A6%E6%8B%94" title="图林·图伦拔">图林·图伦拔</a></li></ul></div><div class="mw-category-group"><h3>W</h3>
        <ul><li><a href="/wiki/%E4%B9%8C%E6%AC%A7%E7%89%9F" title="乌欧牟">乌欧牟</a></li></ul></div><div class="mw-category-group"><h3>X</h3>
        <ul><li><a href="/wiki/%E5%B8%8C%E5%A5%A5%E9%A1%BF" title="希奥顿">希奥顿</a></li>
        <li><a href="/wiki/%E8%BE%9B%E8%91%9B" title="辛葛">辛葛</a></li></ul></div><div class="mw-category-group"><h3>Y</h3>
        <ul><li><a href="/wiki/%E9%9B%85%E5%87%A1%E5%A8%9C" title="雅凡娜">雅凡娜</a></li>
        <li><a href="/wiki/%E4%BC%8A%E5%A5%A5%E6%A2%85%E5%B0%94" title="伊奥梅尔">伊奥梅尔</a></li>
        <li><a href="/wiki/%E4%BC%8A%E7%86%99%E5%B0%94%E6%9D%9C" title="伊熙尔杜">伊熙尔杜</a></li>
        <li><a href="/wiki/%E4%BC%8A%E5%A5%A5%E6%B8%A9" title="伊奥温">伊奥温</a></li>
        <li><a href="/wiki/%E4%BC%8A%E5%A7%86%E6%8B%89%E5%B8%8C%E5%B0%94" title="伊姆拉希尔">伊姆拉希尔</a></li></ul></div></div></div>';
        $out->addHTML( $output );
        $out->addModuleStyles('ext.socialprofile.commenstyle.css');
        $out->addModules( 'ext.socialprofile.commenstyle.js' );
    }

}

?>
