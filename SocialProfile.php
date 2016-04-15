<?php
/**
 * Protect against register_globals vulnerabilities.
 * This line must be present before any global variable is referenced.
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die(
		'This is the setup file for the SocialProfile extension to MediaWiki.' .
		'Please see http://www.mediawiki.org/wiki/Extension:SocialProfile for' .
		' more information about this extension.'
	);
}

/**
 * This is the loader file for the SocialProfile extension. You should include
 * this file in your wiki's LocalSettings.php to activate SocialProfile.
 *
 * If you want to use the UserWelcome extension (bundled with SocialProfile),
 * the <topusers /> tag or the user levels feature, there are some other files
 * you will need to include in LocalSettings.php. The online manual has more
 * details about this.
 *
 * For more info about SocialProfile, please see https://www.mediawiki.org/wiki/Extension:SocialProfile.
 */

// Internationalization files
$wgMessagesDirs['SocialProfile'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['SocialProfileAlias'] = __DIR__ . '/SocialProfile.alias.php';

$wgMessagesDirs['SocialProfileUserBoard'] = __DIR__ . '/UserBoard/i18n';
$wgMessagesDirs['SocialProfileUserProfile'] = __DIR__ . '/UserProfile/i18n';
$wgMessagesDirs['SocialProfileUserRelationship'] = __DIR__ . '/UserRelationship/i18n';
$wgMessagesDirs['SocialProfileUserStats'] = __DIR__ . '/UserStats/i18n';
$wgMessagesDirs['SocialProfileUserSiteFollows'] = __DIR__ . '/UserSiteFollows/i18n';
$wgMessagesDirs['SocialProfileUserUserFollows'] = __DIR__ . '/UserUserFollows/i18n';
$wgMessagesDirs['SocialProfileAdminDashboard'] = __DIR__ . '/AdminDashboard/i18n';
$wgMessagesDirs['SocialProfileSiteStatus'] = __DIR__ . '/SiteStatus/i18n';
$wgMessagesDirs['SocialProfileUserStatus'] = __DIR__ . '/UserStatus/i18n';
$wgMessagesDirs['SocialProfileUploadFiles'] = __DIR__ . '/UploadFiles/i18n';
$wgMessagesDirs['SocialProfileVideos'] = __DIR__ . '/Videos/i18n';
$wgExtensionMessagesFiles['SocialProfileNamespaces'] = __DIR__ . '/SocialProfile.namespaces.php';
$wgExtensionMessagesFiles['AvatarMagic'] = __DIR__ . '/UserProfile/Avatar.magic.i18n.php';

// Classes to be autoloaded
$wgAutoloadClasses['GenerateTopUsersReport'] = __DIR__ . '/UserStats/GenerateTopUsersReport.php';

$wgAutoloadClasses['SpecialAddRelationship'] = __DIR__ . '/UserRelationship/SpecialAddRelationship.php';
$wgAutoloadClasses['SpecialBoardBlast'] = __DIR__ . '/UserBoard/SpecialSendBoardBlast.php';
$wgAutoloadClasses['SpecialSendToFollowers'] = __DIR__ . '/UserBoard/SpecialSendToFollowers.php';
$wgAutoloadClasses['SpecialSendToAllUsers'] = __DIR__ . '/UserBoard/SpecialSendToAllUsers.php';
// $wgAutoloadClasses['SpecialEditProfile'] = __DIR__ . '/UserProfile/SpecialEditProfile.php';
// $wgAutoloadClasses['SpecialPopulateUserProfiles'] = __DIR__ . '/UserProfile/SpecialPopulateExistingUsersProfiles.php';
// $wgAutoloadClasses['SpecialRemoveRelationship'] = __DIR__ . '/UserRelationship/SpecialRemoveRelationship.php';
// $wgAutoloadClasses['SpecialToggleUserPage'] = __DIR__ . '/UserProfile/SpecialToggleUserPageType.php';
// $wgAutoloadClasses['SpecialUpdateProfile'] = __DIR__ . '/UserProfile/SpecialUpdateProfile.php';
$wgAutoloadClasses['SpecialUploadAvatar'] = __DIR__ . '/UserProfile/SpecialUploadAvatar.php';
// $wgAutoloadClasses['SpecialViewRelationshipRequests'] = __DIR__ . '/UserRelationship/SpecialViewRelationshipRequests.php';
// $wgAutoloadClasses['SpecialViewRelationships'] = __DIR__ . '/UserRelationship/SpecialViewRelationships.php';
$wgAutoloadClasses['SpecialViewUserBoard'] = __DIR__ . '/UserBoard/SpecialUserBoard.php';
$wgAutoloadClasses['SpecialViewFollows'] = __DIR__ . '/UserUserFollows/SpecialViewFollows.php';
$wgAutoloadClasses['SpecialEditRank'] = __DIR__ . '/UserSiteFollows/SpecialEditRank.php';
$wgAutoloadClasses['SpecialShowFollowedSites'] = __DIR__ . '/UserSiteFollows/SpecialShowFollowedSites.php';
$wgAutoloadClasses['SpecialRandomwiki'] = __DIR__ . '/SpecialRandomwiki.php';
$wgAutoloadClasses['RemoveAvatar'] = __DIR__ . '/UserProfile/SpecialRemoveAvatar.php';
$wgAutoloadClasses['UpdateEditCounts'] = __DIR__ . '/UserStats/SpecialUpdateEditCounts.php';
$wgAutoloadClasses['UserBoard'] = __DIR__ . '/UserBoard/UserBoardClass.php';
$wgAutoloadClasses['BoardBlastJobs'] = __DIR__ . '/UserBoard/BoardBlastJobs.php';
$wgAutoloadClasses['UserProfile'] = __DIR__ . '/UserProfile/UserProfileClass.php';
$wgAutoloadClasses['UserProfilePage'] = __DIR__ . '/UserProfile/UserProfilePage.php';
$wgAutoloadClasses['UserRelationship'] = __DIR__ . '/UserRelationship/UserRelationshipClass.php';
$wgAutoloadClasses['UserLevel'] = __DIR__ . '/UserStats/UserStatsClass.php';
$wgAutoloadClasses['UserStats'] = __DIR__ . '/UserStats/UserStatsClass.php';
$wgAutoloadClasses['UserStatsTrack'] = __DIR__ . '/UserStats/UserStatsClass.php';
$wgAutoloadClasses['UserSystemMessage'] = __DIR__ . '/UserSystemMessages/UserSystemMessagesClass.php';
$wgAutoloadClasses['TopFansByStat'] = __DIR__ . '/UserStats/TopFansByStat.php';
$wgAutoloadClasses['TopFansRecent'] = __DIR__ . '/UserStats/TopFansRecent.php';
$wgAutoloadClasses['TopUsersPoints'] = __DIR__ . '/UserStats/TopUsers.php';
$wgAutoloadClasses['wAvatar'] = __DIR__ . '/UserProfile/AvatarClass.php';
$wgAutoloadClasses['wSiteAvatar'] = __DIR__ . '/SiteStatus/SiteAvatarClass.php';
$wgAutoloadClasses['AvatarParserFunction'] = __DIR__ . '/UserProfile/AvatarParserFunction.php';
$wgAutoloadClasses['UserSiteFollow'] = __DIR__ . '/UserSiteFollows/UserSiteFollowsClass.php';
$wgAutoloadClasses['UserUserFollow'] = __DIR__ . '/UserUserFollows/UserUserFollowsClass.php';
$wgAutoloadClasses['UserStatus'] = __DIR__ . '/UserStatus/UserStatusClass.php';
$wgAutoloadClasses['ResponseGenerator'] = __DIR__ . '/ResponseGeneratorClass.php';
$wgAutoloadClasses['CropAvatar'] = __DIR__ . '/UserUploadAvatar/crop.php';
$wgAutoloadClasses['UserEditBox'] = __DIR__ . '/UserStats/UserEditBoxClass.php';
$wgAutoloadClasses['SpecialAdminDashboard'] = __DIR__ . '/AdminDashboard/SpecialAdminDashboard.php';
$wgAutoloadClasses['SpecialSiteRank'] = __DIR__ . '/SiteStatus/SpecialSiteRank.php';
$wgAutoloadClasses['AllSitesInfo'] = __DIR__ . '/SiteStatus/AllSitesInfoClass.php';
$wgAutoloadClasses['RecordStatistics'] = __DIR__ . '/HuijiStatistics/interface.php';
$wgAutoloadClasses['RecordStatistics2'] = __DIR__ . '/HuijiStatistics/interface_v2.php';
$wgAutoloadClasses['SpecialCompleteUserInfo'] = __DIR__ . '/CompleteUserInfo/SpecialCompleteUserInfo.php';
$wgAutoloadClasses['QqSdk'] = __DIR__ . '/UserProfile/QqSdkClass.php';
$wgAutoloadClasses['SaeTOAuthV2'] = __DIR__ . '/UserProfile/weiboSdkClass.php';
$wgAutoloadClasses['SaeTClientV2'] = __DIR__ . '/UserProfile/weiboSdkClass.php';
$wgAutoloadClasses['SpecialCallbackQQ'] = __DIR__ . '/SpecialCallbackQQ.php';
$wgAutoloadClasses['SpecialCallbackWeibo'] = __DIR__ . '/SpecialCallbackWeibo.php';
$wgAutoloadClasses['SpecialGlobalSearch'] = __DIR__ . '/GlobalSearch/SpecialGlobalSearch.php';
$wgAutoloadClasses['SpecialUploadFiles'] = __DIR__ . '/UploadFiles/SpecialUploadFiles.php';
$wgAutoloadClasses['SpecialVideos'] = __DIR__ . '/Videos/SpecialVideos.php';
$wgAutoloadClasses['SpecialVideosList'] = __DIR__ . '/Videos/SpecialVideosList.php';
$wgAutoloadClasses['SpecialAddUserEditCounts'] = __DIR__ . '/UserStats/SpecialAddUserEditCounts.php';
$wgAutoloadClasses['SpecialAddFestivalGift'] = __DIR__ . '/SystemGifts/SpecialAddFestivalGift.php';
$wgAutoloadClasses['SpecialSendHiddenGift'] = __DIR__ . '/SystemGifts/SpecialSendHiddenGift.php';
// $wgAutoloadClasses['SpecialFamilyTree'] = __DIR__ . '/FamilyTree/SpecialFamilyTree.php';
$wgAutoloadClasses['QueryInterface'] = __DIR__ . '/QueryInterface.php';
$wgAutoloadClasses['TemplateFork'] = __DIR__ . '/TemplateFork/TemplateForkClass.php';
$wgAutoloadClasses['UploadVideos'] = __DIR__ . '/Videos/UploadVideosClass.php';
$wgAutoloadClasses['VideoTitle'] = __DIR__ . '/Videos/UploadVideosClass.php';
$wgAutoloadClasses['VideoRevision'] = __DIR__ . '/Videos/UploadVideosClass.php';
$wgAutoloadClasses['SpecialInviteUser'] = __DIR__ . '/AdminDashboard/SpecialInviteUser.php';
$wgAutoloadClasses['WikiDoctor'] = __DIR__ . '/AdminDashboard/WikiDoctor.php';
$wgAutoloadClasses['SpecialDesignation'] = __DIR__ . '/UserGifts/SpecialDesignation.php';
$wgAutoloadClasses['SpecialGiveSystemGift'] = __DIR__ . '/SystemGifts/SpecialGiveSystemGift.php';

// New special pages
// $wgSpecialPages['AddRelationship'] = 'SpecialAddRelationship';
// $wgSpecialPages['EditProfile'] = 'SpecialEditProfile';
$wgSpecialPages['GenerateTopUsersReport'] = 'GenerateTopUsersReport';
// $wgSpecialPages['PopulateUserProfiles'] = 'SpecialPopulateUserProfiles';
$wgSpecialPages['RemoveAvatar'] = 'RemoveAvatar';
// $wgSpecialPages['RemoveRelationship'] = 'SpecialRemoveRelationship';
$wgSpecialPages['SendBoardBlast'] = 'SpecialBoardBlast';
$wgSpecialPages['SendToFollowers'] = 'SpecialSendToFollowers';
$wgSpecialPages['SendToAllUsers'] = 'SpecialSendToAllUsers';
$wgSpecialPages['TopFansByStatistic'] = 'TopFansByStat';
$wgSpecialPages['TopUsers'] = 'TopUsersPoints';
$wgSpecialPages['TopUsersRecent'] = 'TopFansRecent';
// $wgSpecialPages['ToggleUserPage'] = 'SpecialToggleUserPage';
$wgSpecialPages['UpdateEditCounts'] = 'UpdateEditCounts';
// $wgSpecialPages['UpdateProfile'] = 'SpecialUpdateProfile';
$wgSpecialPages['UploadAvatar'] = 'SpecialUploadAvatar';
$wgSpecialPages['UserBoard'] = 'SpecialViewUserBoard';
// $wgSpecialPages['ViewRelationshipRequests'] = 'SpecialViewRelationshipRequests';
// $wgSpecialPages['ViewRelationships'] = 'SpecialViewRelationships';
$wgSpecialPages['ViewFollows'] = 'SpecialViewFollows';
$wgSpecialPages['EditRank'] = 'SpecialEditRank';
$wgSpecialPages['ShowFollowedSites'] = 'SpecialShowFollowedSites';
$wgSpecialPages['Randomwiki'] = 'SpecialRandomwiki';
$wgSpecialPages['AdminDashboard'] = 'SpecialAdminDashboard';
$wgSpecialPages['SiteRank'] = 'SpecialSiteRank';
$wgSpecialPages['CompleteUserInfo'] = 'SpecialCompleteUserInfo';
$wgSpecialPages['CallbackQQ'] = 'SpecialCallbackQQ';
$wgSpecialPages['CallbackWeibo'] = 'SpecialCallbackWeibo';
$wgSpecialPages['GlobalSearch'] = 'SpecialGlobalSearch';
$wgSpecialPages['UploadFiles'] = 'SpecialUploadFiles';
$wgSpecialPages['Videos'] = 'SpecialVideos';
$wgSpecialPages['VideosList'] = 'SpecialVideosList';
$wgSpecialPages['AddUserEditCounts'] = 'SpecialAddUserEditCounts';
$wgSpecialPages['AddFestivalGift'] = 'SpecialAddFestivalGift';
$wgSpecialPages['SendHiddenGift'] = 'SpecialSendHiddenGift';
$wgSpecialPages['InviteUser'] = 'SpecialInviteUser';
$wgSpecialPages['Designation'] = 'SpecialDesignation';
// $wgSpecialPages['FamilyTree'] = 'SpecialFamilyTree';
$wgSpecialPages['GiveSystemGift'] = 'SpecialGiveSystemGift';


// Necessary AJAX functions
require_once( "$IP/extensions/SocialProfile/UserBoard/UserBoard_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserRelationship/Relationship_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserUserFollows/UserUserFollows_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserStatus/UserStatus_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserSiteFollows/UserSiteFollows_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/SiteStatus/SiteStatus_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserActivity/UserActivity_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/TemplateFork/TemplateFork_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserProfile/OauthLogin_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/UserGifts/UserGift_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/Videos/UploadVideos_AjaxFunctions.php" );
require_once( "$IP/extensions/SocialProfile/AdminDashboard/AdminDashboard_AjaxFunctions.php" );
// What to display on social profile pages by default?
$wgUserProfileDisplay['board'] = true;
$wgUserProfileDisplay['foes'] = false;
$wgUserProfileDisplay['friends'] = false;
$wgUserProfileDisplay['custom'] = false;
$wgUserProfileDisplay['interests'] = false;
$wgUserProfileDisplay['stats'] = false;
$wgUserProfileDisplay['games'] = false;
$wgUserProfileDisplay['personal'] = false;
$wgUserProfileDisplay['activity'] = true;
$wgUserProfileDisplay['gifts'] = true;
$wgUserProfileDisplay['awards'] = true;
$wgUserProfileDisplay['userboxes'] = false;
// Should we display UserBoard-related things on social profile pages?
$wgUserBoard = true;

// Whether to enable friending or not -- this doesn't do very much actually, so don't rely on it
$wgFriendingEnabled = true;

// Prefix SocialProfile will use to store avatars
// for global avatars on a wikifarm or groups of wikis,
// set this to something static.
$wgAvatarKey = 'my_wiki';
$wgSiteAvatarKey = 'site_avatar';

// Extension credits that show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'SocialProfile',
	'author' => array( 'Aaron Wright', 'David Pean', 'Jack Phoenix' ),
	'version' => '1.7.1',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'descriptionmsg' => 'socialprofile-desc',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'TopUsers',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'Adds a special page for viewing the list of users with the most points.',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'UploadAvatar',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for uploading Avatars',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'RemoveAvatar',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for removing users\' avatars',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'PopulateExistingUsersProfiles',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for initializing social profiles for existing wikis',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'ToggleUserPage',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for updating a user\'s userpage preference',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'UpdateProfile',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page to allow users to update their social profile',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'SendBoardBlast',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page to allow users to send a mass board message by selecting from a list of their friends and foes',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'UserBoard',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'Display User Board messages for a user',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'AddRelationship',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for adding friends/foe requests for existing users in the wiki',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'RemoveRelationship',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for removing existing friends/foes for the current logged in user',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'ViewRelationshipRequests',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for viewing open relationship requests for the current logged in user',
);
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'ViewRelationships',
	'author' => 'David Pean',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A special page for viewing all relationships by type',
);
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Avatar',
	'author' => 'Adam Carter',
	'url' => 'https://www.mediawiki.org/wiki/Extension:SocialProfile',
	'description' => 'A parser function to get the avatar of a given user',
);

