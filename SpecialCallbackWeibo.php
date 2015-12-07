<?php
/**
 * add user info
 *
 */

class SpecialCallbackWeibo extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once('/var/www/html/Confidential.php');
		parent::__construct( 'CallbackWeibo' );

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
		global $wgUser;
		// echo "wb";die;
		$request = $this->getRequest();
		$code = $request->getVal( 'code' );
		$o = new SaeTOAuthV2(  Confidential::$weibo_app_id , Confidential::$weibo_app_secret  );
		if ( $code ) {
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = Confidential::$weibo_callback_url;
			try {
				$token = $o->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
				echo "token error";die;
			}
		}
		$c = new SaeTClientV2( Confidential::$weibo_app_id , Confidential::$weibo_app_secret , $token['access_token'] );
		$uid_get = $c->get_uid();
		$uid = $uid_get['uid'];
		// print_r($uid);die;
		// $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信�er->touch();

		$qq_sdk = new QqSdk();
  	        $checkRes = $qq_sdk->checkOauth( $uid, 'weibo' );
	    if( $checkRes == null ){
	        header('Location: http://huiji.wiki/wiki/special:completeuserinfo?type=weibo&code='.$token['access_token']);
	        exit;
	    }else{
	        // success login redirect to index
	        $user = User::newFromId($checkRes);
		$user->touch();
		$wgUser = $user;
		$this->getContext()->setUser( $user );
	        $user->setCookies(null, null, false);
		wfResetSessionID();
		$request->setSessionData( 'wsLoginToken', null );
	        header('Location: http://www.huiji.wiki/');
	        exit;
	    }
	}
}
