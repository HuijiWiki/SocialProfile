<?php
/**
 * Special:UserActivity - a special page for showing recent social activity
 * The class is called "UserHome" because the "UserActivity" class is at
 * UserActivityClass.php.
 *
 * @file
 * @ingroup Extensions
 */

class UserHome extends SpecialPage {
	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'UserActivity' );
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
		global $wgExtensionAssetsPath, $wgMemc;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.useractivity.css' );
		// Add Javascript
		$out->addModuleScripts( 'ext.socialprofile.useractivity.js' );
		// Set the page title, robot policies, etc.
		$this->setHeaders();

		$out->setPageTitle( $this->msg( 'useractivity-title' )->plain() );

		// Initialize all of these or otherwise we get a lot of E_NOTICEs about
		// undefined variables when the filtering feature (described below) is
		// active and we're viewing a filtered-down feed
		$edits = $votes = $comments = $comments = $gifts = $relationships =
			$messages = $system_gifts = $messages_sent = $network_updates = $domain_creations =
			$user_user_follows = $user_site_follows = $user_update_status = $image_uploads = 0;

		$filter = $request->getVal( 'filter' );
		$item_type = $request->getVal( 'item_type' );

		if ( !$filter ) {
			$filter = "FOLLOWING";
		}
		if ( !$item_type ) {
			$item_type = 'default';
		}
		$output = '
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="/wiki/Special:UserActivity?filter=FOLLOWING_SITES" aria-controls="following_sites" role="tab" >我关注的站点</a></li>
			<li role="presentation"><a href="/wiki/Special:UserActivity?filter=FOLLOWING" aria-controls="following" role="tab" >我关注的用户</a></li>
			<li role="presentation"><a href="/wiki/Special:UserActivity?filter=USER" aria-controls="user" role="tab" >我自己</a></li>
			<li role="presentation"><a href="/wiki/Special:UserActivity?filter=ALL" aria-controls="all" role="tab" >精彩推荐</a></li>
		</ul>';

		// Filtering feature, if enabled
		// The filter message's format is:
		// *filter name (item_type URL parameter)|Displayed text (can be the name of a MediaWiki: message, too)|Type icon name (*not* the image name; see UserActivity::getTypeIcon())
		// For example:
		// *messages|Board Messages|user_message
		// This would add a link that allows filtering non-board messages
		// related events from the filter, only showing board message activity

		$filterMsg = $this->msg( 'useractivity-friendsactivity-filter' );
		if ( !$filterMsg->isDisabled() ) {
			$output .= '<div class="user-home-links-container">
			<h2>' . $this->msg( 'useractivity-filter' )->plain() . '</h2>
			<div class="user-home-links">';

			$lines = explode( "\n", $filterMsg->inContentLanguage()->text() );

			foreach ( $lines as $line ) {
				if ( strpos( $line, '*' ) !== 0 ) {
					continue;
				} else {
					$line = explode( '|' , trim( $line, '* ' ), 3 );
					$type = $line[0];
					$link_text = $line[1];

					// Maybe it's the name of a MediaWiki: message? I18n is
					// always nice, so at least try it and see what happens...
					$linkMsgObj = wfMessage( $link_text );
					if ( !$linkMsgObj->isDisabled() ) {
						$link_text = $linkMsgObj->parse();
					}

					$link_image = $line[2];
					$output .= '<a href="' . htmlspecialchars( $this->getPageTitle()->getFullURL( "item_type={$type}" ) ) .
						"\">".
						UserActivity::getTypeIcon( $type ) . "&nbsp;{$link_text}</a>";
				}
			}

			$output .= Linker::link(
				$this->getPageTitle(),
				$this->msg( 'useractivity-all' )->plain()
			);
			$output .= '</div>
			</div>';
		}

		$fixedLimit = 30;
		$output .= '<div class="user-home-feed" data-filter="'.$filter.'" data-limit="'.$fixedLimit.'" data-item_type="'.$item_type.'">';

		$output .= '</div>
		<div class="cleared"></div><button id="user-activity-more">More</button>';
		$out->addHTML( $output );
	}
}
