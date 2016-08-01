<?php   
/**
* uploadfiles
*/
class SpecialVideos extends SpecialPage{
    
    function __construct(){
        parent::__construct( 'Videos', 'upload' );
    }

    function getGroupName() {
        return 'wiki';
    }

    public function execute( $params ) {
        global $wgUser, $wgLocalFileRepo, $wgContLang;
        // Set the page title, robot policies, etc.
        $this->setHeaders();
        $out = $this->getOutput();
        $output = '';
        $out->addModuleStyles('ext.socialprofile.videos.css');
        $out->addModules( array(
            'skins.bootstrapmediawiki.videohandler',
            'ext.socialprofile.videos.js'
            ) 
        );
        /**
         * Redirect Non-logged in users to Login Page
         * $login = SpecialPage::getTitleFor( 'Userlogin' );
          *$login->getFullURL( 'returnto=Special:SystemGiftList' )
         * It will automatically return them to the ViewSystemGifts page
         */
        
        $login = SpecialPage::getTitleFor( 'Userlogin' );
        if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
            $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
            $out->addHTML( $output );
            return false;
        }
        $request = $this->getRequest();
        $reupload = empty($request->getVal('reupload'))?null:$request->getVal('reupload');
        $filename = empty($request->getVal('filename'))?null:$request->getVal('filename');
        $title = wfMessage('videos-info-title')->parse();
        $line = wfMessage('videos-info')->parse();
            
            $output .="<div class='gray'>".$line."</div>";
            $output .="<div>URL<input type='text' class='video-url' id='uploadvideos' name='uploadvideos'><br>";
            if( !empty($reupload) ){
                if( !$wgUser->isAllowed('reupload') ){
                    throw new PermissionsError( 'reupload' );
                }
                $output .="文件名<input type='text' class='video-name' name='upload-video-name' readonly='true' value='".$filename."'>
                <input  type='hidden' class='upload-new-revision' value='reupload'>";
            }else{
                $output .="文件名<input type='text' class='video-name' name='upload-video-name'>";
            }
            $output .="</div><div class='upload-video-btn'><button class='btn mw-ui-button mw-ui-constructive' id='upload-video-btn' disabled>添加</button></div>";

            $out->addHTML( $output );
 

    }



}

?>
