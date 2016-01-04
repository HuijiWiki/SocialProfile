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
		if ( !$wgUser->isAllowed( 'AddFestivalGift' ) ) {
			$out->permissionRequired( 'AddFestivalGift' );
			return;
		}
		$output = "";
		$output .= "<form method='post' action='/wiki/special:addfestivalgift?method=add' >
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
		$method = $request->getVal('method');
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
		if ( $method == 'add' ) {
			if ( is_numeric($giftId) && is_numeric($editNum) ) {
				$result = SystemGifts::addFestivalGift( $giftId, $editNum, $startTime, $endTime );
				if( $result !== false && $result !== null ){
					$output .= "<script>alert('success');location.reload();</script>";
				}elseif(  $giftId == null || $editNum == null || $startTime == null || $endTime == null ){
					$output .= "<h1>填写不完整</h1>";
				}
			}else{
				$output .= "<h1>输入有误</h1>";
			}
			
		}
		$out->addHTML( $output );
	}


}

?>