<?php

class UserUserFollowAPI extends ApiBase {

    public function execute() {
        global $wgUser;
        if (
            $wgUser->isBlocked() ||
            !$wgUser->isAllowed( 'edit' ) ||
            wfReadOnly()
        ) {
            return true;
        }
        $follower = $this->getMain()->getVal( 'follower' );
        $followee = $this->getMain()->getVal( 'followee' );

        if ( $follower === $wgUser->getName() && $followee !== $follower){
            $huijiUser = HuijiUser::newFromUser($wgUser);
            $followee = User::newFromName($followee);
            if ($huijiUser->follow($followee)){
                $result = $this->getResult();
                // $result->addValue( $this->getModuleName(), 'status', array('a'=>1,'b'=>2,'c'=>3) );
                $result->addValue( $this->getModuleName(), 'status', 'success' );
                return true;
            }
        }
        $result = $this->getResult();
        $result->addValue( $this->getModuleName(), 'status', 'failed' );
        return true;
        
    }

    public function getAllowedParams() {
        return array(
            'follower' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'followee' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            )
        );
    }
}