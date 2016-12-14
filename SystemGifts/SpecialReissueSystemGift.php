<?php

/**
*  special page to add new festival gift
*/
class SpecialReissueSystemGift extends SpecialPage{
	
	function __construct(){

		parent::__construct( 'ReissueSystemGift', 'giftadmin' );
	
	}

	/**
	 * Group this special page under the correct header in Special:SpecialPages.
	 *
	 * @return string
	 */
	
	function getGroupName() {
		return 'wiki';
	}

	/**
	 * Show the special page
	 *
	 */
	public function execute($params){
		global $wgUser;
		$this->checkPermissions();
		$this->checkReadonly();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$this->setHeaders();

		$output = "";
		$output .= "<form method='post' action='/wiki/special:reissuesystemgift?method=add' >
			<div class='form-group'>
			<label for='giftId'>成就ID：</label><input type='text' name='giftId' class='form-control'>
			</div><div class='form-group'>
			<label for='user'>用户名：</label><input type='text' name='user' class='form-control'>
			</div>
			<input class='mw-ui-button mw-ui-progressive' type='submit' value='添加'>
			</form>";
		$giftId = $request->getVal('giftId');
		$userName = $request->getVal('user');
		$method = $request->getVal('method');
		$user = HuijiUser::newFromName($userName);
		if ( $method == 'add' ) {
			if ( is_numeric($giftId) && $user->getName() != null ) {
				$usg = new UserSystemGifts( $user->getName() );
				 if (HuijiFunctions::addLock( 'USG-Reissue-'.$user->getId(), 1 ) ){
					$result = $usg->sendSystemGift( $giftId );
				    HuijiFunctions::releaseLock('USG-Reissue-'.$user->getId());
				    }
				if( $result != false && $result !== null ){
					$output .= "<h1>success</h1>";
				}elseif(  $giftId == null || $userName == null ){
					$output .= "<h1>输入错误</h1>";
				}else{
					$output .= "<h1>补发出错</h1>";
				}
			}else{
				$output .= "<h1>输入有误</h1>";
			}
			
		}
		$out->addHTML( $output );
	}


}
