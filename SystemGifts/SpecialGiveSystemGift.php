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
		$this->checkPermissions();
		$this->checkReadonly();
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
			$output .= "<form method='get' action='/wiki/special:givesystemgift'><div class='form-group'><label for='designation'>称号：</label><select name=\"designation\" class='form-control'>";
			foreach ($giftList as $key => $value) {
				$output .= '<option value ="'.$value['gift_id'].'">'.$value['designation'].'</option>';
			}
			$output .= "</select></div>";
			$output .= "<div class='form-group'><label for='editNum'>获得称号所需的编辑次数：</label>
						<input name='editNum' class='form-control'></div>
						<input class='mw-ui-button mw-ui-progressive' type='submit' value='发送'>
						</form>";
		}else {
			$output .= '<p class="empty-message">请先在礼物管理器中创建一个带有称号的礼物</p>';
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