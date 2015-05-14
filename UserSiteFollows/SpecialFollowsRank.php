<?php
/**
 * A special page for rank all followers by editcounts with this wiki
 * Example URL: index.php?title=Special:ViewRelationships&user=Pean&rel_type=1 (viewing friends)
 * Example URL: index.php?title=Special:ViewRelationships&user=Pean&rel_type=2 (viewing foes)
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialFollowsRank extends SpecialPage {

	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		global $wgUser,$wgSitename;
		parent::__construct( 'FollowsRank' );
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
		global $wgUser,$wgSitename,$wgHuijiPrefix,$wgUserLevels;
		$lang = $this->getLanguage();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		// Set the page title, robot policies, etc.
		$this->setHeaders();

		// Add CSS
		// $out->addModuleStyles( 'ext.socialprofile.useruserfollows.css' );
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );


		// Add JS
		// $out->addModuleScripts( 'ext.socialprofile.useruserfollows.js');

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
		if ( !$user->isLoggedIn() && $user_name == '' ) {
			$out->setPageTitle( $this->msg( 'ur-error-page-title' )->plain() );
			$login = SpecialPage::getTitleFor( 'Userlogin' );
			$out->redirect( htmlspecialchars( $login->getFullURL( 'returnto=Special:FollowsRank' ) ) );
			return false;
		}

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
		$sitefollows = UserSiteFollow::getUserFollowSite($target_user, $wgHuijiPrefix);
		// foreach ( $sitefollows as $follow ) {
		// 	$username = $follow['user'];
		// 	$userPageURL = $follow['userUrl'];
		// 	$avatar_img = $follow['url'];
		// 	$user_level = $follow['level'];
		// 	$username_length = strlen( $follow['user'] );
		// 	$username_space = stripos( $follow['user'], ' ' );
		// 	if ( ( $username_space == false || $username_space >= "30" ) && $username_length > 30 ) {
		// 		$user_name_display = substr( $follow['user'], 0, 30 ) .						' ' . substr( $follow['user'], 30, 50 );
		// 	} else {
		// 		$user_name_display = $follow['user'];
		// 	}
		// 	$output .= "<div class=\"relationship-item\">
		// 		<a href=\"{$userPageURL}\">{$avatar_img}</a>
		// 		<div class=\"relationship-info\">
		// 			<div class=\"relationship-name\">
		// 				<a href=\"{$userPageURL}\">{$user_name_display}</a><i>{$user_level}</i>
		// 			</div>
		// 		<div class=\"relationship-actions\"><ul>";
		// 	$output .= '<li>编辑数:'.$follow['count'].'</li>';
		// 	$output .= $followButton;
		// 	$target = SpecialPage::getTitleFor( 'GiveGift' );
		// 	$query = array('user' => $follow['user']);
		// 	$output .= '<li>'.Linker::LinkKnown($target, '<i class="fa fa-gift"></i>礼物</a>', array(), $query).'</li> ';
		// 	$output .= '</ul></div>
		// 		<div class="cleared"></div>
		// 	</div>';
		// 	$output .= '</div>';
		// 	$output .= '<div class="cleared"></div>';
		// }
		$output .= '<div class="top-users">';
		$last_level_count = 1000;
		$x = 1;
		foreach ( $sitefollows as $user ) {
			$user_title = Title::makeTitle( NS_USER, $user['user'] );
			$commentIcon = $user['url'];

			// Break list into sections based on User Level if it's defined for this site
			// if ( is_array( $wgUserLevels ) ) {
				// $user_level = $user['level'] ;
				if ( $user['count'] < $last_level_count ) {
					// $output .= "<div class=\"top-fan-row\"><div class=\"top-fan-level\">
					// 	{$last_level_count}
					// 	↑</div></div>";
					$last_level_count = $last_level_count - 100;
				}elseif( $user['count'] == $last_level_count ){
					if($last_level_count>0){
						$output .= "<div class=\"top-fan-row\"><div class=\"top-fan-level\">
							{$last_level_count}
							↑</div></div>";
						$last_level_count = $last_level_count;
					}
				}
				
			// }

			$output .= "<div class=\"top-fan-row\">
				<span class=\"top-fan-num\">{$x}.</span>
				<span class=\"top-fan\">
					{$commentIcon} <a href='" . $user['userUrl'] . "'>" .
						$user['user'] .'<i>'.$user['level'] .'</i></a>
				</span>';

			$output .= '<span class="top-fan-points"><b>' .
				number_format( $user['count'] ) . '</b> ' .
				$this->msg( 'top-fans-points' )->plain() . '</span>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
			$x++;
		}

		$output .= '</div><div class="cleared"></div>';

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
			$output .= '</div>';
		}

		$out->addHTML( $output );
	}
}
