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

		// If not otherwise specified, display everything but *votes* in the feed
		if ( $item_type == 'edit' || $item_type == 'all' ) {
			$edits = 1;
		}
		if ( $item_type == 'vote' || $item_type == 'all' ) {
			$votes = 1;
		}
		if ( $item_type == 'comment' || $item_type == 'all' ) {
			$comments = 1;
		}
		if ( $item_type == 'gift-rec' || $item_type == 'all' ) {
			$gifts = 1;
		}
		if ( $item_type == 'friend' || $item_type == 'all' ) {
			$relationships = 1;
		}
		if ( $item_type == 'system_message' || $item_type == 'all' ) {
			$messages = 1;
		}
		if ( $item_type == 'system_gift' || $item_type == 'all' ) {
			$system_gifts = 1;
		}
		if ( $item_type == 'user_message' || $item_type == 'all' ) {
			$messages_sent = 1;
		}
		if ( $item_type == 'network_update' || $item_type == 'all' ) {
			$network_updates = 1;
		}
		if ( $item_type == 'user_update_status' || $item_type == 'all' ) {
			$user_update_status = 1;
		}
		if ( $item_type == 'user_user_follow' || $item_type == 'all' ) {
			$user_user_follows = 1;
		}
		if ( $item_type == 'user_site_follow' || $item_type == 'all' ) {
			$user_site_follows = 1;
		}
		if ( $item_type == 'domain_creation' || $item_type == 'all' ) {
			$domain_creations = 1;
		}
		if ( $item_type == 'image_upload' || $item_type == 'all' ) {
			$image_uploads = 1;
		}

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

		$output .= '<div class="user-home-feed">';

		// $rel = new UserActivity( $user->getName(), ( ( $rel_type == 1 ) ? ' friends' : 'foes' ), 50 );
		$fixedLimit = 30;
		$rel = new UserActivity( $user->getName(), $filter , $fixedLimit );
		if ($item_type != 'default'){
			$rel->setActivityToggle( 'show_edits', $edits );
			$rel->setActivityToggle( 'show_votes', $votes );
			$rel->setActivityToggle( 'show_comments', $comments );
			$rel->setActivityToggle( 'show_gifts_rec', $gifts );
			$rel->setActivityToggle( 'show_relationships', $relationships );
			$rel->setActivityToggle( 'show_system_messages', $messages );
			$rel->setActivityToggle( 'show_system_gifts', $system_gifts );
			$rel->setActivityToggle( 'show_messages_sent', $messages_sent );
			$rel->setActivityToggle( 'show_network_updates', $network_updates );
			$rel->setActivityToggle( 'show_domain_creations', $domain_creations );
			$rel->setActivityToggle( 'show_user_user_follows', $user_user_follows );
			$rel->setActivityToggle( 'show_user_site_follows', $user_site_follows );
			$rel->setActivityToggle( 'show_user_update_status', $user_update_status );
			$rel->setActivityToggle( 'show_image_uploads', $image_uploads );
		}
		/**
		 * Get all relationship activity
		 */
		$key = wfForeignMemcKey( 'huiji',' ','site_activity', $filter, $item_type, $fixedLimit, $user->getName() );
		$data = $wgMemc->get($key);
		if ($data != ''){
			$activity = $data;
		} else {
			$activity = $rel->getActivityListGrouped();
			$wgMemc->set($key, $activity, 60 * 2);
		}
		
		$border_fix = '';

		if ( $activity ) {
			$x = 1;
			$numberOfItems = 30;
			foreach ( $activity as $item ) {
				if ( $x < $numberOfItems ) {
					if (
						( ( count( $activity ) > $numberOfItems ) && ( $x == $numberOfItems - 1 ) ) ||
						( ( count( $activity ) < $numberOfItems ) && ( $x == ( count( $activity ) ) ) )
					) {
						$border_fix = ' border-fix';
					} 

					$typeIcon = UserActivity::getTypeIcon( $item['type'] );
					// $output .= "<div class=\"user-home-activity{$border_fix}\">
					// 	<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/" . $typeIcon . "\" alt=\"\" border=\"0\" />
					// 	{$item['data']}
					// </div>";
					$output .= "<div class=\"user-home-activity{$border_fix}\">
						{$item['data']}
					</div>";
					$x++;
				}
			}
		}

		$output .= '</div>
		<div class="cleared"></div>';
		$out->addHTML( $output );
	}
}
