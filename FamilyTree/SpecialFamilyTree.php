<?php

/**
*  special page to add new festival gift
*/
class SpecialFamilyTree extends SpecialPage{
	
	function __construct(){

		parent::__construct( 'FamilyTree' );
	
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	
	function getGroupName() {
		return 'wiki';
	}

	/**
	 * Show the special page
	 *
	 */
	public function execute($params){
		global $wgUser;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$this->setHeaders();
		$output = "";
		$output .= "<h1>FamilyTree</h1>";
		$out->addHTML( $output );
	}


}

?>