// Hooked functions
$wgAutoloadClasses['SocialProfileHooks'] = __DIR__ . '/SocialProfileHooks.php';

// Loader files
require_once( "$IP/extensions/SocialProfile/UserProfile/UserProfile.php" ); // Profile page configuration loader file
require_once( "$IP/extensions/SocialProfile/UserGifts/Gifts.php" ); // UserGifts (user-to-user gifting functionality) loader file
require_once( "$IP/extensions/SocialProfile/SystemGifts/SystemGifts.php" ); // SystemGifts (awards functionality) loader file
require_once( "$IP/extensions/SocialProfile/UserBoard/UserBoard.php" ); // UserBoard loader file
require_once( "$IP/extensions/SocialProfile/UserActivity/UserActivity.php" ); // UserActivity - recent social changes
require_once( "$IP/extensions/SocialProfile/UserEditRecord/userEditRecord.php"); // Edit Record collected to mongoDB.
require_once( "$IP/extensions/SocialProfile/updateESContent.php"); // Update Page Content in ES.



$wgHooks['CanonicalNamespaces'][] = 'SocialProfileHooks::onCanonicalNamespaces';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'SocialProfileHooks::onLoadExtensionSchemaUpdates';
$wgHooks['ParserFirstCallInit'][] = 'AvatarParserFunction::setupAvatarParserFunction';
$wgHooks['BeforePageDisplay'][] = 'SocialProfileHooks::onBeforePageDisplay';
$wgHooks['MimeMagicGuessFromContent'][] = 'SocialProfileHooks::onMimeMagicGuessFromContent';
$wgHooks['BitmapHandlerTransform'][] = 'SocialProfileHooks::onBitmapHandlerTransform';
$wgHooks['ThumbnailBeforeProduceHTML'][] = 'SocialProfileHooks::onThumbnailBeforeProduceHTML';
// For the Renameuser extension
$wgHooks['RenameUserComplete'][] = 'SocialProfileHooks::onRenameUserComplete';

