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
		$output = '<i>'.$this->msg( 'editranknote' )->plain().'</i>';
		// Add CSS
		$out->addModuleStyles( 'ext.socialprofile.userstats.css' );
		$yesterday = date('Y-m-d',strtotime('-1 days'));
		$allSiteRank = AllSitesInfo::getAllSitesRankData( '', $yesterday );
		$total = count($allSiteRank);
		if($total > 50){
			$allSiteRank = array_slice($allSiteRank,0 ,50);
		}
		$output .= '<div class="top-users">';
		foreach ($allSiteRank as $key => $value) {
			$output .= "<div class=\"top-fan-row\">
				<span class=\"top-fan-num\">{$value['site_rank']}.</span>
				<span class=\"top-fan\"><a href='" . HuijiPrefix::prefixToUrl($value['site_prefix']) . "'>" .
				HuijiPrefix::prefixToSiteName($value['site_prefix']) ."</a></span>
				<span class=\"top-fan-points\">".$value['site_score'].'马赫</span>';
			$output .= '<div class="cleared"></div>';
			$output .= '</div>';
		}
		$output .= '</div><div class="cleared"></div>';
		$out->addHTML( $output );
	}
}
