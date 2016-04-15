<?php

/**
*  special page to send system gift
*/
class SpecialGiveSystemGift extends SpecialPage{
	
	function __construct(){

		parent::__construct( 'GiveSystemGift' );
	
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
		if ( !$wgUser->isAllowed( 'GiveSystemGift' ) ) {
			$out->permissionRequired( 'GiveSystemGift' );
			return;
		}

		$desigGiftId = empty($request->getInt( 'designation' ))?null:$request->getInt( 'designation' );
		$condition = empty($request->getInt('condition'))?null:$request->getInt('condition');
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
						<select name='condition'>
							<option value='3' >编辑次数不少于1次</option>
							<option value='2' >编辑次数不少于500次</option>
						</select>
						<input class='mw-ui-button mw-ui-progressive' type='submit' value='发送'>
						</form>";
		}else {
			$output .= '<h2>暂无成就称号</h2>';
		}
		
		if ( $desigGiftId != null && is_int($desigGiftId) ) {
			$wikiSite = WikiSite::newFromPrefix($wgHuijiPrefix);
			$follower = $wikiSite->getFollowers(true);
			$i = 0;
			if ( $condition == 2 ) {
				foreach ($follower as $key => $value) {
					if ( $value['count'] >= 500 ) {
						$usg = new UserSystemGifts( $value['user'] );
						$usg->sendSystemGift( $desigGiftId );
						$i++;
					}
				}
			}elseif ( $condition == 3 ) {
				foreach ($follower as $key => $value) {
					if ( $value['count'] >= 1 ) {
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