// Set new user tours
$wgHooks['AddNewAccount'][] = 'SocialProfileHooks::onAddNewAccount';

// expose config vars to javascript
$wgHooks['ResourceLoaderGetConfigVars'][] = 'SocialProfileHooks::onResourceLoaderGetConfigVars';
//Image Hooks

$wgHooks['ImageOpenShowImageInlineBefore'][] = 'SocialProfileHooks::onImageOpenShowImageInlineBefore';
$wgHooks['ImagePageAfterImageLinks'][] = 'SocialProfileHooks::onImagePageAfterImageLinks';
$wgHooks['UserGroupsChanged'][] = 'SocialProfileHooks::onUserGroupsChanged';

// ResourceLoader module definitions for certain components which do not have
// their own loader file
// General
$wgResourceModules['ext.socialprofile.clearfix'] = array(
	'styles' => 'clearfix.css',
	'position' => 'top',
	'localBasePath' => __DIR__ . '/shared',
	'remoteExtPath' => 'SocialProfile/shared',
);
$wgResourceModules['ext.socialprofile.responsive'] = array(
	'styles' => 'responsive.less',
	'position' => 'top',
	'localBasePath' => __DIR__ . '/shared',
	'remoteExtPath' => 'SocialProfile/shared',
);
// General/shared JS modules -- not (necessarily) directly used by SocialProfile,
// but rather by other social tools which depend on SP
// @see https://phabricator.wikimedia.org/T100025
$wgResourceModules['ext.socialprofile.flash'] = array(
	'scripts' => 'flash.js',
	'position' => 'bottom',
	'localBasePath' => __DIR__ . '/shared',
	'remoteExtPath' => 'SocialProfile/shared',
);
$wgResourceModules['ext.socialprofile.LightBox'] = array(
	'scripts' => 'LightBox.js',
	'position' => 'bottom',
	'localBasePath' => __DIR__ . '/shared',
	'remoteExtPath' => 'SocialProfile/shared',
);


