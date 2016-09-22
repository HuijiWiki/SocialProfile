<?php 
class SpecialForumDiscussions extends IncludableSpecialPage{
	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ForumDiscussions' );
	}

	/**
	 * Show the new special page
	 *
	 * @param int $limit Show this many entries (LIMIT for SQL)
	 */
	public function execute( $par ) {
		global $wgMemc, $wgExtensionAssetsPath;
		$params = explode('/', $par);
		if (  isset($params[0]) && $params[0] != '' && in_array( $params[0], ['list', 'expanded']) ){
			$mode = $params[0];
		} else {
			$mode = 'expanded';
		}
		if ( isset($params[1]) && $params[1] != '' && is_integer((int)$params[1]) && $params[1] <= 20){
			$count = $params[1];
		} else {
			$count = '5';
		}
		if ( isset($params[2]) && $params[2] != ''){
			$user = $params[2];
		} else {
			$user = null;
		}
		$out = $this->getOutput();
		$out->addModules('ext.socialprofile.special.forumdiscussions');
		$this->setHeaders();
		$out->addHtml('<div class="forumlist-container" data-mode="'.$mode.'" data-count="'.$count.'" data-user="'.$user.'"></div>');
		$out->addHtml( Linker::linkKnown( SpecialPage::getTitleFor('RedirectToForum'), wfMessage('special-forumdiscussions-see-all')->text(), ['class'=>'btn pull-right btn-primary']  ) );
		// $out->addHTML( $output );
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'forum';
	}
}