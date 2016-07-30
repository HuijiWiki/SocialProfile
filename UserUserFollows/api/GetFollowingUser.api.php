<?php

class GetFollowingUser extends ApiBase {

    public function execute() {
        $this->setWarning(
            "Deprecated; Don't use this off site."
        );
        $username = $this->getMain()->getVal( 'username' );
        $user = User::newFromName($username);
        //No such user
        if ($user == '' || $user->getId() == 0 ){
            $result = $this->getResult();
            $result->addValue( $this->getModuleName(), 'result', 'no such user' );
            return true;
        }

        $huijiUser = HuijiUser::newFromUser($user);
        $res = $huijiUser->getFollowingUsers();
        $result = $this->getResult();

        // $result->addValue( $this->getModuleName(), 'status', array('a'=>1,'b'=>2,'c'=>3) );
        $result->addValue( $this->getModuleName(), 'result', $res );
        $result->addValue( $this->getModuleName(), 'count', $huijiUser->getFollowingUsersCount());
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