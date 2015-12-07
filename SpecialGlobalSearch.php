<?php
/**
 * add user info
 *
 */

class SpecialGlobalSearch extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'GlobalSearch' );

	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'wiki';
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$access_token = empty($request->getVal( 'code' ))?null:$request->getVal( 'code' );
		$type = empty($request->getVal( 'type' ))?null:$request->getVal( 'type' );
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		// if(empty($_GET['code'])) {
		// 	$out->setPageTitle( $this->msg( 'complete_user_error' )->plain() );
		// 	return false;
		// }
		$output = "<span>global search</span>";
		$output .= "<form method='get' action='' >
			<input type='hidden' value='special:globalsearch' name='title'>
			<input id='global_search'  >
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='搜索'>
			</form>";

		$out->addHTML( $output );
	}
}
