<?php
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "Not a valid entry point.\n" );
}

$wgHooks['ParserFirstCallInit'][] = 'wfSiteActivity';
/**
 * Register <siteactivity> hook with the Parser
 *
 * @param $parser Parser
 * @return Boolean
 */
function wfSiteActivity( &$parser ) {
	$parser->setHook( 'siteactivity', 'getSiteActivity' );
	return true;
}

function getSiteActivity( $input, $args, $parser ) {
	global $wgMemc, $wgExtensionAssetsPath, $wgUser;
	$parser->getOutput()->addModules('ext.socialprofile.siteactivity.css');
	$parser->getOutput()->addModules('ext.socialprofile.siteactivity.js');
	$parser->disableCache();

	$limit = ( isset( $args['limit'] ) && is_numeric( $args['limit'] ) ) ? $args['limit'] : 10;

	// so that <siteactivity limit=5 /> will return 5 items instead of 4...
	$fixedLimit = $limit + 1;

	$key = wfMemcKey( 'site_activity', 'THIS_SITE', $fixedLimit, $wgUser->getName() );
	$data = $wgMemc->get( $key );
	if ( !$data ) {
		wfDebug( "Got site activity from DB\n" );
		$rel = new UserActivity( $wgUser->getName(), 'THIS_SITE', $fixedLimit );

		$rel->setActivityToggle( 'show_votes', 0 );
		$rel->setActivityToggle( 'show_comments', 0 );
		$activity = $rel->getActivityListGrouped();
		$wgMemc->set( $key, $activity, 60 * 2 );
	} else {
		wfDebug( "Got site activity from cache\n" );
		$activity = $data;
	}		

	$output = '';
	if ( $activity ) {
		$output .= '<div class="mp-site-activity">';

		$x = 1;
		foreach ( $activity as $item ) {
			if ( $x < $fixedLimit ) {
				$typeIcon = UserActivity::getTypeIcon( $item['type'] );
				$output .= '<div class="mp-activity' . ( ( $x == $fixedLimit ) ? ' mp-activity-border-fix' : '' ) . '">'
				. $item['data'] .
				'</div>';
				$x++;
			}
		}

		$output .= '</div>';
	}

	return $output;
}