// UserBoard
$wgResourceModules['ext.socialprofile.userboard.js'] = array(
	'scripts' => 'UserBoard.js',
	'messages' => array( 'userboard_confirmdelete' ),
	'localBasePath' => __DIR__ . '/UserBoard',
	'remoteExtPath' => 'SocialProfile/UserBoard',
	'position' => 'bottom',
);

$wgResourceModules['ext.socialprofile.userboard.css'] = array(
	'styles' => 'UserBoard.css',
	'localBasePath' => __DIR__ . '/UserBoard',
	'remoteExtPath' => 'SocialProfile/UserBoard',
	'position' => 'top' // just in case
);

$wgResourceModules['ext.socialprofile.globalsearch.css'] = array(
	'styles' => 'globalsearch.css',
	'localBasePath' => __DIR__ . '/GlobalSearch',
	'remoteExtPath' => 'SocialProfile/GlobalSearch',
	'position' => 'top' // just in case
);

$wgResourceModules['ext.socialprofile.userinfo.css'] = array(
	'styles' => 'userinfo.css',
	'localBasePath' => __DIR__ . '/CompleteUserInfo',
	'remoteExtPath' => 'SocialProfile/CompleteUserInfo',
	'position' => 'top' // just in case
);

$wgResourceModules['ext.socialprofile.userboard.boardblast.css'] = array(
	'styles' => 'BoardBlast.css',
	'localBasePath' => __DIR__ . '/UserBoard',
	'remoteExtPath' => 'SocialProfile/UserBoard',
	'position' => 'top' // just in case
);

