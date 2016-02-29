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
    $output = '';
    $out->addModuleStyles('ext.socialprofile.videos.css');
    $out->addModules( 'ext.socialprofile.videos.js' );
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
    $page = empty( $request->getVal('page') )?1:$request->getVal('page');
  	$per_page = 10;
    $line = wfMessage('videos-info')->parse();
  	$allVideo = UploadVideos::getAllVideoInfo();
    $star = $per_page*($page-1);
    $res_arr = array_slice($allVideo, $star, $per_page);
  	$output .= "<div class='gray'>".$line."</div>";
  	$output .= "视频列表";
    $output .= '<div class="clear"><ul class="video-list">';
    foreach ($res_arr as $key => $value) {
        $title = Title::newFromID($value['rev_page_id']);
        $userPage = Title::makeTitle( NS_USER, $value['rev_upload_user'] );
        $userPageURL = htmlspecialchars( $userPage->getFullURL() );
        $file = LocalFile::newFromTitle($title, new LocalRepo($wgLocalFileRepo));
        $output .='<li>
        <a href="#" class="video video-thumbnail image lightbox hide-play fluid medium "><img class="video-player" src="'.htmlspecialchars( $file->createThumb(200,100) ).'" alt="'.$value['rev_video_title'].'" data-video="'.$value['rev_video_player_url'].'" /><span class="video-duration" itemprop="duration">'.gmstrftime('%H:%M:%S',$value['rev_video_duration']).'</span><span class="play-circle"></span></a>   
         
        <div class="info">
         <a href="'.htmlspecialchars( $file->getDescriptionUrl() ).'">'.$value['rev_video_title'].'</a><br>
          用户:<a href="'.$userPageURL.'" >'.$value['rev_upload_user'].'</a><br>
          <span class="upload-date">上传时间:'.$value['rev_upload_date'].'</span>
        </div>
            </li>';
    }
    $output .= '</ul></div>';
    /**
     * Build next/prev nav
     */
    // $pcount = $rel->getGiftCountByUsername( $user_name );
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
            // 'user' => $user_name,
            // 'rel_type' => $rel_type,
            'page' => ( $page - 1 )
          )
        ) . '</li>';
      }

      if ( ( $pcount % $per_page ) != 0 ) {
        $numofpages++;
      }
      if ( $numofpages >= 9 && $page < $pcount ) {
        $numofpages = 9 + $page;
      }
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
              // 'user' => $user_name,
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
              // 'user' => $user_name,
              // 'rel_type' => $rel_type,
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
