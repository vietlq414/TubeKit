<?php	// TubeKit Beta 4	// http://www.tubekit.org	// Author: Chirag Shah	// Date: 02/08/2012	ini_set("memory_limit","100M");	require_once("config.php");	require_once("$mpdirectory/rss_fetch.inc");	require_once("parseRSS.php");	$t=getdate();    $today=date('Y-m-d',$t[0]);	$qTableName = $prefix . "_queries";	$oTableName = $prefix . "_once";		$query = "SELECT * FROM $qTableName";	$vresult = mysql_query($query) or die(" ". mysql_error());	while ($line = mysql_fetch_array($vresult, MYSQL_ASSOC)) 	{		$vquery = $line['query'];//		echo "Processing $vquery...\n";	    $vquery = urlencode($vquery);	    $qid = $line['id'];			$rankDoc = $prefix . "_" . $qid . ".ranks";        $fr = fopen($rankDoc, 'w');		$rank = 1;		$maxIndex = $numvideos-49;				// If you're getting throttled by YouTube, you may want to restrict your search results to 		// a smaller number. You can set it in your database, or right here in the code.		// If you want to do it in the code, comment the previous line and uncomment the next line.		// Then set a number in the next line. Currently it's 100.//		$maxIndex = 100;		for ($index=1; $index<=$maxIndex; $index+=50)		{			$url = "http://gdata.youtube.com/feeds/api/videos?vq=$vquery&max-results=50&start-index=$index";//			echo "\tFetching $url\n";			$rss = fetch_rss($url);			foreach ($rss->items as $item) 			{				$yt_url = $item[link];				$ytID = substr($yt_url,31,11);					$query = "SELECT * from $oTableName WHERE yt_id='$ytID' AND query_id='$qid'";				$result = mysql_query($query) or mysql_error();				$num_rows = mysql_num_rows($result);				// Only if there wasn't already a video with the same ID for the same query, process further                                                                                              				if ($num_rows == 0)					  				{					$feedURL = "http://gdata.youtube.com/feeds/api/videos/$ytID";//					echo "\t\tExtracting $feedURL\n";					if(file_get_contents($feedURL))					{						$entry = simplexml_load_file($feedURL);						$video = parseVideoEntry($entry);												$timestamp = time();						$title = $video->title;						if ($title!="") {							$description = $video->description;							$username = $video->username;							$upload_time = $video->published;							$duration = $video->length;							$category = $video->category;							$video_url = $video->watchURL;							$thumb_url = 'http://i1.ytimg.com/vi/' . $ytID . '/0.jpg';							$keywords = $video->keywords;							$view_count = $video->viewCount;							$rating_count = $video->numrating;							$rating_avg = $video->rating;							$comment_count = $video->commentsCount;							$favorited = $video->favoriteCount;								$response_count = $video->responseCount;																			$query = "INSERT INTO $oTableName VALUES('','$qid','$ytID','$timestamp','','','$today'";							$dquery = "SHOW COLUMNS FROM $oTableName";							$result = mysql_query($dquery) or mysql_error();							while($line = mysql_fetch_assoc($result))							{								$fieldName = $line['Field'];								switch($fieldName)								{									case 'title':										$query = $query . ",'$title'";										break;									case 'description':										$query = $query . ",'$description'";										break;									case 'username':										$query = $query . ",'$username'";										break;									case 'upload_time':										$query = $query . ",'$upload_time'";										break;									case 'duration':										$query = $query . ",'$duration'";										break;									case 'category':										$query = $query . ",'$category'";										break;									case 'keywords':										$query = $query . ",'$keywords'";										break;									case 'video_url':										$query = $query . ",'$video_url'";										break;									case 'thumb_url':										$query = $query . ",'$thumb_url'";										break;									case 'view_count':										$query = $query . ",'$view_count'";										break;									case 'rating_count':										$query = $query . ",'$rating_count'";										break;									case 'rating_avg':										$query = $query . ",'$rating_avg'";										break;									case 'comment_count':										$query = $query . ",'$comment_count'";										break;									case 'comments':										$query = $query . ",'$comments'";										break;									case 'response_count':										$query = $query . ",'$response_count'";										break;									case 'favorite_count':										$query = $query . ",'$favorited'";										break;									case 'rank':										$query = $query . ",'$rank'";										break;												default:										break;											} // switch($fieldName)							} // while($line = mysql_fetch_assoc($result))													$query = $query . ")";							$result = mysql_query($query) or mysql_error();						}					}				} // if ($num_rows == 0)						fwrite($fr, "$rank $ytID\n");				$rank++;			} // foreach ($rss->items as $item) 		} // for ($index=1; $index<=51; $index+=50)		fclose($fr);	}		 ?>