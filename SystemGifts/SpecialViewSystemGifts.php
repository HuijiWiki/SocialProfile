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
		$login = SpecialPage::getTitleFor( 'Userlogin' );
	    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
	      $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
	      $out->addHTML( $output );
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

		// $gifts = $rel->getUserGiftList( 0, $per_page, $page );
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
		//get all gift user had got
		$all_gifts = $rel->getUserGiftList( 0, 0, 0 );
		if(!empty($all_gifts)){
			foreach ($all_gifts as $value) {
				$gift_name[] = $value['gift_name'];
			}
			// count every gift total number
			$gift_count = array_count_values($gift_name);
			// del the repeat gift from list
			$repeat = array();
			foreach ($all_gifts as $key => $value) {
				if(isset($repeat[$value['gift_name']])){
		            unset($all_gifts[$key]);
		        }else{
		            $repeat[$value['gift_name']] = $value['gift_name'];
		        }
				$repeat[] = $value['gift_name'];
			}
			$star = $per_page*($page-1);
			$res_arr = array_slice($all_gifts, $star, $per_page);
			foreach ( $res_arr as $key =>$gift ) {
				$gift_image = "<div class='img'><img src=\"{$wgUploadPath}/awards/" .
					SystemGifts::getGiftImage( $gift['gift_id'], 'l' ) .
					'" border="0" alt="" /></div>';

				$output .= "<div class=\"ga-item have\">
					<a href=\"" .
                    htmlspecialchars( $view_system_gift_link->getFullURL( 'user='.$gift['user_name'] .'&gift_id=' . $gift['gift_id'] ) ) .
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
				$gift_count_str = ($gift_count[$gift['gift_name']]>1)?'×'.$gift_count[$gift['gift_name']]:'';
				$output .= '<div class="cleared"></div>
				</a><span class="gift-count-num">'.$gift_count_str.'</span></div>';
			}
			$output .= '</div>';
		}else{
			$output .= "<br><div>你暂时还没收到奖励哟~</div>";
		}

		/**
		 * Build next/prev nav
		 */
		// $pcount = $rel->getGiftCountByUsername( $user_name );
		$pcount = count($all_gifts);
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
					).'</li>';
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
