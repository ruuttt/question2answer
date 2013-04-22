<?php

/*
	Question2Answer (c) Gideon Greenspan

	http://www.question2answer.org/

	
	File: qa-plugin/my-followers-page/qa-my-followers-page.php
	Version: See define()s at top of qa-include/qa-base.php
	Description: Initiates my followers page plugin


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

class qa_my_followers_page {

	const PLUGIN			= 'myfollowerspage';
	const PLUGIN_TITLE		= 'myfollowerspage_plugin_title';
	const ENABLE			= 'myfollowerspage_enable';
	const ENABLE_DFL		= false;
	const REQUEST			= 'myfollowerspage_request';
	const REQUEST_DFL		= 'favorited';
	const SAVE_BUTTON		= 'myfollowerspage_save_button';
	const DFL_BUTTON		= 'myfollowerspage_dfl_button';
	const SAVED_MESSAGE		= 'myfollowerspage_saved_message';
	const PAGE_NAVI			= 'myfollowerspage_navi';
	const PAGE_TITLE		= 'myfollowerspage_page_title';
	const PAGE_TITLE_NON	= 'myfollowerspage_page_title_non';

	var $directory;
	var $urltoroot;

	function load_module($directory, $urltoroot) {
		$this->directory=$directory;
		$this->urltoroot=$urltoroot;
	}

	function option_default($option) {
		if ($option==self::ENABLE) return self::ENABLE_DFL;
		if ($option==self::REQUEST) return self::REQUEST_DFL;
	}

	function admin_form(&$qa_content) {
		$saved=false;
		$error='';
		if (qa_clicked(self::SAVE_BUTTON)) {
			if (qa_post_text(self::ENABLE.'_field')) {
				if (trim(qa_post_text(self::REQUEST.'_field')) == '')
					$error = qa_lang_html(self::PLUGIN.'/'.self::REQUEST.'_error');
			}
			if ($error == '') {
				qa_opt(self::ENABLE,(bool)qa_post_text(self::ENABLE.'_field'));
				qa_opt(self::REQUEST,qa_post_text(self::REQUEST.'_field'));
				$saved=true;
			}
		}
		if (qa_clicked(self::DFL_BUTTON)) {
			qa_opt(self::ENABLE,self::ENABLE_DFL);
			qa_opt(self::REQUEST,self::REQUEST_DFL);
			$saved=true;
		}
		
		$rules = array();
		$rules[self::REQUEST] = self::ENABLE.'_field';
		qa_set_display_rules($qa_content, $rules);

		$ret = array();
		if($saved)
			$ret['ok'] = qa_lang_html(self::PLUGIN.'/'.self::SAVED_MESSAGE);
		else {
			if($error != '')
				$ret['ok'] = '<SPAN STYLE="color:#F00;">'.$error.'</SPAN>';
		}

		$fields = array();
		$fields[] = array(
			'id' => self::ENABLE,
			'label' => qa_lang_html(self::PLUGIN.'/'.self::ENABLE.'_label'),
			'type' => 'checkbox',
			'value' => qa_opt(self::ENABLE),
			'tags' => 'NAME="'.self::ENABLE.'_field" ID="'.self::ENABLE.'_field"',
		);
		$fields[] = array(
			'id' => self::REQUEST,
			'label' => qa_lang_html(self::PLUGIN.'/'.self::REQUEST.'_label'),
			'value' => qa_opt(self::REQUEST),
			'tags' => 'NAME="'.self::REQUEST.'_field" ID="'.self::REQUEST.'_field"',
		);
		$ret['fields'] = $fields;

		$buttons = array();
		$buttons[] = array(
			'label' => qa_lang_html(self::PLUGIN.'/'.self::SAVE_BUTTON),
			'tags' => 'NAME="'.self::SAVE_BUTTON.'" ID="'.self::SAVE_BUTTON.'"',
		);
		$buttons[] = array(
			'label' => qa_lang_html(self::PLUGIN.'/'.self::DFL_BUTTON),
			'tags' => 'NAME="'.self::DFL_BUTTON.'" ID="'.self::DFL_BUTTON.'"',
		);
		$ret['buttons'] = $buttons;

		return $ret;
	}

	function suggest_requests() {
		return array(
			array(
				'title' => qa_lang_html(self::PLUGIN.'/'.self::PLUGIN_TITLE),
				'request' => self::REQUEST_DFL,
				'nav' => null, // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
			),
		);
		/*
		return array();
		*/
	}

	function match_request($request) {
		$option = qa_opt(self::REQUEST);
		if(!empty($option)) {
			if ($request==qa_opt(self::REQUEST))
				return true;
		}
		return false;
	}
	
	function process_request($request) {
		require_once QA_INCLUDE_DIR.'qa-db-selects.php';
		require_once QA_INCLUDE_DIR.'qa-app-format.php';
		
		$userid=qa_get_logged_in_userid();
		if (!isset($userid))
			qa_redirect('login');
		
		if (!QA_FINAL_EXTERNAL_USERS) {
			$users=qa_db_select_with_pending($this->qa_db_user_followers_selectspec($userid));
		} else {
			$users=null;
		}
		$usershtml=qa_userids_handles_html($users);
		
		$qa_content=qa_content_prepare();
		$qa_content['title']=count($users) ? qa_lang_html(self::PLUGIN.'/'.self::PAGE_TITLE) : qa_lang_html(self::PLUGIN.'/'.self::PAGE_TITLE_NON);

		if (!QA_FINAL_EXTERNAL_USERS) {
			$qa_content['ranking_users']=array(
				//'title' => count($users) ? qa_lang_html('main/nav_users') : qa_lang_html('misc/no_favorited_users'),
				'items' => array(),
				'rows' => ceil(count($users)/qa_opt('columns_users')),
				'type' => 'users'
			);
			foreach ($users as $user)
				$qa_content['ranking_users']['items'][]=array(
					'label' => qa_get_user_avatar_html($user['flags'], $user['email'], $user['handle'],
						$user['avatarblobid'], $user['avatarwidth'], $user['avatarheight'], qa_opt('avatar_users_size'), true).' '.$usershtml[$user['userid']],
					'score' => qa_html(number_format($user['points'])),
				);
		}
		return $qa_content;
	}

	function qa_db_user_followers_selectspec($userid) {
		require_once QA_INCLUDE_DIR.'qa-app-updates.php';
		return array(
			'columns' => array('^users.userid', 'handle', 'points', 'flags', '^users.email', 'avatarblobid', 'avatarwidth', 'avatarheight'),
			'source' => "^users JOIN ^userpoints ON ^users.userid=^userpoints.userid JOIN ^userfavorites ON ^users.userid=^userfavorites.userid WHERE ^userfavorites.entityid=$ AND ^userfavorites.entitytype=$",
			'arguments' => array($userid, QA_ENTITY_USER),
			'sortasc' => 'handle',
		);
	}
	
};

/*
	Omit PHP closing tag to help avoid accidental output
*/