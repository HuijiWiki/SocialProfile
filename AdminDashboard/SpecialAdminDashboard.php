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
		// Add Less
		$out->addModuleStyles( 'ext.socialprofile.admindashboard.less' );
		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.admindashboard.css' );		
		// Add js and message
		// $out->addModules( 'skin.bootstrapmediawiki.huiji.getRecordsInterface.js' );
		$out->addModules( 'ext.socialprofile.admindashboard.js' );

		$output = ''; // Prevent E_NOTICE
	    $yesterday = date("Y-m-d",strtotime("-1 day"));
	    $dbr = wfGetDB( DB_SLAVE );
        $counter = new SiteStatsInit( $dbr );
		$totaledit = $counter->edits();
		$ueb = new UserEditBox();
		$rankInfo = AllSitesInfo::getAllSitesRankData( $wgHuijiPrefix, $yesterday );
		$usf = new UserSiteFollow();
		$follows = $usf->getSiteFollowedUser( '',$wgHuijiPrefix );
		// print_r($follows);
		$followCount = count($follows);
		if($followCount >= 8){
			$follows = array_slice($follows, 0, 8);
		}
		$newFollow = array();
		foreach ($follows as $value) {
			$arr['user_name'] = $value['user_name'];
			$arr['follow_date'] = wfMessage( 'comments-time-ago', CommentFunctions::getTimeAgo( strtotime( $value['follow_date'] ) ) )->text();
			$newFollow[] = $arr;
		}
		$sentToAll = SpecialPage::getTitleFor( 'SendToFollowers' );
		$showMore = SpecialPage::getTitleFor( 'EditRank' );
		$output .= $templateParser->processTemplate(
				    'admin_index',
				    array(
				    	'siteRank' => isset($rankInfo[0]['site_rank'])?$rankInfo[0]['site_rank']:'暂无',
				    	'siteScore' => isset($rankInfo[0]['site_score'])?$rankInfo[0]['site_score']:'暂无',
				        'yesterdayCount' => UserSiteFollow::getSiteCountOneday( $wgHuijiPrefix, $yesterday ),
				        'totalCount' => UserSiteFollow::getSiteCount( $wgHuijiPrefix ),
				        'yesterdayEdit' => $ueb->getSiteEditCount( '', $wgHuijiPrefix, $yesterday, $yesterday ),
				        'totalEdit' => $totaledit,
				        'totalView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, '', '' ),
				        'yesterdayView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, $yesterday, $yesterday ),
				        'newFollow' => $newFollow,
				        'sendToAll' => $sentToAll,
				        'showMore' => $showMore,
				    )
				);
		$out->addHtml($output);
	}
}