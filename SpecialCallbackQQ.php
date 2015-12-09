<?php
/**
 * add user info
 *
 */

class SpecialCallbackQQ extends SpecialPage {

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
		$request = $this->getRequest();
		$code = $request->getVal( 'code' );
		$qq_sdk = new QqSdk();
	    $token = $qq_sdk->get_access_token($code,Confidential::$qq_app_id,Confidential::$qq_app_secret);
	    $open_id = $qq_sdk->get_open_id($token['access_token']);
	    $checkRes = $qq_sdk->checkOauth( $open_id['openid'], 'qq' );
	    if( $checkRes == null ){
	        header('Location: http://huiji.wiki/wiki/special:completeuserinfo?type=qq&code='.$token['access_token']);
	        exit;
	    }else{
	        // success login redirect to index
	        $user = User::newFromId($checkRes);
	        $user->setCookies(null, null, true);
	        //echo "<script>location.href = document.referrer;</script>";
	        header('Location: http://huiji.wiki/');
		exit;
	    }
	}
}
