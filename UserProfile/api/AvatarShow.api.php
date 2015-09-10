<?php

/**
 * AJAX functions to avatar.
 */

class ApiAvatarShow extends ApiBase {

	public function execute() {
		$user = $this->getUser();
		// Blocked users cannot submit new comments, and neither can those users
        // without the necessary privileges. Also prevent obvious cross-site request
        // forgeries (CSRF)

        $id = $this->getMain()->getVal( 'userid' );
        $name = $this->getMain()->getVal( 'username' );
        $size = $this->getMain()->getVal( 'size' );
        if ($id == ''){
            if ($name != ''){
                $id = User::idFromName($name);
            } else {
                $id = 0;
            }
        } 
        if ($size = ''){
            $size = 'l';
        }
		$avatar = new wAvatar($id, $size);
        $responseBody = array(
          'state'  => 200,
          'message' => '',
          'html' => $avatar->getAvatarHtml(),
          'url' => $avatar->getAvatarUrlPath(),
        );
        $result = $this->getResult();

	    $result->addValue($this->getModuleName(),'res', $responseBody);
	    return true;       
	}
	public function getAllowedParams() {
        return array(
            'userid' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'username' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'size' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            )
        );
    }
}