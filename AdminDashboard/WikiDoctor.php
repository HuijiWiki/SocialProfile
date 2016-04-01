<?php
class WikiDoctor{
	private $mSite;
	private static $trackingCategory = array(
		//'index-category' => '已索引页面',
		//'noindex-category' => '不可索引页面',
		'duplicate-args-category' => '调用重复模板参数的页面',
		'expensive-parserfunction-category' => '有过多高开销解析器函数调用的页面',
		'post-expand-template-argument-category' => '含有略过模板参数的页面',
		'post-expand-template-inclusion-category' => '模板包含上限已经超过的页面',
		// 'hidden-category-category' => '隐藏分类',
		'broken-file-category' => '含有受损文件链接的页面',
		'node-count-exceeded-category' => '页面的节点数超出限制',
		'expansion-depth-exceeded-category' => '扩展深度超出限制的页面',
		'syntaxhighlight-error-category' => '有语法高亮错误的页面',
		'scribunto-common-error-category' => '有脚本错误的页面',
		'scribunto-module-with-errors-category' => '有错误的Scribunto模块',
	);
	private static $linkReports = array(
		'双重重定向',
		'需要的页面',
		'需要的模板',
		'孤立页面',
		'未分类分类',
		'未分类模板',
		'未分类页面',
		'断链页面',
		'未使用文件',
		'未使用模板',
		'受损重定向'
	);
	public function __construct(){
	}
	public function categoryCheck(){
		foreach ( WikiDoctor::$trackingCategory as $key => $value){
			$cat = Category::newFromName($value);
			if (!$cat){
				continue;
			}
			$num = $cat->getPageCount();
			if ($num){
				return array($value, $num);
			}
		}
		return array('' ,  '');
		
		//Decision Tree;
		//check double redirects
		//check tracking categories
		//check 

	}
	public function linkCheck(){
		$key = array_rand( WikiDoctor::$linkReports );
		return WikiDoctor::$linkReports[$key];
		// shuffle(Category::$linkReports);
		// foreach ($linkReports as $page){
		// 	return $page;
		// }
	}
	public function engagementCheck(){
		global $wgHuijiPrefix;
		$this->mSite = WikiSite::newFromPrefix($wgHuijiPrefix);
		$rating = $this->mSite->getRating();
		$score = $this->mSite->getScore();
		if ($rating == 'A'){
			if ($this->mSite->getScore() < 90){
				return 'score';
			}			
		}
		if ($rating == 'B'){
			if ($this->mSite->getScore() < 80){
				return 'score';
			}			
		}
		if ($rating == 'C'){
			if ($this->mSite->getScore() < 70){
				return 'score';
			}			
		}
		if ($rating == 'D'){
			if ($this->mSite->getScore() < 60){
				return 'score';
			}			
		}
		$stats = $this->mSite->getStats();
		$stats['followers'] *= 50;
		$stats['articles'] *= 10;
		$bottom = min ( $stats['followers'], $stats['edits'] , $stats['articles'] );
		$bottomKey = min(array_keys($stats, $bottom));
		return $bottomKey;
	}

}

?>