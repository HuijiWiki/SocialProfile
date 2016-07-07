<?php

class GetUserFollowRecommend extends ApiBase {

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
        $follower = $this->getMain()->getVal( 'follower' );
        $followee = $this->getMain()->getVal( 'followee' );
        if ( $follower === $wgUser->getName() && $followee !== $follower){
        $huijiUser = HuijiUser::newFromUser($wgUser);
        $followee = User::newFromName($followee);
        if ($huijiUser->follow($followee)){
            $weekRank = UserStats::getUserRank(20,'week');
            $monthRank = UserStats::getUserRank(20,'month');
            $totalRank = UserStats::getUserRank(20,'total');
            if ( count($weekRank) >=8 ) {
                $recommend = $weekRank;
            }elseif ( count($monthRank) >=8 ) {
                $recommend = $monthRank;
            }else{
                $recommend = $totalRank;
            }
            $recommendRes = array();
            $flres = array();
            foreach ($recommend as $value) {
                $tuser = User::newFromName($value['user_name']);
                $isFollow = $huijiUser->isFollowing($tuser);
                if( !$isFollow && $value['user_name'] != $wgUser->getName() ){
                    $flres['avatar'] = $value['avatarImage'];
                    $flres['username'] = $value['user_name'];
                    $flres['userurl'] = $value['user_url'];
                    $recommendRes[] = $flres;
                }         
            }
            $n = count($recommendRes);
            $i = 5;
            $newUser = isset($recommendRes[$i])?$recommendRes[$i]:null;
            $result = $this->getResult();
            $result->addValue( $this->getModuleName(), 'result', $newUser );
            return true;
        }else{
            $result = $this->getResult();
            $result->addValue( $this->getModuleName(), 'result', 'error' );
            return true;
        }
    
    }


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