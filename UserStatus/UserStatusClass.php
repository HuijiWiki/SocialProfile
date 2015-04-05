<?php
/**
 * This Class manages the User and Site follows.
 */
class UserStatus{
	function __construct( ) {

	}   
	public function getAll($user){
		$data = self::getAllCache( $user );
		if ( $data != '' ) {
			return $data;
		} else {
			return self::getAllDB( $user );
		}
	}

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
	public function getAllCache($user){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $user );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user bio and status $data ( user = {$user} ) from cache\n" );
			return $data;
		}		
	}

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
		return 1;
		// return $profileId;

	}
}
