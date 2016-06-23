<?php
/**
 * Special:GiveGift -- a special page for sending out user-to-user gifts
 *
 * @file
 * @ingroup Extensions
 */

class GiveGift extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'GiveGift' );
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
		global $wgMemc, $wgUploadPath;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		$output = ''; // Prevent E_NOTICE

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS & JS
		$out->addModuleStyles( 'ext.socialprofile.usergifts.css' );
		$out->addModules( 'ext.socialprofile.usergifts.js' );

		$userTitle = Title::newFromDBkey( $request->getVal( 'user' ) );
		if ( !$userTitle ) {
			$out->addHTML( $this->displayFormNoUser() );
			return false;
		}

		$user_title = Title::makeTitle( NS_USER, $request->getVal( 'user' ) );
		$this->user_name_to = $userTitle->getText();
		$this->user_id_to = User::idFromName( $this->user_name_to );
		$giftId = $request->getInt( 'gift_id' );

		if ( $user->getID() === $this->user_id_to ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-to-yourself' )->plain() );
		} elseif ( $user->isBlocked() ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-blocked' )->plain() );
		} elseif ( $this->user_id_to == 0 ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-no-user' )->plain() );
		} elseif ( $user->getID() == 0 ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-login' )->plain() );
		} else {
			$gift = new UserGifts( $user->getName() );
			$giftInfo = Gifts::getGift( $request->getInt( 'gift_id' ) );
			if ( $giftId > 0 && (!$giftInfo['repeat']) && $gift->doesUserOwnGift($user->getID(), $giftId) ){
				$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
				$out->addHTML( $this->msg( 'g-error-already-owned-gift' )->plain() );				
			}  elseif ( $giftId > 0 && !Gifts::isAllowedToSendGift( $user->getID(), $giftId ) ){
				$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
				$out->addHTML( $this->msg( 'g-error-not-previleged-to-send-gift' )->plain() );					
			} else{
			
				if ( $request->wasPosted() && $_SESSION['alreadysubmitted'] == false ) {
					$_SESSION['alreadysubmitted'] = true;

					if ( HuijiFunctions::addLock('UG-'.$request->getInt( 'gift_id' ).$this->user_id_to)){

						$ug_gift_id = $gift->sendGift(
							$this->user_name_to,
							$request->getInt( 'gift_id' ),
							0,
							$request->getVal( 'message' )
						);
						//invitationcode
						if ( $giftInfo['gift_type'] == 3 && $ug_gift_id != null ) {
							//give system_gift $this->user_name_to $user->getName()
							
							$res = $gift->addUserGiftInviteInfo( $ug_gift_id );
							// if ($res != 0 ) {
								// $usg1 = new UserSystemGifts( $user->getName() );
								// $usg1->sendSystemGift( 91 );
								// $usg2 = new UserSystemGifts( $this->user_name_to );
								// $usg2->sendSystemGift( 92 );
							// }
						}
						//customcode
						if ( $giftInfo['gift_type'] == 4 && $ug_gift_id != null ) {
							//give system_gift $this->user_name_to $user->getName()
							
							$res = $gift->addCustomInvitationCode( $ug_gift_id , "MaskedShooter");
							// if ($res != 0 ) {
								// $usg1 = new UserSystemGifts( $user->getName() );
								// $usg1->sendSystemGift( 91 );
								// $usg2 = new UserSystemGifts( $this->user_name_to );
								// $usg2->sendSystemGift( 92 );
							// }
						}						
						//user title
						if ( $giftInfo['designation'] != null && $this->user_name_to != null ) {
							$gift->addUserGiftTitleInfo( $giftInfo['gift_id'], $this->user_id_to, $giftInfo['designation'], 'gift' );
						}
			
						// clear the cache for the user profile gifts for this user
						$wgMemc->delete( wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'gifts', $this->user_id_to ) );
			
						$key = wfForeignMemcKey( 'huiji', '', 'gifts', 'unique', 4 );
						$data = $wgMemc->get( $key );
			
						// check to see if this type of gift is in the unique list
						$lastUniqueGifts = $data;
						$found = 1;
			
						if ( is_array( $lastUniqueGifts ) ) {
							foreach ( $lastUniqueGifts as $lastUniqueGift ) {
								if ( $request->getInt( 'gift_id' ) == $lastUniqueGift['gift_id'] ) {
									$found = 0;
								}
							}
						}
			
						if ( $found ) {
							// add new unique to array
							$lastUniqueGifts[] = array(
								'id' => $ug_gift_id,
								'gift_id' => $request->getInt( 'gift_id' )
							);
			
							// remove oldest value
							if ( count( $lastUniqueGifts ) > 4 ) {
								array_shift( $lastUniqueGifts );
							}
			
							// reset the cache
							$wgMemc->set( $key, $lastUniqueGifts );
						}
						$numg = 0;
						$sent_gift = UserGifts::getUserGift( $this->user_name_to,$request->getInt( 'gift_id' ) , $numg );
						if ($sent_gift) {
							$gift_image = 
								Gifts::getGiftImageTag( $sent_gift[0]['gift_id'], 'l' );
			
							$out->setPageTitle( $this->msg( 'g-sent-title', $this->user_name_to )->parse() );
			
							$output .= '<div class="back-links">
								<a href="' . htmlspecialchars( $user_title->getFullURL() ) . '">' .
									$this->msg( 'g-back-link', $this->user_name_to )->parse() .
								'</a>
							</div>
							<div class="g-message">' .
								$this->msg( 'g-sent-message', $this->user_name_to )->parse() .
							'</div>
							<div class="g-container">' .
								$gift_image .
							'<div class="g-title">' . $sent_gift[0]['name'] . '</div>';
							if ( $sent_gift[0]['message'] ) {
								$output .= '<div class="g-user-message">' .
									$sent_gift[0]['message'] .
								'</div>';
							}
							$output .= '</div>
							<div class="cleared"></div>
							<div class="g-buttons">
								<input type="button" class="site-button" value="' . $this->msg( 'g-main-page' )->plain() . '" size="20" onclick="window.location=\'index.php?title=' . $this->msg( 'mainpage' )->inContentLanguage()->escaped() . '\'" />
								<input type="button" class="site-button" value="' . $this->msg( 'g-your-profile' )->plain() . '" size="20" onclick="window.location=\'' . htmlspecialchars( $user->getUserPage()->getFullURL() ) . '\'" />
							</div>';
						}
						
			
						$out->addHTML( $output );
						HuijiFunctions::releaseLock('UG-'.$request->getInt( 'gift_id' ).$this->user_id_to);
					}
					else{
						$_SESSION['alreadysubmitted'] = false;
						$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
						$out->addHTML( $this->msg( 'g-error-system-busy' )->plain() );					
					}
				} else {
					
					$_SESSION['alreadysubmitted'] = false;
	
					if ( $giftId ) {
						$out->addHTML( $this->displayFormSingle() );
					} else {
						$out->addHTML( $this->displayFormAll() );
					}
				}
			}
		}
	}

	/**
	 * Display the form for sending out a single gift.
	 * Relies on the gift_id URL parameter and bails out if it's not there.
	 *
	 * @return String: HTML
	 */
	function displayFormSingle() {
		global $wgUploadPath;

		$out = $this->getOutput();

		$giftId = $this->getRequest()->getInt( 'gift_id' );

		if ( !$giftId || !is_numeric( $giftId ) ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-invalid-link' )->plain() );
			return false;
		}

		$gift = Gifts::getGift( $giftId );

		if ( empty( $gift ) ) {
			return false;
		}

		if ( $gift['group'] == 1 && $this->getUser()->getID() != $gift['creator_user_id'] ) {
			return $this->displayFormAll();
		}

		// Safe titles
		$user = Title::makeTitle( NS_USER, $this->user_name_to );
		$giveGiftLink = SpecialPage::getTitleFor( 'GiveGift' );

		$out->setPageTitle( $this->msg( 'g-give-to-user-title', $gift['gift_name'], $this->user_name_to )->parse() );

		$gift_image = 
			Gifts::getGiftImageTag( $gift['gift_id'], 'l' );

		$output = '<form action="" method="post" enctype="multipart/form-data" name="gift">
			<div class="g-message">' .
				$this->msg(
					'g-give-to-user-message',
					$this->user_name_to,
					htmlspecialchars( $giveGiftLink->getFullURL( 'user=' . $this->user_name_to ) )
				)->text() . "</div>
			<div id=\"give_gift_{$gift['gift_id']}\" class=\"g-container\">
				{$gift_image}
				<div class=\"g-title\">{$gift['gift_name']}</div>";
		if ( $gift['gift_description'] ) {
			$output .= '<div class="g-describe">' .
				$gift['gift_description'] .
			'</div>';
		}
		$output .= '</div>
			<div class="cleared"></div>
			<div class="g-add-message">' . $this->msg( 'g-add-message' )->plain() . '</div>
			<textarea name="message" id="message" rows="4" cols="50"></textarea>
			<div class="g-buttons">
				<input type="hidden" name="gift_id" value="' . $giftId . '" />
				<input type="hidden" name="user_name" value="' . addslashes( $this->user_name_to ) . '" />
				<input type="button" class="site-button" value="' . $this->msg( 'g-send-gift' )->plain() . '" size="20" onclick="document.gift.submit()" />
				<input type="button" class="site-button" value="' . $this->msg( 'g-cancel' )->plain() . '" size="20" onclick="history.go(-1)" />
			</div>
		</form>';

		return $output;
	}

	/**
	 * Display the form for giving out a gift to a user when there was no user
	 * parameter in the URL.
	 *
	 * @return String: HTML
	 */
	function displayFormNoUser() {
		global $wgFriendingEnabled;

		$this->getOutput()->setPageTitle( $this->msg( 'g-give-no-user-title' )->plain() );

		$output = '<form action="" method="get" enctype="multipart/form-data" name="gift">' .
			Html::hidden( 'title', $this->getPageTitle() ) .
			'<div class="g-message">' .
				$this->msg( 'g-give-no-user-message' )->plain() .
			'</div>
			<div class="g-give-container">';

			// If friending is enabled, build a dropdown menu of the user's
			// friends
			if ( $wgFriendingEnabled ) {
				$rel = new UserRelationship( $this->getUser()->getName() );
				$friends = $rel->getRelationshipList( 1 );

				if ( $friends ) {
					$output .= '<div class="g-give-title">' .
						$this->msg( 'g-give-list-friends-title' )->plain() .
					'</div>
					<div class="g-gift-select">
						<select>
							<option value="#" selected="selected">' .
								$this->msg( 'g-select-a-friend' )->plain() .
							'</option>';
					foreach ( $friends as $friend ) {
						$output .= '<option value="' . urlencode( $friend['user_name'] ) . '">' .
							$friend['user_name'] .
						'</option>' . "\n";
					}
					$output .= '</select>
					</div>
					<div class="g-give-separator">' .
						$this->msg( 'g-give-separator' )->plain() .
					'</div>';
				}
			}

			$output .= '<div class="g-give-title">' .
				$this->msg( 'g-give-enter-friend-title' )->plain() .
			'</div>
			<div class="g-give-textbox">
				<input type="text" width="85" name="user" value="" />
				<input class="site-button" type="button" value="' . $this->msg( 'g-give-gift' )->plain() . '" onclick="document.gift.submit()" />
			</div>
			</div>
		</form>';

		return $output;
	}

	function displayFormAll() {
		global $wgGiveGiftPerRow, $wgUploadPath, $wgUser, $wgHuijiPrefix;

		$out = $this->getOutput();
		$u = $this->getUser();
		$user = Title::makeTitle( NS_USER, $this->user_name_to );

		$page = $this->getRequest()->getInt( 'page' );
		if ( !$page || !is_numeric( $page ) ) {
			$page = 1;
		}

		$per_page = 24;
		$per_row = $wgGiveGiftPerRow;
		if ( !$per_row ) {
			$per_row = 3;
		}
		//get user group
		// $user_group = $wgUser->getGroups();
		// if ( in_array( 'staff', $user_group ) ) {
		// 	$group = 1;
		// }elseif ( in_array( 'bureaucrat', $user_group ) ){
		// 	$group = 2;
		// }elseif ( in_array( 'sysop', $user_group ) ){
		// 	$group = 3;
		// }elseif ( empty($user_group) ) {
		// 	$group = 4;
		// }
		if ($u->isAllowed('sendStaffGifts')){
			$group = 1;
		} elseif ($u->isAllowed('sendBureaucratGifts')){
			$group = 2;
		} elseif ($u->isAllowed('sendSysopGifts')){
			$group = 3;
		} elseif ($u->isAllowed('sendGifts')){
			$group = 4;
		}
		$total = Gifts::getGiftCount( $wgHuijiPrefix );
		$gifts = Gifts::getGiftList( $group, $per_page, $page, $wgHuijiPrefix );
		$output = '';

		if ( $gifts ) {
			$out->setPageTitle( $this->msg( 'g-give-all-title', $this->user_name_to )->parse() );

			$output .= '<div class="back-links">
				<a href="' . htmlspecialchars( $user->getFullURL() ) . '">' .
					$this->msg( 'g-back-link', $this->user_name_to )->parse() .
				'</a>
			</div>
			<div class="g-message">' .
				$this->msg( 'g-give-all', $this->user_name_to )->parse() .
			'</div>
			<form action="" method="post" enctype="multipart/form-data" name="gift">';

			$x = 1;
			// var_dump($gifts);die();
			foreach ( $gifts as $gift ) {
				$toUser = HuijiUser::newFromID($this->user_id_to);
				$ug = new UserGifts( $toUser->getName() );
				$res = $ug->doesUserOwnGift( $this->user_id_to, $gift['id'] );
				$fromUser = HuijiUser::newFromID( $wgUser->getID() );
				$level = $fromUser->getLevel();
				$gift_image = 
					Gifts::getGiftImageTag( $gift['id'], 'l' );
				if ($res == true && $gift['repeat'] == 2) {
					$gclass = 'g-give-all g-had-got';
					$warning = '不可重复获得';
				}else if( $level->getLevelNumber() < 5 && $gift['gift_type'] == 3 ){
					$gclass = 'g-give-all g-level-low';
					$warning = '至少达到5级';
				}else{
					$gclass = 'g-give-all';
					$warning = '';
				}
				$output .= "<div id=\"give_gift_{$gift['id']}\" class='".$gclass."'>
					<div class=\"gift-warning\">".$warning."</div>
					{$gift_image}
					<div class=\"g-title g-blue\">{$gift['gift_name']}</div>";

				if ( $gift['gift_description'] ) {
					$output .= "<div class=\"g-describe\">{$gift['gift_description']}</div>";
				}
				$output .= '<div class="cleared"></div>
				</div>';
				if ( $x == count( $gifts ) || $x != 1 && $x % $per_row == 0 ) {
					$output .= '<div class="cleared"></div>';
				}
				$x++;
			}

			/**
			 * Build next/prev nav
			 */
			$giveGiftLink = $this->getPageTitle();

			$numofpages = $total / $per_page;
			$user_name = $user->getText();

			if ( $numofpages > 1 ) {
				$output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';
				if ( $page > 1 ) {
					$output .= '<li>'.Linker::link(
						$giveGiftLink,
						'<span aria-hidden="true">&laquo;</span>',
						// $this->msg( 'g-previous' )->plain(),
						array(),
						array(
							'user' => $user_name,
							'page' => ( $page - 1 )
						)
					) . '</li>';
					// ) . $this->msg( 'word-separator' )->plain();
				}

				if ( ( $total % $per_page ) != 0 ) {
					$numofpages++;
				}
				if ( $numofpages >= 9 ) {
					$numofpages = 9 + $page;
				}
				for ( $i = 1; $i <= $numofpages; $i++ ) {
					if ( $i == $page ) {
						$output .= ( '<li class="active"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>' );
						// $output .= ( $i . ' ' );
					} else {
						$output .= '<li>'.Linker::link(
							$giveGiftLink,
							$i,
							array(),
							array(
								'user' => $user_name,
								'page' => $i
							)
						) . '</li>';
						// ) . $this->msg( 'word-separator' )->plain();
					}
				}

				if ( ( $total - ( $per_page * $page ) ) > 0 ) {
					$output .= '<li>' .
					// $output .= $this->msg( 'word-separator' )->plain() .
						Linker::link(
							$giveGiftLink,
							'<span aria-hidden="true">&raquo;</span>',
							// $this->msg( 'g-next' )->plain(),
							array(),
							array(
								'user' => $user_name,
								'page' => ( $page + 1 )
							)
						);
				}
				$output .= '</nav></div>';
			}

			/**
			 * Build the send/cancel buttons and whatnot
			 */
			$output .= '<div class="g-give-all-message-title">' .
				$this->msg( 'g-give-all-message-title' )->plain() .
			'</div>
				<textarea name="message" id="message" rows="4" cols="50"></textarea>
				<div class="g-buttons">
					<input type="hidden" name="gift_id" id="to_user_gift_id" value="0" />
					<input type="hidden" name="user_name" id="gift-user-name" value="' . addslashes( $this->user_name_to ) . '" />
					<input type="hidden" name="user_id" id="gift-user-id" value="' . User::idFromName($this->user_name_to) . '" />
					<input type="button" id="send-gift-button" class="site-button" value="' . $this->msg( 'g-send-gift' )->plain() . '" size="20" />
					<input type="button" class="site-button" value="' . $this->msg( 'g-cancel' )->plain() . '" size="20" onclick="history.go(-1)" />
				</div>
			</form>';
		} else {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-no-gift' )->plain() );
			$output .= "<div><span><b>Q：</b>怎么才能给别人送礼物？</span><br>
						<span><b>A：</b>只有当礼物被创建了之后，才会出现在礼物列表中。</span><br>
						<span><b>Q：</b>如何创建礼物？</span><br>
						<span><b>A：</b>请联系本站点管理员，将要创建的礼物名称及介绍发邮件至 support@huiji.wiki 。我们小编会及时回复您的:）</span>
						</div>";
		}

		return $output;
	}
}
