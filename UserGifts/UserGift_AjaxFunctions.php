<?php
/**
 * AJAX functions used by usergift.
 */
$wgAjaxExportList[] = 'wfCheckUserIsHaveGift';
$wgAjaxExportList[] = 'wfChangeGiftTitleStatus';
$wgAjaxExportList[] = 'wfChangeGiftTitleStatusOff';
function wfCheckUserIsHaveGift( $user_id, $gift_id ) {
	// global $wgUser;
	// if ( $wgUser->isBlocked() || wfReadOnly() ) {
	// return '';
	// }
	$user = User::newFromID($user_id);
	$ug = new UserGifts( $user->getName() );
	$res = $ug->doesUserOwnGiftOfTheSameGiftType( $user_id, $gift_id );
	if ($res == false) {
		return 'success';
	}else{
		return 'failed';
	}
}

function wfChangeGiftTitleStatus( $userTitleId, $status, $from ){
	global $wgUser;
	if ( $status == 2 ) {
		UserGifts::cleraAllGiftTitle( $from, $wgUser->getId() );
	}
	$dbw = wfGetDB( DB_MASTER );
	$dbw -> update(
			'user_title',
			array(
				'is_open' => $status
			),
			array(
				'ut_id' => $userTitleId,
			),
			__METHOD__
		);
	return 'success';
}

function wfChangeGiftTitleStatusOff( $gift_id, $user_to_id, $title_from ){
	$dbw = wfGetDB( DB_MASTER );
	$dbw -> update(
			'user_title',
			array(
				'is_open' => 1
			),
			array(
				'gift_id' => $gift_id,
				'user_to_id' => $user_to_id,
				'title_from' => $title_from
			),
			__METHOD__
		);
	return 'success';
}

