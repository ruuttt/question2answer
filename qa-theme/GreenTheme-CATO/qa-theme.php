<?php

/*
	Question2Answer 1.3-beta-1 (c) 2010, Gideon Greenspan

	http://www.question2answer.org/

	
	File: qa-theme/Candy/qa-theme.php
	Version: 1.3-beta-1
	Date: 2010-11-04 12:12:11 GMT
	Description: Override something in base theme class for Candy theme


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/
     



if 
 (
   (!qa_is_logged_in()) and 
   !(
     (strpos(qa_self_html(),'login') !== false )||
     (strpos(qa_self_html(),'forgot') !== false )||
     (strpos(qa_self_html(),'vision') !== false )||
     (strpos(qa_self_html(),'welcome') !== false )||
     (strpos(qa_self_html(),'reset') !== false )
   )
 ) {	
	qa_redirect('login');
}else{
	
	class qa_html_theme extends qa_html_theme_base
	{
		
		// source: http://www.question2answer.org/qa/16090/navigate-to-next-and-previous-questions-on-question-page?show=16090#q16090
// get previous question
function get_prev_q(){
 
$myurl=$this->request;
$myurlpieces = explode("/", $myurl);
$myurl=$myurlpieces[0];
 
if (is_numeric($myurl)){
	$query_p = "SELECT * 
	FROM ^posts 
	WHERE postid < $myurl
	AND type='Q'
	ORDER BY postid DESC
	LIMIT 1";
	 
	$prev_q = qa_db_query_sub($query_p);
	 
	while($prev_link = qa_db_read_one_assoc($prev_q, true)){
	 
	$title = $prev_link['title'];
	$pid = $prev_link['postid'];
	 
	$this->output( '
	<A HREF="'. qa_q_path_html($pid, $title) .'" style="padding-left:20px" title="'. $title .'" >&larr; Previous Action </A>','');
	}
}
 
}
 
// get next question
function get_next_q(){ 
 
$myurl=$this->request;
$myurlpieces = explode("/", $myurl);
$myurl=$myurlpieces[0];
 
if (is_numeric($myurl)){ 
	$query_n = "SELECT * 
	FROM ^posts 
	WHERE postid > $myurl
	AND type='Q'
	ORDER BY postid ASC
	LIMIT 1";
	 
	$next_q = qa_db_query_sub($query_n);
	 
	while($next_link = qa_db_read_one_assoc($next_q, true)){
	 
	$title = $next_link['title'];
	$pid = $next_link['postid'];
	 
	$this->output( '
	<A HREF="'. qa_q_path_html($pid, $title) .'" title="'. $title .'" STYLE="float:right;padding-right:20px">Next Action &rarr;</A>','');
	}
}
 
}
 
// adding next and previouls question links after all answer. This can be place anywhere you want just call get_prev_q() and get_next_q() functions
function a_list($a_list){
qa_html_theme_base::a_list($a_list);
$this->get_prev_q();
$this->get_next_q();
}

		
		function nav_user_search() // reverse the usual order
		{
			$this->search();
			$this->nav('user');
		}

				function main()
		{
			$content=$this->content;

$this->get_prev_q();
$this->get_next_q();
			$this->output('<DIV CLASS="qa-main'.(@$this->content['hidden'] ? ' qa-main-hidden' : '').'">');
			
			$this->widgets('main', 'top');
			
			$this->page_title_error();		
			
			$this->widgets('main', 'high');

			/*if (isset($content['main_form_tags']))
				$this->output('<FORM '.$content['main_form_tags'].'>');*/
				
			$this->main_parts($content);
		
			/*if (isset($content['main_form_tags']))
				$this->output('</FORM>');*/
				
			$this->widgets('main', 'low');

			$this->page_links();
			$this->suggest_next();
			
			$this->widgets('main', 'bottom');

			$this->output('</DIV> <!-- END qa-main -->', '');
		}
		
		function sidepanel()
		{
			$this->output('<DIV CLASS="content-flow"><DIV CLASS="content-top"></DIV><DIV CLASS="content-wrapper"><DIV CLASS="qa-sidepanel">');
			$this->widgets('side', 'top');
			$this->sidebar();
			$this->widgets('side', 'high');
			$this->nav('cat', 1);
			$this->widgets('side', 'low');
			$this->output_raw(@$this->content['sidepanel']);
			$this->feed();
			$this->widgets('side', 'bottom');
			$this->output('</DIV>', '');
		}
		
			function nav_main_sub()
		{	$this->output('<div id="menu">');
			$this->output('<div id="nav_left"></div>');
			$this->nav('main');
			$this->output('<div id="nav_right"></div></div>');
			$this->nav('sub');

		}

		function logged_in() // adds points count after logged in username
		{
			qa_html_theme_base::logged_in();
			
			if (qa_is_logged_in()) {
				$userpoints=qa_get_logged_in_points();
				$username=qa_html(qa_get_logged_in_handle());
				$userid=qa_get_logged_in_userid();
				$user = qa_db_select_with_pending(qa_db_user_rank_selectspec($userid));
				$userrank = '';
				if (is_array($user)){
					if (array_key_exists('rank',$user)){
						$userrank = '(#'. number_format((int)$user['rank']).')';
					}
				}
				$pointshtml=($userpoints==1)
					? qa_lang_html_sub('main/1_point', '1', '1')
					: qa_lang_html_sub('main/x_points', qa_html(number_format($userpoints)));
					
				$this->output(
					'<SPAN><a CLASS="qa-logged-in-points" href="index.php?qa=user&qa_1='.$username.'#activity">'.$pointshtml.$userrank.'</a></SPAN>'
				);
			}
		}
			
			function suggest_next()
		{
			$suggest=@$this->content['suggest_next'];
			
			if (!empty($suggest)) {
				$this->output('<p style="clear:both">');
				$this->output($suggest);
				$this->output('</p>');
			}
		}
		
		
		function attribution()
		{
			// Please see the license at the top of this file before changing this link. Thank you.
				
			qa_html_theme_base::attribution();

			// modxclub [start] Please erase. Use this theme according to license of Question2Answer.
			$this->output(
				'<DIV CLASS="qa-designedby">',
				'Designed by <A HREF="http://www.axiologic.ro">Axiologic SaaS</A> and <A HREF="http://www.ecofys.com">Ecofys</A>',
				'</DIV>'
			);
			// modxclub [end]
		}
		
	}
	
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
?>