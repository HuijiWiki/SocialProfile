<?php
/**
 * This Class manages the User and Site follows.
 */
class UserSiteFollow{
	function __construct( ) {

	}

	/** add a user follow site action to the database.
	 *
	 *  @param $user User object: the user_name who initiates the follow
	 *  @param $huiji_prefix string: the wiki to be followed, use prefix as identifier.
	 *	@return bool: true if successfully followed
	 */
	public function addUserSiteFollow($user, $huiji_prefix){
		if (checkUserSiteFollow($user, $huiji_prefix)){
			return 0;
		}
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			'user_site_follow',
			array(
				'f_user_id' => $user->getId(),
				'f_user_name' => $user->getName(),
				'f_wiki_domain' => $huiji_prefix,
				'f_date' => date( 'Y-m-d H:i:s' )
			), __METHOD__
		);
		$followId = $dbw->insertId();
		$this->incFollowCount( $huiji_prefix );
		//Notify Siteadmin maybe?
		return $followId;

	}
	public function deleteUserSiteFollow($user, $huiji_prefix){
		return true;
	}
	public function getSiteCount($huiji_prefix){
		return 1;
	}
	public function checkUserSiteFollow($user, $huiji_prefix){
		return true;
	}
	private function incFollowCount($huiji_prefix){

	}

}