$wgResourceModules['ext.socialprofile.userboard.boardblast.js'] = array(
	'scripts' => 'BoardBlast.js',
	'messages' => array(
		'boardblast-js-sending', 'boardblast-js-error-missing-message',
		'boardblast-js-error-missing-user'
	),
	'localBasePath' => __DIR__ . '/UserBoard',
	'remoteExtPath' => 'SocialProfile/UserBoard',
	'position' => 'bottom',
);

//uploadfiles
$wgResourceModules['ext.socialprofile.uploadfiles.css'] = array(
	'styles' => 'uploadfiles.css',
	'localBasePath' => __DIR__ . '/UploadFiles',
	'remoteExtPath' => 'SocialProfile/UploadFiles',
	'position' => 'top',
);

$wgResourceModules['ext.socialprofile.uploadfiles.js'] = array(
	'scripts' => 'uploadfiles.js',
	'dependencies' => 'mediawiki.notification',
	'localBasePath' => __DIR__ . '/UploadFiles',
	'remoteExtPath' => 'SocialProfile/UploadFiles',
	'position' => 'top',
);

//specialVideos
$wgResourceModules['ext.socialprofile.videos.css'] = array(
	'styles' => 'videos.css',
	'localBasePath' => __DIR__ . '/Videos',
	'remoteExtPath' => 'SocialProfile/Videos',
	'position' => 'top'

);

