<?php
/**
 * A special page for rank all sites in huiji.wiki
 *
 * @file
 * @ingroup Extensions
 * @author slx
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
		$out = $this->getOutput();
		$this->setHeaders();
		$out->addHtml(TopUsersPoints::getRankingDropdown( '站点排行榜'));
		$output = '<i>'.$this->msg( 'editranknote' )->plain().'</i>';
		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );
		$yesterday = date('Y-m-d',strtotime('-1 days'));
		$beforeyesterday = date('Y-m-d',strtotime('-2 days'));
		$allSiteRank = AllSitesInfo::getAllSitesRankData( '', $yesterday );
		$beforeallSiteRank = AllSitesInfo::getAllSitesRankData( '', $beforeyesterday );
		$beforeArr = array();
		foreach ($beforeallSiteRank as $value) {
			$beforeArr[$value['site_prefix']] = $value['site_rank'];
		}
		$output .= '<div class="top-users">';
		$total = count($allSiteRank);
		if($total > 50){
			$allSiteRank = array_slice($allSiteRank,0 ,50);
		} elseif ($total == 0){
			$output .= '<p>站点排行榜正在生成中...请刷新重试！</p>';
			$output .= '</div><div class="cleared"></div>';
			$out->addHTML( $output );
			return;
		}
		
		foreach ($allSiteRank as $key => $value) {
			$diff = abs( $value['site_rank'] - $beforeArr[$value['site_prefix']] );
			if( $diff==0 ){
				$diff ='';
			}
			if ( $value['site_rank'] > $beforeArr[$value['site_prefix']] ) {
				$change = 'glyphicon glyphicon-arrow-down red';
			}elseif ( $value['site_rank'] < $beforeArr[$value['site_prefix']] ) {
				$change = 'glyphicon glyphicon-arrow-up green';
			}else{
				$change = 'glyphicon glyphicon-minus';
			}
			$output .= "<div class=\"top-fan-row\">
				<span class=\"top-fan-num\">{$value['site_rank']}.</span>
				<span class=\"top-fan\"><a href='" . HuijiPrefix::prefixToUrl($value['site_prefix']) . "'>" . (new wSiteAvatar($value['site_prefix'], 's'))->getAvatarHtml() .
				HuijiPrefix::prefixToSiteName($value['site_prefix']) ."</a><i class= \"".$change." hidden-sm hidden-xs\">".$diff."</i><i class=\"fa fa-flag-checkered hidden-sm hidden-xs\">".$value['best_rank']."</i></span><span class=\"top-fan-points\">".$value['site_score'].'马赫</sp>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
		}
		$output .= '</div><div class="cleared"></div>';
		$out->addHTML( $output );
	}
}
