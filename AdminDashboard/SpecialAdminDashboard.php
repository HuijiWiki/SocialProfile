<?php
/**
 * A special page for sysop to read news and manage site.
 *
 * @file
 * @ingroup Extensions
 */

class SpecialAdminDashboard extends UnlistedSpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'AdminDashboard' );
	}

	/**
	 * Show the special page
	 *
	 * @param $par Mixed: parameter passed to the page or null
	 */
	public function execute( $par ) {
		global $wgUploadPath, $wgUser, $wgHuijiPrefix;
		$templateParser = new TemplateParser(  __DIR__ . '/pages' );
		$out = $this->getOutput();
		$user = $this->getUser();
		if ( !$user->isAllowed( 'AdminDashboard' ) ) {
			$out->permissionRequired( 'AdminDashboard' );
			return;
		}
		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModules('ext.socialprofile.userprofile.css');	
		$out->addModules( 'ext.socialprofile.admindashboard.css' );
		
		// Add js and message
		// $out->addModules( 'skins.bootstrapmediawiki.huiji.getRecordsInterface.js' );
		$out->addModules( 'ext.socialprofile.admindashboard.js' );
		$out->addModules('ext.socialprofile.userprofile.js');

		$output = ''; // Prevent E_NOTICE
	    	$yesterday = date("Y-m-d",strtotime("-1 day"));
		$totaledit = SiteStats::edits();
		$ueb = new UserEditBox();
		$rankInfo = AllSitesInfo::getAllSitesRankData( $wgHuijiPrefix, $yesterday );
		$usf = new UserSiteFollow();
		$follows = $usf->getSiteFollowers( '',$wgHuijiPrefix );
		// print_r($follows);
		$followCount = count($follows);
		if($followCount >= 8){
			$follows = array_slice($follows, 0, 8);
			$display = '';
		}else{
			$display = 'none';
		}
		$newFollow = array();
		foreach ($follows as $value) {
			$arr['user_name'] = $value['user_name'];
			$userPage = Title::makeTitle( NS_USER, $value['user_name'] );
			$arr['user_url'] = htmlspecialchars( $userPage->getFullURL() );
			$arr['follow_date'] = wfMessage( 'comments-time-ago', HuijiFunctions::getTimeAgo( strtotime( $value['follow_date'] ) ) )->text();
			$newFollow[] = $arr;
		}
		
		$sentToAll = SpecialPage::getTitleFor( 'SendToFollowers' )->getFullURL();
		$showMore = SpecialPage::getTitleFor( 'EditRank' )->getFullURL();
		$rightsManage = SpecialPage::getTitleFor( '用户权限' )->getFullURL();
		$blockUsers = SpecialPage::getTitleFor( '封禁' )->getFullURL();
		$freezeUsers = SpecialPage::getTitleFor( '解除封禁' )->getFullURL();
		$replaceText = SpecialPage::getTitleFor( '替换文本' )->getFullURL();
		$siteRankPage = SpecialPage::getTitleFor( 'SiteRank' )->getFullURL();
		$allSpecial = SpecialPage::getTitleFor( '特殊页面' )->getFullURL();
		$addEmote = Message::newFromKey('comments-add-emoji-emote')->getTitle()->getFullURL();
		$changePageTitle = Message::newFromKey('Pagetitle')->getTitle()->getFullURL();
		$changeMainpageTitle = Message::newFromKey('MediaWiki:Pagetitle-view-mainpage')->getTitle()->getFullURL();
		$siteAvatar = (new wSiteAvatar($wgHuijiPrefix, 'l'))->getAvatarHtml();
		$token = $user->getEditToken();
		if(is_null($newFollow)){
			$newFollow = false;
		}
		$output .= $templateParser->processTemplate(
				    'admin_index',
				    array(
				    	'siteRank' => isset($rankInfo[0]['site_rank'])?$rankInfo[0]['site_rank']:'暂无',
				    	'siteScore' => isset($rankInfo[0]['site_score'])?$rankInfo[0]['site_score']:'暂无',
				        'yesterdayCount' => UserSiteFollow::getFollowerCountOneday( $wgHuijiPrefix, $yesterday ),
				        'totalCount' => UserSiteFollow::getFollowerCount( $wgHuijiPrefix ),
				        'yesterdayEdit' => $ueb->getSiteEditCount( '', $wgHuijiPrefix, $yesterday, $yesterday ),
				        'totalEdit' => $totaledit,
				        'totalView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, '', '' ),
				        'yesterdayView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, $yesterday, $yesterday ),
				        'newFollow' => $newFollow,
				        'sendToAll' => $sentToAll,
				        'showMore' => $showMore,
				        'display' => $display,
				        'rightsManage' =>$rightsManage,
				        'blockUsers' =>$blockUsers,
				        'freezeUsers' =>$freezeUsers,
				        'replaceText' =>$replaceText,
				        'siteRankPage' =>$siteRankPage,
				        'allSpecial' =>$allSpecial,
				        'siteAvatar' =>$siteAvatar,
				        'addEmote' => $addEmote,
				        'changePageTitle' => $changePageTitle,
				        'changeMainpageTitle' => $changeMainpageTitle,
				        'token' => $token,
				    )
				);
		$out->addHtml($output);
	}
}
