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
		// echo $code.'ssss'.Confidential::$qq_app_id.Confidential::$qq_app_secret;
		$qq_sdk = new QqSdk();
	    $token = $qq_sdk->get_access_token($code,Confidential::$qq_app_id,Confidential::$qq_app_secret);
	    // print_r($token);die;
	    $open_id = $qq_sdk->get_open_id($token['access_token']);
	    // print_r($open_id);die;
	   // $request = $this->getRequest();
	    $checkRes = $qq_sdk->checkOauth( $open_id['openid'], 'qq' );
	    // print_r($checkRes);die;
	    if( $checkRes == null ){
	    	// echo 'null';die;
	        //goto complete user info
	        // header('Location: http://slx.test.huiji.wiki');
	        header('Location: http://test.huiji.wiki/wiki/special:completeuserinfo?code='.$token['access_token']);
	        exit;
	    }else{
	    	// echo '111';die;
	        // success login redirect to index
	        $user = User::newFromId($checkRes);
	        $user->setCookies(null, null, true);
	        header('Location: http://test.huiji.wiki/wiki/%E9%A6%96%E9%A1%B5?loggingIn=1');
	        exit;
	    }
	}
}
