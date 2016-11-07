<?php
class HuijiForum{
	public static $lifetime = "7776000";
	static function register($huijiUser){
		global $wgRequest;
		$data = json_encode(array(
				'identification' => Confidential::$forumAdminAccount,
				'password' => Confidential::$forumAdminPassword
			));
		$ret = self::curlPost('token', $data);
		$auth = "Token ".$ret->token."; userId=".$ret->userId;
		if ($huijiUser->getEmail() != ''){
			$email = $huijiUser->getEmail();
		} else {
			$email = $huijiUser->getId().'@fake.com';
		}
		$rawUrl = $huijiUser->getAvatar()->getAvatarUrlPath();
		$url = substr($rawUrl, 0, strpos( $rawUrl, '?' ) );
		$attributes = json_encode(array(
				'data' => array(
						'attributes' => array(
							'username' => $huijiUser->getName(),
							'email' => $email,
							'password' => Confidential::$forumCommonPassword,
							'avatarUrl' => $url,
							'bio' => $huijiUser->getProfile()['status']
						)
				)
			));
		$ret = self::curlPost('users', $attributes, $auth);
		if ( isset($ret->errors) ){
			foreach($ret->errors as $error) {
				if ($error->source->pointer == "/data/attributes/email"){
					self::login($huijiUser);
					return;
				}
			}
			// print_r($ret->errors);
			// die();灰机wiki
			$time=time();
			$wgRequest->response()->setCookie('flarum_remember', 'undefined', $time+self::$lifetime, ['prefix'=> '']);
			return;
			//Name not legal. please sign up manually
		}
		// $patch = json_encode(array(
		// 		'data' => array(
		// 				'attributes' => array(
		// 					'isActivated' => true
		// 				)
		// 			)
		// 	));
		// self::curlPatch('users/'.$ret->data->id, $patch,$auth);
		$newUserData = json_encode(array(
			'identification' => $ret->data->attributes->username,
			'password' => Confidential::$forumCommonPassword,
			'lifetime' => self::$lifetime
		));
		$newUserToken = self::curlPost('token', $newUserData);
		$wgRequest->response()->setCookie( 'flarum_remember', $newUserToken->token, time()+self::$lifetime, ['prefix' => '']);
		return true;
	}
	static function login($huijiUser){
		global $wgRequest;
		$userData = json_encode(array(
			'identification' => $huijiUser->getName(),
			'password' => Confidential::$forumCommonPassword,
			'lifetime' => self::$lifetime
		));	
		$userToken = self::curlPost('token', $userData);
		$wgRequest->response()->setCookie( 'flarum_remember', $userToken->token?$userToken->token:'undefined',  time()+self::$lifetime, ['prefix' => ''] );
		return true;
	}
	private static function curlPost($apiEndpoint, $dataString, $auth = null){
		$api = 'http://forum.huiji.wiki/api/'.$apiEndpoint;
		$header = array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($dataString),
		);
		if ($auth != null){
			$header['Authorization'] = $auth;
		}
		$curl_opt_a = array(
			CURLOPT_URL => $api,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS =>$dataString,
			CURLOPT_HTTPHEADER =>$header,
		);
		$ch = curl_init();
		curl_setopt_array($ch,$curl_opt_a);
		$out = curl_exec($ch);
		curl_close($ch);
		return json_decode($out);
	}
	private static function curlPatch($apiEndpoint, $dataString, $auth = null){
		$api = 'http://forum.huiji.wiki/api/'.$apiEndpoint;
		$header = array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($dataString),
		);
		if ($auth != null){
			$header['Authorization'] = $auth;
		}
		$curl_opt_a = array(
			CURLOPT_URL => $api,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CUSTOMREQUEST => 'PATCH',
			CURLOPT_POSTFIELDS =>$dataString,
			CURLOPT_HTTPHEADER =>$header,
		);
		$ch = curl_init();
		curl_setopt_array($ch,$curl_opt_a);
		$out = curl_exec($ch);
		curl_close($ch);
		return json_decode($out);		
	}
		
}
?>
