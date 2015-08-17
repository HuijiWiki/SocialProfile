<?php
/**
 * Boardblast Job
 *
 */
class BoardBlastJobs extends Job {
	public function __construct( $title, $params ) {
		// Replace synchroniseThreadArticleData with an identifier for your job.
		parent::__construct( 'boardBlastJobs', $title, $params );
	}
	/**
	 * Execute the job
	 *
	 * @return bool
	 */
	public function run() {
		// Load data from $this->params and $this->title
		$article = new WikiPage( $this->title );

		$user_ids_to = $this->params['user_ids_to'];
		$message = $this->params['message'];
		$sender = $this->params['sender'];
		$user = User::newFromId($sender);
		$b = new UserBoard();
		$count = 0;
		$i = count($user_ids_to);
		$per_num = 100;
		$num = $i/$per_num;
		$int_num = ceil($num);
		for($k=1;$k<=$int_num;$k++){
			$star = $per_num*($k-1);
			$res_arr = array_slice($user_ids_to, $star, $per_num);
			foreach ( $res_arr as $user_id ) {
				$user_to = User::newFromId( $user_id );
				$user->loadFromId();
				$user_name = $user_to->getName();
				$b->sendBoardMessage(
					$user->getID(),
					$user->getName(),
					$user_id,
					$user_name,
					$message,
					1
				);
				// $count++;
			}
			wfDebug('Sending Board Blast batch '.$k.'............................................');
			// ob_flush();
		 //    flush();
		}
		return true;
	}
}
?>