<?php
/**
 * add user info
 *
 */
 use MediaWiki\Auth\AuthManager;
 use MediaWiki\Session\SessionId;
class SpecialCallbackQQ extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once('/var/www/html/Confidential.php');
		parent::__construct( 'CallbackQQ' );

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
		if ($site != $wgHuijiPrefix){
			$out->redirect( "http://".$site.$wgHuijiSuffix.SpecialPage::getTitleFor( 'Callbackqq' )->getLocalURL(
					['code' => $code, 'state' => $state, 'site' => $site, 'sid' => $sessionId]
				));
			return;
		}
		$qq_sdk = new QqSdk();
	    $accessToken = $qq_sdk->get_access_token($code,Confidential::$qq_app_id,Confidential::$qq_app_secret);


		$session = MediaWiki\Session\SessionManager::singleton()->getSessionById($sessionId, false, $request);
		$session->sessionWithRequest($request);
		$session->persist();
		$request->setSessionId(new SessionId($sessionId));

		$authData = $session->getSecret( 'authData' );
		$token = $session->getToken( QQLogin\Auth\QQPrimaryAuthenticationProvider::TOKEN_SALT );
		$redirectUrl = $authData[QQLogin\Auth\QQPrimaryAuthenticationProvider::RETURNURL_SESSION_KEY];
		$authAction = $authData[QQLogin\Auth\QQPrimaryAuthenticationProvider::RETURNURL_AUTHACTION_KEY];
		if ( !$redirectUrl || !$token->match( $request->getVal( 'state' ) ) ) {
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
		// $out->addHtml('<p>hello</p>');
		// $out->redirect( $redirectUrl );
	}
}
