<?php

class GetUserFollowInfo extends ApiBase {

    public function execute() {
        global $wgUser;
        if (
            $wgUser->isBlocked() ||
            !$wgUser->isAllowed( 'edit' ) ||
            wfReadOnly()
        ) {
            return true;
        }
        $this->setWarning(
            "Deprecated; Don't use this off site."
        );
        $username = $this->getMain()->getVal( 'username' );
        $user = User::newFromName( $username );
        $ust = new UserStatus( $user );
        $sites = $ust->getUserAllInfo( );
        $result = $this->getResult();
        // $result->addValue( $this->getModuleName(), 'status', array('a'=>1,'b'=>2,'c'=>3) );
        $result->addValue( $this->getModuleName(), 'result', $sites );
        return true;


    }

    public function getAllowedParams() {
        return array(
            'username' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            )
        );
    }
}