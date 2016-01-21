<?php
/**
 * AJAX functions used by usergift.
 */
$wgAjaxExportList[] = 'wfCheckUserIsHaveGift';
function wfCheckUserIsHaveGift( $user_id, $gift_id ) {
	// global $wgUser;
	// if ( $wgUser->isBlocked() || wfReadOnly() ) {
	// return '';
	// }
	$user = User::newFromID($user_id);
	$ug = new UserGifts( $user->getName() );
	$res = $ug->doesUserOwnGift( $user_id, $gift_id );
	if ($res == false) {
		return 'success';
	}else{
		return 'faild';
	}
}