$wgResourceModules['ext.socialprofile.videos.js'] = array(
	'scripts' => 'videos.js',
	'dependencies' => array(
		'mediawiki.notification',
		'skins.bootstrapmediawiki.videohandler'
	),
	'localBasePath' => __DIR__ . '/Videos',
	'remoteExtPath' => 'SocialProfile/Videos',
	'position' => 'bottom'
);

$wgResourceModules['ext.socialprofile.videopage.js'] = array(
	'scripts' => 'VideoPage.js',
	'dependencies' => 'mediawiki.notification',
	'localBasePath' => __DIR__ . '/Videos',
	'remoteExtPath' => 'SocialProfile/Videos',
	'position' => 'bottom'
);


//familytree
$wgResourceModules['ext.socialprofile.familytree.css'] = array(
	'styles' => 'FamilyTree.css',
	'localBasePath' => __DIR__ . '/FamilyTree',
	'remoteExtPath' => 'SocialProfile/FamilyTree',
	'position' => 'bottom' // just in case
);

$wgResourceModules['ext.socialprofile.familytree.js'] = array(
	'scripts' => 'FamilyTree.js',
	'dependencies' => 'mediawiki.notification',
	'localBasePath' => __DIR__ . '/FamilyTree',
	'remoteExtPath' => 'SocialProfile/FamilyTree',
	'position' => 'bottom',
);

