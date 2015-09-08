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
		global $wgUploadPath;

		$out = $this->getOutput();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$out->addScript( 'http://cdn.jsdelivr.net/jquery.flot/0.8.4/jquery.flot.min.js' );
		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.admindashboard.less' );
		// Add js and messages
		$out->addModules( 'ext.socialprofile.admindashboard.js' );

		$output = ''; // Prevent E_NOTICE
		$output .= file_get_contents(__DIR__.'/pages/index.php');
		$out->addHtml($output);
	}
}