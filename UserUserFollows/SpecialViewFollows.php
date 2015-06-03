<?php
/**
 * A special page for viewing all relationships by type
 * Example URL: index.php?title=Special:ViewRelationships&user=Pean&rel_type=1 (viewing friends)
 * Example URL: index.php?title=Special:ViewRelationships&user=Pean&rel_type=2 (viewing foes)
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialViewFollows extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		parent::__construct( 'ViewFollows' );
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	function getGroupName() {
		return 'users';
	}

	/**
	 * Show the special page
	 *
	 * @param $params Mixed: parameter(s) passed to the page or null
	 */
	public function execute( $params ) {
		$lang = $this->getLanguage();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.useruserfollows.css' );

		// Add JS
		$out->addModuleScripts( 'ext.socialprofile.useruserfollows.js');

		$output = '';

		/**
		 * Get query string variables
		 */
		$user_name = $request->getVal( 'user' );
		$rel_type = $request->getInt( 'rel_type' );
		$page = $request->getInt( 'page' );

		/**
		 * Redirect Non-logged in users to Login Page
		 * It will automatically return them to the ViewRelationships page
		 */
		// if ( !$user->isLoggedIn() && $user_name == '' ) {
		// 	$out->setPageTitle( $this->msg( 'ur-error-page-title' )->plain() );
		// 	$login = SpecialPage::getTitleFor( 'Userlogin' );
		// 	$out->redirect( htmlspecialchars( $login->getFullURL( 'returnto=Special:ViewFollows' ) ) );
		// 	return false;
		// }

		/**
		 * Set up config for page / default values
		 */
		if ( !$page || !is_numeric( $page ) ) {
			$page = 1;
		}
		if ( !$rel_type || !is_numeric( $rel_type ) ) {
			$rel_type = 2;
		}
		$per_page = 50;
		$per_row = 2;

		/**
		 * If no user is set in the URL, we assume its the current user
		 */
		if ( !$user_name ) {
			$user_name = $user->getName();
		}
		$user_id = User::idFromName( $user_name );
		$target_user = User::newFromId( $user_id );
		$userPage = Title::makeTitle( NS_USER, $user_name );

		/**
		 * Error message for username that does not exist (from URL)
		 */
		if ( $user_id == 0 ) {
			$out->setPageTitle( $this->msg( 'ur-error-title' )->plain() );
			$output = '<div class="relationship-error-message">' .
				$this->msg( 'ur-error-message-no-user' )->plain() .
			'</div>
			<div class="relationship-request-buttons">
				<input type="button" class="site-button" value="' . $this->msg( 'ur-main-page' )->plain() . '" onclick=\'window.location="index.php?title=' . $this->msg( 'mainpage' )->inContentLanguage()->escaped() . '"\' />';
			if ( $user->isLoggedIn() ) {
				$output .= '<input type="button" class="site-button" value="' . $this->msg( 'ur-your-profile' )->plain() . '" onclick=\'window.location="' . htmlspecialchars( $user->getUserPage()->getFullURL() ) . '"\' />';
			}
			$output .= '</div>';
			$out->addHTML( $output );
			return false;
		}

		/**
		 * Get all relationships
		 */
		$uuf = new UserUserFollow();
		$follows = $uuf->getFollowList( $target_user, $rel_type, $perpage, $page);
		$followerCount = UserUserFollow::getFollowerCount($target_user);
		$followingCount = UserUserFollow::getFollowingCount($target_user);
		$back_link = Title::makeTitle( NS_USER, $user_name );
		$target = SpecialPage::getTitleFor('ViewFollows');
		$query1 = array('user' => $user_name, 'rel_type' => 1);
		$query2 = array('user' => $user_name, 'rel_type' => 2);
		$blast = SpecialPage::getTitleFor('SendBoardBlast');

		if ( $rel_type == 1 ) {
			$out->setPageTitle( $this->msg( 'ur-title-friend', $user_name )->parse() );

			$total = $followingCount;
			$target = SpecialPage::getTitleFor('ViewFollows');
			$query1 = array('user' => $user_name, 'rel_type' => 1);
			$query2 = array('user' => $user_name, 'rel_type' => 2);
			$rem = $this->msg( 'ur-remove-relationship-friend' )->plain();
			$output .= '<div class="back-links">
			<a href="' . htmlspecialchars( $back_link->getFullURL() ) . '">' .
				$this->msg( 'ur-backlink', $user_name )->parse() .
			'</a> | '.Linker::LinkKnown($target, '关注我的人', array(), $query2).'
		</div>
		<div class="relationship-wrapper"><div class="relationship-count">' .
			$this->msg(
				'ur-relationship-count-friends',
				$user_name,
				$total
			)->text() . '</div><div class="relationship-list">';
		} else {
			$out->setPageTitle( $this->msg( 'ur-title-foe', $user_name )->parse() );

			$total = $followerCount;

			$rem = $this->msg( 'ur-remove-relationship-foe' )->plain();

			$output .= '<div class="back-links">
			<a href="' . htmlspecialchars( $back_link->getFullURL() ) . '">' .
				$this->msg( 'ur-backlink', $user_name )->parse() .
			'</a> | '.Linker::LinkKnown($target, '我关注的人', array(), $query1).' | '.Linker::LinkKnown($blast, '向关注我的人群发信息').'
		</div>
		<div class="relationship-count">'
			. $this->msg(
				'ur-relationship-count-foes',
				$user_name,
				$total
			)->text() . '</div>';
		}
		if ( $follows ) {
			$x = 1;

			foreach ( $follows as $follow ) {
				// $indivRelationship = UserRelationship::getUserRelationshipByID(
				// 	$relationship['user_id'],
				// 	$user->getID()
				// );

				$username = $follow['user_name'];
				$userobj = User::newFromName($username);
				$ust = new UserStatus($userobj);
				$allinfo = $ust->getUserAllInfoDB( );

				// Safe titles
				$userPage = Title::makeTitle( NS_USER, $allinfo['username'] );
				// $indivFollow = $uuf->checkUserUserFollow($user, User::newFromId($follow['user_id']));
				$is_follow = $allinfo['is_follow'];
				if ($is_follow == 'Y') {
					$followButton = '<li  class="user-user-follow unfollow" data-username="'.$allinfo['username'].'"><a><i class="fa fa-minus-square-o"></i>取关</a></li> ';
				} else {
					$followButton = '<li class="user-user-follow" data-username="'.$allinfo['username'].'"><i class="fa fa-plus-square-o"></i></i>关注</li> ';
				}

				$userPageURL = htmlspecialchars( $userPage->getFullURL() );
				// $avatar = new wAvatar( $follow['user_id'], 'ml' );

				// $avatar_img = $avatar->getAvatarURL();
				$avatar_img = $allinfo['url'];
				$user_gender = $allinfo['gender'];
				$user_status = $allinfo['status'];
				$user_count = $allinfo['usercounts'];
				$user_counted = $allinfo['usercounted'];
				$editcount = $allinfo['editcount'];
				// $commonfollow = $allinfo['commonfollow'];
				// $minefollowerhim = $allinfo['minefollowerhim'];
				$user_level = $allinfo['level'];

				$username_length = strlen( $allinfo['username'] );
				$username_space = stripos( $allinfo['username'], ' ' );

				if ( ( $username_space == false || $username_space >= "30" ) && $username_length > 30 ) {
					$user_name_display = substr( $allinfo['username'], 0, 30 ) .
						' ' . substr( $allinfo['username'], 30, 50 );
				} else {
					$user_name_display = $allinfo['username'];
				}
				if ($user_gender == 'male'){
					$genderIcon = '♂';
				} elseif ($user_gender == 'female'){
					$genderIcon = '♀';
				} else {
					$genderIcon = '';
				}
				$output .= "<div class=\"relationship-item\">
					<a href=\"{$userPageURL}\" data-name=\"{$user_name_display}\">{$avatar_img}</a>
					<div class=\"relationship-info\">
						<div class=\"relationship-name\">
							<a href=\"{$userPageURL}\">{$user_name_display}</a><i>{$genderIcon}</i><i>{$user_level}</i>
						</div>
					<div class=\"relationship-actions\">";
				if(empty($user_status)){
					$output .= '<div>这个人很懒</div>';
				}else{
					$output .= '<div>'.$user_status.'</div>';
				}
				$output .= '<div>关注数:'.$user_count.' | 被关注:'.$user_counted.' | 编辑:'.$editcount.'</div>';
				$output .= '<ul class="relationship-list-btn">'.$followButton;
				$target = SpecialPage::getTitleFor( 'GiveGift' );
				$query = array('user' => $follow['user_name']);
				$output .= '<li>'.Linker::LinkKnown($target, '<i class="fa fa-gift"></i>礼物</a>', array(), $query).'</li> </ul>';
				/*$output .=  '<li>共同关注:';
				if(!empty($commonfollow)){
					foreach ($commonfollow as $val) {
						$output .= $val.'&nbsp';
					}
				}else{
					$output .= '(<i>暂无</i>)';
				}
				$output .= '</li><li>我关注的谁也关注Ta:';
				if(!empty($minefollowerhim)){
					foreach ($minefollowerhim as $val) {
						$output .= $val.'&nbsp';
					}
				}else{
					$output .= '(<i>暂无</i>)';
				}
				$output .= '</li>';*/
				$output .= '</div>
					<div class="cleared"></div>
				</div>';

				$output .= '</div>';
				if ( $x == count( $follows ) || $x != 1 && $x % $per_row == 0 ) {
					$output .= '<div class="cleared"></div>';
				}
				$x++;
			}
		}

		/**
		 * Build next/prev nav
		 */
		$total = intval( str_replace( ',', '', $total ) );
		$numofpages = $total / $per_page;

		$pageLink = $this->getPageTitle();

		if ( $numofpages > 1 ) {
			$output .= '<div class="page-nav">';
			if ( $page > 1 ) {
				$output .= Linker::link(
					$pageLink,
					$this->msg( 'ur-previous' )->plain(),
					array(),
					array(
						'user' => $user_name,
						'rel_type' => $rel_type,
						'page' => ( $page - 1 )
					)
				) . $this->msg( 'word-separator' )->plain();
			}

			if ( ( $total % $per_page ) != 0 ) {
				$numofpages++;
			}
			if ( $numofpages >= 9 && $page < $total ) {
				$numofpages = 9 + $page;
			}
			if ( $numofpages >= ( $total / $per_page ) ) {
				$numofpages = ( $total / $per_page ) + 1;
			}

			for ( $i = 1; $i <= $numofpages; $i++ ) {
				if ( $i == $page ) {
					$output .= ( $i . ' ' );
				} else {
					$output .= Linker::link(
						$pageLink,
						$i,
						array(),
						array(
							'user' => $user_name,
							'rel_type' => $rel_type,
							'page' => $i
						)
					) . $this->msg( 'word-separator' )->plain();
				}
			}

			if ( ( $total - ( $per_page * $page ) ) > 0 ) {
				$output .= $this->msg( 'word-separator' )->plain() .
					Linker::link(
						$pageLink,
						$this->msg( 'ur-next' )->plain(),
						array(),
						array(
							'user' => $user_name,
							'rel_type' => $rel_type,
							'page' => ( $page + 1 )
						)
					);
			}
			$output .= '</div></div></div>';
		}

		$out->addHTML( $output );
	}
}
