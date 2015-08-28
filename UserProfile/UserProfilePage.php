<?php
/**
 * User profile Wiki Page
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class UserProfilePage extends Article {

	/**
	 * @var Title
	 */
	public $title = null;

	/**
	 * @var String: user name of the user whose profile we're viewing
	 */
	public $user_name;

	/**
	 * @var Integer: user ID of the user whose profile we're viewing
	 */
	public $user_id;

	/**
	 * @var User: User object representing the user whose profile we're viewing
	 */
	public $user;

	/**
	 * @var Boolean: is the current user the owner of the profile page?
	 */
	public $is_owner;

	/**
	 * @var Array: user profile data (interests, etc.) for the user whose
	 * profile we're viewing
	 */
	public $profile_data;

	/**
	 * Constructor
	 */
	function __construct( $title ) {
		global $wgUser,$wgHuijiPrefix;
		parent::__construct( $title );
		$this->user_name = $title->getText();
		$this->user_id = User::idFromName( $this->user_name );
		$this->user = User::newFromId( $this->user_id );
		$this->user->loadFromDatabase();

		$this->is_owner = ( $this->user_name == $wgUser->getName() );

		$profile = new UserProfile( $this->user_name );
		$this->profile_data = $profile->getProfile();
	}

	/**
	 * Is the current user the owner of the profile page?
	 * In other words, is the current user's username the same as that of the
	 * profile's owner's?
	 *
	 * @return Boolean
	 */
	function isOwner() {
		return $this->is_owner;
	}

	function view() {
		global $wgOut, $wgUser, $wgHuijiprefix, $wgOnlineStatusBarDefaultOffline, $wgOnlineStatusBarDefaultOnline;

		$wgOut->setPageTitle( $this->mTitle->getPrefixedText() );

		// No need to display noarticletext, we use our own message
		if ( !$this->user_id ) {
			parent::view();
			return '';
		}
		$staff = '';
		$bureaucrat = '';
		$sysop = '';
		$rollback = '';
		$autoconfirmed = '';
		if (in_array( 'staff', $this->user->getEffectiveGroups(true))){
			$staff = '<li>职员</li> ';
		}
		if (in_array( 'bureaucrat', $this->user->getEffectiveGroups(true))){
			$bureaucrat = '<li>行政员</li> ';
		}
		if (in_array( 'sysop', $this->user->getEffectiveGroups(true))){
			$sysop = '<li>管理员</li> ';
		}
		if (in_array( 'rollback', $this->user->getEffectiveGroups(true))){
			$rollback = '<li>回退员</li> ';
		}
		if (in_array( 'autoconfirmed', $this->user->getEffectiveGroups(true))){
			$autoconfirmed = '<li>自动确认用户</li> ';
		}
		$usf = new UserSiteFollow();
		$uuf = new UserUserFollow();
		$topFollowedSites = $usf->getTopFollowedSites( $this->user );
		$temp = array();
		$res = array();
		$count = array();

		foreach( $topFollowedSites as $key => $value ){
			// if ( $wgUser->isLoggedIn() ) {
			$user = User::newFromName( $this->user_name );
			// }
			$temp['url'] = 'http://'.$key.'.huiji.wiki';
			$temp['name'] = $value;
			$temp['count'] = UserStats::getSiteEditsCount($user,$key);
			$res[] = $temp;
		}
		
		//sort by edit num
		foreach ($res as $val) {
			$count[] = $val['count'];
		}
		array_multisort($count, SORT_DESC, $res);
		$userCount = UserSiteFollow::getUserCount($this->user);

		if ($this->isOwner()){
			$target = SpecialPage::getTitleFor('ViewFollows');
			$query = array('user' => $this->user_name, 'rel_type' => 1);
			$button1 = '<li class="mw-ui-button">'.Linker::LinkKnown($target, '<i class="fa fa-users"></i>朋友', array(), $query).'</li> ';
		} elseif ($uuf->checkUserUserFollow($wgUser, $this->user) ){
			$button1 = '<li id="user-user-follow" class="unfollow mw-ui-button"><a><i class="fa fa-minus-square-o"></i>取关</a></li> ';
		} else {
			$button1 = '<li id="user-user-follow" class="mw-ui-button"><i class="fa fa-plus-square-o"></i></i>关注</li> ';
		}
		if ($this->isOwner()){
			$target = SpecialPage::getTitleFor('ViewGifts');
			$query = array('user' => $this->user_name);
			$button2 = '<li class="mw-ui-button">'.Linker::LinkKnown($target, '<i class="fa fa-gift"></i>礼物</a>', array(), $query).'</li> ';
		} else {
			$target = SpecialPage::getTitleFor( 'GiveGift' );
			$query = array('user' => $this->user_name);
			$button2 = '<li class="mw-ui-button">'.Linker::LinkKnown($target, '<i class="fa fa-gift"></i>赠送</a>', array(), $query).'</li> ';
		}
		$contributions = SpecialPage::getTitleFor( 'Contributions' );
		$watchlist = SpecialPage::getTitleFor('Watchlist');
		$send_message = SpecialPage::getTitleFor('UserBoard');
		$user_safe = urlencode( $this->user );
		$tools = array();
		if ($wgUser->isLoggedIn()){
			if (!$this->isOwner()){
				$tools[] = '<li><a href="' . htmlspecialchars( $send_message->getFullURL( 'user=' . $wgUser->getName() . '&conv=' . $user_safe  ) ) . '" rel="nofollow">' .
			 			wfMessage( 'user-send-message' )->escaped() . '</a></li>';
				if ($wgUser->isAllowed('block')){
					# Block / Change block / Unblock links
					if ($this->user->isBlocked() && $this->user->getBlock()->getType() != Block::TYPE_AUTO){
						$tools[] = '<li>'.Linker::linkKnown( # Change block link
                           SpecialPage::getTitleFor( 'Block', $this->user_name ),
                           wfMessage( 'change-blocklink' )->escaped()
                       ).'</li>';
                       $tools[] = '<li>'.Linker::linkKnown( # Unblock link
                           SpecialPage::getTitleFor( 'Unblock', $this->user_name ),
                           wfMessage( 'unblocklink' )->escaped()
                       ).'</li>';
					} else {
						$tools[] = '<li>'.Linker::linkKnown( # Block link
                        	SpecialPage::getTitleFor( 'Block', $this->user_name ),
                        	wfMessage( 'blocklink' )->escaped()
                        ).'</li>';
					}
				}
				# Block log link
				$tools[] = '<li>'.Linker::linkKnown(
	                SpecialPage::getTitleFor( 'Log', 'block' ),
	                wfMessage( 'sp-contributions-blocklog' ),
	                array(),
	                array( 'page' => $this->mTitle->getPrefixedText() )
	            ).'</li>';
	            # Suppression log link
	            if ( $wgUser->isAllowed( 'suppressionlog' ) ) {
                 	$tools[] = '<li>'.Linker::linkKnown(
                    	SpecialPage::getTitleFor( 'Log', 'suppress' ),
                        wfMessage( 'sp-contributions-suppresslog' )->escaped(),
                        array(),
                        array( 'offender' => $this->user_name )
                   ).'</li>';
                }
			}
			# Uploads
		        $tools[] = '<li>'.Linker::linkKnown(
				SpecialPage::getTitleFor( 'Listfiles', $this->user_name ),
				wfMessage( 'sp-contributions-uploads' )->escaped()
			).'</li>';
			# Other logs link
			$tools[] = '<li>'.Linker::linkKnown(
				SpecialPage::getTitleFor( 'Log', $this->user_name ),
				wfMessage( 'sp-contributions-logs' )->escaped()
			).'</li>';
			# Add link to deleted user contributions for priviledged users
			if ( $wgUser->isAllowed( 'deletedhistory' ) ) {
				$tools[] = '<li>'.Linker::linkKnown(
					SpecialPage::getTitleFor( 'DeletedContributions', $this->user_name ),
					wfMessage( 'sp-contributions-deleted' )->escaped()
				).'</li>';
			}
     		# Add a link to change user rights for privileged users
			$userrightsPage = new UserrightsPage();
			$userrightsPage->setContext( $this->getContext() );
			if ( $userrightsPage->userCanChangeRights( $this->user ) ) {
				$tools[] = '<li>'.Linker::linkKnown(
					SpecialPage::getTitleFor( 'Userrights', $this->user_name ),
					wfMessage( 'sp-contributions-userrights' )->escaped()
				).'</li>';
			}
			if ($this->isOwner()){
				$tools[] = '<li><a href="' . htmlspecialchars( $watchlist->getFullURL() ) . '">' . wfMessage( 'user-watchlist' )->escaped() . '</a></li>';
			}
		}
		//user isonline
		// $_SESSION['username'] = $wgUser->getName();
		// $user = User::newFromName( $this->user_name );
		// $isonline = OnlineStatusBar_StatusCheck::getStatus( $user );
		// if($isonline === 'online'){
		// 	$online = '在线';
		// }else{
		// 	$online = '未知';
		// }
		// $wgOut->addModuleScripts( 'ext.socialprofile.useruserfollows.js' ); #this script is already added globally
		// $wgOut->addHTML($wgAjaxExportList); # What is that for??
		$wgOut->addHTML( '<div class="profile-page"><div id="profile-top" class="jumbotron row">' );
		$wgOut->addHTML( $this->getProfileTop( $this->user_id, $this->user_name ) );
        $wgOut->addHTML('
            <div class="col-md-6 col-sm-12 col-xs-12 profile-top-right">
                <div class="profile-top-right-top">
                    <div><h4><span class="icon-huiji"></span>在本wiki</h4></div>
                    <ul>'.
                    $staff.$bureaucrat.$sysop.$rollback.$autoconfirmed
                    .'</ul>
                </div>
                <div class="profile-top-right-bottom">
                    <ul>');
        foreach ($res as $value) {
        	$Turl[] = $value['url'];
        	$Tname[] = $value['name'];
        	$Tcount[] = $value['count'];
        }
        if(isset($Tname)){
        	$num = ( count($Tname) > 3 )?3:count($Tname);
	        for ($i=0; $i < $num; $i++) { 
	           	$wgOut->addHTML('<li><a href="'.$Turl[$i].'">'.$Tname[$i].'</a></li>');
	        }
        }
        
        $wgOut->addHTML(' </ul>

        ');
        if( $this->isOwner() ){
        	$wgOut->addHTML('<a >查看我关注的<i id="site-following-count">'.$userCount.'</i>个wiki</a>');
        }else{
        	$wgOut->addHTML('<a >关注了<i>'.$userCount.'</i>个wiki</a>');
        }
                    
        $wgOut->addHTML('
                    <div>
                        <ul class="profile-interactive">'.
                            $button1.$button2.
                            '<li class="dropdown-toggle mw-ui-button" data-toggle="dropdown" aria-expanded="false"><span class="glyphicon glyphicon-align-justify"></span></li>
                            <ul class="dropdown-menu" role="menu">
                                        '.implode('', $tools).' 
                                        <li><a href="' . htmlspecialchars( $contributions->getFullURL('target='. $user_safe.'&contribs=user'  )) . '" rel="nofollow">' . wfMessage( 'user-contributions' )->escaped() . '</a></li>
                            </ul>
                        </ul>
                    </div>
                </div>
            </div>
        ');
        $wgOut->addHTML( '<div class="cleared"></div></div>');
		// // User does not want social profile for User:user_name, so we just
		// // show header + page content
		// if (
		// 	$this->getTitle()->getNamespace() == NS_USER &&
		// 	$this->profile_data['user_id'] &&
		// 	$this->profile_data['user_page_type'] == 0
		// )
		// {
		// 	parent::view();
		// 	return '';
		// }

		// Left side
		$wgOut->addHTML( '<div id="user-page-left" class="col-md-6">' );

		if ( !wfRunHooks( 'UserProfileBeginLeft', array( &$this ) ) ) {
			wfDebug( __METHOD__ . ": UserProfileBeginLeft messed up profile!\n" );
		}
		if ($this->user_id != $wgUser->getId()) {
			$wgOut->addHTML( $this->getCommonInterest( $wgUser->getId(),$this->user_id) );
		}

		$wgOut->addHTML( $this->getRelationships( $this->user_name, 1 ) );
		$wgOut->addHTML( $this->getRelationships( $this->user_name, 2 ) );
		$wgOut->addHTML( $this->getGifts( $this->user_name ) );
		$wgOut->addHTML( $this->getCustomInfo( $this->user_name ) );
		$wgOut->addHTML( $this->getInterests( $this->user_name ) );
		$wgOut->addHTML( $this->getFanBoxes( $this->user_name ) );
		$wgOut->addHTML( $this->getUserStats( $this->user_id, $this->user_name ) );
        $wgOut->addHTML( $this->getEditingActivity( $this->user_name ) );
        $wgOut->addHTML( $this->getNonEditingActivity( $this->user_name ) );

		if ( !wfRunHooks( 'UserProfileEndLeft', array( &$this ) ) ) {
			wfDebug( __METHOD__ . ": UserProfileEndLeft messed up profile!\n" );
		}

		$wgOut->addHTML( '</div>' );

		wfDebug( "profile start right\n" );

		// Right side
		$wgOut->addHTML( '<div id="user-page-right" class="col-md-6">' );

		if ( !wfRunHooks( 'UserProfileBeginRight', array( &$this ) ) ) {
			wfDebug( __METHOD__ . ": UserProfileBeginRight messed up profile!\n" );
		}
        $wgOut->addHTML( $this->getAwards( $this->user_name ) );
		$wgOut->addHTML( $this->getPersonalInfo( $this->user_id, $this->user_name ) );
		// Hook for BlogPage
		if ( !wfRunHooks( 'UserProfileRightSideAfterActivity', array( $this ) ) ) {
			wfDebug( __METHOD__ . ": UserProfileRightSideAfterActivity hook messed up profile!\n" );
		}
		$wgOut->addHTML( $this->getCasualGames( $this->user_id, $this->user_name ) );
		$wgOut->addHTML( $this->getUserBoard( $this->user_id, $this->user_name ) );

		if ( !wfRunHooks( 'UserProfileEndRight', array( &$this ) ) ) {
			wfDebug( __METHOD__ . ": UserProfileEndRight messed up profile!\n" );
		}

		$wgOut->addHTML( '</div><div class="cleared"></div></div>' );
	}

	function getUserStatsRow( $label, $value ) {
		$output = ''; // Prevent E_NOTICE

		if ( $value != 0 ) {
			$output = "<div>
					<b>{$label}</b>
					{$value}
			</div>";
		}

		return $output;
	}

	function getUserStats( $user_id, $user_name ) {
		global $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['stats'] == false ) {
			return '';
		}

		$output = ''; // Prevent E_NOTICE

		$stats = new UserStats( $user_id, $user_name );
		$stats_data = $stats->getUserStats();

		$total_value = $stats_data['edits'] . $stats_data['votes'] .
						$stats_data['comments'] . $stats_data['recruits'] .
						$stats_data['poll_votes'] .
						$stats_data['picture_game_votes'] .
						$stats_data['quiz_points'];

		if ( $total_value != 0 ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-stats-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="action-left">
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="profile-info-container panel-body bold-fix">' .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-edits', $stats_data['edits'] )->escaped(),
					$stats_data['edits']
				) .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-votes', $stats_data['votes'] )->escaped(),
					$stats_data['votes']
				) .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-comments', $stats_data['comments'] )->escaped(),
					$stats_data['comments'] ) .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-recruits', $stats_data['recruits'] )->escaped(),
					$stats_data['recruits']
				) .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-poll-votes', $stats_data['poll_votes'] )->escaped(),
					$stats_data['poll_votes']
				) .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-picture-game-votes', $stats_data['picture_game_votes'] )->escaped(),
					$stats_data['picture_game_votes']
				) .
				$this->getUserStatsRow(
					wfMessage( 'user-stats-quiz-points', $stats_data['quiz_points'] )->escaped(),
					$stats_data['quiz_points']
				);
			if ( $stats_data['currency'] != '10,000' ) {
				$output .= $this->getUserStatsRow(
					wfMessage( 'user-stats-pick-points', $stats_data['currency'] )->escaped(),
					$stats_data['currency']
				);
			}
			$output .= '</div></div>';
		}

		return $output;
	}

	/**
	 * Get three of the polls the user has created and cache the data in
	 * memcached.
	 *
	 * @return Array
	 */
	function getUserPolls() {
		global $wgMemc;

		$polls = array();

		// Try cache
		$key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'polls', $this->user_id );
		$data = $wgMemc->get( $key );

		if( $data ) {
			wfDebug( "Got profile polls for user {$this->user_id} from cache\n" );
			$polls = $data;
		} else {
			wfDebug( "Got profile polls for user {$this->user_id} from DB\n" );
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				array( 'poll_question', 'page' ),
				array(
					'page_title', 'UNIX_TIMESTAMP(poll_date) AS poll_date'
				),
				/* WHERE */array( 'poll_user_id' => $this->user_id ),
				__METHOD__,
				array( 'ORDER BY' => 'poll_id DESC', 'LIMIT' => 3 ),
				array( 'page' => array( 'INNER JOIN', 'page_id = poll_page_id' ) )
			);
			foreach( $res as $row ) {
				$polls[] = array(
					'title' => $row->page_title,
					'timestamp' => $row->poll_date
				);
			}
			$wgMemc->set( $key, $polls );
		}
		return $polls;
	}

	/**
	 * Get three of the quiz games the user has created and cache the data in
	 * memcached.
	 *
	 * @return Array
	 */
	function getUserQuiz() {
		global $wgMemc;

		$quiz = array();

		// Try cache
		$key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'quiz', $this->user_id );
		$data = $wgMemc->get( $key );

		if( $data ) {
			wfDebug( "Got profile quizzes for user {$this->user_id} from cache\n" );
			$quiz = $data;
		} else {
			wfDebug( "Got profile quizzes for user {$this->user_id} from DB\n" );
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'quizgame_questions',
				array(
					'q_id', 'q_text', 'UNIX_TIMESTAMP(q_date) AS quiz_date'
				),
				array(
					'q_user_id' => $this->user_id,
					'q_flag' => 0 // the same as QUIZGAME_FLAG_NONE
				),
				__METHOD__,
				array(
					'ORDER BY' => 'q_id DESC',
					'LIMIT' => 3
				)
			);
			foreach( $res as $row ) {
				$quiz[] = array(
					'id' => $row->q_id,
					'text' => $row->q_text,
					'timestamp' => $row->quiz_date
				);
			}
			$wgMemc->set( $key, $quiz );
		}

		return $quiz;
	}

	/**
	 * Get three of the picture games the user has created and cache the data
	 * in memcached.
	 *
	 * @return Array
	 */
	function getUserPicGames() {
		global $wgMemc;

		$pics = array();

		// Try cache
		$key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'picgame', $this->user_id );
		$data = $wgMemc->get( $key );
		if( $data ) {
			wfDebug( "Got profile picgames for user {$this->user_id} from cache\n" );
			$pics = $data;
		} else {
			wfDebug( "Got profile picgames for user {$this->user_id} from DB\n" );
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'picturegame_images',
				array(
					'id', 'title', 'img1', 'img2',
					'UNIX_TIMESTAMP(pg_date) AS pic_game_date'
				),
				array(
					'userid' => $this->user_id,
					'flag' => 0 // PICTUREGAME_FLAG_NONE
				),
				__METHOD__,
				array(
					'ORDER BY' => 'id DESC',
					'LIMIT' => 3
				)
			);
			foreach( $res as $row ) {
				$pics[] = array(
					'id' => $row->id,
					'title' => $row->title,
					'img1' => $row->img1,
					'img2' => $row->img2,
					'timestamp' => $row->pic_game_date
				);
			}
			$wgMemc->set( $key, $pics );
		}

		return $pics;
	}

	/**
	 * Get the casual games (polls, quizzes and picture games) that the user
	 * has created if $wgUserProfileDisplay['games'] is set to true and the
	 * PictureGame, PollNY and QuizGame extensions have been installed.
	 *
	 * @param $user_id Integer: user ID number
	 * @param $user_name String: user name
	 * @return String: HTML or nothing if this feature isn't enabled
	 */
	function getCasualGames( $user_id, $user_name ) {
		global $wgUser, $wgOut, $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['games'] == false ) {
			return '';
		}

		$output = '';

		// Safe titles
		$quiz_title = SpecialPage::getTitleFor( 'QuizGameHome' );
		$pic_game_title = SpecialPage::getTitleFor( 'PictureGameHome' );

		// Combine the queries
		$combined_array = array();

		$quizzes = $this->getUserQuiz();
		foreach( $quizzes as $quiz ) {
			$combined_array[] = array(
				'type' => 'Quiz',
				'id' => $quiz['id'],
				'text' => $quiz['text'],
				'timestamp' => $quiz['timestamp']
			);
		}

		$polls = $this->getUserPolls();
		foreach( $polls as $poll ) {
			$combined_array[] = array(
				'type' => 'Poll',
				'title' => $poll['title'],
				'timestamp' => $poll['timestamp']
			);
		}

		$pics = $this->getUserPicGames();
		foreach( $pics as $pic ) {
			$combined_array[] = array(
				'type' => 'Picture Game',
				'id' => $pic['id'],
				'title' => $pic['title'],
				'img1' => $pic['img1'],
				'img2' => $pic['img2'],
				'timestamp' => $pic['timestamp']
			);
		}

		usort( $combined_array, array( 'UserProfilePage', 'sortItems' ) );

		if ( count( $combined_array ) > 0 ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage('casual-games-title')->escaped().'
				</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="action-left">
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="casual-game-container panel-body">';

			$x = 1;

			foreach( $combined_array as $item ) {
				$output .= ( ( $x == 1 ) ? '<p class="item-top">' : '<p>' );

				if ( $item['type'] == 'Poll' ) {
					$ns = ( defined( 'NS_POLL' ) ? NS_POLL : 300 );
					$poll_title = Title::makeTitle( $ns, $item['title'] );
					$casual_game_title = wfMessage( 'casual-game-poll' )->escaped();
					$output .= '<a href="' . htmlspecialchars( $poll_title->getFullURL() ) .
						"\" rel=\"nofollow\">
							{$poll_title->getText()}
						</a>
						<span class=\"item-small\">{$casual_game_title}</span>";
				}

				if ( $item['type'] == 'Quiz' ) {
					$casual_game_title = wfMessage( 'casual-game-quiz' )->escaped();
					$output .= '<a href="' .
						htmlspecialchars( $quiz_title->getFullURL( 'questionGameAction=renderPermalink&permalinkID=' . $item['id'] ) ) .
						"\" rel=\"nofollow\">
							{$item['text']}
						</a>
						<span class=\"item-small\">{$casual_game_title}</span>";
				}

				if ( $item['type'] == 'Picture Game' ) {
					if( $item['img1'] != '' && $item['img2'] != '' ) {
						$image_1 = $image_2 = '';
						$render_1 = wfFindFile( $item['img1'] );
						if ( is_object( $render_1 ) ) {
							$thumb_1 = $render_1->transform( array( 'width' =>  25 ) );
							$image_1 = $thumb_1->toHtml();
						}

						$render_2 = wfFindFile( $item['img2'] );
						if ( is_object( $render_2 ) ) {
							$thumb_2 = $render_2->transform( array( 'width' =>  25 ) );
							$image_2 = $thumb_2->toHtml();
						}

						$casual_game_title = wfMessage( 'casual-game-picture-game' )->escaped();

						$output .= '<a href="' .
							htmlspecialchars( $pic_game_title->getFullURL( 'picGameAction=renderPermalink&id=' . $item['id'] ) ) .
							"\" rel=\"nofollow\">
								{$image_1}
								{$image_2}
								{$item['title']}
							</a>
							<span class=\"item-small\">{$casual_game_title}</span>";
					}
				}

				$output .= '</p>';

				$x++;
			}

			$output .= '</div></div>';
		}

		return $output;
	}

	function sortItems( $x, $y ) {
		if ( $x['timestamp'] == $y['timestamp'] ) {
			return 0;
		} elseif ( $x['timestamp'] > $y['timestamp'] ) {
			return - 1;
		} else {
			return 1;
		}
	}

	function getProfileSection( $label, $value, $required = true ) {
		global $wgUser, $wgOut;

		$output = '';
		if ( $value || $required ) {
			if ( !$value ) {
				if ( $wgUser->getName() == $this->getTitle()->getText() ) {
					$value = wfMessage( 'profile-updated-personal' )->escaped();
				} else {
					$value = wfMessage( 'profile-not-provided' )->escaped();
				}
			}

			$value = $wgOut->parse( trim( $value ), false );

			$output = "<div><b>{$label}</b>{$value}</div>";
		}
		return $output;
	}

	function getPersonalInfo( $user_id, $user_name ) {
		global $wgUser, $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['personal'] == false ) {
			return '';
		}

		$stats = new UserStats( $user_id, $user_name );
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel( $stats_data['points'] );
		$level_link = Title::makeTitle( NS_HELP, wfMessage( 'user-profile-userlevels-link' )->inContentLanguage()->text() );

		$this->initializeProfileData( $user_name );
		$profile_data = $this->profile_data;

		$defaultCountry = wfMessage( 'user-profile-default-country' )->inContentLanguage()->text();

		// Current location
		$location = $profile_data['location_city'] . ', ' . $profile_data['location_state'];
		if ( $profile_data['location_country'] != $defaultCountry ) {
			if ( $profile_data['location_city'] && $profile_data['location_state'] ) { // city AND state
				$location = $profile_data['location_city'] . ', ' .
							$profile_data['location_state'] . ', ' .
							$profile_data['location_country'];
			} elseif ( $profile_data['location_city'] && !$profile_data['location_state'] ) { // city, but no state
				$location = $profile_data['location_city'] . ', ' . $profile_data['location_country'];
			} elseif ( $profile_data['location_state'] && !$profile_data['location_city'] ) { // state, but no city
				$location = $profile_data['location_state'] . ', ' . $profile_data['location_country'];
			} else {
				$location = '';
				$location .= $profile_data['location_country'];
			}
		}

		if ( $location == ', ' ) {
			$location = '';
		}

		// Hometown
		$hometown = $profile_data['hometown_city'] . ', ' . $profile_data['hometown_state'];
		if ( $profile_data['hometown_country'] != $defaultCountry ) {
			if ( $profile_data['hometown_city'] && $profile_data['hometown_state'] ) { // city AND state
				$hometown = $profile_data['hometown_city'] . ', ' .
							$profile_data['hometown_state'] . ', ' .
							$profile_data['hometown_country'];
			} elseif ( $profile_data['hometown_city'] && !$profile_data['hometown_state'] ) { // city, but no state
				$hometown = $profile_data['hometown_city'] . ', ' . $profile_data['hometown_country'];
			} elseif ( $profile_data['hometown_state'] && !$profile_data['hometown_city'] ) { // state, but no city
				$hometown = $profile_data['hometown_state'] . ', ' . $profile_data['hometown_country'];
			} else {
				$hometown = '';
				$hometown .= $profile_data['hometown_country'];
			}
		}

		if ( $hometown == ', ' ) {
			$hometown = '';
		}

		$joined_data = $profile_data['real_name'] . $location . $hometown .
						$profile_data['birthday'] . $profile_data['occupation'] .
						$profile_data['websites'] . $profile_data['places_lived'] .
						$profile_data['schools'] . $profile_data['about'];
		$edit_info_link = SpecialPage::getTitleFor( 'UpdateProfile' );

		$output = '';
		if ( $joined_data ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-personal-info-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">';
			if ( $wgUser->getName() == $user_name ) {
				$output .= '<a href="' . htmlspecialchars( $edit_info_link->getFullURL() ) . '">' .
					wfMessage( 'user-edit-this' )->escaped() . '</a>';
			}
			$output .= '</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="profile-info-container panel-body">' .
				$this->getProfileSection( wfMessage( 'user-personal-info-real-name' )->escaped(), $profile_data['real_name'], false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-location' )->escaped(), $location, false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-hometown' )->escaped(), $hometown, false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-birthday' )->escaped(), $profile_data['birthday'], false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-occupation' )->escaped(), $profile_data['occupation'], false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-websites' )->escaped(), $profile_data['websites'], false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-places-lived' )->escaped(), $profile_data['places_lived'], false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-schools' )->escaped(), $profile_data['schools'], false ) .
				$this->getProfileSection( wfMessage( 'user-personal-info-about-me' )->escaped(), $profile_data['about'], false ) .
			'</div></div>';
		} elseif ( $wgUser->getName() == $user_name ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-personal-info-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">
						<a href="' . htmlspecialchars( $edit_info_link->getFullURL() ) . '">' .
							wfMessage( 'user-edit-this' )->escaped() .
						'</a>
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="no-info-container panel-body">' .
				wfMessage( 'user-no-personal-info' )->escaped() .
			'</div></div>';
		}

		return $output;
	}

	/**
	 * Get the custom info (site-specific stuff) for a given user.
	 *
	 * @param $user_name String: user name whose custom info we should fetch
	 * @return String: HTML
	 */
	function getCustomInfo( $user_name ) {
		global $wgUser, $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['custom'] == false ) {
			return '';
		}

		$this->initializeProfileData( $user_name );

		$profile_data = $this->profile_data;

		$joined_data = $profile_data['custom_1'] . $profile_data['custom_2'] .
						$profile_data['custom_3'] . $profile_data['custom_4'];
		$edit_info_link = SpecialPage::getTitleFor( 'UpdateProfile' );

		$output = '';
		if ( $joined_data ) {
			$output .= '<div class="panel panel-default">
				<div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'custom-info-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">';
			if ( $wgUser->getName() == $user_name ) {
				$output .= '<a href="' . htmlspecialchars( $edit_info_link->getFullURL() ) . '/custom">' .
					wfMessage( 'user-edit-this' )->escaped() . '</a>';
			}
			$output .= '</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="profile-info-container panel-body">' .
				$this->getProfileSection( wfMessage( 'custom-info-field1' )->escaped(), $profile_data['custom_1'], false ) .
				$this->getProfileSection( wfMessage( 'custom-info-field2' )->escaped(), $profile_data['custom_2'], false ) .
				$this->getProfileSection( wfMessage( 'custom-info-field3' )->escaped(), $profile_data['custom_3'], false ) .
				$this->getProfileSection( wfMessage( 'custom-info-field4' )->escaped(), $profile_data['custom_4'], false ) .
			'</div></div>';
		} elseif ( $wgUser->getName() == $user_name ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'custom-info-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">
						<a href="' . htmlspecialchars( $edit_info_link->getFullURL() ) . '/custom">' .
							wfMessage( 'user-edit-this' )->escaped() .
						'</a>
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="no-info-container panel-body">' .
				wfMessage( 'custom-no-info' )->escaped() .
			'</div></div>';
		}

		return $output;
	}

	/**
	 * Get the interests (favorite movies, TV shows, music, etc.) for a given
	 * user.
	 *
	 * @param $user_name String: user name whose interests we should fetch
	 * @return String: HTML
	 */
	function getInterests( $user_name ) {
		global $wgUser, $wgUserProfileDisplay;

		if ( $wgUserProfileDisplay['interests'] == false ) {
			return '';
		}

		$this->initializeProfileData( $user_name );

		$profile_data = $this->profile_data;
		$joined_data = $profile_data['movies'] . $profile_data['tv'] .
						$profile_data['music'] . $profile_data['books'] .
						$profile_data['video_games'] .
						$profile_data['magazines'] . $profile_data['drinks'] .
						$profile_data['snacks'];
		$edit_info_link = SpecialPage::getTitleFor( 'UpdateProfile' );

		$output = '';
		if ( $joined_data ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'other-info-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">';
			if ( $wgUser->getName() == $user_name ) {
				$output .= '<a href="' . htmlspecialchars( $edit_info_link->getFullURL() ) . '/personal">' .
					wfMessage( 'user-edit-this' )->escaped() . '</a>';
			}
			$output .= '</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="profile-info-container panel-body">' .
				$this->getProfileSection( wfMessage( 'other-info-movies' )->escaped(), $profile_data['movies'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-tv' )->escaped(), $profile_data['tv'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-music' )->escaped(), $profile_data['music'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-books' )->escaped(), $profile_data['books'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-video-games' )->escaped(), $profile_data['video_games'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-magazines' )->escaped(), $profile_data['magazines'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-snacks' )->escaped(), $profile_data['snacks'], false ) .
				$this->getProfileSection( wfMessage( 'other-info-drinks' )->escaped(), $profile_data['drinks'], false ) .
			'</div></div>';
		} elseif ( $this->isOwner() ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'other-info-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">
						<a href="' . htmlspecialchars( $edit_info_link->getFullURL() ) . '/personal">' .
							wfMessage( 'user-edit-this' )->escaped() .
						'</a>
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="no-info-container panel-body">' .
				wfMessage( 'other-no-info' )->escaped() .
			'</div></div>';
		}
		return $output;
	}

	/**
	 * Get the header for the social profile page, which includes the user's
	 * points and user level (if enabled in the site configuration) and lots
	 * more.
	 *
	 * @param $user_id Integer: user ID
	 * @param $user_name String: user name
	 */
	function getProfileTop( $user_id, $user_name ) {
		global $wgOut, $wgUser, $wgLang;
		global $wgUserLevels;

		$stats = new UserStats( $user_id, $user_name );
		$stats_data = $stats->getUserStats();
		$user_level = new UserLevel( $stats_data['points'] );
		$level_link = Title::makeTitle( NS_HELP, wfMessage( 'user-profile-userlevels-link' )->inContentLanguage()->text() );

		$this->initializeProfileData( $user_name );
		$profile_data = $this->profile_data;

		// Variables and other crap
		$page_title = $this->getTitle()->getText();
		$title_parts = explode( '/', $page_title );
		$user = $title_parts[0];
		$id = User::idFromName( $user );
		$user_safe = urlencode( $user );

		// Safe urls
		$add_relationship = SpecialPage::getTitleFor( 'AddRelationship' );
		$remove_relationship = SpecialPage::getTitleFor( 'RemoveRelationship' );
		$give_gift = SpecialPage::getTitleFor( 'GiveGift' );
		$send_board_blast = SpecialPage::getTitleFor( 'SendBoardBlast' );
		$update_profile = SpecialPage::getTitleFor( 'UpdateProfile' );
		$watchlist = SpecialPage::getTitleFor( 'Watchlist' );
		$contributions = SpecialPage::getTitleFor( 'Contributions', $user );
		$send_message = SpecialPage::getTitleFor( 'UserBoard' );
		$upload_avatar = SpecialPage::getTitleFor( 'UploadAvatar' );
		$user_page = Title::makeTitle( NS_USER, $user );
		$user_social_profile = Title::makeTitle( NS_USER_PROFILE, $user );
		$user_wiki = Title::makeTitle( NS_USER_WIKI, $user );
		$us = new UserStatus($this->user);
		$city = $us->getCity();
		$birthday = $us->getBirthday();
		$status = $us->getStatus();
		$gender = $us->getGender();
		if ($gender == 'male'){
			$genderIcon = '♂';
			$gendertext = '他';
		} elseif ($gender == 'female'){
			$genderIcon = '♀';
			$gendertext = '她';
		} else {
			$genderIcon = '♂/♀';
			$gendertext = 'TA';
		}
		if ($this->isOwner()){
			$gendertext = '你';
		}
		if ( $id != 0 ) {
			$relationship = UserRelationship::getUserRelationshipByID( $id, $wgUser->getID() );
		}
		$avatar = new wAvatar( $this->user_id, 'l' );

		wfDebug( 'profile type: ' . $profile_data['user_page_type'] . "\n" );
		$output = '';

		//get more
		$target = SpecialPage::getTitleFor('FollowSites');
		$query = array('user_id' => $wgUser->getId(), 'target_user_id' => $this->user_id);
		$mailVerify = $wgUser->getEmailAuthenticationTimestamp();
		if ($mailVerify == NULL) {
			 $href = "/wiki/Special:ConfirmEmail";
		}else{
			$href = "/wiki/Special:UploadAvatar";
		}

		$output .= '<div id="profile-right" class="col-md-6 col-sm-12 col-xs-12">';

		$output .= '<div id="profile-title-container">
				<h1 id="profile-title">
				<div id="profile-image">' .($this->isOwner()? ('<div class="profile-image-container" id="crop-avatar"><div class="avatar-view">'.$avatar->getOwnerAvatarURL().'</div>'.$this->cropModal().'</div>'): $avatar->getAvatarURL()) .'</div>' .
					$user_name .
				'</h1></div>';
        $output .='<div class="modal fade watch-url" tabindex="-1" role="dialog" aria-labelledby="mySmModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                          <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title" id="gridSystemModalLabel">'.$gendertext.'关注的wiki</h4>
                          </div>
                            <div class="modal-body">
	                            <div class="list-group">
								</div>
								'.Linker::LinkKnown($target, '<i class="fa fa-arrows-alt"></i> 全部', array('type'=>'button', 'class'=>'btn btn-default'), $query).'
							</div>
                        </div>
                      </div>
                    </div>';
		// Show the user's level and the amount of points they have if
		// UserLevels has been configured contributions
		$notice = SpecialPage::getTitleFor('ViewFollows');
		$contributions = SpecialPage::getTitleFor('Contributions');
        $output .='<div>
					    <ul class="user-follow-msg">
					        <li><h5>编辑</h5>'.Linker::link( $contributions, $stats_data['edits'], array(), array( 'target' => $user,'contribs' => 'user' ) ).'</li>
					        <li><h4>|</h4></li>
					        <li><h5>关注</h5>'.Linker::link( $notice, UserUserFollow::getFollowingCount(User::newFromName($user)), array(  'id' => 'user-following-count'  ), array( 'user' => $user,'rel_type' => 1 ) ).'</li>
					        <li><h4>|</h4></li>
					        <li><h5>被关注</h5>'.Linker::link( $notice, UserUserFollow::getFollowerCount(User::newFromName($user)), array( 'id' => 'user-follower-count' ), array( 'user' => $user,'rel_type' => 2 ) ).'</li>
                        </ul>
                        <div class="cleared"></div>
                    </div>
                    <!--<span id="user-site-count">'.'</span>个站点。-->';
		if ( $wgUserLevels ) {
			$progress = $user_level->getLevelProgress()*100;
			$output .= '<div id="honorific-level" class="label label-info">
						<a href="' . htmlspecialchars( $level_link->getFullURL() ) . '" rel="nofollow">' . $user_level->getLevelName() . '</a>
					</div>
					<div id="points-level" class="progress">
						<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="'.$progress.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$progress.'%">
							<span class="sr-only">'.wfMessage(
								'user-profile-points',
								$wgLang->formatNum( $stats_data['points'] )
							)->escaped().'</span>
						</div>

					</div>';
		}
		$output .= '<div class="profile-actions">';
        $output .='<div class="form-container '.($this->isOwner()?'owner':'').'"><div class="form-msg"><span class="form-location '.($city == ''&& $this->isOwner()?'edit-on':'').'" data-toggle="yes">'.($city == ''?($this->isOwner()?'填写居住地':'居住地未公开'):$city).'</span>
                    <span class="span-color">|</span><span class="form-date '.(($birthday == ''|| $birthday == '0000-00-00') && $this->isOwner()?'edit-on':'').'" data-birthday="'.($birthday == ''||$birthday == '0000-00-00'?'':$birthday).'">'.($birthday == ''||$birthday == '0000-00-00'?($this->isOwner()?'填写生日':'生日未公开'):'').'</span>
                    <span class="span-color">|</span><span class="form-sex">'.$genderIcon.'</span></div>';
        $output .='<div class="user-autograph"><span class="form-autograph '.($status == '' && $this->isOwner()?'edit-on':'').'" data-toggle="yes">'.($status == ''?($this->isOwner()?'填写个人状态':'这个人很懒，什么都没有写...'):$status).'</span>
                    <span class="glyphicon glyphicon-pencil form-change">修改</span></div></div>';

		// Links to User:user_name from User_profile:
		// if ( $this->getTitle()->getNamespace() == NS_USER_PROFILE && $this->profile_data['user_id'] && $this->profile_data['user_page_type'] == 0 ) {
		// 	$output .= '| <a href="' . htmlspecialchars( $user_page->getFullURL() ) . '" rel="nofollow">' .
		// 		wfMessage( 'user-page-link' )->escaped() . '</a> ';
		// }

		// // Links to User:user_name from User_profile:
		// if ( $this->getTitle()->getNamespace() == NS_USER && $this->profile_data['user_id'] && $this->profile_data['user_page_type'] == 0 ) {
		// 	$output .= '| <a href="' . htmlspecialchars( $user_social_profile->getFullURL() ) . '" rel="nofollow">' .
		// 		wfMessage( 'user-social-profile-link' )->escaped() . '</a> ';
		// }

		// if ( $this->getTitle()->getNamespace() == NS_USER && ( !$this->profile_data['user_id'] || $this->profile_data['user_page_type'] == 1 ) ) {
		// 	$output .= '| <a href="' . htmlspecialchars( $user_wiki->getFullURL() ) . '" rel="nofollow">' .
		// 		wfMessage( 'user-wiki-link' )->escaped() . '</a>';
		// }

		$output .= '</div></div>';

		return $output;
	}

	/**
	 * This is currently unused, seems to be a leftover from the ArmchairGM
	 * days.
	 *
	 * @param $user_name String: user name
	 * @return String: HTML
	 */
	function getProfileImage( $user_name ) {
		global $wgUser;

		$avatar = new wAvatar( $this->user_id, 'l' );
		$avatarTitle = SpecialPage::getTitleFor( 'UploadAvatar' );

		$output = '<div class="profile-image">';
		if ( $wgUser->getName() == $this->user_name ) {
			if ( strpos( $avatar->getAvatarImage(), 'default_' ) != false ) {
				$caption = 'upload image';
			} else {
				$caption = 'new image';
			}
			$output .= '<a href="' . htmlspecialchars( $avatarTitle->getFullURL() ) . '" rel="nofollow">' .
						$avatar->getAvatarURL() . '<br />
					(' . $caption . ')
				</a>';
		} else {
			$output .= $avatar->getAvatarURL();
		}
		$output .= '</div></div>';

		return $output;
	}

	/**
	 * Get the relationships for a given user.
	 *
	 * @param $user_name String: name of the user whose relationships we want
	 *                           to fetch
	 * @param $rel_type Integer: 1 for friends, 2 (or anything else than 1) for
	 *                           foes
	 */
	function getRelationships( $user_name, $rel_type ) {
		global $wgMemc, $wgUser, $wgUserProfileDisplay, $wgLang;

		// If not enabled in site settings, don't display
		if ( $rel_type == 1 ) {
			if ( $wgUserProfileDisplay['friends'] == false ) {
				return '';
			}
		} else {
			if ( $wgUserProfileDisplay['foes'] == false ) {
				return '';
			}
		}

		$output = ''; // Prevent E_NOTICE

		$count = 4;
		$rel = new UserRelationship( $user_name );
		$key = wfForeignMemcKey( 'huiji', '', 'relationship', 'profile', "{$rel->user_id}-{$rel_type}" );
		$data = $wgMemc->get( $key );

		// Try cache
		if ( !$data ) {
			$friends = $rel->getRelationshipList( $rel_type, $count );
			$wgMemc->set( $key, $friends );
		} else {
			wfDebug( "Got profile relationship type {$rel_type} for user {$user_name} from cache\n" );
			$friends = $data;
		}

		$stats = new UserStats( $rel->user_id, $user_name );
		$stats_data = $stats->getUserStats();
		$view_all_title = SpecialPage::getTitleFor( 'ViewRelationships' );

		if ( $rel_type == 1 ) {
			$relationship_count = $stats_data['friend_count'];
			$relationship_title = wfMessage( 'user-friends-title' )->escaped();
		} else {
			$relationship_count = $stats_data['foe_count'];
			$relationship_title = wfMessage( 'user-foes-title' )->escaped();
		}

		if ( count( $friends ) > 0 ) {
			$x = 1;
			$per_row = 4;

			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' . $relationship_title . '</div>
				<div class="user-section-actions">
					<div class="action-right">';
			if ( intval( str_replace( ',', '', $relationship_count ) ) > 4 ) {
				$output .= '<a href="' . htmlspecialchars( $view_all_title->getFullURL( 'user=' . $user_name . '&rel_type=' . $rel_type ) ) .
					'" rel="nofollow">' . wfMessage( 'user-view-all' )->escaped() . '</a>';
			}
			$output .= '</div>
					<div class="action-left">';
			if ( intval( str_replace( ',', '', $relationship_count ) ) > 4 ) {
				$output .= wfMessage( 'user-count-separator', $per_row, $relationship_count )->escaped();
			} else {
				$output .= wfMessage( 'user-count-separator', $relationship_count, $relationship_count )->escaped();
			}
			$output .= '</div>
				</div>
				<div class="cleared"></div>
			</div>
			<div class="cleared"></div>
			<div class="user-relationship-container panel-body">';

			foreach ( $friends as $friend ) {
				$user = Title::makeTitle( NS_USER, $friend['user_name'] );
				$avatar = new wAvatar( $friend['user_id'], 'ml' );

				// Chop down username that gets displayed
				$user_name = $wgLang->truncate( $friend['user_name'], 9, '..' );

				$output .= "<a href=\"" . htmlspecialchars( $user->getFullURL() ) . "\" title=\"{$friend['user_name']}\" rel=\"nofollow\">
					{$avatar->getAvatarURL()}<br />
					{$user_name}
				</a>";

				if ( $x == count( $friends ) || $x != 1 && $x % $per_row == 0 ) {
					$output .= '<div class="cleared"></div>';
				}

				$x++;
			}

			$output .= '</div></div>';
		}

		return $output;
	}

	/**
	 * Gets the recent social activity for a given user.
	 *
	 * @param $user_name String: name of the user whose activity we want to fetch
	 */
	function getNonEditingActivity( $user_name ) {
		global $wgUser, $wgUserProfileDisplay, $wgExtensionAssetsPath, $wgUploadPath;

		// If not enabled in site settings, don't display
		if ( $wgUserProfileDisplay['activity'] == false ) {
			return '';
		}

		$output = '';

		$limit = 8;
		$rel = new UserActivity( $user_name, 'user', $limit );
		$rel->setActivityToggle( 'show_votes', 0 );
		$rel->setActivityToggle( 'show_gifts_sent', 1 );
		$rel->setActivityToggle( 'show_edits', 0 );
		$rel->setActivityToggle( 'show_comments', 0 );
		/**
		 * Get all relationship activity
		 */
		$activity = $rel->getActivityList();

		if ( $activity ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-recent-activity-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="panel-body">';

			$x = 1;

			if ( count( $activity ) < $limit ) {
				$style_limit = count( $activity );
			} else {
				$style_limit = $limit;
			}

			foreach ( $activity as $item ) {
				$item_html = '';
				$title = Title::makeTitle( $item['namespace'], $item['pagetitle'] );
				$user_title = Title::makeTitle( NS_USER, $item['username'] );
				$user_title_2 = Title::makeTitle( NS_USER, $item['comment'] );

				if ( $user_title_2 ) {
					$user_link_2 = '<a href="' . htmlspecialchars( $user_title_2->getFullURL() ) .
						'" rel="nofollow">' . $item['comment'] . '</a>';
				}

				$comment_url = '';
				if ( $item['type'] == 'comment' ) {
					$comment_url = "#comment-{$item['id']}";
				}
				if (array_key_exists('site', $item)){
					$site_link = '<b><a href="' . HuijiPrefix::prefixToUrl($item['site']) .
						"{$comment_url}\">" . HuijiPrefix::prefixToSiteName($item['site'])  . '</a></b> ';					
				}
				$page_link = '<b><a href="' . htmlspecialchars( $title->getFullURL() ) .
					"{$comment_url}\">" . $title->getPrefixedText() . '</a></b> ';
				$b = new UserBoard(); // Easier than porting the time-related functions here
				$item_time = '<span class="item-small">' .
					wfMessage( 'user-time-ago', $b->getTimeAgo( $item['timestamp'] ) )->escaped() .
				'</span>';

				if ( $x < $style_limit ) {
					$item_html .= '<div class="activity-item">'.UserActivity::getTypeIcon( $item['type'] ) ;
				} else {
					$item_html .= '<div class="activity-item-bottom">'.UserActivity::getTypeIcon( $item['type'] ) ;
				}

				$viewGift = SpecialPage::getTitleFor( 'ViewGift' );

				switch( $item['type'] ) {
					case 'edit':
						$item_html .= wfMessage( 'user-recent-activity-edit' )->escaped() . " {$page_link} {$item_time}
							<div class=\"item\">";
						if ( $item['comment'] ) {
							$item_html .= "\"{$item['comment']}\"";
						}
						$item_html .= '</div>';
						break;
					case 'vote':
						$item_html .= wfMessage( 'user-recent-activity-vote' )->escaped() . " {$page_link} {$item_time}";
						break;
					case 'comment':
						$item_html .= wfMessage( 'user-recent-activity-comment' )->escaped() . " {$page_link} {$item_time}
							<div class=\"item\">
								\"{$item['comment']}\"
							</div>";
						break;
					case 'gift-sent':
						$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
							Gifts::getGiftImage( $item['namespace'], 'm' ) .
							'" border="0" alt="" />';
						$item_html .= wfMessage( 'user-recent-activity-gift-sent' )->escaped() . " {$user_link_2} {$item_time}
						<div class=\"item\">
							<a href=\"" . htmlspecialchars( $viewGift->getFullURL( "gift_id={$item['id']}" ) ) . "\" rel=\"nofollow\">
								{$gift_image}
								{$item['pagetitle']}
							</a>
						</div>";
						break;
					case 'gift-rec':
						$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
							Gifts::getGiftImage( $item['namespace'], 'm' ) .
							'" border="0" alt="" />';
						$item_html .= wfMessage( 'user-recent-activity-gift-rec' )->escaped() . " {$user_link_2} {$item_time}</span>
								<div class=\"item\">
									<a href=\"" . htmlspecialchars( $viewGift->getFullURL( "gift_id={$item['id']}" ) ) . "\" rel=\"nofollow\">
										{$gift_image}
										{$item['pagetitle']}
									</a>
								</div>";
						break;
					case 'system_gift':
						$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
							SystemGifts::getGiftImage( $item['namespace'], 'm' ) .
							'" border="0" alt="" />';
						$viewSystemGift = SpecialPage::getTitleFor( 'ViewSystemGift' );
						$item_html .= wfMessage( 'user-recent-system-gift' )->escaped() . " {$item_time}
								<div class=\"item\">
									<a href=\"" . htmlspecialchars( $viewSystemGift->getFullURL( "gift_id={$item['id']}" ) ) . "\" rel=\"nofollow\">
										{$gift_image}
										{$item['pagetitle']}
									</a>
								</div>";
						break;
					case 'friend':
						$item_html .= wfMessage( 'user-recent-activity-friend' )->escaped() .
							" <b>{$user_link_2}</b> {$item_time}";
						break;
					case 'foe':
						$item_html .= wfMessage( 'user-recent-activity-foe' )->escaped() .
							" <b>{$user_link_2}</b> {$item_time}";
						break;
					case 'system_message':
						$item_html .= "{$item['comment']} {$item_time}";
						break;
					case 'user_message':
						$item_html .= wfMessage( 'user-recent-activity-user-message' )->escaped() .
							" <b><a href=\"" . UserBoard::getUserBoardURL( $user_title_2->getText() ) .
								"\" rel=\"nofollow\">{$item['comment']}</a></b>  {$item_time}
								<div class=\"item\">
								\"{$item['namespace']}\"
								</div>";
						break;
					case 'network_update':
						$network_image = SportsTeams::getLogo( $item['sport_id'], $item['team_id'], 's' );
						$item_html .= wfMessage( 'user-recent-activity-network-update' )->escaped() .
								'<div class="item">
									<a href="' . SportsTeams::getNetworkURL( $item['sport_id'], $item['team_id'] ) .
									"\" rel=\"nofollow\">{$network_image} \"{$item['comment']}\"</a>
								</div>";
						break;
					case 'user_user_follow':
						$item_html .= wfMessage( 'user-recent-activity-follow' )->escaped() .
							" <b>{$user_link_2}</b> {$item_time}";
						break;	
					case 'user_site_follow':
						$item_html .= wfMessage( 'user-recent-activity-follow' )->escaped() .
							" <b>{$site_link}</b> {$item_time}";
						break;						
					}

					$item_html .= '</div>';

					if ( $x <= $limit ) {
						$items_html_type['all'][] = $item_html;
					}
					$items_html_type[$item['type']][] = $item_html;

				$x++;
			}

			$by_type = '';
			foreach ( $items_html_type['all'] as $item ) {
				$by_type .= $item;
			}
			$output .= "<div id=\"recent-all\">$by_type</div></div></div>";
		}

		return $output;
	}
	/**
	 * Gets the recent social activity for a given user.
	 *
	 * @param $user_name String: name of the user whose activity we want to fetch
	 */
	function getEditingActivity( $user_name ) {
		global $wgUser, $wgUserProfileDisplay, $wgExtensionAssetsPath, $wgUploadPath;

		// If not enabled in site settings, don't display
		if ( $wgUserProfileDisplay['activity'] == false ) {
			return '';
		}

		$output = '';

		$limit = 8;
		$rel = new UserActivity( $user_name, 'user', $limit );
		$rel->setActivityToggle( 'show_votes', 0 );
		$rel->setActivityToggle( 'show_edits', 1 );
		$rel->setActivityToggle( 'show_comments', 1);		
		$rel->setActivityToggle( 'show_relationships', 0);
		$rel->setActivityToggle( 'show_system_gifts', 0);
		$rel->setActivityToggle( 'show_system_messages', 0);
		$rel->setActivityToggle( 'show_messages_sent', 0);
		$rel->setActivityToggle( 'show_user_user_follows', 0);
		$rel->setActivityToggle( 'show_user_site_follows', 0);		
		$rel->setActivityToggle( 'show_user_update_status', 0);
		$rel->setActivityToggle( 'show_gifts_sent', 0);		
		$rel->setActivityToggle( 'show_gifts_rec', 0);		/**
		 * Get all relationship activity
		 */
		$activity = $rel->getActivityList();

		if ( $activity ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-recent-local-activity-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="panel-body">';

			$x = 1;

			if ( count( $activity ) < $limit ) {
				$style_limit = count( $activity );
			} else {
				$style_limit = $limit;
			}

			foreach ( $activity as $item ) {
				$item_html = '';
				$title = Title::makeTitle( $item['namespace'], $item['pagetitle'] );
				$user_title = Title::makeTitle( NS_USER, $item['username'] );
				$user_title_2 = Title::makeTitle( NS_USER, $item['comment'] );

				if ( $user_title_2 ) {
					$user_link_2 = '<a href="' . htmlspecialchars( $user_title_2->getFullURL() ) .
						'" rel="nofollow">' . $item['comment'] . '</a>';
				}

				$comment_url = '';
				if ( $item['type'] == 'comment' ) {
					$comment_url = "#comment-{$item['id']}";
				}

				$page_link = '<b><a href="' . htmlspecialchars( $title->getFullURL() ) .
					"{$comment_url}\">" . $title->getPrefixedText() . '</a></b> ';
				$b = new UserBoard(); // Easier than porting the time-related functions here
				$item_time = '<span class="item-small">' .
					wfMessage( 'user-time-ago', $b->getTimeAgo( $item['timestamp'] ) )->escaped() .
				'</span>';

				if ( $x < $style_limit ) {
					$item_html .= '<div class="activity-item">'.UserActivity::getTypeIcon( $item['type'] ) ;
				} else {
					$item_html .= '<div class="activity-item-bottom">'.UserActivity::getTypeIcon( $item['type'] ) ;
				}

				$viewGift = SpecialPage::getTitleFor( 'ViewGift' );

				switch( $item['type'] ) {
					case 'edit':
						$item_html .= wfMessage( 'user-recent-activity-edit' )->escaped() . " {$page_link} {$item_time}
							<div class=\"item\">";
						if ( $item['comment'] ) {
							$item_html .= "\"{$item['comment']}\"";
						}
						$item_html .= '</div>';
						break;
					case 'vote':
						$item_html .= wfMessage( 'user-recent-activity-vote' )->escaped() . " {$page_link} {$item_time}";
						break;
					case 'comment':
						$item_html .= wfMessage( 'user-recent-activity-comment' )->escaped() . " {$page_link} {$item_time}
							<div class=\"item\">
								\"{$item['comment']}\"
							</div>";
						break;
					case 'gift-sent':
						$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
							Gifts::getGiftImage( $item['namespace'], 'm' ) .
							'" border="0" alt="" />';
						$item_html .= wfMessage( 'user-recent-activity-gift-sent' )->escaped() . " {$user_link_2} {$item_time}
						<div class=\"item\">
							<a href=\"" . htmlspecialchars( $viewGift->getFullURL( "gift_id={$item['id']}" ) ) . "\" rel=\"nofollow\">
								{$gift_image}
								{$item['pagetitle']}
							</a>
						</div>";
						break;
					case 'gift-rec':
						$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
							Gifts::getGiftImage( $item['namespace'], 'm' ) .
							'" border="0" alt="" />';
						$item_html .= wfMessage( 'user-recent-activity-gift-rec' )->escaped() . " {$user_link_2} {$item_time}</span>
								<div class=\"item\">
									<a href=\"" . htmlspecialchars( $viewGift->getFullURL( "gift_id={$item['id']}" ) ) . "\" rel=\"nofollow\">
										{$gift_image}
										{$item['pagetitle']}
									</a>
								</div>";
						break;
					case 'system_gift':
						$gift_image = "<img src=\"{$wgUploadPath}/awards/" .
							SystemGifts::getGiftImage( $item['namespace'], 'm' ) .
							'" border="0" alt="" />';
						$viewSystemGift = SpecialPage::getTitleFor( 'ViewSystemGift' );
						$item_html .= wfMessage( 'user-recent-system-gift' )->escaped() . " {$item_time}
								<div class=\"user-home-item-gift\">
									<a href=\"" . htmlspecialchars( $viewSystemGift->getFullURL( "gift_id={$item['id']}" ) ) . "\" rel=\"nofollow\">
										{$gift_image}
										{$item['pagetitle']}
									</a>
								</div>";
						break;
					case 'friend':
						$item_html .= wfMessage( 'user-recent-activity-friend' )->escaped() .
							" <b>{$user_link_2}</b> {$item_time}";
						break;
					case 'foe':
						$item_html .= wfMessage( 'user-recent-activity-foe' )->escaped() .
							" <b>{$user_link_2}</b> {$item_time}";
						break;
					case 'system_message':
						$item_html .= "{$item['comment']} {$item_time}";
						break;
					case 'user_message':
						$item_html .= wfMessage( 'user-recent-activity-user-message' )->escaped() .
							" <b><a href=\"" . UserBoard::getUserBoardURL( $user_title_2->getText() ) .
								"\" rel=\"nofollow\">{$item['comment']}</a></b>  {$item_time}
								<div class=\"item\">
								\"{$item['namespace']}\"
								</div>";
						break;
					case 'network_update':
						$network_image = SportsTeams::getLogo( $item['sport_id'], $item['team_id'], 's' );
						$item_html .= wfMessage( 'user-recent-activity-network-update' )->escaped() .
								'<div class="item">
									<a href="' . SportsTeams::getNetworkURL( $item['sport_id'], $item['team_id'] ) .
									"\" rel=\"nofollow\">{$network_image} \"{$item['comment']}\"</a>
								</div>";
						break;
					}

					$item_html .= '</div>';

					if ( $x <= $limit ) {
						$items_html_type['all'][] = $item_html;
					}
					$items_html_type[$item['type']][] = $item_html;

				$x++;
			}

			$by_type = '';
			foreach ( $items_html_type['all'] as $item ) {
				$by_type .= $item;
			}
			$output .= "<div id=\"recent-all\">$by_type</div></div></div>";
		}

		return $output;
	}

	function getGifts( $user_name ) {
		global $wgUser, $wgMemc, $wgUserProfileDisplay, $wgUploadPath;

		// If not enabled in site settings, don't display
		if ( $wgUserProfileDisplay['gifts'] == false ) {
			return '';
		}

		$output = '';

		// User to user gifts
		$g = new UserGifts( $user_name );
		$user_safe = urlencode( $user_name );

		// Try cache
		$key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'gifts', "{$g->user_id}" );
		$data = $wgMemc->get( $key );

		if ( !$data ) {
			wfDebug( "Got profile gifts for user {$user_name} from DB\n" );
			$gifts = $g->getUserGiftList( 0, 4 );
			$wgMemc->set( $key, $gifts, 60 * 60 * 4 );
		} else {
			wfDebug( "Got profile gifts for user {$user_name} from cache\n" );
			$gifts = $data;
		}

		$gift_count = $g->getGiftCountByUsername( $user_name );
		$gift_link = SpecialPage::getTitleFor( 'ViewGifts' );
		$per_row = 4;

		if ( $gifts ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-gifts-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">';
			if ( $gift_count > 4 ) {
				$output .= '<a href="' . htmlspecialchars( $gift_link->getFullURL( 'user=' . $user_safe ) ) . '" rel="nofollow">' .
					wfMessage( 'user-view-all' )->escaped() . '</a>';
			}
			$output .= '</div>
					<div class="action-left">';
			if ( $gift_count > 4 ) {
				$output .= wfMessage( 'user-count-separator', '4', $gift_count )->escaped();
			} else {
				$output .= wfMessage( 'user-count-separator', $gift_count, $gift_count )->escaped();
			}
			$output .= '</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="user-gift-container panel-body">';

			$x = 1;

			foreach ( $gifts as $gift ) {
				if ( $gift['status'] == 1 && $user_name == $wgUser->getName() ) {
					$g->clearUserGiftStatus( $gift['id'] );
					$wgMemc->delete( $key );
					$g->decNewGiftCount( $wgUser->getID() );
				}

				$user = Title::makeTitle( NS_USER, $gift['user_name_from'] );
				$gift_image = '<img src="' . $wgUploadPath . '/awards/' .
					Gifts::getGiftImage( $gift['gift_id'], 'ml' ) .
					'" border="0" alt="" />';
				$gift_link = $user = SpecialPage::getTitleFor( 'ViewGift' );
				$class = '';
				if ( $gift['status'] == 1 ) {
					$class = 'class="user-page-new"';
				}
				$output .= '<a href="' . htmlspecialchars( $gift_link->getFullURL( 'gift_id=' . $gift['id'] ) ) . '" ' .
					$class . " rel=\"nofollow\">{$gift_image}</a>";
				if ( $x == count( $gifts ) || $x != 1 && $x % $per_row == 0 ) {
					$output .= '<div class="cleared"></div>';
				}

				$x++;
			}

			$output .= '</div></div>';
		}

		return $output;
	}

	function getAwards( $user_name ) {
		global $wgUser, $wgMemc, $wgUserProfileDisplay, $wgUploadPath;

		// If not enabled in site settings, don't display
		if ( $wgUserProfileDisplay['awards'] == false ) {
			return '';
		}

		$output = '';

		// System gifts
		$sg = new UserSystemGifts( $user_name );

		// Try cache
		$sg_key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'system_gifts', "{$sg->user_id}" );
		$data = $wgMemc->get( $sg_key );
		if ( !$data ) {
			wfDebug( "Got profile awards for user {$user_name} from DB\n" );
			$system_gifts = $sg->getUserGiftList( 0, 5 );
			$wgMemc->set( $sg_key, $system_gifts, 60 * 60 * 4 );
		} else {
			wfDebug( "Got profile awards for user {$user_name} from cache\n" );
			$system_gifts = $data;
		}

		$system_gift_count = $sg->getGiftCountByUsername( $user_name );
		$system_gift_link = SpecialPage::getTitleFor( 'ViewSystemGifts' );
		$per_row = 5;

		if ( $system_gifts ) {
			$x = 1;
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-awards-title' )->escaped() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">';
			if ( $system_gift_count > 5 ) {
				$output .= '<a href="' . htmlspecialchars( $system_gift_link->getFullURL( 'user=' . $user_name ) ) . '" rel="nofollow">' .
					wfMessage( 'user-view-all' )->escaped() . '</a>';
			}
			$output .= '</div>
					<div class="action-left">';
			if ( $system_gift_count > 5 ) {
				$output .= wfMessage( 'user-count-separator', '5', $system_gift_count )->escaped();
			} else {
				$output .= wfMessage( 'user-count-separator', $system_gift_count, $system_gift_count )->escaped();
			}
			$output .= '</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="user-gift-container panel-body">';

			foreach ( $system_gifts as $gift ) {
				if ( $gift['status'] == 1 && $user_name == $wgUser->getName() ) {
					$sg->clearUserGiftStatus( $gift['id'] );
					$wgMemc->delete( $sg_key );
					$sg->decNewSystemGiftCount( $wgUser->getID() );
				}

				$gift_image = '<img src="' . $wgUploadPath . '/awards/' .
					SystemGifts::getGiftImage( $gift['gift_id'], 'ml' ) .
					'" border="0" alt="" />';
				$gift_link = $user = SpecialPage::getTitleFor( 'ViewSystemGift' );

				$class = '';
				if ( $gift['status'] == 1 ) {
					$class = 'class="user-page-new"';
				}
				$output .= '<a href="' . htmlspecialchars( $gift_link->getFullURL( 'gift_id=' . $gift['id'] ) ) .
					'" ' . $class . " rel=\"nofollow\">
					{$gift_image}
				</a>";

				if ( $x == count( $system_gifts ) || $x != 1 && $x % $per_row == 0 ) {
					$output .= '<div class="cleared"></div>';
				}
				$x++;
			}

			$output .= '</div></div>';
		}

		return $output;
	}

	/**
	 * Get the user board for a given user.
	 *
	 * @param $user_id Integer: user's ID number
	 * @param $user_name String: user name
	 */
	function getUserBoard( $user_id, $user_name ) {
		global $wgUser, $wgOut, $wgUserProfileDisplay;

		// Anonymous users cannot have user boards
		if ( $user_id == 0 ) {
			return '';
		}

		// Don't display anything if user board on social profiles isn't
		// enabled in site configuration
		if ( $wgUserProfileDisplay['board'] == false ) {
			return '';
		}

		$output = ''; // Prevent E_NOTICE

		// Add JS
		$wgOut->addModules( 'ext.socialprofile.userprofile.js' );

		$rel = new UserRelationship( $user_name );
		$friends = $rel->getRelationshipList( 1, 4 );

		$stats = new UserStats( $user_id, $user_name );
		$stats_data = $stats->getUserStats();
		$total = $stats_data['user_board'];

		// If the user is viewing their own profile or is allowed to delete
		// board messages, add the amount of private messages to the total
		// sum of board messages.
		if ( $wgUser->getName() == $user_name || $wgUser->isAllowed( 'userboard-delete' ) ) {
			$total = $total + $stats_data['user_board_priv'];
		}

		$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
			<div class="user-section-title">' .
				wfMessage( 'user-board-title' )->escaped() .
			'</div>
			<div class="user-section-actions">
				<div class="action-right">';
		if ( $wgUser->getName() == $user_name ) {
			if ( $friends ) {
				$output .= '<a href="' . UserBoard::getBoardBlastURL() . '">' .
					wfMessage( 'user-send-board-blast' )->escaped() . '</a>';
			}
			if ( $total > 10 ) {
				$output .= wfMessage( 'pipe-separator' )->escaped();
			}
		}
		if ( $total > 10 ) {
			$output .= '<a href="' . UserBoard::getUserBoardURL( $user_name ) . '">' .
				wfMessage( 'user-view-all' )->escaped() . '</a>';
		}
		$output .= '</div>
				<div class="action-left">';
		if ( $total > 10 ) {
			$output .= wfMessage( 'user-count-separator', '10', $total )->escaped();
		} elseif ( $total > 0 ) {
			$output .= wfMessage( 'user-count-separator', $total, $total )->escaped();
		}
		$output .= '</div>
				<div class="cleared"></div>
			</div>
		</div>
		<div class="cleared"></div> <div class="panel-body">';

		if ( $wgUser->getName() != $user_name ) {
			if ( $wgUser->isLoggedIn() && !$wgUser->isBlocked() ) {
				$output .= '<div class="user-page-message-form">
						<input type="hidden" id="user_name_to" name="user_name_to" value="' . addslashes( $user_name ) . '" />
						<span class="profile-board-message-type">' .
							wfMessage( 'userboard_messagetype' )->escaped() .
						'</span>
						<select id="message_type">
							<option value="0">' .
								wfMessage( 'userboard_public' )->escaped() .
							'</option>
							<option value="1">' .
								wfMessage( 'userboard_private' )->escaped() .
							'</option>
						</select><p><div class="form-group" style="padding:14px;">
                                      <textarea class="form-control" name="message" id="message" placeholder=""></textarea>
                                    </div>
						
						<div class="user-page-message-box-button">
							<input type="button" value="' . wfMessage( 'userboard_sendbutton' )->escaped() . '" class="site-button mw-ui-button mw-ui-progressive" />
						</div>
					</div>';
			} else {
				$login_link = SpecialPage::getTitleFor( 'Userlogin' );
				$output .= '<div class="user-page-message-form">' .
					wfMessage( 'user-board-login-message', $login_link->getFullURL() )->escaped() .
				'</div>';
			}
		}

		$output .= '<div id="user-page-board">';
		$b = new UserBoard();
		$output .= $b->displayMessages( $user_id, 0, 10 );
		$output .= '</div></div></div>';

		return $output;
	}

	/**
	 * Gets the user's fanboxes if $wgEnableUserBoxes = true; and
	 * $wgUserProfileDisplay['userboxes'] = true; and the FanBoxes extension is
	 * installed.
	 *
	 * @param $user_name String: user name
	 * @return String: HTML
	 */
	function getFanBoxes( $user_name ) {
		global $wgOut, $wgUser, $wgMemc, $wgUserProfileDisplay, $wgEnableUserBoxes;

		if ( !$wgEnableUserBoxes || $wgUserProfileDisplay['userboxes'] == false ) {
			return '';
		}

		// Add CSS & JS
		$wgOut->addModules( 'ext.fanBoxes' );

		$output = '';
		$f = new UserFanBoxes( $user_name );

		// Try cache
		/*
		$key = wfForeignMemcKey( 'huiji', '', 'user', 'profile', 'fanboxes', "{$f->user_id}" );
		$data = $wgMemc->get( $key );

		if ( !$data ) {
			wfDebug( "Got profile fanboxes for user {$user_name} from DB\n" );
			$fanboxes = $f->getUserFanboxes( 0, 10 );
			$wgMemc->set( $key, $fanboxes );
		} else {
			wfDebug( "Got profile fanboxes for user {$user_name} from cache\n" );
			$fanboxes = $data;
		}
		*/

		$fanboxes = $f->getUserFanboxes( 0, 10 );

		$fanbox_count = $f->getFanBoxCountByUsername( $user_name );
		$fanbox_link = SpecialPage::getTitleFor( 'ViewUserBoxes' );
		$per_row = 1;

		if ( $fanboxes ) {
			$output .= '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">' .
					wfMessage( 'user-fanbox-title' )->plain() .
				'</div>
				<div class="user-section-actions">
					<div class="action-right">';
			// If there are more than ten fanboxes, display a "View all" link
			// instead of listing them all on the profile page
			if ( $fanbox_count > 10 ) {
				$output .= Linker::link(
					$fanbox_link,
					wfMessage( 'user-view-all' )->plain(),
					array(),
					array( 'user' => $user_name )
				);
			}
			$output .= '</div>
					<div class="action-left">';
			if ( $fanbox_count > 10 ) {
				$output .= wfMessage( 'user-count-separator' )->numParams( 10, $fanbox_count )->parse();
			} else {
				$output .= wfMessage( 'user-count-separator' )->numParams( $fanbox_count, $fanbox_count )->parse();
			}
			$output .= '</div>
					<div class="cleared"></div>

				</div>
			</div>
			<div class="cleared"></div>

			<div class="user-fanbox-container panel-body clearfix">';

			$x = 1;
			$tagParser = new Parser();
			foreach ( $fanboxes as $fanbox ) {
				$check_user_fanbox = $f->checkIfUserHasFanbox( $fanbox['fantag_id'] );

				if ( $fanbox['fantag_image_name'] ) {
					$fantag_image_width = 45;
					$fantag_image_height = 53;
					$fantag_image = wfFindFile( $fanbox['fantag_image_name'] );
					$fantag_image_url = '';
					if ( is_object( $fantag_image ) ) {
						$fantag_image_url = $fantag_image->createThumb(
							$fantag_image_width,
							$fantag_image_height
						);
					}
					$fantag_image_tag = '<img alt="" src="' . $fantag_image_url . '" />';
				}

				if ( $fanbox['fantag_left_text'] == '' ) {
					$fantag_leftside = $fantag_image_tag;
				} else {
					$fantag_leftside = $fanbox['fantag_left_text'];
					$fantag_leftside = $tagParser->parse(
						$fantag_leftside, $this->getTitle(),
						$wgOut->parserOptions(), false
					);
					$fantag_leftside = $fantag_leftside->getText();
				}

				$leftfontsize = '10px';
				$rightfontsize = '11px';
				if ( $fanbox['fantag_left_textsize'] == 'mediumfont' ) {
					$leftfontsize = '11px';
				}

				if ( $fanbox['fantag_left_textsize'] == 'bigfont' ) {
					$leftfontsize = '15px';
				}

				if ( $fanbox['fantag_right_textsize'] == 'smallfont' ) {
					$rightfontsize = '10px';
				}

				if ( $fanbox['fantag_right_textsize'] == 'mediumfont' ) {
					$rightfontsize = '11px';
				}

				// Get permalink
				$fantag_title = Title::makeTitle( NS_FANTAG, $fanbox['fantag_title'] );
				$right_text = $fanbox['fantag_right_text'];
				$right_text = $tagParser->parse(
					$right_text, $this->getTitle(), $wgOut->parserOptions(), false
				);
				$right_text = $right_text->getText();

				// Output fanboxes
				$output .= "<div class=\"fanbox-item\">
					<div class=\"individual-fanbox\" id=\"individualFanbox" . $fanbox['fantag_id'] . "\">
						<div class=\"show-message-container-profile\" id=\"show-message-container" . $fanbox['fantag_id'] . "\">
							<a class=\"perma\" style=\"font-size:8px; color:" . $fanbox['fantag_right_textcolor'] . "\" href=\"" . htmlspecialchars( $fantag_title->getFullURL() ) . "\" title=\"{$fanbox['fantag_title']}\">" . wfMessage( 'fanbox-perma' )->plain() . "</a>
							<table class=\"fanBoxTableProfile\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
								<tr>
									<td id=\"fanBoxLeftSideOutputProfile\" style=\"color:" . $fanbox['fantag_left_textcolor'] . "; font-size:$leftfontsize\" bgcolor=\"" . $fanbox['fantag_left_bgcolor'] . "\">" . $fantag_leftside . "</td>
									<td id=\"fanBoxRightSideOutputProfile\" style=\"color:" . $fanbox['fantag_right_textcolor'] . "; font-size:$rightfontsize\" bgcolor=\"" . $fanbox['fantag_right_bgcolor'] . "\">" . $right_text . "</td>
								</tr>
							</table>
						</div>
					</div>";

				if ( $wgUser->isLoggedIn() ) {
					if ( $check_user_fanbox == 0 ) {
						$output .= '<div class="fanbox-pop-up-box-profile" id="fanboxPopUpBox' . $fanbox['fantag_id'] . '">
							<table cellpadding="0" cellspacing="0" align="center">
								<tr>
									<td style="font-size:10px">' .
										wfMessage( 'fanbox-add-fanbox' )->plain() .
									'</td>
								</tr>
								<tr>
									<td align="center">
										<input type="button" class="fanbox-add-button-half" value="' . wfMessage( 'fanbox-add' )->plain() . '" size="10" />
										<input type="button" class="fanbox-cancel-button" value="' . wfMessage( 'cancel' )->plain() . '" size="10" />
									</td>
								</tr>
							</table>
						</div>';
					} else {
						$output .= '<div class="fanbox-pop-up-box-profile" id="fanboxPopUpBox' . $fanbox['fantag_id'] . '">
							<table cellpadding="0" cellspacing="0" align="center">
								<tr>
									<td style="font-size:10px">' .
										wfMessage( 'fanbox-remove-fanbox' )->plain() .
									'</td>
								</tr>
								<tr>
									<td align="center">
										<input type="button" class="fanbox-remove-button-half" value="' . wfMessage( 'fanbox-remove' )->plain() . '" size="10" />
										<input type="button" class="fanbox-cancel-button" value="' . wfMessage( 'cancel' )->plain() . '" size="10" />
									</td>
								</tr>
							</table>
						</div>';
					}
				}

				// Show a message to anonymous users, prompting them to log in
				if ( $wgUser->getID() == 0 ) {
					$output .= '<div class="fanbox-pop-up-box-profile" id="fanboxPopUpBox' . $fanbox['fantag_id'] . '">
						<table cellpadding="0" cellspacing="0" align="center">
							<tr>
								<td style="font-size:10px">' .
									wfMessage( 'fanbox-add-fanbox-login' )->parse() .
								'</td>
							</tr>
							<tr>
								<td align="center">
									<input type="button" class="fanbox-cancel-button" value="' . wfMessage( 'cancel' )->plain() . '" size="10" />
								</td>
							</tr>
						</table>
					</div>';
				}

				$output .= '</div>';

				$x++;
			}

			$output .= '</div></div>';
		}

		return $output;
	}

	/**
	 * Initialize UserProfile data for the given user if that hasn't been done
	 * already.
	 *
	 * @param $username String: name of the user whose profile data to initialize
	 */
	private function initializeProfileData( $username ) {
		if ( !$this->profile_data ) {
			$profile = new UserProfile( $username );
			$this->profile_data = $profile->getProfile();
		}
	}
	/**
	 * Get common interests with the user you are watching
	 *
	 * @param $target_user_id:current user; $user_id:his id
	 * @return array
	 */
	function getCommonInterest( $user_id,$target_user_id){
		global $wgUser;
		$user_id = $this->user_id;
		$target_user_id = $wgUser->getId();
		$res = UserSiteFollow::getCommonInterest($user_id,$target_user_id);
		$us = new UserStatus($this->user);
		$gender = $us->getGender();
		if ($gender == 'male'){
			$genderIcon = '他';
		} elseif ($gender == 'female'){
			$genderIcon = '她';
		} else {
			$genderIcon = 'TA';
		}
		$output = '<div class="panel panel-default"><div class="user-section-heading panel-heading">
				<div class="user-section-title">我和'.$genderIcon.'的共同兴趣:
				</div>
				<div class="user-section-actions">
					<div class="action-right">
					</div>
					<div class="action-left">
					</div>
					<div class="cleared"></div>
				</div>
			</div>
			<div class="cleared"></div>
			<div class="common-interest-container panel-body">';
			
		if(!empty($res)){
			foreach ($res as $value) {
				$Iname = HuijiPrefix::prefixToSiteName($value);
				$Iurl = HuijiPrefix::prefixToUrl($value);
				$output .= '<span class="label label-primary"><a href="'.$Iurl.'">'.$Iname.'&nbsp;</a></span>';
			}
		}else{
			$output .='<p>&nbsp;您和'.$genderIcon.'还没有共同兴趣~</p>';
		}
		$output .='</div></div>';
		return $output;
	}

    function cropModal(){
        $output = '<div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <form class="avatar-form" action="/index.php" enctype="multipart/form-data" method="post">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title" id="avatar-modal-label">修改头像</h4>
                </div>
                <div class="modal-body">
                  <div class="avatar-body">

                    <!-- Upload image and data -->
                    <div class="avatar-upload">
                      <input type="hidden" class="avatar-src" name="avatar_src">
                      <input type="hidden" class="avatar-data" name="avatar_data">
                      <label for="avatarInput">本地上传</label>
                      <input type="file" class="avatar-input" id="avatarInput" name="avatar_file">
                    </div>

                    <!-- Crop and preview -->
                    <div class="row">
                      <div class="col-md-9">
                        <div class="avatar-wrapper"></div>
                      </div>
                      <div class="col-md-3">
                        <div class="avatar-preview preview-lg"></div>
                        <div class="avatar-preview preview-md"></div>
                        <div class="avatar-preview preview-sm"></div>
                      </div>
                    </div>

                    <div class="row avatar-btns">
                      <div class="col-md-9">
                        <div class="btn-group">
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-90" title="Rotate -90 degrees">向左旋转</button>
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-15">-15deg</button>
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-30">-30deg</button>
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="-45">-45deg</button>
                        </div>
                        <div class="btn-group">
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="90" title="Rotate 90 degrees">向右旋转</button>
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="15">15deg</button>
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="30">30deg</button>
                          <button type="button" class="btn btn-primary" data-method="rotate" data-option="45">45deg</button>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block avatar-save">完成</button>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div> -->
              </form>
            </div>
          </div>
        </div><!-- /.modal -->

        <!-- Loading state -->
        <div class="loading" aria-label="Loading" role="img" tabindex="-1"></div>';

        return $output;
    }
}
