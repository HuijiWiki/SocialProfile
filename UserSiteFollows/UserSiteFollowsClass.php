<?php
/**
 * Functions for managing relationship data
 */
class UserSiteFollow{
	function __construct( ) {

	}
	public function addUserSiteFollow($user, $wikidomain){
		return true;
	}
	public function deleteUserSiteFollow($user, $wikidomain){
		return true;
	}
	public function getSiteCount($wikidomain){
		return 1;
	}
	public function checkUserSiteFollow($user, $wikidomain){
		return true;
	}

}
