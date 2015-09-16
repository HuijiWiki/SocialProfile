<?php

require_once __DIR__ . "/Maintenance.php";

class HelloWorld extends Maintenance {
	public function execute() {
		$allSite = HuijiPrefix::getAllPrefix();
		$today = date('Y-m-d');
		$yesterday = date('Y-m-d',strtotime('-1 days'));
		$lastWeek = date('Y-m-d',strtotime('-8 days'));
		$lastMonth = date('Y-m-d',strtotime('-31 days'));
		$ueb = new UserEditBox();
		$editUserYesterday = $ueb->getSiteEditUserCount( $yesterday, $yesterday);
		$editUserWeek = $ueb->getSiteEditUserCount( $lastWeek, $yesterday);
		$editUserMonth = $ueb->getSiteEditUserCount( $lastMonth, $yesterday);
		$viewDate = array();
		$editDate = array();
		$editUserDate = array();
		foreach ($allSite as $value) {
			$viewResult['yesterday'] = $ueb->getSiteViewCount( '', $value, $yesterday, $yesterday );
			$viewResult['week'] = $ueb->getSiteViewCount( '', $value, $lastWeek, $yesterday );
			$viewResult['month'] = $ueb->getSiteViewCount( '', $value, $lastMonth, $yesterday );
			$editResult['yesterday'] = $ueb->getSiteEditCount( '', $value, $yesterday, $yesterday );
			$editResult['week'] = $ueb->getSiteEditCount( '', $value, $lastWeek, $yesterday );
			$editResult['month'] = $ueb->getSiteEditCount( '', $value, $lastMonth, $yesterday );
			$viewDate[$value] = round($viewResult['yesterday']+$viewResult['week']/7+$viewResult['month']/30);
			$editDate[$value] = round($editResult['yesterday']+$editResult['week']/7+$editResult['month']/30);
			$editUserDate[$value] = round(isset($editUserYesterday[$value])?$editUserYesterday[$value]:0+(isset($editUserWeek[$value])?$editUserWeek[$value]:0)/7+(isset($editUserMonth[$value])?$editUserMonth[$value]:0)/30);
		}
		//sort arr
		asort($viewDate);
		asort($editDate);
		asort($editUserDate);
		$i=1;
		//loop score
		$viewRes = array();
		$editRes = array();
		$editUserRes = array();
		foreach ($viewDate as $key => $value) {
			$viewRes[$key] = $i*10;
			$i++;
		}
		$j=1;
		foreach ($editDate as $key => $value) {
			$editRes[$key] = $j*10;
			$j++;
		}
		$k=1;
		foreach ($editUserDate as $key => $value) {
			$editUserRes[$key] = $k*10;
			$k++;
		}
		//highest score
		$highest = ($k-1)*100;
		//Comprehensive 2 4 4
		$allRank = array();
		foreach ($viewRes as $key => $value) {
			$allRank[$key] = $value*2 + $editRes[$key]*4 +$editUserRes[$key]*4;
		}
		arsort($allRank);
		$x = 1;
		//final rank
		foreach ($allRank as $key => $value) {
			$rank = $x;
			$score = round(100*$value/$highest, 2);
			//insert
			$dbw = wfGetDB( DB_MASTER );
			$dbw->insert(
				'site_rank',
				array(
					'site_rank' => $rank,
					'site_score' => $score,
					'site_prefix' => $key,
					'site_rank_date' => $yesterday
				), __METHOD__
			);
			$x++;
		}
	}
}

$maintClass = 'HelloWorld';

require_once RUN_MAINTENANCE_IF_MAIN;