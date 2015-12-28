<?php

/**
*  special page to add new festival gift
*/
class SpecialAddFestivalGift extends SpecialPage{
	
	function __construct(){

		parent::__construct( 'AddFestivalGift' );
	
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
	public function execute($params){
		global $wgUser;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$this->setHeaders();
		/**
		 * only staff can operate this special page
		 */
		// if ( !$wgUser->isAllowed( 'AddFestivalGift' ) ) {
		// 	$out->permissionRequired( 'AddFestivalGift' );
		// 	return;
		// }
		$output = "";
		$output .= "<form method='get' action='/wiki/special:addfestivalgift' >
			成就ID：<input type='text' name='giftId' >
			达成次数：<input type='text' name='editnum' >
			开始时间：<input type='date' name='starttime' >
			结束时间：<input type='date' name='endtime' >
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='添加'>
			</form>";
		$giftId = $request->getVal('giftId');
		$editNum = $request->getVal('editnum');
		$startTime = $request->getVal('starttime');
		$endTime = $request->getVal('endtime');
		$giftList = SystemGifts::getInfoFromFestivalGift();
		$i = 0;
		if ($giftList != null) {
			foreach ($giftList as $value) {
				if ($i<5) {
					$giftimg = SystemGifts::getGiftImage( $value['giftId'], 'ml');
					$output .= "<img src=/uploads/awards/".$giftimg."> 开始时间:".$value['startTime']."--结束时间:".$value['endTime']."--达到次数:".$value['editNum']."<br>";
				}
				$i++;
			}
		}
		if ( $giftId != null && $editNum != null && $startTime != null && $endTime != null ) {
			$result = SystemGifts::addFestivalGift( $giftId, $editNum, $startTime, $endTime );
			if( $result !== false ){
				$output .= "<h1>success</h1>";
			}else{
				$output .= "<h1>error</h1>";
			}
		}
		$out->addHTML( $output );
	}


}

?>