<?php
/**
 * A special page to view the list of system gifts (awards) a user has.
 *
 * @file
 * @ingroup Extensions
 */

class ViewSystemGifts extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ViewSystemGifts' );
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
		$user_name = $request->getVal( 'user' );
		if ($user_name) {
			$user = User::newFromName( $user_name );
		}
		$page = $request->getInt( 'page', 1 );

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the ViewSystemGifts page
		 */
		if ( $user->getID() == 0 && $user_name == '' ) {
			$out->setPageTitle( $this->msg( 'ga-error-title' )->plain() );
			$login = SpecialPage::getTitleFor( 'Userlogin' );
			$out->redirect( htmlspecialchars( $login->getFullURL( 'returnto=Special:ViewSystemGifts' ) ) );
			return false;
		}

		/**
		 * If no user is set in the URL, we assume it's the current user
		 */
		if ( !$user_name ) {
			$user_name = $user->getName();
		}
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
		$per_page = 20;
		$per_row = 2;

		/**
		 * Get all Gifts for this user into the array
		 */
		$rel = new UserSystemGifts( $user_name );

		$gifts = $rel->getUserGiftList( 0, $per_page, $page );
		$total = '<span style="color:#428bca;font-size:20px;font-weight: bold;">'.$rel->getGiftCountByUsername( $user_name ).'</span>';
		$curUserObj = User::newFromName($user_name);
		$uuf = new UserUserFollow();
		$follows = $uuf->getFollowList( $curUserObj, 1, '', $page);
		$follows[] = array('user_name'=>$curUserObj->getName());
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
		if ( $curUserObj->getName() == $wgUser->getName() ) {
			$who = '我';
		}else{
			$who = $curUserObj->getName();
		}
		// print_r($countRes);
		/**
		 * Show gift count for user
		 */
		$allGiftList = '/wiki/'.SpecialPage::getTitleFor( 'SystemGiftList' );
		$out->setPageTitle( $this->msg( 'ga-title', $rel->user_name )->parse() );
		$output .= '<div class="back-links">' .
			$this->msg(
				'ga-back-link',
				htmlspecialchars( $user->getUserPage()->getFullURL() ),
				$rel->user_name
			)->text() . '</div>';
		$output .= '<div class="ga-count">' .
			$this->msg( 'ga-count', $rel->user_name, $total )->parse() .
		', 在'.$who.'的好友中排第<span style="color:#428bca;font-size:20px;font-weight: bold;">'.$countRes[$curUserObj->getName()].
		'</span>名</div>';
		$output .= '<div><a href="'.$allGiftList.'">查看所有奖励</a></div><div class="giftlist">';
		// Safelinks
		$view_system_gift_link = SpecialPage::getTitleFor( 'ViewSystemGift' );
		// print_r($gifts);
		
		// print_r($countRes);
		if ( $gifts ) {
			foreach ( $gifts as $gift ) {
				$gift_image = "<div class='img'><img src=\"{$wgUploadPath}/awards/" .
					SystemGifts::getGiftImage( $gift['gift_id'], 'ml' ) .
					'" border="0" alt="" /></div>';

				$output .= "<div class=\"ga-item have\">
					<a href=\"" .
                    htmlspecialchars( $view_system_gift_link->getFullURL( 'gift_id=' . $gift['id'] ) ) .
                    "\" data-toggle='popover' data-trigger='hover' title='{$gift['gift_name']}' data-content='{$gift['gift_description']}'>
                    {$gift_image}";

				if ( $gift['status'] == 1 ) {
					if ( $user_name == $user->getName() ) {
						$rel->clearUserGiftStatus( $gift['id'] );
						$rel->decNewSystemGiftCount( $user->getID() );
					}
					/*$output .= '&nbsp<span class="label label-success">' .
						$this->msg( 'ga-new' )->plain() . '</span>';*/
				}

				$output .= '<div class="cleared"></div>
				</a></div>';
			}
			$output .= '</div>';
		}

		/**
		 * Build next/prev nav
		 */
		$pcount = $rel->getGiftCountByUsername( $user_name );
		$numofpages = $pcount / $per_page;

		$page_link = $this->getPageTitle();

		if ( $numofpages > 1 ) {
			$output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';

			if ( $page > 1 ) {
				$output .= '<li>'.Linker::link(
					$page_link,
					'<span aria-hidden="true">&laquo;</span>',
					array(),
					array(
						'user' => $user_name,
						// 'rel_type' => $rel_type,
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
			// if ( $numofpages >= ( $total / $per_page ) ) {
			// 	$numofpages = ( $total / $per_page ) + 1;
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
							'user' => $user_name,
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
							'user' => $user_name,
							// 'rel_type' => $rel_type,
							'page' => ( $page + 1 )
						)
					).'</li>';	
			}

			$output .= '</nav></div>';
		}

		/**
		 * Output everything
		 */
		$out->addHTML( $output );
	}
	function getGroupName() {
    		return 'users';
	}
}
