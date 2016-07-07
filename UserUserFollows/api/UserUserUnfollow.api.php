<?php

class UserUserUnfollowAPI extends ApiBase {

    public function execute() {
        global $wgUser;
        $user = $this->getUser();
        if (
            $user->isBlocked() ||
            !$user->isAllowed( 'edit' ) ||
            wfReadOnly()
        ) {
            return true;
        }
        $follower = $this->getMain()->getVal( 'follower' );
        $followee = $this->getMain()->getVal( 'followee' );

        if ( $follower === $wgUser->getName() && $followee !== $follower){
            $huijiUser = HuijiUser::newFromUser($wgUser);
            $followee = User::newFromName($followee);
            if ($huijiUser->unfollow($followee)){
                $result = $this->getResult();
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