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
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.systemgifts.css' );

		$output = '';
		$page = $request->getInt( 'page', 1 );
		$user_name = $wgUser->getName();

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the ViewSystemGifts page
		 */
		$login = SpecialPage::getTitleFor( 'Userlogin' );
	    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
	      $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
	      $out->addHTML( $output );
	      return false;
	    }

		/**
		 * If no user is set in the URL, we assume it's the current user
		 */
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
		$per_page = 50;
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
		$output .= '<div class="back-links">' .
			$this->msg(
				'ga-back-link',
				htmlspecialchars( $wgUser->getUserPage()->getFullURL() ),
				$rel->user_name
			)->text() . '</div>';
		$output .= '<div class="ga-count">' .
			$this->msg( 'ga-count', '我', $total )->parse() .
		', 在'.$who.'的好友中排第<span style="color:#428bca;font-size:20px;font-weight: bold;">'.$countRes[$wgUser->getName()].'</span>名</div><div class="giftlist">';

		// Safelinks
		// print_r($gifts);
		
		// print_r($countRes);
		if ( $gifts ) {
			foreach ( $gifts as $gift ) {
				$gift_image = "<div class='img'><img src=\"{$wgUploadPath}/awards/" .
					SystemGifts::getGiftImage( $gift['id'], 'l' ) .
					'" border="0" alt="" /></div>';
					$sg = new SystemGifts();
                if ( $sg->doesUserHaveGift( $user_id, $gift['id'] ) ) {
                				$s = 'ga-item have';
                				}else{
                				$s= 'ga-item';
                				}
				$output .= "<div class='".$s."'>
				    <a data-toggle='popover' data-trigger='hover' title='{$gift['gift_name']}' data-content='{$gift['gift_description']}'>
                    {$gift_image}";


				$output .= '<div class="cleared"></div>
				</a></div>';
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
			$output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';

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
			if ( $numofpages >= 9 && $page < $pcount ) {
				$numofpages = 9 + $page;
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

			$output .= '</nav></div>';
		}
        
		/**
		 * Output everything
		 */
		$out->addHTML( $output );
	}
}
