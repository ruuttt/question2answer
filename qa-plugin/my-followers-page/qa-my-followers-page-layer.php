<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/

	
	File: qa-plugin/my-followers-page/qa-my-followers-page-layer.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Theme layer class for my followers page plugin


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

class qa_html_theme_layer extends qa_html_theme_base {

	const PLUGIN			= 'myfollowerspage';
	const ENABLE			= 'myfollowerspage_enable';
	const REQUEST			= 'myfollowerspage_request';
	const REQUEST_DFL		= 'favorited';
	const PAGE_NAVI			= 'myfollowerspage_navi';

	function nav($navtype, $level=null)
	{
		if(qa_opt(self::ENABLE) && qa_opt(self::REQUEST) != '') {
			if ($navtype == 'sub') {
				$request = strtolower(qa_request_part(0));
				if($request == 'account' || $request == 'favorites' || $request == qa_opt(self::REQUEST)) {
					//$this->content['suggest_next']=qa_lang_html_sub('misc/suggest_favorites_add', '<SPAN CLASS="qa-favorite-image">&nbsp;</SPAN>');
					if (!QA_FINAL_EXTERNAL_USERS) {
						$this->content['navigation']['sub']=qa_account_sub_navigation();
						$this->content['navigation']['sub'][qa_opt(self::REQUEST)] = array(
							'label' => qa_lang_html(self::PLUGIN.'/'.self::PAGE_NAVI),
							'url' => qa_path_html(qa_opt(self::REQUEST)),
						);					
					}
				}
			}
		}
		qa_html_theme_base::nav($navtype, $level=null);
	}

}

/*
	Omit PHP closing tag to help avoid accidental output
*/