// UserRelationship
$wgResourceModules['ext.socialprofile.userrelationship.css'] = array(
	'styles' => 'UserRelationship.css',
	'localBasePath' => __DIR__ . '/UserRelationship',
	'remoteExtPath' => 'SocialProfile/UserRelationship',
	'position' => 'top' // just in case
);

$wgResourceModules['ext.socialprofile.userrelationship.js'] = array(
	'scripts' => 'UserRelationship.js',
	'localBasePath' => __DIR__ . '/UserRelationship',
	'remoteExtPath' => 'SocialProfile/UserRelationship',
	'position' => 'bottom',
);

// UserStats
$wgResourceModules['ext.socialprofile.userstats.css'] = array(
	'styles' => 'TopList.css',
	'localBasePath' => __DIR__ . '/UserStats',
	'remoteExtPath' => 'SocialProfile/UserStats',
	'position' => 'top' // just in case
);

// UserSiteFollows
$wgResourceModules['ext.socialprofile.usersitefollows.js'] = array(
	'scripts' => 'UserSiteFollows.js',
	'localBasePath' => __DIR__ . '/UserSiteFollows',
	'remoteExtPath' => 'SocialProfile/UserSiteFollows',
	'position' => 'bottom',
);

// UserUserFollows
$wgResourceModules['ext.socialprofile.useruserfollows.js'] = array(
	'scripts' => 'UserUserFollows.js',
	'localBasePath' => __DIR__ . '/UserUserFollows',
	'remoteExtPath' => 'SocialProfile/UserUserFollows',
	'position' => 'bottom',
);
// UserUserFollows
$wgResourceModules['ext.socialprofile.useruserfollows.css'] = array(
	'styles' => 'UserUserFollows.css',
	'localBasePath' => __DIR__ . '/UserUserFollows',
	'remoteExtPath' => 'SocialProfile/UserUserFollows',
	'position' => 'top',
);
// SiteActivity
$wgResourceModules['ext.socialprofile.siteactivity.css'] = array(
	'styles' => 'SiteActivityHook.css',
	'localBasePath' => __DIR__ . '/UserActivity',
	'remoteExtPath' => 'SocialProfile/UserActivity',
	'position' => 'top' // just in case
);
$wgResourceModules['ext.socialprofile.siteactivity.js'] = array(
	'scripts' => 'SiteActivityHook.js',
	'localBasePath' => __DIR__ . '/UserActivity',
	'remoteExtPath' => 'SocialProfile/UserActivity',
	'position' => 'bottom' // just in case
);

