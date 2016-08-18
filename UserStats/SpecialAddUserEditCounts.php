<?php

/**
* special page for add user's edit number
*/
class SpecialAddUserEditCounts extends UnlistedSpecialPage{
	
	public function __construct() {
		parent::__construct( 'AddUserEditCounts', 'AddUserEditCounts' );

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
	 */
	public function execute( $params ) {
		global $wgMemc, $wgUser;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$userName = empty($request->getVal( 'user' ))?null:$request->getVal( 'user' );
		$num = empty($request->getVal('num'))?null:$request->getVal('num');
		$date = empty($request->getVal('date'))?null:$request->getVal('date');
		$this->checkPermissions();
		$this->checkReadonly();			
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$output = "";
		$output .= "<form method='get' action='/wiki/special:addusereditcounts' >
			用户名：<input type='text' name='user' >
			补签次数：<input type='text' name='num' >
			补签日期：<input type='date' name='date' >
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='添加'>
			</form>";

		if( $userName != null && $num != null && $date != null ){
			$user = User::newFromName( $userName );
			if( !empty($user->getId()) && is_numeric($num) ){
				$resJson = RecordStatistics2::insertOneFakedPageEditRecord( $user->getId(), $num, $date );
				$resObj = json_decode($resJson);
				if ( $resObj->status !== 'fail' ) {
					$output .= "<h1>success</h1>";
					$key = wfForeignMemcKey('huiji','', 'user_daily_edit', 'all_days', $user->getId() );
					$wgMemc->delete($key);
				}
			}else{
				$output .= "<h1> something wrong~ </h1>";
			}
		}
		$out->addHTML( $output );

	}


}

?>