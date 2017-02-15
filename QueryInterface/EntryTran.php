<?php

require_once('Util.php');
class EntryTran
{
	const MODE_SOFT = 1;
	const MODE_HARD = 2;
	const MODE_HARDEST = 3;
	public function __construct($foreign){
		$this->foreign = $foreign;
	}
	public function getResult($mode, $flag = '*'){
		global $wgHuijiPrefix;
		$target = '';
		if ($this->foreign == ''){
			return '';
		}
		switch ($mode) {
			case self::MODE_HARDEST:
				$target = $this->lookupUserTable();
				if ($target != $this->foreign){
					return $target;
				}
				$res = json_decode(EntryTran::getEntry($this->foreign, 'en', $wgHuijiPrefix, 0, 2));
				foreach($res->result->objects as $entry){
					if (isset($entry->entry) && $entry->entry != $this->foreign){
						$target = (string)$entry->entry;
					}
				}
				if ($target != $this->foreign){
					return $target;
				}
				$target = (string)$this->lookupDict($flag);
				if ($target != ''){
					return $target;
				}
				if(strlen($this->foreign) == mb_strlen($this->foreign, 'utf-8')){
					$target = $this->lookupYoudao();
					return $target;					
				}
				return $this->foreign;
				break;
			case self::MODE_HARD:
				$target = $this->lookupUserTable();
				if ($target != $this->foreign){
					return $target;
				}
				$res = json_decode(EntryTran::getEntry($this->foreign, 'en', $wgHuijiPrefix, 0, 2));
				foreach($res->result->objects as $entry){
					if (isset($entry->entry) && $entry->entry != $this->foreign){
						$target = (string)$entry->entry;
					}
				}
				if ($target != $this->foreign){
					return $target;
				}
				$target = (string)$this->lookupDict($flag);
				if ($target != ''){
					return $target;
				}
				return $this->foreign;
				break;
			case self::MODE_SOFT:
				$target = $this->lookupUserTable();
				if ($target != $this->foreign){
					return $target;
				}
				$res = json_decode(EntryTran::getEntry($this->foreign, 'en', $wgHuijiPrefix, 0, 2));
				foreach($res->result->objects as $entry){
					if (isset($entry->entry) && $entry->entry != $this->foreign){
						$target = (string)$entry->entry;
					}
				}
				if ($target != (string)$this->foreign){
					return $target;
				}
				return $this->foreign;
				break;
			
			default:
				return $this->foreign;
				break;
		}

	}
	private function lookupYoudao(){
		try{
			$out = json_decode(Util::curl_get_youdao('huijidata', '587017573', str_replace('&amp;', 'and', urlencode($this->foreign))), true);
			if(isset($out['translation']) && isset($out['translation'][0])){
				return (string)$out['translation'][0];
			} else {
				return $this->foreign;
			}
			
		} catch(Exception $e){
			return $this->foreign;
		}
	}
	private function lookupDict($flag){
		$dbr = wfGetDB(DB_SLAVE, array(), 'huiji_home');
		$res = $small = [];
		foreach (explode(' ', $this->foreign) as $key){
			foreach(explode('-', $key) as $part){
				$where = array('trans_foreign' => $part );
				if ($flag != "*"){
					$where['trans_usage'] = $flag;
				}
				$temp = $dbr->selectField(
					'trans_base',
					'trans_chinese',
					$where,
					__METHOD__
				);	
				if ($temp == ''){
					return '';
				}
				$small[] = $temp;
			}
			$res[] = implode("-", $small);
			$small = [];
		}
		return implode("·", $res);
	}
	private function lookupUserTable(){		
		$json = json_decode(wfMessage('huiji-translation-pairs')->plain(), true);
		$target = $this->foreign;
		if (isset($json['version']) && $json['version'] == 2){
			foreach ($json["regex"] as $key => $value) {
			 	$target = preg_replace($key, $value, $target);
			}
			$target = isset($json["link"][$target])
				?
				$json["link"][$target]
				:
				$target;
			$target = isset($json["plain"][$target])
				?
				$json["link"]["target"]
				:
				$target;
			return $target;
		} else {
			return isset($json["target"])
				?
				$json["target"]
				:
				$target;
		}		
	}

	static function post($method,$data_string)
	{
		return Util::curl_post_json('huijidata.com','8080', 'entryTranslation', 'entryTran', $method, $data_string);
	}
	 
	static function getTran($content, $lang, $sitePrefix, $offset=0, $size=98)
	{
		$data =json_encode(array('content'=>$content,'lang'=>$lang,'sitePrefix'=>$sitePrefix,'size'=>$size,'offset'=>$offset));
		return self::post('toLang',$data);

	}

	static function getEntry($content, $lang, $sitePrefix, $offset=0, $size=98)
	{
		$data =json_encode(array('content'=>$content,'lang'=>$lang,'sitePrefix'=>$sitePrefix,'size'=>$size,'offset'=>$offset));
		return self::post('fromLang',$data);

	}

	static function getSuggest($content, $type)
	{
		$data =json_encode(array('content'=>$content,'type'=>$type));
		return self::post('suggest',$data);

	}

}
?>
