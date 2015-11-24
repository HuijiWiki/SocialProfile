<?php
/**
 * add user info
 *
 */

class SpecialCompleteUserInfo extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
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
		$out = $this->getOutput();
		$request = $this->getRequest();

		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$code = $request->getVal( 'code' );
		if(empty($_GET['code'])) {
		    exit('参数非法');
		}
		$qq_sdk = new QqSdk();
		$token = $qq_sdk->get_access_token($_GET['code']);
		$open_id = $qq_sdk->get_open_id($token['access_token']);
		$user_info = $qq_sdk->get_user_info($token['access_token'], $open_id['openid']);
		if( $user_info['gender'] == '男' ){
			$gender = 'male';
		}elseif( $user_info['gender'] == '女' ){
			$gender = 'female';
		}else{
 			$gender = null;
  		}
  // 		$user_info['figureurl_qq_1'];
		// Add CSS & JS
		// $out->addModuleStyles( 'ext.socialprofile. .css' );
		// $out->addModules( 'ext.socialprofile. .js' );

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the UserBoard page
		 */
		// if ( $currentUser->getID() == 0 && $user_name == '' ) {
		// 	$login = SpecialPage::getTitleFor( 'Userlogin' );
		// 	$out->redirect( $login->getFullURL( 'returnto=Special:UserBoard' ) );
		// 	return false;
		// }
		$output="<form><label for='qqloginname'>用户名</label><input type='text' id='qqloginusername' class='form-control' value='".$user_info['nickname']."' name='qqloginname'>
		<label for='qqloginemail'>邮箱</label><input type='email' class='form-control' id='qqloginemail' placeholder=\"请输入邮箱\" name='qqloginemail'>
		<label for='qqloginpass'>密码</label><input type='password' id='qqloginpassword' class='form-control' placeholder=\"请输入密码\" name='qqloginpass'>  
		<input id='qqOpenId' type='hidden' value='".$open_id['openid']."' >
		<input id='userGender' type='hidden' value='".$gender."' >
		<input id='userAvatar' type='hidden' value='".$user_info['figureurl_qq_1']."' >
		<div class='mw-ui-button  mw-ui-block mw-ui-constructive' id='qqConfirm'>提交</div></form>";
		$out->addHTML( $output );
	}
}
