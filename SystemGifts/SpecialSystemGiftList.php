<?php
/**
 * A special page to view the list of system gifts.
 *
 * @file
 * @ingroup Extensions
 */

class SystemGiftList extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'SystemGiftList' );
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
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.systemgifts.css' );

		$output = '';
		$page = $request->getInt( 'page', 1 );

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the ViewSystemGifts page
		 */
		if ( $user->getID() == 0 && $user_name == '' ) {
			$out->setPageTitle( $this->msg( 'ga-error-title' )->plain() );
			$login = SpecialPage::getTitleFor( 'Userlogin' );
			$out->redirect( htmlspecialchars( $login->getFullURL( 'returnto=Special:SystemGiftList' ) ) );
			return false;
		}

		/**
		 * If no user is set in the URL, we assume it's the current user
		 */
		$user_name = $wgUser->getName();
		$user_id = User::idFromName( $user_name );

		/**
		 * Error message for username that does not exist (from URL)
		 */
		if ( $user_id == 0 ) {
			$out->setPageTitle( $this->msg( 'ga-error-title' )->plain() );
			$out->addHTML( $this->msg( 'ga-error-message-no-user' )->plain() );
			return false;
		}

		/**
		* Config for the page
		*/
		$per_page = 10;
		$per_row = 2;

		/**
		 * Get all Gifts for this user into the array
		 */
		$rel = new UserSystemGifts( $user_name );
		// $gifts = SystemGifts::getGiftList( $per_page, $page );
		$gifts = SystemGifts::getGiftList( $per_page, $page );
		// print_r($gifts);
		$total = '<span style="color:#428bca;font-size:20px;font-weight: bold;">'.$rel->getGiftCountByUsername( $user_name ).'</span>';
		// $curUserObj = User::newFromName($user_name);
		$uuf = new UserUserFollow();
		$follows = $uuf->getFollowList( $wgUser, 1, '', $page);
		$follows[] = array('user_name'=>$wgUser->getName());
		$giftCount = array();
		foreach ($follows as $value) {
			$giftCount[$value['user_name']] = $rel->getGiftCountByUsername( $value['user_name'] );
		}
		arsort($giftCount);
		$max = count($giftCount);
		$countRes = array();
		$i=1;
		foreach ($giftCount as $key => $value) {
			$countRes[$key] = $i;
			$i++;
		}
		if ( $wgUser->getName() == $wgUser->getName() ) {
			$who = '我';
		}else{
			$who = $wgUser->getName();
		}
		// print_r($countRes);
		/**
		 * Show gift count for user
		 */
		$out->setPageTitle( $this->msg( 'gl-title' )->parse() );
		$output .= '<div class="giftlist"><div class="back-links">' .
			$this->msg(
				'ga-back-link',
				htmlspecialchars( $wgUser->getUserPage()->getFullURL() ),
				$rel->user_name
			)->text() . '</div>';
		$output .= '<div class="ga-count">' .
			$this->msg( 'ga-count', '我', $total )->parse() .
		', 在'.$who.'的好友中排第<span style="color:#428bca;font-size:20px;font-weight: bold;">'.$countRes[$wgUser->getName()].'</span>名</div>';

		// Safelinks
		$view_system_gift_link = SpecialPage::getTitleFor( 'ViewSystemGift' );
		// print_r($gifts);
		
		// print_r($countRes);
		if ( $gifts ) {
			foreach ( $gifts as $gift ) {
				$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
					SystemGifts::getGiftImage( $gift['id'], 'ml' ) .
					'" border="0" alt="" />';

				$output .= "<div class=\"ga-item\">
					{$gift_image}
					<a href=\"" .
						htmlspecialchars( $view_system_gift_link->getFullURL( 'gift_id=' . $gift['id'] ) ) .
						"\">{$gift['gift_name']}</a>";
				$sg = new SystemGifts();
				if ( $sg->doesUserHaveGift( $user_id, $gift['id'] ) ) {
					$output .= '&nbsp<span class="label label-success">you got it</span>';
				}
				$output .= '<div class="cleared"></div>
				</div>';
			}
			$output .= '</div>';
		}

		/**
		 * Build next/prev nav
		 */
		$pcount = systemGifts::getGiftCount();
		$numofpages = $pcount / $per_page;
		// echo $total;
		$page_link = $this->getPageTitle();

		if ( $numofpages > 1 ) {
			$output .= '<nav class="page-nav pagination">';

			if ( $page > 1 ) {
				$output .= '<li>'.Linker::link(
					$page_link,
					'<span aria-hidden="true">&laquo;</span>',
					array(),
					array(
						// 'rel_type' => $rel_type,
						'page' => ( $page - 1 )
					)
				) . '</li>';
			}

			if ( ( $pcount % $per_page ) != 0 ) {
				$numofpages++;
			}
			if ( $numofpages >= 50 && $page < $pcount ) {
				$numofpages = 50 + $page;
			}
			// if ( $numofpages >= ( $pcount / $per_page ) ) {
			// 	$numofpages = ( $pcount / $per_page ) + 1;
			// }

			for ( $i = 1; $i <= $numofpages; $i++ ) {
				if ( $i == $page ) {
					$output .= ( '<li class="active"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>' );
				} else {
					$output .= '<li>' .Linker::link(
						$page_link,
						$i,
						array(),
						array(
							'page' => $i
						)
					);
				}
			}

			if ( ( $pcount - ( $per_page * $page ) ) > 0 ) {
				$output .= '<li>' .
					Linker::link(
						$page_link,
						'<span aria-hidden="true">&raquo;</span>',
						array(),
						array(
							// 'rel_type' => $rel_type,
							'page' => ( $page + 1 )
						)
					).'</li>';	
			}

			$output .= '</nav>';
		}
        
		/**
		 * Output everything
		 */
		$out->addHTML( $output );
	}
}
