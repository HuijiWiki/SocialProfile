<?php
$wgAjaxExportList[] = 'wfUserActivityResponse';
function wfUserActivityResponse( $username, $filter, $item_type, $limit, $earlierThan ) {
	global $wgMemc;
	$output = '';

	$edits = $votes = $comments = $comments = $gifts = $relationships =
		$messages = $system_gifts = $messages_sent = $network_updates = $domain_creations =
		$user_user_follows = $user_site_follows = $user_update_status = $image_uploads = 0;

	if ( !$filter ) {
		$filter = "FOLLOWING";
	}
	if ( !$item_type ) {
		$item_type = 'default';
	}

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

	// $output .= '<div class="user-home-feed">';

	// $rel = new UserActivity( $user->getName(), ( ( $rel_type == 1 ) ? ' friends' : 'foes' ), 50 );
	$fixedLimit = $limit;

	$rel = new UserActivity2( $username, $filter , $fixedLimit, $earlierThan );
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

	// $output .= '<div class="user-home-feed">';
	/**
	 * Get all relationship activity
	 */
	$key = wfForeignMemcKey( 'huiji','','site_activity', $filter, $item_type, $fixedLimit, $username, $earlierThan );
	$data = $wgMemc->get($key);
	if ($data != ''){
		$activity = $data;
	} else {
		$activity = $rel->getActivityListGrouped();
		$wgMemc->set($key, $activity, 60 * 1);
	}
	
	$border_fix = '';
	$last = '';

	if ( $activity ) {
		$x = 1;
		$numberOfItems = $limit;
		foreach ( $activity as $item ) {
			if ( $x < $numberOfItems ) {
				if (
					( ( count( $activity ) > $numberOfItems ) && ( $x == $numberOfItems - 1 ) ) ||
					( ( count( $activity ) < $numberOfItems ) && ( $x == ( count( $activity ) ) ) )
				) {
					$border_fix = ' border-fix';
					$last = $item['timestamp'];
				} 
				/* There can be a very weird bug that leads to $item['data'] == 1 */
				/* This is a temprary fix. */
				if ($item['data'] == 1 ){
					//wfDebug("feed error: type:".$item_type['type']."time:".$item['timestamp']);
					continue;
				}

				// $typeIcon = UserActivity::getTypeIcon( $item['type'] );
				// $output .= "<div class=\"user-home-activity{$border_fix}\">
				// 	<img src=\"{$wgExtensionAssetsPath}/SocialProfile/images/" . $typeIcon . "\" alt=\"\" border=\"0\" />
				// 	{$item['data']}
				// </div>";
				$output .= "<div class=\"user-home-activity{$border_fix}\">
					{$item['data']}
				</div>";
				// $last = $item['timestamp'];
				$x++;
			}
		}
		$end = false;
	} else {
		$end = true;
	}

	$out = array(
		"success" => true,
		"continuation" => $last,
		"output" => $output,
		"earlierThan" => $earlierThan,
		"end" => $end
	);
	return json_encode($out);
}
?>