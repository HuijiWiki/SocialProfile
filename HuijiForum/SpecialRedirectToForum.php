<?php
class SpecialRedirectToForum extends SpecialPage {
	public function __construct( $name = 'RedirectToForum' ) {
		parent::__construct( $name );
	}

	public function execute( $par ) {
		$hj = HuijiUser::newFromUser($this->getUser());
		if ($this->getUser()->isLoggedIn()){
			if (!isset($_COOKIE['flarum_remember'])){
				HuijiForum::register($hj);
			}
		} else {
			if (isset($_COOKIE['flarum_remember'])){
				$wgRequest->response()->clearCookie('flarum_remember', ['prefix' => '']);	
			}
		}
		$this->getOutput()->redirect( 'http://forum.huiji.wiki' );
	}
	function getGroupName() {
    		return 'redirects';
	}
}