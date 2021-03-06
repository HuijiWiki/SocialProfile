<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$wgAvailableRights[] = 'giftadmin';
$wgGroupPermissions['staff']['giftadmin'] = true;
$wgGroupPermissions['sysop']['giftadmin'] = true;

$wgAvailableRights[] = 'sendStaffGifts';
$wgAvailableRights[] = 'sendBureaucratGifts';
$wgAvailableRights[] = 'sendSysopGifts';
$wgAvailableRights[] = 'sendGifts';

$wgGroupPermissions['staff']['sendStaffGifts'] = true;
$wgGroupPermissions['staff']['sendBureaucratGifts'] = true;
$wgGroupPermissions['staff']['sendSysopGifts'] = true;
$wgGroupPermissions['staff']['sendGifts'] = true;
$wgGroupPermissions['bureaucrat']['sendBureaucratGifts'] = true;
$wgGroupPermissions['bureaucrat']['sendSysopGifts'] = true;
$wgGroupPermissions['bureaucrat']['sendGifts'] = true;
$wgGroupPermissions['sysop']['sendSysopGifts'] = true;
$wgGroupPermissions['sysop']['sendGifts'] = true;
$wgGroupPermissions['user']['sendGifts'] = true;


$wgUserGiftsDirectory = "$IP/extensions/SocialProfile/UserGifts";

// Special Pages etc.
$wgAutoloadClasses['Gifts'] = "{$wgUserGiftsDirectory}/GiftsClass.php";
$wgAutoloadClasses['UserGifts'] = "{$wgUserGiftsDirectory}/UserGiftsClass.php";

$wgAutoloadClasses['GiveGift'] = "{$wgUserGiftsDirectory}/SpecialGiveGift.php";
$wgSpecialPages['GiveGift'] = 'GiveGift';

$wgAutoloadClasses['ViewGifts'] = "{$wgUserGiftsDirectory}/SpecialViewGifts.php";
$wgSpecialPages['ViewGifts'] = 'ViewGifts';

$wgAutoloadClasses['ViewGift'] = "{$wgUserGiftsDirectory}/SpecialViewGift.php";
$wgSpecialPages['ViewGift'] = 'ViewGift';

$wgAutoloadClasses['GiftManager'] = "{$wgUserGiftsDirectory}/SpecialGiftManager.php";
$wgSpecialPages['GiftManager'] = 'GiftManager';

$wgAutoloadClasses['GiftManagerLogo'] = "{$wgUserGiftsDirectory}/SpecialGiftManagerLogo.php";
$wgSpecialPages['GiftManagerLogo'] = 'GiftManagerLogo';

$wgAutoloadClasses['RemoveMasterGift'] = "{$wgUserGiftsDirectory}/SpecialRemoveMasterGift.php";
$wgSpecialPages['RemoveMasterGift'] = 'RemoveMasterGift';

$wgAutoloadClasses['RemoveGift'] = "{$wgUserGiftsDirectory}/SpecialRemoveGift.php";
$wgSpecialPages['RemoveGift'] = 'RemoveGift';

$wgMessagesDirs['UserGifts'] = __DIR__ . '/i18n';

// Register the CSS & JS with ResourceLoader
$wgResourceModules['ext.socialprofile.usergifts.css'] = array(
	'styles' => 'UserGifts.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'SocialProfile/UserGifts',
	'position' => 'top'
);

$wgResourceModules['ext.socialprofile.usergifts.js'] = array(
	'scripts' => 'UserGifts.js',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'SocialProfile/UserGifts',
);

// designation
$wgResourceModules['ext.socialprofile.designation.js'] = array(
	'scripts' => 'designation.js',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'SocialProfile/UserGifts',
);

// Credits
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'GiftManager',
	'version' => '1.0',
	'description' => 'Adds a special page to administrate available gifts and add new ones',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'GiftManagerLogo',
	'version' => '1.0',
	'description' => 'Adds a special page to upload new gift images',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'GiveGift',
	'version' => '1.0',
	'description' => 'Adds a special page to give out gifts to your friends/foes',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'RemoveGift',
	'version' => '1.0',
	'description' => 'Adds a special page to remove gifts',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'RemoveMasterGift',
	'version' => '1.0',
	'description' => 'Adds a special page to delete gifts from the database',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'ViewGift',
	'version' => '1.0',
	'description' => 'Adds a special page to view given gifts',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);
$wgExtensionCredits['specialpage'][] = array(
	'name' => 'ViewGifts',
	'version' => '1.0',
	'description' => 'Adds a special page to view given gifts',
	'author' => array( 'Aaron Wright', 'David Pean' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile'
);
