<?php
/**
* QQ SDK
*/ 
 
class QqSdk{ 

 
	//配置APP参数
	private $app_id = Confidential::$qq_app_id; 
	private $app_secret = Confidential::$qq_app_secret; 
	private $redirect = 'http://test.huiji.wiki/callbackqq.php';
	 
	function __construct() { 
	    require_once('/var/www/html/Confidential.php');
	} 
	 
	/**
	* [get_access_token 获取access_token]
	* @param [string] $code [登陆后返回的$_GET['code']]
	* @return [array] [expires_in 为有效时间 , access_token 为授权码 ; 失败返回 error , error_description ]
	*/ 
	function get_access_token($code) { 
		//获取access_token
		$token_url = 'https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&'
		. 'client_id=' . $this->app_id . '&redirect_uri=' . urlencode($this->redirect)//回调地址
		. '&client_secret=' . $this->app_secret . '&code=' . $code; 
		$token = array();		//expires_in 为access_token 有效时间增量 
		parse_str($this->_curl_get_content($token_url), $token); 
		return $token; 
	} 
	 
	/**
	* [get_open_id 获取用户唯一ID，openid]
	* @param [string] $token [授权码]
	* @return [array] [成功返回client_id 和 openid ;失败返回error 和 error_msg]
	*/ 
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
	 
	/**
	* [get_user_info 获取用户信息]
	* @param [string] $token [授权码]
	* @param [string] $open_id [用户唯一ID]
	* @return [array] [ret：返回码，为0时成功。msg为错误信息,正确返回时为空。...params]
	*/ 
	function get_user_info($token, $open_id) { 
	 
		//组装URL
		$user_info_url = 'https://graph.qq.com/user/get_user_info?'
		. 'access_token=' . $token 
		. '&oauth_consumer_key=' . $this->app_id 
		. '&openid=' . $open_id 
		. '&format=json'; 
		 
		$info = json_decode($this->_curl_get_content($user_info_url), TRUE); 
		 
		return $info; 
	} 
	 
	private function _curl_get_content($url) { 
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_URL, $url); 
		//设置超时时间为3s
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