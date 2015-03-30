<?php
class UserSiteFollow{
	public $user_id;
	public $user_name;
	public $wiki_domain;


    /**
	 * Constructor
	 */
	public function __construct( $username , $wikidomain) {
		$title1 = Title::newFromDBkey( $username );
		$this->user_name = $title1->getText();
		$this->user_id = User::idFromName( $this->user_name );
		$this->wikidomain = $wikidomain;
	}


	public function addUserSiteFollow($user, $wikidomain){
		$dbw = wgGetDB( DB_MASTER );
		$s = $dbw->insert(
			'user_site_follow',
			array(
			     'f_user_id' => $this->user_id,
			     'f_user_name' => $this->user_name,
			     'f_wiki_domain' => $this->wiki_domain,
			     'f_date' => date('T-m-d H:i:s')
			),
		 __METHOD__
		);
	}

	public function deleteUserSiteFollow($user, $wikidomain){

	}

	public function getSiteCount($wikidomain){}
}

?>