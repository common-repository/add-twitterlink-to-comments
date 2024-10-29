<?php
/*
 Plugin Name: Add Twitterlink to comments
 Plugin URI:  http://omblogs.dk/myplugins/
 Description:  Will add a link to the comment authors twitteraccount to the comment left by the comment author (if he has used an email associated with a twitteraccount)
 Version: 0.1
 Author: Therese Hansen
 */

function get_twitter_account_from_email($user_email){
	$host = 'www.twitter.com';
	$port = 80;
	$err_num = 10;
	$err_msg = 10;
	$agent = 'Wordpress';

	$fp = fsockopen($host, $port, $err_num, $err_msg, 10);
	$twitterURI = '/users/show.xml?email='.$user_email;

	if (!$fp) {
			
	} else {

		fputs($fp, "GET $twitterURI HTTP/1.1\r\n");
		fputs($fp, "Host: $host\n");
		fputs($fp, "Connection: close\n\n");
		for ($i = 1; $i < 800; $i++){$response = fgets($fp, 256);$reply = $reply.$response;}
		fclose($fp);
		//remove the first part of reply to leave the XML - I should probably have used a regular expression
		$pieces = explode("<", $reply);
		$impl="";
		for ($i=1; $i<=200; $i++)
		{
			if ($pieces[$i]!=""){
				$tag = '<'.$pieces[$i];
				$impl = $impl.$tag;
			}
		}
		//reply is now the desired XML-document or if wrong email it is an error message
		$reply=$impl;
		//get username from XML if it exist

		if ($doc = new DOMDocument()){
			$doc->loadXML($reply);

			$dataset = $doc->getElementsByTagName('screen_name');

			if ($dataset->length==0){return "";}
				$username = $dataset->item(0)->nodeValue;

			return $username;
		}
		else {echo "<!-- $reply -->"; return "";}
	}
}


function addtwitterurl($comment_data){
	$email = $comment_data['comment_author_email'];

	$username = get_twitter_account_from_email($email);
		$username=str_replace(" ","",$username);
		$username=str_replace("\n","",$username);

	if (empty($username)){return $comment_data;} else{
		$url = "<a href=\"http://www.twitter.com/$username\">follow @$username on twitter<\a>";
		$comment_data['comment_content']= $comment_data['comment_content']."\n\n";
		$comment_data['comment_content']= $comment_data['comment_content'].$url;
		
	}
	return $comment_data;
}
add_action('preprocess_comment','addtwitterurl',0);
?>