<?php
/**
 * A special page for rank all sites in huiji.wiki
 *
 * @file
 * @ingroup Extensions
 * @author 
 * @copyright Copyright © 2007, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class SpecialSiteRank extends SpecialPage {
	/**
	 * Constructor -- set up the new special page
	 */
	public function __construct() {
		global $wgUser,$wgSitename;
		parent::__construct( 'SiteRank' );
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
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );

		$page = $request->getInt( 'page' );
		/**
		 * Set up config for page / default values
		 */
		if ( !$page || !is_numeric( $page ) ) {
			$page = 1;
		}
		// if ( !$rel_type || !is_numeric( $rel_type ) ) {
		// 	$rel_type = 2;
		// }
		$per_page = 20;
		$per_row = 2;
		$allSiteRank = AllSitesInfo::getAllSitesRank();
		$total = count($allSiteRank);
		$star_page = $per_page*($page-1);
		foreach ($allSiteRank as $key => $value) {
			$output .= "<div class=\"top-fan-row\">
				<span class=\"top-fan-num\">{$value}.</span>
				<span class=\"top-fan\"><a href='" . HuijiPrefix::prefixToUrl($key) . "'>" .
						HuijiPrefix::prefixToSiteName($key) .'</a></span>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
		}
		// print_r($allSiteRank);


		// $user_id = User::idFromName( $user_name );
		// $target_user = User::newFromId( $user_id );
		// $userPage = Title::makeTitle( NS_USER, $user_name );
		// $sitefollows = UserSiteFollow::getUserFollowSite($target_user, $wgHuijiPrefix);
		// $total = count($sitefollows);
		// $star_page = $per_page*($page-1);
		// $result = array_slice($sitefollows,$star_page ,$per_page );
		// if( !$result ){
		//     $output .= '<div class="top-users"><h3>此页暂时没有排行</h3>';
		// }
		// $x = $star_page+1;
		// foreach ( $result as $user ) {
		// 	$user_title = Title::makeTitle( NS_USER, $user['user'] );
		// 	$commentIcon = $user['url'];
		// 	$output .= "<div class=\"top-fan-row\">
		// 		<span class=\"top-fan-num\">{$x}.</span>
		// 		<span class=\"top-fan\"><a href='" . $user['userUrl'] . "'>
		// 			{$commentIcon} </a><a href='" . $user['userUrl'] . "'>" .
		// 				$user['user'] .'</a><i>'.$user['level'] .'
		// 		</i></span>';
		// 	$output .= '<span class="top-fan-points"><b>' .
		// 		number_format( $user['count'] ) . '</b> ' .
		// 		$this->msg( 'top-fans-times' )->plain() . '</span>';
		// 	$output .= '<div class="cleared"></div>';
		// 	$output .= '</div>';
		// 	$x++;
		// }
		$output .= '</div><div class="cleared"></div>';
		/**
		 * Build next/prev nav
		 */
		$total = intval( str_replace( ',', '', $total ) );
		$numofpages = $total / $per_page;
		$pageLink = $this->getPageTitle();
		if ( $numofpages > 1 ) {
			$output .= '<nav class="page-nav pagination">';
			if ( $page > 1 ) {
				$output .= '<li>'.Linker::link(
					$pageLink,
					'<span aria-hidden="true">&laquo;</span>',
					array(),
					array(
						'user' => $user_name,
						'rel_type' => $rel_type,
						'page' => ( $page - 1 )
					)
				) . '</li>';
			}
			if ( ( $total % $per_page ) != 0 ) {
				$numofpages++;
			}
			// if ( $numofpages >= 9 && $page < $total ) {
			// 	$numofpages = 9 + $page;
			// }
			// if ( $numofpages >= ( $total / $per_page ) ) {
			// 	$numofpages = ( $total / $per_page ) + 1;
			// }
			for ( $i = 1; $i <= $numofpages; $i++ ) {
				if ( $i == $page ) {
					$output .= ( '<li class="active"><a href="#">'.$i.' <span class="sr-only">(current)</span></a></li>' );
				} else {
					$output .= '<li>'.Linker::link(
						$pageLink,
						$i,
						array(),
						array(
							'user' => $user_name,
							'rel_type' => $rel_type,
							'page' => $i
						)
					) . '</li>';
				}
			}
			if ( ( $total - ( $per_page * $page ) ) > 0 ) {
				$output .= '<li>' .
					Linker::link(
						$pageLink,
						'<span aria-hidden="true">&raquo;</span>',
						array(),
						array(
							'user' => $user_name,
							'rel_type' => $rel_type,
							'page' => ( $page + 1 )
						)
					).'</li>';
			}
			$output .= '</nav>';
		}
		$out->addHTML( $output );
	}
}
