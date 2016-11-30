<?php
use MediaWiki\MediaWikiServices;
/**
 * A temporary fix. Needs clean up.
 */
class AsyncEventJob extends Job {
	public function __construct( $title, $params ) {
		// Replace synchroniseThreadArticleData with an identifier for your job.
		parent::__construct( 'asyncEventJob', $title, $params );
		$this->removeDuplicates = true;
	}

	/**
	 * Execute the job
	 *
	 * @return bool
	 */
	public function run() {
		switch ($this->params[0]) {
		 	case 'entrytran_save':
				// Load data from $this->params and $this->title
				list($type, $article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId) = $this->params;
				return $this->saveEntryTran($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId);
		 		break;
		 	case 'entrytran_undelete':
		 		list($type, $title, $revision, $oldPageId) = $this->params;
 				return $this->unDeleteEntryTran( $title, $revision, $oldPageId );
		 		break;
		 	case 'es_upsertpage':
		 		list($type, $title, $rev) = $this->params;
		 		return $this->upsertPage($title, $rev);
		 	case 'es_undeletepage':
		 		list($type, $title, $revision, $oldPageId ) = $this->params;
		 		return $this->unDeletePage($title, $revision, $oldPageId);
		 	case 'baidu_push_update':
		 		return $this->baiduPush('update', $this->title);
		 	case 'baidu_push_new':
		 		return $this->baiduPush('new', $this->title);
			case 'baidu_push_delete':
		 		return $this->baiduPush('delete', $this->title);
		 	default:
		 		break;
		 } 
	

		return true;
	}
	public function baiduPush($type, $title){
		global $wgHuijiPrefix, $wgRequest;
		$pi = new HuijiPageInfo($title->getArticleID(), RequestContext::getMain());
		$score = $pi->pageScore();
		if ($score < 40 && $type == 'update'){ //Baidu restrict update quota
			return;
		}
		$urls = array();
		$urls[] = $title->getFullURL();
		if ($type == "new"){
			$api = "http://data.zz.baidu.com/urls?site=".$wgHuijiPrefix.".huiji.wiki&token=".Confidential::$baidu_push_key."&type=original";
		} elseif ($type == "update") {
			$api = "http://data.zz.baidu.com/update?site=".$wgHuijiPrefix.".huiji.wiki&token=".Confidential::$baidu_push_key;
		} else {
			$api = "http://data.zz.baidu.com/del?site=".$wgHuijiPrefix.".huiji.wiki&token=".Confidential::$baidu_push_key;
		}
		
		$ch = curl_init();
		$options =  array(
		    CURLOPT_URL => $api,
		    CURLOPT_POST => true,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_POSTFIELDS => implode("\n", $urls),
		    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
		);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch); 
		$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'baidu' );
		$logger->debug('BAIDU PUSH COMPLETE', ['result'  => $result, 'title' => $title->getPrefixedText() ]);
	}
	public function saveEntryTran($article, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId){
	    if($article == null || $revision == null || $article->getTitle() == null) return true;
	    
		$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'updateEntryTran' );
	    $logger->debug( "parsing begins at ".time(), [$article, $content] );
		$links= [];
		try{
			$parserOutput = $content->getParserOutput($article->getTitle());
			$links = $parserOutput->getLanguageLinks();
			$logger->debug( "parsing ends at ".time(), [$article, $content] );
		} catch(Exception $e){
			$logger->error($e->getMessage());
			exit();
		}
		if(count($links) == 0){
			upsert("", $article->getTitle()->getText(),"");
		}
	 	
		$redirectToPage = $content->getRedirectTarget();
        //redirectPageTitle
        $toTitle = $redirectToPage != null ? $redirectToPage->getText():$article->getTitle()->getText();

		$preRev = $revision->getPrevious();	
		if($preRev == null){
			insert($toTitle, $article->getTitle()->getText(),$links);
			return;
		}else{
			$preLinks = [];
			try{
				$content = $preRev->getContent(Revision::RAW);
				$parserOutput = $content->getParserOutput($article->getTitle());
				$preLinks = $parserOutput->getLanguageLinks();
			} catch(Exception $e){
				$logger->error($e->getMessage());
				exit();
			}

			if(count($links) != count($preLinks)){
				insert($toTitle,$article->getTitle()->getText(),$links);
				return true;
			}

			$preSet = [];
			$set = [];
			
			foreach ( $preLinks as $link) {
	  	        	list( $key, $title ) = explode( ':', $link, 2 );
	               		$preSet[$key] = $title;
			}

			foreach ( $links as $link) {
	  	        	list( $key, $title ) = explode( ':', $link, 2 );
				if($preSet[$key] == null || $preSet[$key] != $title){
					insert($toTitle,$article->getTitle()->getText(),$links);
					return true;
				}
			}

	    }   		
	}
	public function unDeleteEntryTran($title, $revision, $oldPageId){
		if($title == null || $title->getNamespace() !== 0) return;	
		$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'updateEntryTran' );
		$links= [];
		try{
			$content = $revision->getContent(Revision::RAW);
			$redirectTitleObject = $content->getRedirectTarget(); 
			$toTitle = $redirectTitleObject != null ? $redirectTitleObject->getText():$title->getText();
			//$logger->debug( "parsing begins at ".time(), [$article, $content] );
			$parserOutput = $content->getParserOutput($title);
			//$logger->debug( "parsing ends at ".time(), [$article, $content] );
			$links = $parserOutput->getLanguageLinks();
		} catch(Exception $e){
			$logger->error($e->getMessage());
			exit();
		}
		if(count($links) >0 ){
			insert($toTitle,$title->getText(),$links);
			return true;
		}
	}
	public function upsertPage($title, $rev){
		global $wgHuijiPrefix, $wgSitename,$wgIsProduction;
		$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'updateESContent' );
		if($wgIsProduction == false) return true;
		if($rev == null || $title == null || $title->getNamespace() !== 0) return true;
		$old_rev = $rev->getPrevious();
		$old_redirect = null;
		$new_redirect = null;
		$old_redirectId = -1;
		$new_redirectId = -1;
		$category = array();
		//new & old content
		if($old_rev != null && ($old_content = $old_rev->getContent(Revision::RAW)) != null) $old_redirect = $old_content->getRedirectTarget();
		if(($new_content = $rev->getContent(Revision::RAW)) != null) $new_redirect = $new_content->getRedirectTarget();

		//new & old redirect 
	         
		if($old_redirect != null){
			$old_redirectId = $old_redirect->getArticleID();
		}else{
			$old_redirectId = -1;
		}

		if($new_redirect != null){
			$new_redirectId = $new_redirect->getArticleID();
		}else{
			$new_redirectId = -1;
		}
		$logger->debug('parser begins at '.time(), [$title, $rev]);
		//category
		$options = $new_content->getContentHandler()->makeParserOptions( 'canonical' );
	    $output = $new_content->getParserOutput( $title, $rev->getId(), $options,true);
	    $extract = new TextExtracts\ExtractFormatter($output->getText(), true, MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'textextracts' ));
	    $category = array_map( 'strval', array_keys( $output->getCategories() ) );

	    $logger->debug('parser ends at '.time(), [$title, $rev]);

		$titleName = ($title->getText() == "首页") ? $wgSitename : $title->getText();
		$preTitle = $old_rev != null ? $old_rev->getTitle()->getText():null;
		$redirectPageTitle = $new_redirect != null ? $new_redirect->getText():null;
		$post_data = array(
			'timestamp' => $rev->getTimestamp(),
			'content' => $extract->getText(),
			'sitePrefix' => $wgHuijiPrefix,
			'siteName' => $wgSitename,
			'id' => $title->getArticleID(),
			'title' => $titleName,
			'preTitle' => $preTitle,
			'preRedirectPageId' => $old_redirectId,
			'redirectPageId' => $new_redirectId,
			'category' => $category,
			'redirectPageTitle' => $redirectPageTitle,
			
		);
		$post_data_string = json_encode($post_data);
	//	wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
		curl_post_json('upsert',$post_data_string);
		return true;
	}
	public function unDeletePage($title, $revision, $oldPageId){
		global $wgHuijiPrefix, $wgSitename,$wgIsProduction;
		$logger = MediaWiki\Logger\LoggerFactory::getInstance( 'updateESContent' );
		if($wgIsProduction == false) return true;	
		//title
		if($title == null || $title->getNamespace() !== 0) return;
		$titleT = ($title->getText() == "首页") ? $wgSitename : $title->getText();
		// new_content ,   new_redirect 
		if(($new_content = $revision->getContent(Revision::RAW)) != null) $new_redirect = $new_content->getRedirectTarget();
		//redirectPageTitle
		$redirectPageTitle = $new_redirect != null ? $new_redirect->getText():null;
		//new_redirectId
		if($new_redirect != null){
			$new_redirectId = $new_redirect->getArticleID();
		}else{
			$new_redirectId = -1;
		}

		//category
		$logger->debug('parser begins at '.time(), [$title, $revision]);
		$options = $new_content->getContentHandler()->makeParserOptions( 'canonical' );
	    $output = $new_content->getParserOutput( $title, $revision->getId(), $options,true);
	    $extract = new ExtractFormatter($output->getText(), true, MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'textextracts' ));
	    $category = array_map( 'strval', array_keys( $output->getCategories() ) );

	    $logger->debug('parser ends at '.time(), [$title, $revision]);
		$post_data = array(
			'timestamp' => $revision->getTimestamp(),
			'content' => $extract->getText(),
			'sitePrefix' => $wgHuijiPrefix,
			'siteName' => $wgSitename,
			'id' => $title->getArticleID(),
			'title' => $titleT,
			'preTitle' => null,
			'preRedirectPageId' => -1,
			'redirectPageId' => $new_redirectId,
			'category' => $category,
			'redirectPageTitle' => $redirectPageTitle,
			
		);
		$post_data_string = json_encode($post_data);
	//	wfErrorLog($post_data_string,"/var/log/mediawiki/SocialProfile.log");
		curl_post_json('upsert',$post_data_string);
		return true;
	}
}