// AdminDashboard
$wgResourceModules['ext.socialprofile.admindashboard.css'] = array(
	'styles' => array(
					'timeline.css',
        			'../../UserUploadAvatar/cropper.min.css',
	    			'../../UserUploadAvatar/main.css'
				),
	'dependencies' => array(
	                'skins.bootstrapmediawiki.top'
	                ),
	'localBasePath' => __DIR__ . '/AdminDashboard/css',
	'remoteExtPath' => 'SocialProfile/AdminDashboard/css',
	'position' => 'top' // just in case
);
// AdminDashboard
$wgResourceModules['ext.socialprofile.admindashboard.js'] = array(
	'scripts' => array(
					// 'morris.min.js',
					// 'raphael-min.js',
					// 'flot-data.js',
					'morris-data.js',
					'sb-admin-2.js',
					'../../UserUploadAvatar/cropper.min.js',
        			'../../UserUploadAvatar/main.js',
        			'admin.js'
				),
	'localBasePath' => __DIR__ . '/AdminDashboard/js',
	'remoteExtPath' => 'SocialProfile/AdminDashboard/js',
	'position' => 'bottom',
);
// UserAcitvity
$wgResourceModules['ext.socialprofile.useractivity.js'] = array(
	'scripts' => array(
					'UserActivity.js',
				),
	'dependencies' => 'skins.bootstrapmediawiki.bottom',
	'localBasePath' => __DIR__ . '/UserActivity',
	'remoteExtPath' => 'SocialProfile/UserActivity',
	'position' => 'bottom',
);
// qq&weibo Login
$wgResourceModules['ext.socialprofile.qqLogin.js'] = array(
	'scripts' => array(
					'qqLogin.js',
				),
	'dependencies' => array('mediawiki.cookie','mediawiki.api.options','mediawiki.notification'),
	'localBasePath' => __DIR__ . '/CompleteUserInfo',
	'remoteExtPath' => 'SocialProfile/CompleteUserInfo',
	'position' => 'bottom',
);
$wgResourceModules['ext.socialprofile.inviteuser.js'] = array(
	'scripts' => array(
					'js/clean-blog.min.js',
				),
	'localBasePath' => __DIR__ . '/AdminDashboard',
	'remoteExtPath' => 'SocialProfile/AdminDashboard',
	'position' => 'bottom',
);
$wgResourceModules['ext.socialprofile.inviteuser.css'] = array(
	'styles' => array(
					'css/clean-blog.min.css',
				),
	'localBasePath' => __DIR__ . '/AdminDashboard',
	'remoteExtPath' => 'SocialProfile/AdminDashboard',
	'position' => 'top',
);
$wgResourceModules['ext.guidedTour.tour.newuser'] = array(
	'scripts' => 'tours/newuser.js',
	// Note that you should have an explicit dependency on the GuidedTour library.
	'dependencies' => array('ext.guidedTour', 'ext.guidedTour.lib', 'ext.guidedTour.launcher'),
	'messages' => array(
		// This is a list of messages for your tour.
		// You can include messages from your regular extension,
		// or MediaWiki core, and you will also need messages specifically
		// for the title, description and buttons of your tour.
		// Note the naming scheme for tour-specific messages is based on your tour name.
		// You do not need to add messages that are built-in to GuidedTour, like the okay button.
		'guidedtour-tour-newuser-welcome-title',
		'guidedtour-tour-newuser-welcome-description',
		'guidedtour-tour-newuser-edit-title',
		'guidedtour-tour-newuser-edit-description',
		'guidedtour-tour-newuser-veedit-title',
		'guidedtour-tour-newuser-edit-page-description',
		'guidedtour-tour-newuser-showinfobox-title',
		'guidedtour-tour-newuser-showinfobox-description',
		'guidedtour-tour-newuser-showtoc-title',
		'guidedtour-tour-newuser-showtoc-description',
		'guidedtour-tour-newuser-showsidebar-title',
		'guidedtour-tour-newuser-showsidebar-description',
		'guidedtour-tour-firstedit-preview-title',
		'guidedtour-tour-firstedit-preview-description',
		'guidedtour-tour-firstedit-save-title',
		'guidedtour-tour-firstedit-save-description',
		'guidedtour-tour-firstedit-save-title',
		'guidedtour-tour-firsteditve-save-description',
		'guidedtour-tour-firstedit-save-description',
	),
        // localBasePath and remoteExtPath should match what your extension normally uses.
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'SocialProfile/resources',
	'position' => 'bottom',
);
// End ResourceLoader stuff

if( !defined( 'NS_USER_WIKI' ) ) {
	define( 'NS_USER_WIKI', 200 );
}

if( !defined( 'NS_USER_WIKI_TALK' ) ) {
	define( 'NS_USER_WIKI_TALK', 201 );
}

if( !defined( 'NS_USER_PROFILE' ) ) {
	define( 'NS_USER_PROFILE', 202 );
}

if( !defined( 'NS_USER_PROFILE_TALK' ) ) {
	define( 'NS_USER_PROFILE_TALK', 203 );
}

//API
$wgAutoloadClasses['ApiAvatarSubmit'] = __DIR__ . '/UserProfile/api/AvatarSubmit.api.php';
$wgAPIModules['avatarsubmit'] = 'ApiAvatarSubmit';
$wgAutoloadClasses['ApiAvatarShow'] = __DIR__ . '/UserProfile/api/AvatarShow.api.php';
$wgAPIModules['avatarshow'] = 'ApiAvatarShow';

//Log
global $wgLogTypes, $wgLogNames, $wgLogHeaders, $wgLogActions;
$wgLogTypes[]                    = 'AdminDashboard';
$wgLogNames['AdminDashboard']           = 'admindashboardpage';
$wgLogHeaders['AdminDashboard']         = 'admindashboardpagetext';
$wgLogActions['AdminDashboard/addDescription'] = 'admindashboardlogentry';
$wgLogActions['AdminDashboard/setSiteProperty'] = 'admindashboardlogentry';
