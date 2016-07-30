<?php   
/**
* invite user
*/
class SpecialInviteUser extends UnlistedSpecialPage{
    
    function __construct(){
        parent::__construct( 'InviteUser' );
    }
    public function getGroup(){
        return 'user';
    }

    public function execute( $params ) {
        global $wgUser, $wgHuijiPrefix, $wgLocalFileRepo, $wgContLang;
        // Set the page title, robot policies, etc.
        $this->setHeaders();
        $out = $this->getOutput();
        $request = $this->getRequest();
        $prefix = $request->getVal('prefix');
        $userName = $request->getVal('user');
        $redirect = $wgHuijiPrefix;
        $output = '';
        if ( $prefix == null || $userName == null ) {
            $output .= '<div class="alert alert-danger">您输入的参数不正确。请返回首页。</div>';
            $out->addHTML( $output );
            return false;
        }
        $site = WikiSite::newFromPrefix($prefix);
        if ($site->getId() != null) {
            $huijiUser = HuijiUser::newFromName( $userName );
            if ($huijiUser->getId() != null ) {
                $userStats = $huijiUser->getStats(false);
                $siteStats = $site->getStats(false);
                $userGroup = $huijiUser->getEffectiveGroups();
                if ( in_array('sysop', $userGroup) ) {
                    $group = '管理员';
                }elseif ( in_array('bureaucrat', $userGroup) ) {
                    $group = '行政员';
                }else{
                    $group = '编辑者';
                }
                $output .= "<div><span class=\"invite-title\">各位大神和水友：</span>
                            <p>我是<b>".$site->getName()."</b>的".$group.":".$userName."，目前已经在".$site->getName()."编辑了".$userStats['edits']."次。</p>
                            <p>诚挚的邀请您加入".$site->getName()."的编辑组。</p>
                            <p>关于".$site->getName()."</p>
                            ".$site->getDescription()."。<br>
                            自".substr($site->getDate(),0,10)."创立以来，已经有".$siteStats['pages']."个页面，".$siteStats['edits']."次编辑和".$siteStats['followers']."个编辑者。<br>
                            参与编辑组的好处：<br>
                            * 汇聚星星之火，让更多人发现和了解".$site->getName()."<br>
                            * 驾驭强大的维基系统，构建你的专属世界<br>
                            * 获得专属标识，邂逅与你频率相同的“机”友<br>
                            * 无需前置审核，不受束缚的热情创造<br>
                            把爱分享给世界，世界也将用爱回报。<br>
                            //（灰色小字）纯粹兴趣分享，无任何经济回报<br>
                            <form  class='complete-user-info'><p>快速注册</p>
                            用户名:<input type='text' id='qqloginusername' placeholder='用户名' name='qqloginname'><br>
                            密码:<input type='password' id='qqloginpassword'  placeholder=\"请输入密码\" name='qqloginpass'><br>
                            邮箱:<input type='email' id='qqloginemail' placeholder=\"请输入邮箱\" name='qqloginemail'><br>
                            <input id='redirect_url' type='hidden' value='".$redirect."' >
                            <input id='inviter' type='hidden' value='".$userName."' >
                            <input id='inviteuser' type='hidden' value=1 >";

                if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
                    $submitButton ="<div class='mw-ui-button  mw-ui-block mw-ui-constructive btn' data-loading-text='提交中...' id='qqConfirm'>提交</div>";
                }else{
                    $submitButton ="<div class='mw-ui-button  mw-ui-block mw-ui-constructive btn' data-loading-text='提交中...' id='qqConfirm' disabled>提交</div>";
                }
                $output .= "</form></div>";
            }else{
                $output .= '<h1>user输入有误</h1>';
            }
        }else{
            $output .= '<div class="alert alert-danger">您输入的参数不正确。请返回首页。</div>';
            $out->addHTML( $output );
            return false;
        }
        $customInvitationMessage = wfMessage('custom-invitation-message')->parse();
        if ($customInvitationMessage != ''){
            $hasCustomInvitationMessage = true;
        } else {
            $hasCustomInvitationMessage = false;
        }
        $templateParser = new TemplateParser(  __DIR__ . '/pages' );
        $output = $templateParser->processTemplate(
            'invites',
            array(
                'siteName' => $site->getName(),
                'duty' => $group,
                'userName' => $userName,
                'numberOfEditsByMe' => $userStats['edits'],
                'siteDescription' => $site->getDescription(),
                'siteTime' => substr($site->getDate(),0,10),
                'numberOfPages' => $siteStats['pages'],
                'numberOfEdits' => $siteStats['edits'],
                'numberOfFollowers' => $siteStats['followers'],
                'redirect' => $redirect,
                'submitButton' => $submitButton,
                'customInvitationMessage' => $customInvitationMessage,
                'hasCustomInvitationMessage' => $hasCustomInvitationMessage,
            )
        );
        $out->addModuleStyles(array('ext.socialprofile.inviteuser.css', 'ext.socialprofile.userinfo.css', 'mediawiki.special.userlogin.signup.styles'));
        $out->addModules(array('ext.socialprofile.inviteuser.js', 'mediawiki.cookie', 'ext.socialprofile.qqLogin.js'));
        $out->addHTML( $output );

    }



}

?>
