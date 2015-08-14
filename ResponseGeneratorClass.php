<?php 
class ResponseGenerator{
	const SUCCESS = 0;
	const ERROR_NOT_LOGGED_IN = 1;
	const ERROR_BLOCKED = 2;
	const ERROR_READ_ONLY = 3;
	const ERROR_NOT_ALLOWED = 4;
	const ERROR_UNKNOWN = 5;

	/**
	 *
	 * prepare the error to be returned.
	 * @param $num Integer: error code.
	 * @return json error string.
	 */

	public static function getJson($num){
		switch ($num) {
			case self::SUCCESS:
				$data = '{"success": true,"message": "'.wfMessage('socialprofile-success')->text().'"}';
				return $data;
			case self::ERROR_NOT_LOGGED_IN:
				$data = '{
			  		"success": false,"message": "'.wfMessage('socialprofile-error-not-logged-in')->text().'"}';
				return $data;
			case self::ERROR_BLOCKED:
				$data = '{
			  		"success": false,
			  		"message": "'.wfMessage('socialprofile-error-blocked')->text()
					.'"}';
				return $data;
			case self::ERROR_READ_ONLY:
				$data = '{
			  		"success": false,
			  		"message": "'.wfMessage('socialprofile-error-read_only')->text()
					.'"}';
				return $data;
			case self::ERROR_NOT_ALLOWED:
				$data = '{
			  		"success": false,
			  		"message": "'.wfMessage('socialprofile-error-not-allowed')->text()
					.'"}';
				return $data;
			default:
				$data = '{
			  		"success": false,
			  		"message": "'.wfMessage('socialprofile-error-unknown')->text()
					.'"}';
				return $data;
		}
	}
}
?>