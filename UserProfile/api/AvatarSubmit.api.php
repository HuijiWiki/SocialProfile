<?php

/**
 * AJAX functions to upload avatar.
 */

class ApiAvatarSubmit extends ApiBase {

	public function execute() {
		$user = $this->getUser();
		// Blocked users cannot submit new comments, and neither can those users
        // without the necessary privileges. Also prevent obvious cross-site request
        // forgeries (CSRF)
        if (
            wfReadOnly()
        ) {
             $responseBody = array(
              'state'  => 200,
              'message' => '本维基处于只读状态。',
              'result' => $avatar->getResult(),
            );
            $result = $this->getResult();
            $result->addValue($this->getModuleName(),'res', $responseBody);   
            return true;      
        } elseif (!$user->isAllowed( 'upload' ) ){
             $responseBody = array(
              'state'  => 200,
              'message' => '您没有上传头像的权限。您是否未验证邮箱？',
              'result' => $avatar->getResult(),
            );
            $result = $this->getResult();
            $result->addValue($this->getModuleName(),'res', $responseBody);   
            return true;        
        } elseif ($user->isBlocked()){
             $responseBody = array(
              'state'  => 200,
              'message' => '您已被封禁。',
              'result' => $avatar->getResult(),
            );
            $result = $this->getResult();
            $result->addValue($this->getModuleName(),'res', $responseBody);   
            return true;              
        }

        $avatar_src = $this->getMain()->getVal( 'avatar_src' );
        $avatar_data = $this->getMain()->getVal( 'avatar_data' );
        $avatar_file = $this->getMain()->getUpload( 'avatar_file' );
		$avatar = new CropAvatar(
			$avatar_src,
			$avatar_data,
			$avatar_file   
	    );
        $responseBody = array(
          'state'  => 200,
          'message' => $avatar->getMsg(),
          'result' => $avatar->getResult(),
        );
        $result = $this->getResult();

	    $result->addValue($this->getModuleName(),'res', $responseBody);
	    return true;       
	}
	public function getAllowedParams() {
        return array(
            'avatar_src' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'avatar_data' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'avatar_file' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'upload'
            )
        );
    }
}