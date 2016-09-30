<?php
/**
 * add user info
 *
 */
 use MediaWiki\Auth\AuthManager;
 use MediaWiki\Session\SessionId;
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
		global $wgCentralServer, $wgHuijiSuffix, $wgHuijiPrefix;
		$request = $this->getRequest();
		$code = $request->getVal( 'code' );
		$site = $request->getVal( 'site' );
		$sessionId = $request->getVal('sid');
		$out = $this->getOutput();
		$state = $request->getVal( 'state' );
		$state = str_replace(' ', '+', $state);//Weibo ignores + sign in my token. Fuck.
		if ($site != $wgHuijiPrefix){
			$out->redirect( "http://".$site.$wgHuijiSuffix.SpecialPage::getTitleFor( 'CallbackWeibo' )->getLocalURL(
					['code' => $code, 'state' => $state, 'site' => $site, 'sid' => $sessionId]
				));
			return;
		}
		$sdk = new SaeTOAuthV2(  Confidential::$weibo_app_id , Confidential::$weibo_app_secret  );
		if ( $code ) {
			$keys = array();
			$keys['code'] = $code;
			$keys['redirect_uri'] = Confidential::$weibo_callback_url;
			try {
				$accessToken = $sdk->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
				echo "token error";die;
			}
		}
	    
		$session = MediaWiki\Session\SessionManager::singleton()->getSessionById($sessionId, false, $request);
		//TODO : validate session. It can be null in some cases
		if ($session == null){
			$out->redirect( SpecialPage::getTitleFor( 'UserLogin' )->getLocalURL() );
			return;
		}
		$session->sessionWithRequest($request);
		$session->persist();
		$request->setSessionId(new SessionId($sessionId));

		$authData = $session->getSecret( 'authData' );
		$token = $session->getToken( WeiboLogin\Auth\WeiboPrimaryAuthenticationProvider::TOKEN_SALT );
		$redirectUrl = $authData[WeiboLogin\Auth\WeiboPrimaryAuthenticationProvider::RETURNURL_SESSION_KEY];
		$authAction = $authData[WeiboLogin\Auth\WeiboPrimaryAuthenticationProvider::RETURNURL_AUTHACTION_KEY];


		if ( !$redirectUrl || !$token->match( $state )) {
			$out->redirect( SpecialPage::getTitleFor( 'UserLogin' )->getLocalURL() );
			return;
		}
		$redirectUrl = wfAppendQuery( $redirectUrl, [ 'code' => $accessToken['access_token'], 'sid' => $sessionId ] );
		// NO ERROR, let js do the rest
		//$out->addModules('ext.HuijiMiddleware.callbackqq.js');
		// $out->addHtml('<p>'.$request->getSession()->getId().'</p>');
		//header("refresh: 2; url=$redirectUrl");
		setcookie("huiji_session", $session->getId(), strtotime( '+90 days' ), "/", ".huiji.wiki", false, true );
		//$out->redirect('http://hearthstone.huiji.wiki/wiki/1');
		//include($redirectUrl);
		$out->redirect( $redirectUrl );
		return;
		
		// $c = new SaeTClientV2( Confidential::$weibo_app_id , Confidential::$weibo_app_secret , $token['access_token'] );
		// $uid_get = $c->get_uid();
		// $uid = $uid_get['uid'];
		// $user_message = $c->show_user_by_id( $uid);//æ ¹æ®IDèŽ·å–ç”¨æˆ·ç­‰åŸºæœ¬ä¿¡æer->touch();

	}
}
