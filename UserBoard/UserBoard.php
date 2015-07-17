<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

global $wgAvailableRights, $wgGroupPermissions;

$wgAvailableRights[] = 'SendToFollowers';
$wgGroupPermissions['sysop']['SendToFollowers'] = true;
$wgAvailableRights[] = 'SendToAllUser';
$wgGroupPermissions['staff']['SendToAllUser'] = true;
