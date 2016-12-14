<?php

/**
*  special page to add new festival gift
*/
class SpecialAddFestivalGift extends SpecialPage{
	
	function __construct(){

		parent::__construct( 'AddFestivalGift', 'giftadmin' );
	
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
		$this->checkPermissions();
		$this->checkReadonly();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$this->setHeaders();
		/**
		 * only staff can operate this special page
		 */
		$output = "";
		$output .= "<form method='post' action='/wiki/special:addfestivalgift?method=add' >
			<div class=\"form-group\">
			<label for='giftId'>成就ID：</label><input type='text' name='giftId' class='form-control' >
			</div><div class=\"form-group\">
			<label for='editnum'>所需编辑次数：</label><input type='text' name='editnum' class='form-control'>
			</div><div class=\"form-group\">
			<label for='starttime'>开始时间：</label><input type='date' name='starttime' class='form-control'>
			</div><div class=\"form-group\">
			<label for='endtime'>结束时间：</label><input type='date' name='endtime' class='form-control'>
			</div>
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
					$giftimg = SystemGifts::getGiftImageTag( $value['giftId'], 'ml');
					$output .= $giftimg." 开始时间:".$value['startTime']."--结束时间:".$value['endTime']."--达到次数:".$value['editNum']."<br>";
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