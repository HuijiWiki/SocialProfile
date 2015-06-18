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
	global $wgMemc, $wgExtensionAssetsPath;

	$parser->disableCache();

	$limit = ( isset( $args['limit'] ) && is_numeric( $args['limit'] ) ) ? $args['limit'] : 10;

	// so that <siteactivity limit=5 /> will return 5 items instead of 4...
	$fixedLimit = $limit + 1;

	$key = wfForeignMemcKey( 'huiji', '', 'site_activity', 'all', $fixedLimit );
	$data = $wgMemc->get( $key );
	if ( !$data ) {
		wfDebug( "Got site activity from DB\n" );
		$rel = new UserActivity( '', 'ALL', $fixedLimit );

		$rel->setActivityToggle( 'show_votes', 0 );
		$activity = $rel->getActivityListGrouped();
		$wgMemc->set( $key, $activity, 60 * 2 );
	} else {
		wfDebug( "Got site activity from cache\n" );
		$activity = $data;
	}

	$output = '';
	if ( $activity ) {
		$output .= '<h2>' . wfMessage( 'useractivity-siteactivity' )->plain() . '</h2><ul class="mp-site-activity">';
		$x = 1;
		foreach ( $activity as $item ) {
			if ( $x < $fixedLimit ) {
				$typeIcon = UserActivity::getTypeIcon( $item['type'] );
				$output .= '<li class="mp-activity' . ( ( $x == $fixedLimit ) ? ' mp-activity-border-fix' : '' ) . '">'
				. $typeIcon 
				. $item['data'] .
				'</li>';
				$x++;
			}
		}

		$output .= '</ul>';
	}

	return $output;
}
