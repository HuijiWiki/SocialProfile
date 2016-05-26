<?php
/**
 * This Class manages users donation
 */
class UserDonation{
	//add info
	static function addUserDonationInfo( $userName, $sitePrefix, $donationValue ){
		$dbw = wfGetDB( DB_MASTER );
		$dbw -> insert(
				'user_donation',
				array(
					'user_name' => $userName,
					'site_prefix' => $sitePrefix,
					'donation_value' => $donationValue,
					'date' => date('Y-m-d H:i:s', time()),
					'month' => date('Y-m', time()),
				),
				 __METHOD__
			);
		if ( $dbw->insertId() ) {
			return true;
		}
	}

	//if month is not null, get info by month and prefix 
	static function getDonationInfoByPrefix( $sitePrefix, $month ){
		$where = array();
		if ( $sitePrefix != null ) {
			if ( $month != null ) {
				$where = array(
							'site_prefix' => $sitePrefix,
							'month' => $month 
						);
			}else{
				$where = array(
							'site_prefix' => $sitePrefix
						);
			}	
		}else{
			if ( $month != null ) {
				$where = array(
							'month' => $month 
						);
			}else{
				$where = array();
			}
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
					'user_donation',
					array(
						'user_name',
						'site_prefix',
						'donation_value',
						'date',
						'month'
					),
					$where,
					__METHOD__
				);
		$result = array();
		if ($res) {
			foreach ($res as $key => $value) {
				$result[] = array(
							'userName' => $value->user_name,
							'sitePrefix' => $value->site_prefix,
							'donationValue' => $value->donation_value,
							'donateDate' => $value->date,
							'donateMonth' => $value->month,
						);
			}
		}
		return $result;
	}

	// static function getAllSiteMonthRank(){
	// 	$month = date('Y-m', time());
	// 	$rankDate = self::getDonationInfoByPrefix( '', $month );
	// 	$rankResult = array();
	// 	foreach ($rankDate as $key => $value) {
	// 		if ( !isset( $rankResult[$value['userName']] ) ) {
	// 			$rankResult[$value['userName']] = $value['donationValue'];
	// 		}else{
	// 			$rankResult[$value['userName']] += $value['donationValue'];
	// 		}
	// 	}
	// 	arsort($rankResult);
	// 	return $rankResult;
	// }
	// static function getAllSiteTotalRank(){
	// 	$rankDate = self::getDonationInfoByPrefix( '', '' );
	// 	$rankResult = array();
	// 	foreach ($rankDate as $key => $value) {
	// 		if ( !isset( $rankResult[$value['userName']] ) ) {
	// 			$rankResult[$value['userName']] = $value['donationValue'];
	// 		}else{
	// 			$rankResult[$value['userName']] += $value['donationValue'];
	// 		}
	// 	}
	// 	arsort($rankResult);
	// 	return $rankResult;
	// }

	//if month(ex:'2016-05') is null, get all sites donation total rank,else get one month rank
	static function getAllSiteDonationUserRank( $month ){
		$rankDate = self::getDonationInfoByPrefix( '', $month );
		$rankResult = array();
		foreach ($rankDate as $key => $value) {
			if ( !isset( $rankResult[$value['userName']] ) ) {
				$rankResult[$value['userName']] = $value['donationValue'];
			}else{
				$rankResult[$value['userName']] += $value['donationValue'];
			}
		}
		arsort($rankResult);
		return $rankResult;
	}

	// static function getCurrentMonthRankByPrefix( $sitePrefix ){
	// 	$month = date('Y-m', time());
	// 	$rankDate = self::getDonationInfoByPrefix( $sitePrefix, $month );
	// 	$rankResult = array();
	// 	foreach ($rankDate as $key => $value) {
	// 		if ( !isset( $rankResult[$value['userName']] ) ) {
	// 			$rankResult[$value['userName']] = $value['donationValue'];
	// 		}else{
	// 			$rankResult[$value['userName']] += $value['donationValue'];
	// 		}
	// 	}
	// 	arsort($rankResult);
	// 	return $rankResult;

	// }
	// static function getTotalRankByPrefix( $sitePrefix ){
	// 	$rankDate = self::getDonationInfoByPrefix( $sitePrefix, '' );
	// 	$rankResult = array();
	// 	foreach ($rankDate as $key => $value) {
	// 		if ( !isset( $rankResult[$value['userName']] ) ) {
	// 			$rankResult[$value['userName']] = $value['donationValue'];
	// 		}else{
	// 			$rankResult[$value['userName']] += $value['donationValue'];
	// 		}
	// 	}
	// 	arsort($rankResult);
	// 	return $rankResult;
	// }

	//$month = date('Y-m', time())
	//if month is null, get one site total rank,else get one month rank
	static function getDonationRankByPrefix( $sitePrefix, $month ){
		$rankDate = self::getDonationInfoByPrefix( $sitePrefix, $month );
		$rankResult = array();
		foreach ($rankDate as $key => $value) {
			if ( !isset( $rankResult[$value['userName']] ) ) {
				$rankResult[$value['userName']] = $value['donationValue'];
			}else{
				$rankResult[$value['userName']] += $value['donationValue'];
			}
		}
		arsort($rankResult);
		return $rankResult;
	}

	//if month is null, get all site rank,else  get all site rank in one month
	static function getAllSiteDonationRank( $month ){
		$rankDate = self::getDonationInfoByPrefix( '', $month );
		$rankResult = array();
		foreach ($rankDate as $key => $value) {
			if ( !isset( $rankResult[$value['sitePrefix']] ) ) {
				$rankResult[$value['sitePrefix']] = $value['donationValue'];
			}else{
				$rankResult[$value['sitePrefix']] += $value['donationValue'];
			}
		}
		arsort($rankResult);
		return $rankResult;
	}

}