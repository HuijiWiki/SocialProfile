<?php   
/**
* uploadfiles
*/
class SpecialUploadFiles extends SpecialPage{
	
	function __construct(){
		parent::__construct( 'UploadFiles', 'upload' );
	}

	function getGroupName() {
		return 'wiki';
	}

	public function execute( $params ) {
    global $wgFileExtensions, $wgUser;
    $this->checkReadonly();   
    // Set the page title, robot policies, etc.
  
    $this->setHeaders();
    $out = $this->getOutput();
    $output = '';
    /**
     * Redirect Non-logged in users to Login Page
     * $login = SpecialPage::getTitleFor( 'Userlogin' );
      *$login->getFullURL( 'returnto=Special:SystemGiftList' )
     * It will automatically return them to the ViewSystemGifts page
     */
    $login = SpecialPage::getTitleFor( 'Userlogin' );
    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
      $output .= '请先<a class="login-in need-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
      $out->addHTML( $output );
      return false;
    }
    $this->checkPermissions();
	
    $title = wfMessage('uploadfiles-info-title')->parse();
    $subtitle = wfMessage('uploadfiles-info-can-be-modified')->parse();
    $line = wfMessage('uploadfiles-info')->parse();
		
		$output .="<h4>".$title."</h4><span class='gray'>".$subtitle."</span>";
		$output .="<div class='gray'>".$line."</div>";
    $output .= "<div class='gray'><p>允许上传的文件类型为".implode("，",$wgFileExtensions)."</div>";
		$output .="<form id='uploadfiles' enctype='multipart/form-data' method='post' class='clear'>
		                <input id='hiddenText' type='text' style='display:none' >
		                <input type='file' id='file' name='file' multiple='multiple'>
		                <div id='drag-area'>
                            <p>把要上传的文件拖动到此处<b>或</b></p>
                            <span class='file-btn'>选择电脑上的文件</span>
		                </div>
		          </form>
		          <div class='clear'><button class='btn mw-ui-button mw-ui-constructive' id='upload-btn' data-loading-text='上传中...'>上传</button></div>";
		$output .= '<div class="modal fade img-description" tabindex="-1" role="dialog" aria-labelledby="imgDescriptionModalLabel" aria-hidden="true">
                       <div class="modal-dialog">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                                   <h4 class="modal-title" id="gridSystemModalLabel">批量添加文件描述（请注意保存）</h4>
                               </div>
                               <div class="modal-body">
                                   <div class="form-group">
                                       <label for="des-text" class="control-label">描述:</label>
                                       <textarea class="form-control" id="des-text"></textarea>
                                       <label for="des-category" class="control-label">分类:(使用半角英文","隔开)</label>
                                       <input class="form-control" type="text" id="des-category">
                                   </div>
                               </div>
                               <div class="modal-footer">
                                   <button type="button" class="btn btn-primary des-save">保存</button>
                               </div>
                           </div>
                       </div>
                   </div>';
        $output .= '<div class="modal fade self-img-description" tabindex="-1" role="dialog" aria-labelledby="imgDescriptionModalLabel" aria-hidden="true">
                               <div class="modal-dialog">
                                   <div class="modal-content">
                                       <div class="modal-header">
                                           <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                                           <h4 class="modal-title" id="gridSystemModalLabel">添加文件描述（请注意保存）</h4>
                                       </div>
                                       <div class="modal-body">
                                           <div class="form-group">
                                               <label for="self-des-text" class="control-label">描述:</label>
                                               <textarea class="form-control" id="self-des-text"></textarea>
                                               <label for="self-des-category" class="control-label">分类:(使用半角英文","隔开)</label>
                                               <input class="form-control" type="text" id="self-des-category">
                                           </div>
                                       </div>
                                       <div class="modal-footer">
                                           <button type="button" class="btn btn-primary self-des-save">保存</button>
                                       </div>
                                   </div>
                               </div>
                           </div>';
		$out->addHTML( $output );
    $out->addModuleStyles('ext.socialprofile.uploadfiles.css');
    $out->addModules( 'ext.socialprofile.uploadfiles.js' );
	}



}

?>