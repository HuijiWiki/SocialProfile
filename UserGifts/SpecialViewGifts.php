<?php
/**
 * Special:ViewGifts -- a special page for viewing the list of user-to-user
 * gifts a given user has received
 *
 * @file
 * @ingroup Extensions
 */

class ViewGifts extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ViewGifts' );
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'users';
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgUploadPath, $wgUser;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$currentUser = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.usergifts.css' );

		$user_name = $request->getVal( 'user' );
		$page = $request->getInt( 'page', 1 );
		$output = '';

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the ViewGifts page
		 */
		$login = SpecialPage::getTitleFor( 'Userlogin' );
	    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
		    $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=singup' ).'">创建用户</a>。';
		    $out->addHTML( $output );
		    return false;
	    }

		/**
		 * If no user is set in the URL, we assume it's the current user
		 */
		if ( !$user_name ) {
			$user_name = $currentUser->getName();
		}
		$user_id = User::idFromName( $user_name );
		$user = Title::makeTitle( NS_USER, $user_name );

		/**
		 * Error message for username that does not exist (from URL)
		 */
		if ( $user_id == 0 ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-no-user' )->plain() );
			return false;
		}

		/**
		 * Config for the page
		 */
		$per_page = 5;
		$per_row = 2;

		/**
		 * Get all gifts for this user into the array
		 */
		$rel = new UserGifts( $user_name );

		// $gifts = $rel->getUserGiftList( 0, $per_page, $page );
		$gifts = $rel->getUserGiftList( 0 );
		$total = $rel->getGiftCountByUsername( $user_name );

		/**
		 * Show gift count for user
		 */
		$out->setPageTitle( $this->msg( 'g-list-title', $rel->user_name )->parse() );

		$output .= '<div class="back-links">
			<a href="' . $user->getFullURL() . '">' .
				$this->msg( 'g-back-link', $rel->user_name )->parse() .
			'</a>
		</div>
		<div class="g-count">' .
			$this->msg( 'g-count', $rel->user_name, $total )->parse() .
		'</div>';
// print_r($gifts);die();
		if ( $gifts ) {
			$x = 1;
			foreach ($gifts as $value) {
				$gift_name[] = $value['gift_name'];
			}
			// count every gift total number
			$gift_count = array_count_values($gift_name);
			// del the repeat gift from list
			$repeat = array();
			foreach ($gifts as $key => $value) {
				if(isset($repeat[$value['gift_name']])){
		            unset($gifts[$key]);
		        }else{
		            $repeat[$value['gift_name']] = $value['gift_name'];
		        }
				$repeat[] = $value['gift_name'];
			}
			$star = $per_page*($page-1);
			$res_arr = array_slice($gifts, $star, $per_page);
			// Safe links
			$viewGiftLink = SpecialPage::getTitleFor( 'ViewGift' );
			$giveGiftLink = SpecialPage::getTitleFor( 'GiveGift' );
			$removeGiftLink = SpecialPage::getTitleFor( 'RemoveGift' );
			foreach ( $res_arr as $gift ) {
				$giftname_length = strlen( $gift['gift_name'] );
				$giftname_space = stripos( $gift['gift_name'], ' ' );

				if ( ( $giftname_space == false || $giftname_space >= "30" ) && $giftname_length > 30 ) {
					$gift_name_display = substr( $gift['gift_name'], 0, 30 ) .
						' ' . substr( $gift['gift_name'], 30, 50 );
				} else {
					$gift_name_display = $gift['gift_name'];
				}

				$user_from = Title::makeTitle( NS_USER, $gift['user_name_from'] );
				$gift_count_str = ($gift_count[$gift['gift_name']]>1)?'×'.$gift_count[$gift['gift_name']]:'';
				$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
					Gifts::getGiftImage( $gift['gift_id'], 'l' ) .
					'" border="0" alt="" />';
				$output .= '<div class="g-item">
					<a data-toggle="popover" data-trigger="hover" data-original-title='.str_replace(' ', '', $gift_name_display)."（来自".str_replace(' ', '', $gift['user_name_from'])."）".' data-content="'.$gift['gift_description'].'" href="' . htmlspecialchars( $viewGiftLink->getFullURL( 'gift_id=' . $gift['gift_id'] .'&user='.$user_name ) ) . '">' .
						$gift_image .
					'<span class="gift-count-num">'.$gift_count_str.'</span></a>
					<div class="g-title">';
				
				if ( $gift['status'] == 1 ) {
					if ( $user_name == $currentUser->getName() ) {
						$rel->clearUserGiftStatus( $gift['id'] );
						$rel->decNewGiftCount( $currentUser->getID() );
					}
					$output .= '<span class="g-new">' .
						$this->msg( 'g-new' )->plain() .
					'</span>';
				}
				$output .= '</div>';
				$output .= '
					<div class="g-actions">';
				if ( $rel->user_name == $currentUser->getName() ) {
					$output .= '&#160;';
					// $output .= $this->msg( 'pipe-separator' )->escaped();
					$output .= '&#160;';
					$output .= '<a href="' . htmlspecialchars( $removeGiftLink->getFullURL( 'gift_id=' . $gift['id'] ) ) . '">' .
						$this->msg( 'g-remove-gift' )->plain() . '</a>';
				}
				$output .= '</div>
					<div class="cleared"></div>';
				$output .= '</div>';


				$x++;
			}
		}

		/**
		 * Build next/prev nav
		 */
		$pcount = count($gifts);
		$numofpages = $pcount / $per_page;

		$pageLink = $this->getPageTitle();

		if ( $numofpages > 1 ) {
			$output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';
			if ( $page > 1 ) {
				$output .= '<li>'.Linker::link(
					$pageLink,
					'<span aria-hidden="true">&laquo;</span>',
					array(),
					array(
						'user' => $user_name,
						'page' => ( $page - 1 )
					)
				) . '</li>';
			}

			if ( ( $pcount % $per_page ) != 0 ) {
				$numofpages++;
			}
			if ( $numofpages >= 9 && $page < $pcount ) {
				$numofpages = 9 + $page;
			}
			if ( $numofpages >= ( $pcount / $per_page ) ) {
				$numofpages = ( $pcount / $per_page ) + 1;
			}

			for ( $i = 1; $i <= $numofpages; $i++ ) {
				if ( $i == $page ) {
					$output .= ( '<li class="active"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>' );
				} else {
					$output .= '<li>' .Linker::link(
						$pageLink,
						$i,
						array(),
						array(
							'user' => $user_name,
							'page' => $i
						)
					) .'</li>';
				}
			}

			if ( ( $pcount - ( $per_page * $page ) ) > 0 ) {
				$output .= '<li>' .
					Linker::link(
						$pageLink,
						'<span aria-hidden="true">&raquo;</span>',
						array(),
						array(
							'user' => $user_name,
							'page' => ( $page + 1 )
						)
					).'</li>';
			}
			$output .= '</nav></div>';
		}

		$out->addHTML( $output );
	}
}
