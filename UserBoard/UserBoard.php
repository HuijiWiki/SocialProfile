<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

global $wgAvailableRights, $wgGroupPermissions;

$wgAvailableRights[] = 'SendToFollowers';
$wgGroupPermissions['sysop']['SendToFollowers'] = true;
$wgAvailableRights[] = 'SendToAllUsers';
$wgGroupPermissions['staff']['SendToAllUsers'] = true;
$wgAvailableRights[] = 'AdminDashboard';
$wgGroupPermissions['sysop']['AdminDashboard'] = true;
$wgAvailableRights[] = 'AddUserEditCounts';
$wgGroupPermissions['staff']['AddUserEditCounts'] = true;
$wgAvailableRights[] = 'AddFestivalGift';
$wgGroupPermissions['staff']['AddFestivalGift'] = true;

$wgJobClasses['boardBlastJobs'] = 'BoardBlastJobs';
