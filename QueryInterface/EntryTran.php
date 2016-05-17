<?php

require_once('Util.php');
class EntryTran
{

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

//var_dump(EntryTran::getSuggest("Russell Be",'entry'));
?>
