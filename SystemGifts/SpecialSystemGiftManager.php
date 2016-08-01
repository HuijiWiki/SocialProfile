<?php
/**
 * Special:SystemGiftManager -- a special page to create new system gifts
 * (awards)
 *
 * @file
 * @ingroup Extensions
 */

class SystemGiftManager extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'SystemGiftManager'/*class*/, 'giftadmin'/*restriction*/ );
	}
	function getGroupName() {
    		return 'wiki';
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Show a message if the database is in read-only mode
		if ( wfReadOnly() ) {
			$out->readOnlyPage();
			return;
		}

		// If user is blocked, s/he doesn't need to access this page
		if ( $user->isBlocked() ) {
			$out->blockedPage();
			return;
		}

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.systemgifts.css' );

		if ( $request->wasPosted() ) {
			$g = new SystemGifts();
			$gift_category = $request->getVal( 'gift_category' );
			if ( !$request->getInt( 'id' ) ) {
				// Add the new system gift to the database
				$gift_id = $g->addGift(
					$request->getVal( 'gift_name' ),
					$request->getVal( 'gift_description' ),
					$request->getVal( 'gift_category' ),
					$request->getInt( 'gift_threshold' ),
					$request->getVal( 'gift_prefix' ),
					$request->getVal( 'designation' )
				);
				$out->addHTML(
					'<span class="view-status">' .
					$this->msg( 'ga-created' )->plain() .
					'</span><br /><br />'
				);
			} else {
				$gift_id = $request->getInt( 'id' );
				$g->updateGift(
					$gift_id,
					$request->getVal( 'gift_name' ),
					$request->getVal( 'gift_description' ),
					$request->getVal( 'gift_category' ),
					$request->getInt( 'gift_threshold' ),
					$request->getVal( 'gift_prefix' ),
					$request->getVal( 'designation' )
				);
				$out->addHTML(
					'<span class="view-status">' .
					$this->msg( 'ga-saved' )->plain() .
					'</span><br /><br />'
				);
			}
			$g->update_system_gifts( $gift_id );
			$out->addHTML( $this->displayForm( $gift_id ) );
		} else {
			$gift_id = $request->getInt( 'id' );
			if ( $gift_id || $request->getVal( 'method' ) == 'edit' ) {
				$out->addHTML( $this->displayForm( $gift_id ) );
			} else {
				$out->addHTML(
					'<div><b><a href="' .
					htmlspecialchars( $this->getPageTitle()->getFullURL( 'method=edit' ) ) . '">' .
						$this->msg( 'ga-addnew' )->plain() . '</a></b></div>'
				);
				$out->addHTML( $this->displayGiftList() );
			}
		}
	}

	/**
	 * Display the text list of all existing system gifts and a delete link to
	 * users who are allowed to delete gifts.
	 *
	 * @return String: HTML
	 */
	function displayGiftList() {
		$output = ''; // Prevent E_NOTICE
		// $page = 0;
		$request = $this->getRequest();
		$per_page = 30;
		$page = $request->getInt( 'page', 1 );
		// $gifts = SystemGifts::getGiftList( $per_page, $page );
		$gifts = SystemGifts::getGiftList( $per_page, $page );
		$user = $this->getUser();
		$pcount = SystemGifts::getGiftCount();
		$output .= '<div id="views">';
		if ( $gifts ) {
			foreach ( $gifts as $gift ) {
				$deleteLink = '';
				if ( $user->isAllowed( 'giftadmin' ) ) {
					$removePage = SpecialPage::getTitleFor( 'RemoveMasterSystemGift' );
					$deleteLink = '<a href="' .
						htmlspecialchars( $removePage->getFullURL( "gift_id={$gift['id']}" ) ) .
						'" style="font-size:10px; color:red;">' .
						$this->msg( 'delete' )->plain() . '</a>';
				}

				$output .= '<div class="Item">
					<a href="' . htmlspecialchars( $this->getPageTitle()->getFullURL( 'id=' . $gift['id'] ) ) . '">' .
						$gift['gift_name'] . '</a> ' .
						$deleteLink . '</div>' . "\n";
			}
		}
		$output .= '</div>';
		/**
		 * Build next/prev nav
		 */
		$numofpages = $pcount / $per_page;

		$page_link = $this->getPageTitle();

		if ( $numofpages > 1 ) {
			$output .= '<div class="text-align: left"><nav class="page-nav pagination">';

			if ( $page > 1 ) {
				$output .= '<li>'.Linker::link(
					$page_link,
					'<span aria-hidden="true">&laquo;</span>',
					array(),
					array(
						// 'user' => $user_name,
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

		return  $output;
	}

	function displayForm( $gift_id ) {
		global $wgUploadPath;

		$form = '<div><b><a href="' . htmlspecialchars( $this->getPageTitle()->getFullURL() ) .
			'">' . $this->msg( 'ga-viewlist' )->plain() . '</a></b></div>';

		if ( $gift_id ) {
			$gift = SystemGifts::getGift( $gift_id );
		}

		$form .= '<form action="" method="post" enctype="multipart/form-data" name="gift">
		<table border="0" cellpadding="5" cellspacing="0" width="500">
			<tr>
				<td width="200" class="view-form">' . $this->msg( 'ga-giftname' )->plain() . '</td>
				<td width="695"><input type="text" size="45" class="createbox" name="gift_name" value="' . ( isset( $gift['gift_name'] ) ? $gift['gift_name'] : '' ) . '"/></td>
			</tr>
			<tr>
				<td width="200" class="view-form" valign="top">' . $this->msg( 'ga-giftdesc' )->plain() . '</td>
				<td width="695"><textarea class="createbox" name="gift_description" rows="2" cols="30">' . ( isset( $gift['gift_description'] ) ? $gift['gift_description'] : '' ) . '</textarea></td>
			</tr>
			<tr>
				<td width="200" class="view-form">' . $this->msg( 'ga-gifttype' )->plain() . '</td>
				<td width="695">
					<select name="gift_category">' . "\n";
			$g = new SystemGifts();
			foreach ( $g->getCategories() as $category => $id ) {
				$sel = '';
				if ( isset( $gift['gift_category'] ) && $gift['gift_category'] == $id ) {
					$sel = ' selected="selected"';
				}
				$indent = "\t\t\t\t\t\t";
				$form .= $indent . '<option' . $sel .
					" value=\"{$id}\">{$category}</option>\n";
			}
			$form .= "\t\t\t\t\t" . '</select>
				</td>
			</tr>
		<tr>
			<td width="200" class="view-form">' . $this->msg( 'ga-threshold' )->plain() . '</td>
			<td width="695"><input type="text" size="25" class="createbox" name="gift_threshold" value="' .
				( isset( $gift['gift_threshold'] ) ? $gift['gift_threshold'] : '' ) . '"/></td>
		</tr>
		<tr>
			<td width="200" class="view-form">称号</td>
			<td width="695"><input type="text" size="25" class="createbox" name="designation" value="' .
				( !empty( $gift['designation'] ) ? $gift['designation'] : '' ) . '"/></td>
		</tr>
		<tr>
			<td width="200" class="view-form">站点</td>
			<td width="695"><input type="text" size="25" class="createbox" name="gift_prefix" value="' .
				( !empty( $gift['gift_prefix'] ) ? $gift['gift_prefix'] : '' ) . '"/></td>
		</tr>';

		if ( $gift_id ) {
			$sgml = SpecialPage::getTitleFor( 'SystemGiftManagerLogo' );
			$gift_image = SystemGifts::getGiftImageTag( $gift['gift_id'], 'l' );
			$form .= '<tr>
			<td width="200" class="view-form" valign="top">' . $this->msg( 'ga-giftimage' )->plain() . '</td>
			<td width="695">' . $gift_image .
			'<a href="' . htmlspecialchars( $sgml->getFullURL( 'gift_id=' . $gift_id ) ) . '">' .
				$this->msg( 'ga-img' )->plain() . '</a>
			</td>
			</tr>';
		}

		if ( isset( $gift['gift_id'] ) ) {
			$button = $this->msg( 'edit' )->plain();
		} else {
			$button = $this->msg( 'ga-create-gift' )->plain();
		}

		$form .= '<tr>
		<td colspan="2">
			<input type="hidden" name="id" value="' . ( isset( $gift['gift_id'] ) ? $gift['gift_id'] : '' ) . '" />
			<input type="button" class="createbox" value="' . $button . '" size="20" onclick="document.gift.submit()" />
			<input type="button" class="createbox" value="' . $this->msg( 'cancel' )->plain() . '" size="20" onclick="history.go(-1)" />
		</td>
		</tr>
		</table>

		</form>';
		return $form;
	}

	function displayPage(){
		
		return '<div class="page-nav-wrapper">' . $output . '</div>';
	}
}
