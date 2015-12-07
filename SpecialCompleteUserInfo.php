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
		$out = $this->getOutput();
		$request = $this->getRequest();
		$access_token = empty($request->getVal( 'code' ))?null:$request->getVal( 'code' );
		$type = empty($request->getVal( 'type' ))?null:$request->getVal( 'type' );
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		if(empty($_GET['code'])) {
			$out->setPageTitle( $this->msg( 'complete_user_error' )->plain() );
			return false;
		}
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
		$output = "<span>您当前使用的第三方账号登录，建议您绑定官方账号更有利于您的账户安全！</span>";
		$output .= "<form  class='complete-user-info'><label for='qqloginname'>用户名</label><input type='text' id='qqloginusername' class='form-control' value='".$nickname."' name='qqloginname'>
			<label for='qqloginemail'>邮箱</label><input type='email' class='form-control' id='qqloginemail' placeholder=\"请输入邮箱\" name='qqloginemail'>
			<label for='qqloginpass'>密码</label><input type='password' id='qqloginpassword' class='form-control' placeholder=\"请输入密码\" name='qqloginpass'>  
			<input id='qqOpenId' type='hidden' value='".$openid."' >
			<input id='userGender' type='hidden' value='".$gender."' >
			<input id='userAvatar' type='hidden' value='".$avatar."' >
			<input id='userType' type='hidden' value='".$type."' >
			<div class='mw-ui-button  mw-ui-block mw-ui-constructive' id='qqConfirm'>提交</div></form>";

		$out->addHTML( $output );
	}
}
