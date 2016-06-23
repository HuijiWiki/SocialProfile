<?php
/**
 * A special page to view an individual system gift (award).
 *
 * @file
 * @ingroup Extensions
 */

class ViewSystemGift extends UnlistedSpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ViewSystemGift' );
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

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.systemgifts.css' );

		$output = ''; // Prevent E_NOTICE

		// If gift ID wasn't passed in the URL parameters or if it's not
		// numeric, display an error message
		$giftId = $request->getVal( 'gift_id' );
		$user_name = $request->getVal( 'user' );
		$page = $request->getInt( 'page', 1 );
		$per_page = 10;
		$user = User::newFromName($user_name);
		if ( !$giftId || !is_numeric( $giftId ) ) {
			$out->setPageTitle( $this->msg( 'ga-error-title' )->plain() );
			$out->addHTML( $this->msg( 'ga-error-message-invalid-link' )->plain() );
			return false;
		}

		$gift = UserSystemGifts::getUserGift( $giftId, $user_name );
		$profileURL = htmlspecialchars( Title::makeTitle( NS_USER, $user_name )->getFullURL() );
		$star = $per_page*($page-1);
		$page_gifts = array_slice($gift, $star, $per_page);
		$output .= '<div class="back-links">' .
			$this->msg( 'ga-back-link', $profileURL, $user_name )->text() .
		'</div>';
		$output .= '<span>'.$user_name.'共获得此成就'.count($gift).'次</span>';
		if ( count($page_gifts) > 0 ) {
			$i = 1;
			foreach ($page_gifts as $value) {
				$out->setPageTitle( $this->msg( 'ga-gift-title', $value['user_name'], $value['name'] )->parse() );

				
				if ( $value['status'] == 1 ) {
					if ( $value['user_name'] == $user->getName() ) {
						$g = new UserSystemGifts( $value['user_name'] );
						$g->clearUserGiftStatus( $value['id'] );
						$g->decNewSystemGiftCount( $user->getID() );
					}
				}
				// DB stuff
				$dbr = wfGetDB( DB_SLAVE );
				$res = $dbr->select(
					'user_system_gift',
					array(
						'DISTINCT sg_user_name', 'sg_user_id', 'sg_gift_id',
						'sg_date'
					),
					array(
						"sg_gift_id = {$value['gift_id']}",
						'sg_user_name <> ' . $dbr->addQuotes( $value['user_name'] )
					),
					__METHOD__,
					array(
						'GROUP BY' => 'sg_user_name',
						'ORDER BY' => 'sg_date DESC',
						'OFFSET' => 0,
						'LIMIT' => 6
					)
				);
				// If someone else in addition to the current user has gotten this
				// award, then and only then show the "Other recipients of this
				// award" header and the list of avatars
				if ( $value['gift_count'] > 1 && $i == 1){
					$output .= '<div class="ga-recent">
						<div class="ga-recent-title">' .
							$this->msg( 'ga-recent-recipients-award' )->plain() .
						'</div>
						<div class="ga-gift-count">' .
							$this->msg(
								'ga-gift-given-count'
							)->numParams(
								$value['gift_count']
							)->parse() .
						'</div>';

					foreach ( $res as $row ) {
						$userToId = $row->sg_user_id;
						$avatar = new wAvatar( $userToId, 'ml' );
						$userNameLink = Title::makeTitle( NS_USER, $row->sg_user_name );

						$output .= '<a href="' . htmlspecialchars( $userNameLink->getFullURL() ) . "\">
						{$avatar->getAvatarURL()}
					</a>";
					}

					$output .= '<div class="cleared"></div>
					</div>'; // .ga-recent
				}

				$message = $out->parse( trim( $value['description'] ), false );
				$output .= '<div class="ga-description-container">';

				$giftImage = SystemGifts::getGiftImageTag( $giftId, 'l');
				if ( !empty($value['designation']) ) {
					$title_name = '【称号】';
				}else{
					$title_name = '';
				}
				$output .= "<div class=\"ga-description\">
						{$giftImage}
						<div class=\"ga-name\">{$value['name']}<br>{$title_name}"."{$value['designation']}</div>
						<div class=\"ga-timestamp\">({$value['timestamp']})</div>
						<div class=\"ga-description-message\">{$message}</div>";
				$output .= '<div class="cleared"></div>
					</div>';
				$output .= '</div>';
				$i++;
			}
			/**
			 * Build next/prev nav
			 */
			// $pcount = $rel->getGiftCountByUsername( $user_name );
			$pcount = count($gift);
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

		} else {
			$out->setPageTitle( $this->msg( 'ga-error-title' )->plain() );
			$out->addHTML( $this->msg( 'ga-error-message-invalid-link' )->plain() );
		}
	}
}
