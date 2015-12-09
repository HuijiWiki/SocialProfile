<?php
class SpecialRandomwiki extends SpecialPage {
	public function __construct( $name = 'Randomwiki' ) {
		parent::__construct( $name );
	}

	public function execute( $par ) {
		global $wgCookieDomain, $wgHuijiSuffix;
		$prefix = HuijiPrefix::getRandomPrefix();

		if ( is_null ($prefix) ) {
			$this->setHeader();
			$this->getOutput()->addWikiMsg( strtolower( $this->getName() ) . '-nopages');
			return;
		}
		$this->getOutput()->redirect( 'http://'.$prefix.$wgHuijiSuffix );
	}
	function getGroupName() {
    		return 'redirects';
	}

}
