<?php
/**
 * This Class manages the User and Site follows.
 */
class UserStatus{
	private $user;

	/** 
	 * Construct a UserStatus with giver user
	 * @param $aUser User Object
	 */
	public function __construct($aUser){
		$this->user = $aUser;
	}

	public function getGender(){
		$data = $this->getAllCache( );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['gender'];
	}  
	public function getProvince(){
		$data = $this->getAllCache( );
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['province'];
	}  
	public function getCity(){
		$data = $this->getAllCache();
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['city'];
	}  
	public function getBirthday(){
		$data = $this->getAllCache();
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB();
			$all = json_decode($data, true);
		}
		return $all['birthday'];
	}  
	public function getStatus(){
		$data = $this->getAllCache();
		if ($data != ''){
			$all = json_decode($data, true);
		} else {
			$data = $this->getAllDB( );
			$all = json_decode($data, true);
		}
		return $all['status'];
	}  
	public function getAll(){
		$data = $this->getAllCache( );
		if ( $data != '' ) {
			return $data;
		} else {
			return $this->getAllDB( );
		}
	}
    /**
     * GET ALL INFO FROM DATABASE
     * @param user User object
     * 
     * @return data String a json string contains requested info.
     */
	public function getAllDB(){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $this->user->getName() );

		$gender = $this->user->getOption('gender');
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
				'up_user_id' => $this->user->getId(),
			),
			__METHOD__
		);
		$data = '{"name":"'
			.$this->user->getName()
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
	public function getAllCache(){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $this->user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user bio and status $data ( user = {$this->user} ) from cache\n" );
			return $data;
		}		
	}
    /**
     * INSERT OR UPDATE ALL THE INFO 
     * @param user User object
     * @param others String.
     * @return true if success.
     */
	public function setAll($gender, $province, $city, $birthday, $status){
		global $wgMemc;
		$key = wfMemcKey( 'user_profile', 'get_all', $this->user->getName() );
		$this->user->setOption('gender', $gender);
		$dbw = wfGetDB( DB_MASTER );
		$dbw->upsert(
			'user_profile',
			array(
				'up_user_id' => $this->user->getId(),
				'up_location_state' => $province,
				'up_location_city' => $city,
				'up_birthday' => $birthday,
				'up_about' => $status,
				'up_date' => date( 'Y-m-d H:i:s' ),
			),
			array(
				'up_user_id' => $this->user->getId(),
			),
			array(
				'up_location_state' => $province,
				'up_location_city' => $city,
				'up_birthday' => $birthday,
				'up_about' => $status,
				'up_date' => date( 'Y-m-d H:i:s' ),
			),			
			__METHOD__
		);
		// $profileId = $dbw->insertId();
		$wgMemc->delete( $key );
		return true;
		// return $profileId;

	}
}
