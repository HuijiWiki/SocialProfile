<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

global $wgAvailableRights, $wgGroupPermissions;

$wgAvailableRights[] = 'SendToFollowers';
$wgGroupPermissions['staff']['SendToFollowers'] = true;
$wgAvailableRights[] = 'SendToAllUser';
$wgGroupPermissions['sysop']['SendToAllUser'] = true;
