<?php
/**
 * all sites info ex:siterank..
 */
class AllSitesInfo{

	static function getAllSitesRankFromCache(){

		
	}

	static function getAllSitesRankFromDB(){


	}

	static function getAllSitesRank(){
		$allSite = HuijiPrefix::getAllPrefix();
		$today = date('Y-m-d');
		$yesterday = date('Y-m-d',strtotime('-1 days'));
		$lastWeek = date('Y-m-d',strtotime('-8 days'));
		$lastMonth = date('Y-m-d',strtotime('-31 days'));
		$ueb = new UserEditBox();
		$viewDate = array();
		$editDate = array();
		foreach ($allSite as $value) {
			$viewResult['yesterday'] = $ueb->getSiteViewCount( '', $value, $yesterday, $yesterday );
			$viewResult['week'] = $ueb->getSiteViewCount( '', $value, $lastWeek, $yesterday );
			$viewResult['month'] = $ueb->getSiteViewCount( '', $value, $lastMonth, $yesterday );
			$editResult['yesterday'] = $ueb->getSiteEditCount( '', $value, $yesterday, $yesterday );
			$editResult['week'] = $ueb->getSiteEditCount( '', $value, $lastWeek, $yesterday );
			$editResult['month'] = $ueb->getSiteEditCount( '', $value, $lastMonth, $yesterday );
			$viewDate[$value] = round($viewResult['yesterday']+$viewResult['week']/7+$viewResult['month']/30);
			$editDate[$value] = round($editResult['yesterday']+$editResult['week']/7+$editResult['month']/30);
		}
		asort($viewDate);
		asort($editDate);
		$i=1;
		$viewRes = array();
		$editRes = array();
		foreach ($viewDate as $key => $value) {
			$viewRes[$key] = $i*10;
			$i++;
		}
		$j=1;
		foreach ($editDate as $key => $value) {
			$editRes[$key] = $j*10;
			$j++;
		}
		$allRank = array();
		foreach ($viewRes as $key => $value) {
			$allRank[$key] = $value*3 + $editRes[$key]*7;
		}
		arsort($allRank);
		$x = 1;
		// print_r($editRes);
		// print_r($viewRes);
		foreach ($allRank as $key => $value) {
			$numRank[$key] = $x;
			$x++;
		}
		if ($numRank) {
			return $numRank;
		}
	}

}