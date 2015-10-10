<?php

/**
 * all sites info ex:siterank..
 */
class AllSitesInfo{

	static function getAllSitesRankData( $prefix, $yesterday ){

		$data = self::getAllSitesRankFromCache( $prefix, $yesterday );
		if ( $data != '' && count($data) > 0 ) {
			$result = $data;
		} else {
			$result = self::getAllSitesRankFromDB( $prefix, $yesterday );
		}
		return $result;
		
	}

	static function getAllSitesRankFromCache( $prefix, $yesterday ){

		global $wgMemc;
		$key = wfForeignMemcKey('huiji','', 'site_rank', 'all_site_rank', $prefix, $yesterday );
		$data = $wgMemc->get( $key );
		if ( $data != '' ) {
			wfDebug( "Got site rank ( site = {$prefix},data = {$yesterday} ) from cache\n" );
			return $data;
		}
		
	}

	static function getAllSitesRankFromDB( $prefix, $yesterday ){

		global $wgMemc;
		wfDebug( "Got site rank ( site = {$prefix},data = {$yesterday} ) from DB\n" );
		$key = wfForeignMemcKey('huiji','', 'site_rank', 'all_site_rank', $prefix, $yesterday );
		$allSiteRank = array();
		if( $prefix == '' ){
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'site_rank',
				array(
					'site_rank',
					'site_score',
					'site_prefix'
				),
				array(
					'site_rank_date' => $yesterday
				),
				__METHOD__,
				array( 
					'ORDER BY' => 'site_rank ASC'
				)
			);
			if ( $res != false ) {
				foreach ($res as $value) {
					$result['site_prefix'] = $value->site_prefix;
					$result['site_rank'] = $value->site_rank;
					$result['site_score'] = $value->site_score;
					$result['best_rank'] = AllSitesInfo::getSiteBestRank( $value->site_prefix );
					$allSiteRank[] = $result;
				}
			}
			$wgMemc->set( $key, $allSiteRank );
			return $allSiteRank;
		}else{
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				'site_rank',
				array(
					'site_rank',
					'site_score'
				),
				array(
					'site_prefix' => $prefix,
					'site_rank_date' => $yesterday
				),
				__METHOD__
			);
			if ( $res != false ) {
				foreach ($res as $value) {
					$result['site_rank'] = $value->site_rank;
					$result['site_score'] = $value->site_score;
					$allSiteRank[] = $result;
				}
			}
			$wgMemc->set( $key, $allSiteRank );
			return $allSiteRank;
		}

	}

	static function getAllSitesRank(){
		$allSite = HuijiPrefix::getAllPrefix();
		$today = date('Y-m-d');
		$yesterday = date('Y-m-d',strtotime('-1 days'));
		$lastWeek = date('Y-m-d',strtotime('-8 days'));
		$lastMonth = date('Y-m-d',strtotime('-31 days'));
		$ueb = new UserEditBox();
		$editUserYesterday = $ueb->getSiteEditUserCount( $yesterday, $yesterday);
		$editUserWeek = $ueb->getSiteEditUserCount( $lastWeek, $yesterday);
		$editUserMonth = $ueb->getSiteEditUserCount( $lastMonth, $yesterday);
		// print_r($editUserYesterday);
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
		// print_r($editUserDate);
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
		// print_r($allRank);
		//final rank
		foreach ($allRank as $key => $value) {
			$rank = $x;
			$score = round(100*$value/$highest, 2);
			// $numRank['rank'] = $x;
			// $numRank['score'] = round(100*$value/$highest, 2);
			// $res[$key] = $numRank;
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
			//best rank
			$key_rank = AllSitesInfo::getSiteBestRank( $key );
			$site_rank = (!is_null($key_rank))?$key_rank:1000;
			if( $rank < $site_rank ){
				$dbw = wfGetDB( DB_MASTER );
				$dbw->upsert(
					'site_best_rank',
					array(
						'site_rank' => $rank,
						'site_prefix' => $key
					),
					array(
						'site_prefix' => $key
					),
					array(
						'site_rank' => $rank
					), __METHOD__
				);
			}
			$x++;
		}
	}

	//get site bset rank
	static function getSiteBestRank( $prefix ){
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'site_best_rank',
			array(
				'site_rank'
			),
			array(
				'site_prefix' => $prefix,
			),
			__METHOD__
		);
		$result = 0;
		if ( $res ) {
			foreach ($res as $value) {
				$result = $value->site_rank;
			}
		}
		return $result;

	}
	//get one page fork count
	static function getPageForkCount( $page_id ){
		$dbw = wfGetDB( DB_SLAVE );
		$res = $dbw->select(
			'template_fork_count',
			array(
				'fork_count'
			),
			array(
				'template_id' => $page_id
			),
			__METHOD__
		);
		$result = '';
		if( $res ){
			foreach ($res as $value) {
				$result = $value->fork_count;
			}
		}
		return $result;
	}
}
