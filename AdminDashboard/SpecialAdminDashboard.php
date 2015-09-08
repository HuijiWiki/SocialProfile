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
		// Add Less
		$out->addModuleStyles( 'ext.socialprofile.admindashboard.less' );
		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.admindashboard.css' );		
		// Add js and messages
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
				        'yesterdayEdit' => $ueb->getSiteEditCountOneday( $wgHuijiPrefix, $yesterday ),
				        'totalEdit' => $totaledit,
				        'totalView' => $ueb->getSiteViewCountTotal( $wgHuijiPrefix ),
				        'yesterdayView' => $ueb->getSiteViewCountOneday( $wgHuijiPrefix,$yesterday )
				    )
				);
		$out->addHtml($output);
	}
}