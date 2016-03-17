<?php   
/**
* uploadfiles
*/
class SpecialVideosList extends SpecialPage{

  function __construct(){
  	parent::__construct( 'VideosList' );
  }

  function getGroupName() {
  	return 'wiki';
  }

  public function execute( $params ) {
    global $wgUser, $wgLocalFileRepo;
    // Set the page title, robot policies, etc.
    $this->setHeaders();
    $out = $this->getOutput();
    $request = $this->getRequest();
    $type = empty($request->getVal( 'type' ))?0:$request->getVal( 'type' );

    $output = '';
    $out->addModuleStyles('ext.socialprofile.videos.css');
    $out->addModules( 'ext.socialprofile.videos.js' );
    /**
     * Redirect Non-logged in users to Login Page
     * $login = SpecialPage::getTitleFor( 'Userlogin' );
      *$login->getFullURL( 'returnto=Special:SystemGiftList' )
     * It will automatically return them to the ViewSystemGifts page
     */
    $out->addHTML(UploadVideos::dropDown());
    $login = SpecialPage::getTitleFor( 'Userlogin' );
    if ( $wgUser->getID() == 0 || $wgUser->getName() == '' ) {
      $output .= '请先<a class="login-in" data-toggle="modal" data-target=".user-login">登录</a>或<a href="'.$login->getFullURL( 'type=signup' ).'">创建用户</a>。';
      $out->addHTML( $output );
      return false;
    }
    $request = $this->getRequest();
    $page = empty( $request->getVal('page') )?1:$request->getVal('page');
  	$per_page = 10;
    $line = wfMessage('videos-info')->parse();
  	$allVideo = UploadVideos::getAllVideoInfo( $type );
    $star = $per_page*($page-1);
    $res_arr = array_slice($allVideo, $star, $per_page);
  	$output .= "<div class='gray'>".$line."</div>";
    $output .= '<div class="clear">';
    if ( $type == 0 ) {
       $clas = 'video-list';
    }elseif( $type == 1 ){
      $clas = 'audio-list';
    }else{
      $target = SpecialPage::getTitleFor( 'Videoslist' );
      $output .= '您的URL出错了，访问'.Linker::LinkKnown($target, '视频文件列表</a>', array(), array()).'or'.Linker::LinkKnown($target, '音频文件列表</a>', array(), array( 'type'=>1 )).'</div>';
      $out->addHTML( $output );
      return false;
    }
    if ( empty( $allVideo ) ) {
        $target = SpecialPage::getTitleFor( 'Videos' );
        $output .= '<b class="gray">暂时还没有媒体文件，去'.Linker::LinkKnown($target, '上传</a>', array(), array()).'</b>';
    }else{
        $output .= '<ul class="'.$clas.'">';
        foreach ($res_arr as $key => $value) {
            $vt = VideoTitle::newFromID($value['rev_page_id']);
            $userPage = Title::makeTitle( NS_USER, $vt->getAddedByUser() );
            $userPageURL = htmlspecialchars( $userPage->getFullURL() );
            $file = LocalFile::newFromTitle($vt, new LocalRepo($wgLocalFileRepo));
            $output .='<li>'.$vt->getThumbnail().'
            <div class="info">
             <a href="'.htmlspecialchars( $file->getDescriptionUrl() ).'">'.$vt->getText().'</a><br>
              用户:<a href="'.$userPageURL.'" >'.$vt->getAddedByUser().'</a><br>
              <span class="upload-date">上传时间:'.$vt->getAddedOnDate().'</span>
            </div>
                </li>';
        }
        $output .= '</ul>';
    }
    $output .= '</div>';
    /**
     * Build next/prev nav
     */
    $pcount = count($allVideo);
    $numofpages = $pcount / $per_page;

    $page_link = $this->getPageTitle();

    if ( $numofpages > 1 ) {
      $output .= '<div class="page-nav-wrapper"><nav class="page-nav pagination">';

      if ( $page > 1 ) {
        $output .= '<li>'.Linker::link(
          $page_link,
          '<span aria-hidden="true">&laquo;</span>',
          array(),
          array(
            'page' => ( $page - 1 )
          )
        ) . '</li>';
      }

      if ( ( $pcount % $per_page ) != 0 ) {
        $numofpages++;
      }
      // if ( $numofpages >= 9 && $page < $pcount ) {
      //   $numofpages = 9 + $page;
      // }
      // if ( $numofpages >= ( $total / $per_page ) ) {
      //  $numofpages = ( $total / $per_page ) + 1;
      // }

      for ( $i = 1; $i <= $numofpages; $i++ ) {
        if ( $i == $page ) {
          $output .= ( '<li class="active"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>' );
        } else {
          $output .= '<li>' .Linker::link(
            $page_link,
            $i,
            array(),
            array(
              'page' => $i
            )
          ).'</li>';
        }
      }

      if ( ( $pcount - ( $per_page * $page ) ) > 0 ) {
        $output .= '<li>' .
          Linker::link(
            $page_link,
            '<span aria-hidden="true">&raquo;</span>',
            array(),
            array(
              'page' => ( $page + 1 )
            )
          ).'</li>';  
      }

      $output .= '</nav></div>';
    }
  	$out->addHTML( $output );

  }



  }

  ?>
