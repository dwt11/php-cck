<?php  if(!defined('DWTINC')) exit('dwtx');
/**
 * 分词功能110723整合
 *
 * @version        $Id: test.helper.php 5 15:01 2011年7月23日Z  $
 * 
 */

//标题分词，先用DEDE分词文字，判断禁用词中是否有分词，如果没有则输出分词
if ( ! function_exists('getsplitword'))
{
   function getsplitword($word)
    {
		require_once(DWTINC."/splitword.class.php");
 		require(DWTPATH."/data/cache/jykeywords.inc");
 
		global $cfg_soft_lang;
		global $dsql;

		//echo $keyword;
		$sp = new SplitWord($cfg_soft_lang, $cfg_soft_lang);
		$sp->SetSource($word, $cfg_soft_lang, $cfg_soft_lang);
		$sp->StartAnalysis();
		$keywords = $sp->GetFinallyResult(' ');
		//$titles = ereg_replace("[ ]{1,}"," ",trim($keywords));
  
  
		$ks = explode(' ',$keywords);
		$keywords="";
		
		
		//根据TAGS数据库中保存的词扩展分词
		//先用DEDE分词，如果大于两个字节，则在TAGS个中搜索类似的词，然后用类似的词在标题中找，如果有则输出
			  $liketag="";
		foreach($ks as $k)//分词循环
		{
			//dump("基本分词：".$k);
			if (strlen($k)>2&&strlen($k)<5)
			{
		
			  
			  
			    $dsql->SetQuery("select id,tag from `#@__tagindex` WHERE tag like '%$k%' and tag<>'$k' ORDER BY total desc");
				$dsql->Execute('word');
				//$hotword = "";
				if(is_array($row=$dsql->GetArray('word')))
				{
					while($row=$dsql->GetArray('word'))
					{
						$isjy=strpos($word,$row['tag']);//dump($_Csff);//判断禁用词中是否有分词，如果没有则输出
						if($isjy)$liketag.=$row['tag']." ";
					
					}
				//	dump("相似分词：".$liketag);

				}
				else
				{
					$liketag.=$k." ";
				}
			}
					if(strlen($k)>4)$liketag.=$k." ";
		}  
			//dump("加上相似后的分词:".$liketag);
		
		
		
		
		$ks = explode(' ',$liketag);
		$jykeyword=implode(",",$_Csff);//获得禁用关键词，转换为字符串
					

		foreach($ks as $k)//分词循环
		{
			$k = trim($k);
			if(strlen($k)>2)
			{
				$isjy=strpos($jykeyword,$k);//dump($_Csff);//判断禁用词中是否有分词，如果没有则输出
				if($isjy==false)$keywords.=$k." ";
			}
		}  
			//dump("最终分词:".$keywords);
		return $keywords;
    }
}