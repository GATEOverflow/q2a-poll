<?php

	class qa_poll_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function suggest_requests() // for display in admin interface
		{	
			return array(
				array(
					'title' => qa_opt('poll_page_title'),
					'request' => 'polls',
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		function match_request($request)
		{
			if ($request=='polls')
				return true;

			return false;
		}

		function process_request($request)
		{
			require_once QA_INCLUDE_DIR.'qa-db-selects.php';
			require_once QA_INCLUDE_DIR.'qa-app-format.php';
			require_once QA_INCLUDE_DIR.'qa-app-q-list.php';
			
			$sort=qa_get('sort');


		//	Get list of questions, plus category information

			$nonetitle=qa_lang_html('main/no_questions_found');
			
			$categorypathprefix=null; // only show category list and feed when sorting by date
			$feedpathprefix=null;

			$selectspec=array(
				'columns' => array(
					'^posts.postid', '^posts.categoryid', '^posts.type', 'basetype' => 'LEFT(^posts.type,1)', 'hidden' => "INSTR(^posts.type, '_HIDDEN')>0",
					'^posts.acount', '^posts.selchildid', '^posts.upvotes', '^posts.downvotes', '^posts.netvotes', '^posts.views', '^posts.hotness',
					'^posts.flagcount', 'title' => 'BINARY ^posts.title', 'tags' => 'BINARY ^posts.tags', 'created' => 'UNIX_TIMESTAMP(^posts.created)',
					'categoryname' => 'BINARY ^categories.title', 'categorybackpath' => "BINARY ^categories.backpath",
				),
				
				'arraykey' => 'postid',
				'source' => '^posts LEFT JOIN ^categories ON ^categories.categoryid=^posts.categoryid JOIN ^postmeta ON ^posts.postid=^postmeta.post_id AND ^postmeta.meta_key=$ AND ^postmeta.meta_value>0 ORDER BY ^posts.created DESC',
				'arguments' => array('is_poll'),
			);			
			$selectspec['columns']['content']='BINARY ^posts.content';
			$selectspec['columns']['notify']='BINARY ^posts.notify';
			$selectspec['columns']['updated']='UNIX_TIMESTAMP(^posts.updated)';
			$selectspec['columns'][]='^posts.format';
			$selectspec['columns'][]='^posts.lastuserid';
			$selectspec['columns']['lastip']='INET_NTOA(^posts.lastip)';
			$selectspec['columns'][]='^posts.parentid';
			$selectspec['columns']['lastviewip']='INET_NTOA(^posts.lastviewip)';
			
			$questions = qa_db_select_with_pending($selectspec);
			
			global $qa_start;
			
		//	Prepare and return content for theme

			$qa_content=qa_q_list_page_content(
				$questions, // questions
				qa_opt('page_size_qs'), // questions per page
				$qa_start, // start offset
				count($questions), // total count
				qa_opt('poll_page_title'), // title if some questions
				$nonetitle, // title if no questions
				null, // categories for navigation
				null, // selected category id
				false, // show question counts in category navigation
				null, // prefix for links in category navigation
				null, // prefix for RSS feed paths
				null, // suggest what to do next
				null // extra parameters for page links
			);
			
			if (!$countslugs)
				$qa_content['navigation']['sub']=qa_qs_sub_navigation($sort);

			return $qa_content;
		}
		

	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/