<?php
/**
 * A special page for show all sites user following
 *user_id :thte user who is visting
 *target_user_id :user be visted
 * Example URL: index.php?title=Special:FollowSites&user_id=*&target_user_id=* 
 *
 * @file
 * @ingroup Extensions
 * @author David Pean <david.pean@gmail.com>
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class SpecialFollowSites extends SpecialPage {
	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		global $wgUser,$wgSitename;
		parent::__construct( 'FollowSites' );
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
		$output = '<i>'.$this->msg( 'editranknote' )->plain().'</i>';
		// Add CSS
		// $out->addModuleStyles( 'ext.socialprofile.useruserfollows.css' );
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );
		// Add JS
		$out->addModuleScripts( 'ext.socialprofile.usersitefollows.js');
		$output = '';
		/**
		 * Get query string variables
		 */
		$user_id = $request->getVal( 'user_id' );
		$target_user_id = $request->getInt( 'target_user_id' );
		$rel_type = $request->getInt( 'rel_type' );
		$page = $request->getInt( 'page' );
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
		if ( $target_user_id == 0 || !is_int( $target_user_id ) ) {
			$out->setPageTitle( $this->msg( 'g-error-title' )->plain() );
			$out->addHTML( $this->msg( 'g-error-message-invalid-link' )->plain() );
			return false;
		}
		$sites = UserSiteFollow::getFullFollowedSites( $user_id,$target_user_id );
		if( !$sites ){
		    $output .= '<div class="top-users"><h3>暂时还没有关注哦</h3>';
		}
		$output .= '<div class="top-users">';
		foreach ( $sites as $user ) {
			$site_name = $user['val'];
			$domain_name = $user['key'];
			$is_follow = $user['is'];
			if ( $user_id == '' || $user_id == 0 ) {
				$output .= '<div class=\"top-fan-row\"><a href=http://'.$domain_name.'.huiji.wiki class="list-group-item">' .$site_name .'</a>';
				
			}else{
				if ($is_follow == 'Y') {
					$output .= '<div class=\"top-fan-row\"><a href=http://'.$domain_name.'.huiji.wiki class="list-group-item">' .$site_name .'<span class="badge user-site-follow-from-modal unfollow">取关</span></a>';
				}else{
					$output .= '<div class=\"top-fan-row\"><a href=http://'.$domain_name.'.huiji.wiki class="list-group-item">' .$site_name .'<span class="badge user-site-follow-from-modal">关注</span></a>';
				}
			}
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
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
