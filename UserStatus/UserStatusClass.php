<?php
/**
 * This Class manages the User and Site follows.
 */
class UserStatus{
	function __construct( ) {

	} 
	public function getGender($user){
		$data = self::getAllCache( $user );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = self::getAllDB( $user );
			$all = json_decode($data, true);
		}
		return $all['gender'];
	}  
	public function getProvince($user){
		$data = self::getAllCache( $user );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = self::getAllDB( $user );
			$all = json_decode($data, true);
		}
		return $all['province'];
	}  
	public function getCity($user){
		$data = self::getAllCache( $user );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = self::getAllDB( $user );
			$all = json_decode($data, true);
		}
		return $all['city'];
	}  
	public function getCity($user){
		$data = self::getAllCache( $user );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = self::getAllDB( $user );
			$all = json_decode($data, true);
		}
		return $all['birthday'];
	}  
	public function getStatus($user){
		$data = self::getAllCache( $user );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = self::getAllDB( $user );
			$all = json_decode($data, true);
		}
		return $all['status'];
	}  
	public function getAll($user){
		$data = self::getAllCache( $user );
		if ( $data != '' ) {
			return $data;
		} else {
			return self::getAllDB( $user );
		}
	}
    /**
     * GET ALL INFO FROM DATABASE
     * @param user User object
     * 
     * @return data String a json string contains requested info.
     */
	public function getAllDB($user){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $user->getName() );

		$gender = $user->getOption('gender');
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectRow(
			'user_profile',
			array(
				'up_location_state',
				'up_location_city',
				'up_birthday',
				'up_about'
			),
			array(
				'up_user_id' => $user->getId(),
			),
			__METHOD__
		);
		$data = '{"name":"'
			.$user->getName()
			.'","gender":"'
			.$gender
			.'","province":"'
			.$res->up_location_state
			.'","city":"'
			.$res->up_location_city
			.'","birthday":"'
			.$res->up_birthday
			.'","status":"'
			.$res->up_about
			.'"}';

		$wgMemc->set( $key, $data );
		return $data;
	}
    /**
     * GET ALL INFO FROM CACHE
     * @param user User object
     * 
     * @return data String a json string contains requested info.
     */
	public function getAllCache($user){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $user );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user bio and status $data ( user = {$user} ) from cache\n" );
			return $data;
		}		
	}
    /**
     * INSERT OR UPDATE ALL THE INFO 
     * @param user User object
     * @param others String.
     * @return true if success.
     */
	public function setAll($user, $gender, $province, $city, $birthday, $status){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $user->getName() );
		$user->setOption('gender', $gender);
		$dbw = wfGetDB( DB_MASTER );
		$dbw->upsert(
			'user_profile',
			array(
				'up_user_id' => $user->getId(),
				'up_location_state' => $province,
				'up_location_city' => $city,
				'up_birthday' => $birthday,
				'up_about' => $status,
			),
			array(
				'up_user_id' => $user->getId(),
			),
			array(
				'up_location_state' => $province,
				'up_location_city' => $city,
				'up_birthday' => $birthday,
				'up_about' => $status,
			),			
			__METHOD__
		);
		// $profileId = $dbw->insertId();
		$wgMemc->delete( $key );
		return true;
		// return $profileId;

	}
}
