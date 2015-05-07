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
		$key = wfForeignMemcKey('huiji','', 'user_profile', 'get_all', $this->user->getName() );

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
		$key = wfForeignMemcKey('huiji','', 'user_profile', 'get_all', $this->user->getName() );
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
		$key = wfForeignMemcKey('huiji','', 'user_profile', 'get_all', $this->user->getName() );
		if ($gender == 'male'){
			$this->user->setOption('gender', 'male');
		} elseif ($gender == 'female'){
			$this->user->setOption('gender', 'female');
		} else {
			$this->user->setOption('gender', null);
		}
		$this->user->saveSettings();
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
	/**
	 * Get user's followed from the
	 * database
	 *
	 * @param $username:current user
	 * @return array
	 */
	public static function getFollowedByUser( $username ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = array();
		$res = $dbr->select(
			'user_user_follow',
			array(
				'f_target_user_name'
			),
			array(
				'f_user_name' => $username
			),
			__METHOD__
		);
		foreach ($res as $value) {
			$req[] = $value->f_target_user_name;
		}
		$res = $req;
		return $res;
	}
	/**
     * GET USER INFO
     * @param user UserName
     * 
     * @return data String a json string contains requested info.
     */
	public function getUserAllInfo( ){
		$data = $this->getUserAllInfoCache( );
		if ( $data != '' ) {
			return $data;
		} else {
			return $this->getUserAllnfoDB( );
		}
	}
	/**
     * GET USER INFO FROM CACHE
     * @param user UserName
     * 
     * @return data String a json string contains requested info.
     */
	public function getUserAllInfoCache( ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'user_all_info', 'get_all', $this->user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user bio and status $data ( user = {$this->user} ) from cache\n" );
			return $data;
		}
	}
	/**
     * GET USER INFO FROM DATABASE
     * @param user UserName
     * 
     * @return data String a json string contains requested info.
     */
	public function getUserAllInfoDB( ){
		global $wgMemc,$wgUser;
		$username = $this->user->getName();
		// return $username;
		$key = wfForeignMemcKey('huiji','', 'user_all_info', 'get_all', $username );
		$result = array();
		$data = array();
		$result['username'] = $username;
		$user_id = $this->user->getId();
		$avatar = new wAvatar( $user_id, 'm' );
		$result['url'] = $avatar->getAvatarURL();
		$gender = $this->getGender();
		$status = $this->getStatus();
		$result['gender'] = $gender;
		$result['status'] = $status;
		$dbr = wfGetDB( DB_SLAVE );
		$level = $dbr->select(
			'user_stats',
			array(
				'stats_total_points'
			),
			array(
				'stats_user_name' => $username
			),__METHOD__
		);
		foreach ($level as $value) {
			$result['level'] = $value->stats_total_points;
		}
		//关注数
		$req = $dbr->select(
			'user_user_follow',
			array( 'COUNT( f_target_user_name ) AS ucount' ),
			array(
				'f_user_name' => $username
			),
			__METHOD__
		);
		if( $req != false ){
			$result['usercount'] = $req->ucount;
		}
		//粉丝数
		$reqed = $dbr->select(
			'user_user_follow',
			array( 'COUNT( f_user_name ) AS ucount' ),
			array(
				'f_target_user_name' => $username
			),
			__METHOD__
		);
		if( $reqed != false ){
			$result['usercounted'] = $reqed->ucount;
		}
		//编辑数
		$edt = $dbr->select(
			'user',
			array( 'user_editcount' ),
			array(
				'user_name' => $this->user->getName()
			),
			__METHOD__
		);
		if( $edt != false ){
			$result['editcount'] = $edt->user_editcount;
		}
		
		//判断是否关注
		$current_user = $wgUser->getName();
		// return $current_user;
		$follower = self::getFollowedByUser($current_user);
		// return 'qqq';
		if(in_array($username, $follower)){
			$result['is_follow'] = 'Y';
		}else{
			$result['is_follow'] = 'N';
		}
		//共同关注的用户
		$cfollow = array();
		$ufollower = self::getFollowedByUser($username);
		foreach ($follower as $value) {
			// $user1 = $value->f_target_user_name;
			if(in_array($value, $ufollower)){
				$cfollow[] = $value; 
			}
		}
		$result['commonfollow'] = $cfollow;
		//我关注的谁也关注他
		$followehe = $dbr->select(
			'user_user_follow',
			array( 'f_user_name' ),
			array(
				'f_target_user_name' => $username
			),
			__METHOD__
		);
		$followhim = array();
		foreach ($followehe as $val) {
			$folname = $val->f_user_name;
			if(in_array($folname, $follower)){
				$followhim[] = $folname;
			}
		}
		$result['minefollowerhim'] = $followhim;
		// $data = $result;
		$wgMemc->set( $key, $result );
		return $result;
	}
	/**
     * GET SIMPLE USER INFO
     * @param user UserName
     * 
     * @return data array contains requested info.
     */
	public function getSimpleUserInfo( ){
		$data = $this->getSimpleUserInfoCache( );
		if ( $data != '' ) {
			return $data;
		} else {
			return $this->getSimpleUserInfoDB( );
		}
	}
	/**
     * GET SIMPLE USER INFO FROM CACHE
     * @param user UserName
     * 
     * @return data array contains requested info.
     */
	public function getSimpleUserInfoCache( ){
		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'simple_user_info', 'get_all', $this->user->getName() );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got user bio and status $data ( user = {$this->user} ) from cache\n" );
			return $data;
		}
	}
	public function getSimpleUserInfoDB( ){
		global $wgMemc;
		$username = $this->user->getName();
		$key = wfForeignMemcKey('huiji','', 'simple_user_info', 'get_all', $username );
		$result['username'] = $username;
		$user_id = $this->user->getId();
		$avatar = new wAvatar( $user_id, 'm' );
		$result['url'] = $avatar->getAvatarURL();
		$dbr = wfGetDB( DB_SLAVE );
		$level = $dbr->select(
			'user_stats',
			array(
				'stats_total_points'
			),
			array(
				'stats_user_name' => $username
			),__METHOD__
		);
		foreach ($level as $value) {
			$result['level'] = $value->stats_total_points;
		}
		$gender = $this->getGender();
		$result['gender'] = $gender;
		//判断是否关注
		$current_user = $wgUser->getName();
		// return $current_user;
		$follower = self::getFollowedByUser($current_user);
		// return 'qqq';
		if(in_array($username, $follower)){
			$result['is_follow'] = 'Y';
		}else{
			$result['is_follow'] = 'N';
		}
		$wgMemc->set( $key, $result );
		return $result;
	}
}
