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
		// Set the page title, robot policies, etc.
		$this->setHeaders();
		$out = $this->getOutput();
		$output = '';
		$output .="<h1>图像上传</h1><h4>关于维基共享资源著作权的提示</h4><span class='gray'>（如果您是本维基的管理员，您可以点击<a>这里</a>编辑这段内容）</span>";
		$output .="<p class='gray'>您可以自由上传：</p><ul class='gray'><li>您自己的作品</li><li>著作权处于共有领域的作品</li><li>获得著作权所有人许可上传的作品</li><li>点击图片进行描述，点击名字进行修改</li></ul><p class='gray'>请不要上传他人创作的、著作权未知的图像。</p><p class='gray'>您还可以使用<a href='/wiki/Special:Upload'>旧版</a>进行上传</p>";
		$output .="<form id='uploadfiles' enctype='multipart/form-data' method='post' class='clear'>
		                <input id='hiddenText' type='text' style='display:none' >
		                <input type='file' id='file' name='file' multiple='multiple'>
		                <div id='drag-area'>
                            <p>把要上传的文件拖动到此处<b>或</b></p>
                            <span class='file-btn'>选择电脑上的图片</span>
		                </div>
		          </form>
		          <div class='clear'><div class='btn mw-ui-button mw-ui-constructive' id='upload-btn' data-loading-text='上传中...'>上传</div></div>";
		$output .= '<div class="modal fade img-description" tabindex="-1" role="dialog" aria-labelledby="imgDescriptionModalLabel" aria-hidden="true">
                       <div class="modal-dialog">
                           <div class="modal-content">
                               <div class="modal-header">
                                   <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                                   <h4 class="modal-title" id="gridSystemModalLabel">批量添加文件描述（请注意保存）</h4>
                               </div>
                               <div class="modal-body">
                                   <div class="form-group">
                                       <label for="message-text" class="control-label">描述:</label>
                                       <textarea class="form-control" id="des-text"></textarea>
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
                                               <label for="message-text" class="control-label">描述:</label>
                                               <textarea class="form-control" id="self-des-text"></textarea>
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
