<?php
/**
 * add user info
 *
 */

class SpecialCompleteUserInfo extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once('/var/www/html/Confidential.php');
		parent::__construct( 'CompleteUserInfo' );

	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'users';
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		global $wgRequest;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$access_token = empty($request->getVal( 'code' ))?null:$request->getVal( 'code' );
		$type = empty($request->getVal( 'type' ))?null:$request->getVal( 'type' );
		$redirect = empty($request->getVal( 'redirect' ))?null:$request->getVal( 'redirect' );
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		if(empty($_GET['code'])) {
			$out->setPageTitle( $this->msg( 'complete_user_error' )->plain() );
			return false;
		}
		$out->addModuleStyles('mediawiki.special.userlogin.signup.styles');
		$out->addModuleScripts('ext.socialprofile.qqLogin.js');
		$out->addModuleStyles('ext.socialprofile.userinfo.css');
		if( $type == 'qq' ){
			$qq_sdk = new QqSdk();
			$open_id = $qq_sdk->get_open_id($access_token);
			if( array_key_exists('openid', $open_id ) ){
				$user_info = $qq_sdk->get_user_info($access_token, $open_id['openid'], Confidential::$qq_app_id);
				if( $user_info['gender'] == '男' ){
					$gender = 'male';
				}elseif( $user_info['gender'] == '女' ){
					$gender = 'female';
				}else{
		 			$gender = null;
		  		}
		  		$nickname = $user_info['nickname'];
		  		$openid = $open_id['openid'];
		  		$avatar = $user_info['figureurl_qq_1'];
			}
		}else if ( $type == 'weibo' ) {
			$c = new SaeTClientV2( Confidential::$weibo_app_id , Confidential::$weibo_app_secret , $access_token );
			$uid_get = $c->get_uid();
			$uid = $uid_get['uid'];
			$user_info = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
			if( $user_info['gender'] == 'm' ){
				$gender = 'male';
			}elseif( $user_info['gender'] == 'f' ){
				$gender = 'female';
			}else{
	 			$gender = null;
	  		}
	  		$nickname = $user_info['screen_name'];
	  		$openid = $uid;
	  		$avatar = $user_info['avatar_large'];
		}
		$output = "<form  class='complete-user-info'><p>您当前使用的第三方账号登录，现在我们只需要您补充一点信息</p><input type='text' id='qqloginusername' placeholder='用户名' value='".$nickname."' name='qqloginname'>
		    <input type='password' id='qqloginpassword'  placeholder=\"请输入密码\" name='qqloginpass'>
			<input type='email'  id='qqloginemail' placeholder=\"请输入邮箱\" name='qqloginemail'>
			<input id='qqOpenId' type='hidden' value='".$openid."' >
			<input id='userGender' type='hidden' value='".$gender."' >
			<input id='userAvatar' type='hidden' value='".$avatar."' >
			<input id='userType' type='hidden' value='".$type."' >
			<input id='redirect_url' type='hidden' value='".$redirect."' >
			<input id='inviteuser' type='hidden' value=0 >
			<input id='wpCreateaccountToken' type='hidden' value='".$wgRequest->getSession()->getToken( '', 'createaccount' )->toString()."' >
            <div class='mw-ui-button  mw-ui-block mw-ui-constructive btn' data-loading-text='提交中...' id='qqConfirm'>提交</div></form>";

		$output .=	'<div class="mw-createacct-benefits-container unite-container">'.
			    "<h2>".$this->msg( 'createacct-benefit-heading' )."</h2>".
			    '<div class="mw-createacct-benefits-list">';
	
		for ( $benefitIdx = 1; $benefitIdx <= 3; $benefitIdx++ ) {
			// Pass each benefit's head text (by default a number) as a parameter to the body's message for PLURAL handling.
			$headUnescaped = $this->msg( "createacct-benefit-head$benefitIdx" )->text();
			$output.= '<div class="mw-number-text '.$this->msg( "createacct-benefit-icon$benefitIdx" ).'">'.
			                '<h3>'.$this->msg( "createacct-benefit-head$benefitIdx" ).'</h3>'.
			                '<p>'.$this->msg( "createacct-benefit-body$benefitIdx" )->params( $headUnescaped )->escaped().'</p>'.
			            "</div>";
		}
		$output .=	"</div>".
			 "</div>";		

		$out->addHTML( $output );
	}
}
