<?php
/**
 * Special page for creating and editing user-to-user gifts.
 *
 * @file
 */
class SpecialDesignation extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'Designation' );
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
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgUser, $wgUploadPath, $wgHuijiPrefix;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		$out->setPageTitle( $this->msg( 'designation' )->plain() );
		$output = '';

	    $login = SpecialPage::getTitleFor( 'Userlogin' );
	    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
	      $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
	      $out->addHTML( $output );
	      return false;
	    }

		// Show a message if the database is in read-only mode
		if ( wfReadOnly() ) {
			$out->readOnlyPage();
			return;
		}

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.usergifts.css' );
		$out->addModuleStyles( 'ext.socialprofile.usergifts.css' );
		$out->addModules( 'ext.socialprofile.designation.js');

		$HuijiUser = HuijiUser::newFromId($wgUser->getId());
		$giftList = $HuijiUser->getUserDesignation( 'gift', 0 );
		$systemGiftList = $HuijiUser->getUserDesignation( 'system_gift', 0 );


		// $wikiSite = WikiSite::newFromPrefix($wgHuijiPrefix);
		// $follower = $wikiSite->getFollowers(true);
		// print_r($follower);die();
		// print_r($systemGiftList);die();
		$output .= '<div id="gift-list" class="list-wrap"><div class="list-title">称号</div>';
		if ( count($giftList) > 0 ) {
			foreach ($giftList as $key => $value) {
				$gifts = UserGifts::getUserGift( $wgUser->getName(), $value['gift_id'], 1 );
				$description = empty($gifts[0]['description']) ? '<small>(暂无描述)</small>' : $gifts[0]['description'];
				$giftImage = '<img src="' . $wgUploadPath . '/awards/' . Gifts::getGiftImage( $value['gift_id'], 'l' ) .'" border="0" alt="" />';
				$output .= '<div class="admin-setting-li">
						'.$giftImage.'
				        <div class="setting-title" title="'.$value['title_content'].'">称号：'.$value['title_content'].'</div>
				        <p class="setting-des" title="'.$description.'">描述：'.$description.'</p>
				        <div class="setting-toggle">';
				if ( $value['is_open'] == 1 ) {
					$open = 'false';
				}elseif ( $value['is_open'] == 2 ) {
					$open = 'true';
				}
				$output .= '<span class="toggle" data-value="'.$open.'" data-state="false"></span>
				       		<input class="gift-title-id" type="hidden" value="'.$value['ut_id'].'">
				       		<input class="gift-title-from" type="hidden" value="gift">
				        </div>
				    </div>';
			}
		}else{
			$output .= '<span>暂无</span>';
		}
		$output .='</div>';
		
		$output .= '<div id="system-list" class="list-wrap"><div class="list-title">系统称号</div>';
		if ( count($systemGiftList) > 0 ) {
			foreach ($systemGiftList as $key => $value) {
				$gifts = UserSystemGifts::getUserGift( $value['gift_id'], $wgUser->getName() );
				$description = empty($gifts[0]['description']) ? '<small>(暂无描述)</small>' : $gifts[0]['description'];
				$giftImage = '<img src="' . $wgUploadPath . '/awards/' . SystemGifts::getGiftImage( $value['gift_id'], 'l' ) .'" border="0" alt="" />';
				$output .= '<div class="admin-setting-li">
						'.$giftImage.'
				        <div class="setting-title" title="'.$value['title_content'].'">称号：'.$value['title_content'].'</div>
				        <p class="setting-des" title="'.$description.'">描述：'.$description.'</p>
				        <div class="setting-toggle">';
				if ( $value['is_open'] == 1 ) {
					$open = 'false';
				}elseif ( $value['is_open'] == 2 ) {
					$open = 'true';
				}
				$output .= '<span class="toggle" data-value="'.$open.'" data-state="false"></span>
				       		<input class="gift-title-id" type="hidden" value="'.$value['ut_id'].'">
				       		<input class="gift-title-from" type="hidden" value="system_gift">
				        </div>
				    </div>';
			}
		}else{
			$output .= '<span>暂无</span>';
		}
		$output .= '</div>';
		$out->addHTML( $output );
	}
		
}
