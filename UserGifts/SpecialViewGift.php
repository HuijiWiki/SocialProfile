<?php

class ViewGift extends UnlistedSpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'ViewGift' );
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
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.usergifts.css' );

		$giftId = $this->getRequest()->getInt( 'gift_id' );
		$page = $this->getRequest()->getInt( 'page', 1 );
		$per_page = 10;
		
		$user_name = $this->getRequest()->getVal( 'user' );
		if ( !$giftId || !is_numeric( $giftId ) ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-invalid-link' )->plain() );
			return false;
		}
		$i = 1;
		// $gift = UserGifts::getUserGift( $giftId, '' );
		$gifts = UserGifts::getUserGift( $user_name, $giftId, '0' );
		$star = $per_page*($page-1);
		$page_gifts = array_slice($gifts, $star, $per_page);
		$output = '<div class="back-links">
				<a href="' . htmlspecialchars( Title::makeTitle( NS_USER, $user_name )->getFullURL() ) . '">'
				. $this->msg( 'g-back-link', $user_name )->parse() . '</a>
			</div>';
		if ( count($page_gifts) >= 1 ) {
			foreach ($page_gifts as $key => $gift) {
				if ( $gift['status'] == 1 ) {
					if ( $gift['user_name_to'] == $user->getName() ) {
						$g = new UserGifts( $gift['user_name_to'] );
						$g->clearUserGiftStatus( $gift['id'] );
						$g->decNewGiftCount( $user->getID() );
					}
				}

				// DB stuff
				$dbr = wfGetDB( DB_SLAVE );
				$res = $dbr->select(
					'user_gift',
					array( 'DISTINCT ug_user_name_to', 'ug_user_id_to', 'ug_date' ),
					array(
						'ug_gift_id' => $giftId,
						'ug_user_name_to <> ' . $dbr->addQuotes( $gift['user_name_to'] )
					),
					__METHOD__,
					array(
						'GROUP BY' => 'ug_user_name_to',
						'ORDER BY' => 'ug_date DESC',
						'LIMIT' => 6
					)
				);
				if ( $gift['gift_count'] > 1 && $i == 1){
					$output .= '<div class="g-recent">
							<div class="g-recent-title">' .
								$this->msg( 'g-recent-recipients' )->plain() .
							'</div>
							<div class="g-gift-count">' .
								$this->msg( 'g-given', $gift['gift_count'] )->parse() .
							'</div>';

					foreach ( $res as $row ) {
						$userToId = $row->ug_user_id_to;
						$avatar = new wAvatar( $userToId, 'ml' );
						$userNameLink = Title::makeTitle( NS_USER, $row->ug_user_name_to );

						$output .= '<a href="' . htmlspecialchars( $userNameLink->getFullURL() ) . "\">
							{$avatar->getAvatarURL()}
						</a>";
					}

					$output .= '<div class="cleared"></div>
					</div>';
				}
				$out->setPageTitle( $this->msg(
					'g-description-title',
					$gift['user_name_to'],
					$gift['name']
				)->parse() );

				

				$sender = Title::makeTitle( NS_USER, $gift['user_name_from'] );
				$removeGiftLink = SpecialPage::getTitleFor( 'RemoveGift' );
				$giveGiftLink = SpecialPage::getTitleFor( 'GiveGift' );

				$giftImage = '<img src="' . $wgUploadPath . '/awards/' .
					Gifts::getGiftImage( $gift['gift_id'], 'l' ) .
					'" border="0" alt="" />';

				$message = $out->parse( trim( $gift['message'] ), false );
				$inviteCode = UserGifts::checkIsInviteGift($gift['id']);
				$userTitle = UserGifts::checkIsTitleGift($gift['gift_id'], $gift['user_id_to']);
				if ( $userTitle != null ) {
					$title_name = '称号：';
				}else{
					$title_name = '';
				}
				// print_r($gift);die();
				$output .= '<div class="g-description-container">';
				$output .= '<div class="g-description">' .
						$giftImage .
						'<div class="g-name">' . $gift['name'] . '</div>';
				// if ( $userTitle != null && $wgUser->getID() == $gift['user_id_to'] ) {
				// 	$output .= '<span>'.$title_name.$gift['designation'].'<br>开关</span>';
				// }
				$output .= '<div class="g-timestamp">(' . $gift['timestamp'] . ')</div>
						<div class="g-from">' . $this->msg(
							'g-from',
							htmlspecialchars( $sender->getFullURL() ),
							$gift['user_name_from']
						)->text() . '</div>';
				if ( $message ) {
					$output .= '<div class="g-user-message">' . $message . '</div>';
				}
				$output .= '<div class="cleared"></div>
						<div class="g-describe">' . $gift['description'] . '</div>';
						if( $inviteCode != null && $wgUser->getID() == $gift['user_id_to'] ){
							$output .= '<div class="invite-code well well-sm">邀请码：'.$inviteCode.'    <small>(仅自己可见)</small></div>';
						}
				$output .= '<div class="g-actions">';
				if ( $gift['user_name_to'] == $user->getName() ) {
					// $output .= $this->msg( 'pipe-separator' )->escaped();
					$output .= '<a href="' . htmlspecialchars( $removeGiftLink->getFullURL( 'gift_id=' . $gift['id'] ) ) . '">' .
						$this->msg( 'g-remove-gift' )->plain() . '</a>';
				}
				$output .= '</div>
					</div></div>';
				$i++;
			}
			/**
			 * Build next/prev nav
			 */
			// $pcount = $rel->getGiftCountByUsername( $user_name );
			$pcount = count($gifts);
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
							'gift_id' => $giftId,
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
								'gift_id' => $giftId,
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
								'gift_id' => $giftId,
								'page' => ( $page + 1 )
							)
						).'</li>';	
				}

				$output .= '</nav></div>';
			}
			$out->addHTML( $output );
		}else {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-invalid-link' )->plain() );
		}
	}
}
