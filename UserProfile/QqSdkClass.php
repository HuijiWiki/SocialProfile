<?php
/**
* QQ SDK
*/ 
 
class QqSdk{ 
	 private $app_id;
	 private $app_secret;
	 // private $redirect = 'http://test.huiji.wiki/callbackqq.php';
	 private $redirect = 'http://test.huiji.wiki/wiki/special:callbackqq';

	function __construct() { 
	    require_once('/var/www/html/Confidential.php');
	    $app_id = Confidential::$qq_app_id; 
		$app_secret = Confidential::$qq_app_secret; 
		
	}

	//配置APP参数
	
	 
	 
	function get_access_token($code,$appid,$appsecret) { 
		//获取access_token
		$token_url = 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&'
		. 'client_id=' . $appid . '&redirect_uri=' . urlencode($this->redirect)//回调地址
		. '&client_secret=' . $appsecret . '&code=' . $code; 
		$token = array();		//expires_in 为access_token 有效时间增量 
		parse_str($this->_curl_get_content($token_url), $token); 
		return $token; 
	} 
	 
	function get_open_id($token) { 
		$str = $this->_curl_get_content('https://graph.qq.com/oauth2.0/me?access_token=' . $token);
		if (strpos($str, "callback") !== false) { 
			$lpos = strpos($str, "("); 
			$rpos = strrpos($str, ")"); 
			$str = substr($str, $lpos + 1, $rpos - $lpos -1); 
		} 
		$user = json_decode($str, TRUE); 
		 
		return $user; 
	} 

	function get_user_info($token, $open_id, $appid) { 
	 
		$user_info_url = 'https://graph.qq.com/user/get_user_info?'
		. 'access_token=' . $token 
		. '&oauth_consumer_key=' . $appid
		. '&openid=' . $open_id 
		. '&format=json'; 
		 
		$info = json_decode($this->_curl_get_content($user_info_url), TRUE); 
		 
		return $info; 
	} 
	 
	private function _curl_get_content($url) { 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		//超时时间3s
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 3); 
		$result = curl_exec($ch); 
		curl_close($ch); 
		 
		return $result; 
	}

	//add info to table outh
	function addInfoToOauth( $otype, $openid, $userid ){
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->insert(
			'oauth',
			array(
				'o_type' => $otype,
				'open_id' => $openid,
				'user_id' => $userid,
			),
			__METHOD__
		);
		$u = User::newFromId( $userid );
		$u->setCookies(null,null,true );
		// $u->setSession();
		if($res){
			return true;
		}
	}
	//check is exist openid
	function checkOauth( $openid, $type ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'oauth',
			array(
				'user_id'
			),
			array(
				'open_id' => $openid,
				'o_type' => $type
			),
			__METHOD__
		);
		$user_id = null;
		if ($res){
			foreach($res as $value){
				$user_id = $value->user_id;
			}
		}
		return $user_id;
	}

	//check the use issue comlpete his/her info
	function checkUserIsComplete( $openid, $type ){
		$user_id = $this->checkOauth( $open_id, $type );
		if( !empty( $user_id ) && !empty( User::newFromId( $user_id ) ) ){
			return true;
		}else{
			return false;
		}
	}
 
} 
 
/* end of Qq_sdk.php */ 