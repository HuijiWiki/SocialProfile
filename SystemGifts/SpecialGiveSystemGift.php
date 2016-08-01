<?php

/**
*  special page to send system gift
*/
class SpecialGiveSystemGift extends SpecialPage{
	
	function __construct(){

		parent::__construct( 'GiveSystemGift', 'giftadmin' );
	
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
		global $wgUser, $wgHuijiPrefix;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$this->setHeaders();
		/**
		 * only staff can operate this special page
		 */

		$desigGiftId = empty($request->getInt( 'designation' ))?null:$request->getInt( 'designation' );
		// $condition = empty($request->getInt('condition'))?null:$request->getInt('condition');
		$editNum = empty($request->getInt('editNum'))?null:$request->getInt('editNum');
		$output = "";

		$login = SpecialPage::getTitleFor( 'Userlogin' );
	    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
	      $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
	      $out->addHTML( $output );
	      return false;
	    }
	    
		$giftList = UserSystemGifts::getDesignationGiftList();
		if ( count($giftList) > 0 ) {
			$output .= "<form method='get' action='/wiki/special:givesystemgift' >称号：<select name=\"designation\">";
			foreach ($giftList as $key => $value) {
				$output .= '<option value ="'.$value['gift_id'].'">'.$value['designation'].'</option>';
			}
			$output .= "</select>";
			$output .= "达成条件：
						<input name='editNum' >
						<input class='mw-ui-button mw-ui-progressive' type='submit' value='发送'>
						</form>";
		}else {
			$output .= '<h2>暂无成就称号</h2>';
		}
		
		if ( $desigGiftId != null && is_int($desigGiftId) ) {
			$wikiSite = WikiSite::newFromPrefix($wgHuijiPrefix);
			$follower = $wikiSite->getFollowers(true);
			$i = 0;
			if ( $editNum != null && is_numeric($editNum) ) {
				foreach ($follower as $key => $value) {
					if ( $value['count'] >= $editNum ) {
						$usg = new UserSystemGifts( $value['user'] );
						$usg->sendSystemGift( $desigGiftId );
						$i++;
					}
				}
			}
			$output .= '<span>共'.$i.'个人获得了此成就~</span>';
		}
		$out->addHTML( $output );
	}


}

?>