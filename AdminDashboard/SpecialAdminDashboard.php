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

		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$out->addScript( '<script src="http://echarts.baidu.com/build/dist/echarts.js"></script>' );
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
		$output .= $templateParser->processTemplate(
				    'admin_index',
				    array(
				        'yesterdayCount' => UserSiteFollow::getSiteCountOnedayDB( $wgHuijiPrefix, $yesterday ),
				        'totalCount' => UserSiteFollow::getSiteCount( $wgHuijiPrefix ),
				        'yesterdayEdit' => $ueb->getSiteEditCount( '', $wgHuijiPrefix, $yesterday, $yesterday ),
				        'totalEdit' => $totaledit,
				        'totalView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, '', '' ),
				        'yesterdayView' => $ueb->getSiteViewCount( -1, $wgHuijiPrefix, $yesterday, $yesterday )
				    )
				);
		$out->addHtml($output);
	}
}