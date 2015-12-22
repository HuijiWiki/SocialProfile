<?php   
/**
* uploadfiles
*/
class SpecialUploadFiles extends SpecialPage{
	
	function __construct(){
		parent::__construct( 'UploadFiles' );
	}

	function getGroupName() {
		return 'wiki';
	}

	public function execute( $params ) {
		$out = $this->getOutput();
		$output = '';
		$output .="<h1>文件上传</h1>";
		$out->addHTML( $output );

	}



}

?>