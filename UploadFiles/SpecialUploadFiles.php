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
		$output .="<form id='uploadfiles' enctype='multipart/form-data' method='post'><input type='file' id='file' name='file'></form> <div class='btn' id='upload-btn'>上传</div>";
		$out->addHTML( $output );
        $out->addModuleStyles('ext.socialprofile.uploadfiles.css');
        $out->addModules( 'ext.socialprofile.uploadfiles.js' );
	}



}

?>