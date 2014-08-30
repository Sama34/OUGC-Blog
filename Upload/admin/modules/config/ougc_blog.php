<?php

/***************************************************************************
 *
 *	OUGC Blog plugin (/admin/modules/config/ougc_blog.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Adds a blog system to your forum.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Check requirements
ougc_blog_pl_check();

// Set url to use
$ougc_blog->set_url('index.php?module=config-ougc_blog');

$ougc_blog->lang_load();

$sub_tabs['ougc_blog_cat_view'] = array(
	'title'			=> $lang->ougc_blog_tab_cat,
	'link'			=> $ougc_blog->build_url(array('action' => 'categories')),
	'description'	=> $lang->ougc_blog_tab_cat_desc
);
if($mybb->get_input('manage') != 'blog')
{
	$sub_tabs['ougc_blog_cat_add'] = array(
		'title'			=> $lang->ougc_blog_tab_cat_add,
		'link'			=> $ougc_blog->build_url(array('action' => 'add')),
		'description'	=> $lang->ougc_blog_tab_cat_add_desc
	);
}
if($mybb->get_input('action') == 'edit' && $mybb->get_input('manage') != 'blog')
{
	$sub_tabs['ougc_blog_edit'] = array(
		'title'			=> $lang->ougc_blog_tab_edit_cat,
		'link'			=> $ougc_blog->build_url(array('action' => 'edit', 'cid' => $mybb->get_input('cid', 1))),
		'description'	=> $lang->ougc_blog_tab_edit_cat_desc,
	);
}

$page->add_breadcrumb_item($lang->ougc_blog_manage, $ougc_blog->build_url());

if($mybb->get_input('manage') == 'blog')
{
	if(!($category = $ougc_blog->get_category($mybb->get_input('cid', 1))))
	{
		$ougc_blog->redirect($lang->ougc_blog_error_invalidcategory, true);
	}

	// Set url to use
	$ougc_blog->set_url($ougc_blog->build_url(array('manage' => 'blog', 'cid' => $category['cid'])));

	$sub_tabs['ougc_blog_view'] = array(
		'title'			=> $lang->ougc_blog_manage,
		'link'			=> $ougc_blog->build_url(),
		'description'	=> $lang->ougc_blog_manage_desc
	);
	$sub_tabs['ougc_blog_add'] = array(
		'title'			=> $lang->ougc_blog_tab_add,
		'link'			=> $ougc_blog->build_url(array('action' => 'add')),
		'description'	=> $lang->ougc_blog_tab_add_desc
	);
	if($mybb->get_input('action') == 'edit')
	{
		$sub_tabs['ougc_blog_edit'] = array(
			'title'			=> $lang->ougc_blog_tab_edit,
			'link'			=> $ougc_blog->build_url(array('action' => 'edit', 'pid' => $mybb->get_input('pid', 1))),
			'description'	=> $lang->ougc_blog_tab_edit_desc,
		);
	}
	$sub_tabs['ougc_blog_import'] = array(
		'title'			=> $lang->ougc_blog_tab_import,
		'link'			=> $ougc_blog->build_url(array('action' => 'import')),
		'description'	=> $lang->ougc_blog_tab_import_desc
	);

	if($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));

		if(!($add = $mybb->get_input('action') == 'add'))
		{
			if(!($blog = $ougc_blog->get_page($mybb->get_input('pid', 1))))
			{
				$ougc_blog->redirect($lang->ougc_blog_error_invalidpage, true);
			}

			$page->add_breadcrumb_item(strip_tags($blog['name']));
		}

		foreach(array('category', 'cid', 'subject', 'url', 'message', 'visible') as $key)
		{
			if(!isset($mybb->input[$key]) && isset($blog[$key]))
			{
				if(isset($blog[$key]))
				{
					$mybb->input[$key] = $blog[$key];
				}
				else
				{
					$mybb->input[$key] = '';
				}
			}
			unset($key);
		}

		$page->output_header($lang->ougc_blog_manage);
		$page->output_nav_tabs($sub_tabs, $add ? 'ougc_blog_add' : 'ougc_blog_edit');

		if($mybb->request_method == 'post')
		{
			$errors = array();
			if(!$mybb->get_input('subject') || isset($mybb->input['subject']{100}))
			{
				$errors[] = $lang->ougc_blog_error_invalidname;
			}

			$url = $ougc_blog->clean_url($mybb->get_input('url'));
			$query = $db->simple_select('ougc_blog_posts', 'pid', 'url=\''.$db->escape_string($url).'\''.($add ? '' : ' AND pid!=\''.$mybb->get_input('pid', 1).'\''), array('limit' => 1));

			if($db->num_rows($query))
			{
				$errors[] = $lang->ougc_blog_error_invalidurl;
			}

			if(empty($errors))
			{
				$method = $add ? 'insert_page' : 'update_page';
				$lang_val = $add ? 'ougc_blog_success_add' : 'ougc_blog_success_edit';

				if(!$ougc_blog->get_category($mybb->get_input('category', 1)))
				{
					$mybb->input['category'] = (int)$category['cid'];
				}


				$ougc_blog->{$method}(array(
					'cid'			=> $mybb->get_input('category', 1),
					'subject'		=> $mybb->get_input('subject'),
					'url'			=> $url,
					'uid'			=> $mybb->user['uid'],
					'ipaddress'		=> get_ip(),
					'visible'		=> $mybb->get_input('visible', 1),
					'message'		=> $mybb->get_input('message')
				), $mybb->get_input('pid', 1));
				$ougc_blog->update_cache();
				$ougc_blog->log_action();
				$ougc_blog->redirect($lang->{$lang_val});
			}
			else
			{
				$page->output_inline_error($errors);
			}
		}

		$form = new Form($ougc_blog->build_url(($add ? 'action=add' : array('action' => 'edit', 'pid' => $blog['pid']))), 'post');
		$form_container = new FormContainer($sub_tabs['ougc_blog_'.($add ? 'add' : 'edit')]['description']);

		$form_container->output_row($lang->ougc_blog_form_category, $lang->ougc_blog_form_category_desc, $ougc_blog->generate_category_select('category', $mybb->get_input('cid', 1)));
		$form_container->output_row($lang->ougc_blog_form_name.' <em>*</em>', $lang->ougc_blog_form_name_desc, $form->generate_text_box('subject', $mybb->get_input('subject')));
		$form_container->output_row($lang->ougc_blog_form_url.' <em>*</em>', $lang->ougc_blog_form_url_desc, $form->generate_text_box('url', $mybb->get_input('url')));

		$form_container->output_row($lang->ougc_blog_form_visible, $lang->ougc_blog_form_visible_desc, $form->generate_yes_no_radio('visible', $mybb->get_input('visible', 1)));
		$form_container->output_row($lang->ougc_blog_form_template, $lang->ougc_blog_form_template_desc, $form->generate_text_area('message', $mybb->get_input('message'), array('rows' => 5, 'id' => 'message', 'class' => '', 'style' => 'width: 100%; height: 500px;')));

		$form_container->end();
		$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_blog_button_submit), $form->generate_reset_button($lang->reset)));
		$form->end();

		$page->output_footer();
	}
	elseif($mybb->get_input('action') == 'delete')
	{
		if(!$ougc_blog->get_page($mybb->get_input('pid', 1)))
		{
			$ougc_blog->redirect($lang->ougc_blog_error_invalidpage, true);
		}

		if($mybb->request_method == 'post')
		{
			if(!verify_post_check($mybb->get_input('my_post_key'), true))
			{
				$ougc_blog->redirect($lang->invalid_post_verify_key2, true);
			}

			!$mybb->get_input('no') or $ougc_blog->redirect();

			$ougc_blog->delete_page($mybb->get_input('pid', 1));
			$ougc_blog->log_action();
			$ougc_blog->update_cache();
			$ougc_blog->redirect($lang->ougc_blog_success_delete);
		}

		$page->output_confirm_action($ougc_blog->build_url(array('action' => 'delete', 'pid' => $mybb->get_input('pid', 1))));
	}
	elseif($mybb->get_input('action') == 'update')
	{
		if(!($page = $ougc_blog->get_page($mybb->get_input('pid', 1))))
		{
			$ougc_blog->redirect($lang->ougc_blog_error_invalidpage, true);
		}

		if(!verify_post_check($mybb->get_input('my_post_key'), true))
		{
			$ougc_blog->redirect($lang->invalid_post_verify_key2, true);
		}

		$ougc_blog->update_page(array('visible' => (int)!(bool)$page['visible']), $mybb->get_input('pid', 1));
		$ougc_blog->log_action();
		$ougc_blog->update_cache();
		$ougc_blog->redirect();
	}
	else
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));
		$page->output_header($lang->ougc_blog_manage);
		$page->output_nav_tabs($sub_tabs, 'ougc_blog_view');

		$table = new Table;
		$table->construct_header($lang->ougc_blog_form_name, array('width' => '60%'));
		$table->construct_header($lang->ougc_blog_form_visible, array('width' => '10%', 'class' => 'align_center'));
		$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

		$ougc_blog->build_limit();

		$query = $db->simple_select('ougc_blog_posts', 'COUNT(cid) AS blog', 'cid=\''.(int)$category['cid'].'\'');
		$count = (int)$db->fetch_field($query, 'blog');

		$multipage = $ougc_blog->build_multipage($count);
	
		if(!$count)
		{
			$table->construct_cell('<div align="center">'.$lang->ougc_blog_view_empty.'</div>', array('colspan' => 5));
			$table->construct_row();

			$table->output($sub_tabs['ougc_blog_view']['title']);
		}
		else
		{
			$query = $db->simple_select('ougc_blog_posts', '*', 'cid=\''.(int)$category['cid'].'\'', array('limit_start' => $ougc_blog->query_start, 'limit' => $ougc_blog->query_limit));

			echo $multipage;

			while($blog = $db->fetch_array($query))
			{
				$edit_link = $ougc_blog->build_url(array('action' => 'edit', 'pid' => $blog['pid']));
				$blog['subject'] = htmlspecialchars_uni($blog['subject']);

				$blog['visible'] or $blog['subject'] = '<em>'.$blog['subject'].'</em>';

				$table->construct_cell('<a href="'.$edit_link.'"><strong>'.$blog['subject'].'</strong></a> <span style="font-size: 90%">('.$ougc_blog->build_page_link($lang->ougc_blog_view_page, $blog['pid']).')</span>');
				$table->construct_cell('<a href="'.$ougc_blog->build_url(array('action' => 'update', 'pid' => $blog['pid'], 'my_post_key' => $mybb->post_code)).'"><img src="styles/default/images/icons/bullet_o'.(!$blog['visible'] ? 'ff' : 'n').'.png" alt="" title="'.(!$blog['visible'] ? $lang->ougc_blog_form_disabled : $lang->ougc_blog_form_visible).'" /></a>', array('class' => 'align_center'));

				$popup = new PopupMenu('page_'.$blog['pid'], $lang->options);
				$popup->add_item($lang->edit, $edit_link);
				$popup->add_item($lang->delete, $ougc_blog->build_url(array('action' => 'delete', 'pid' => $blog['pid'])));
				$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

				$table->construct_row();
			}

			$table->output($sub_tabs['ougc_blog_view']['title']);
		}

		$page->output_footer();
	}
}
elseif($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
{
	if(!($add = $mybb->get_input('action') == 'add'))
	{
		if(!($category = $ougc_blog->get_category($mybb->get_input('cid', 1))))
		{
			$ougc_blog->redirect($lang->ougc_blog_error_invalidcategory, true);
		}

		$page->add_breadcrumb_item(strip_tags($category['name']));
	}

	foreach(array('name', 'url') as $key)
	{
		if(!isset($mybb->input[$key]) && isset($category[$key]))
		{
			if(isset($category[$key]))
			{
				$mybb->input[$key] = $category[$key];
			}
			else
			{
				$mybb->input[$key] = '';
			}
		}
		unset($key);
	}

	$page->output_header($lang->ougc_blog_manage);
	$page->output_nav_tabs($sub_tabs, $add ? 'ougc_blog_cat_add' : 'ougc_blog_edit');

	if($mybb->request_method == 'post')
	{
		$errors = array();
		if(!$mybb->get_input('name') || isset($mybb->input['name']{100}))
		{
			$errors[] = $lang->ougc_blog_error_invalidname;
		}

		if(!$mybb->get_input('url'))
		{
			$errors[] = $lang->ougc_blog_error_invalidurl;
		}

		$url = $ougc_blog->clean_url($mybb->get_input('url'));
		$query = $db->simple_select('ougc_blog_categories', 'cid', 'url=\''.$db->escape_string($url).'\''.($add ? '' : ' AND cid!=\''.$mybb->get_input('cid', 1).'\''), array('limit' => 1));

		if($db->num_rows($query))
		{
			$errors[] = $lang->ougc_blog_error_invalidurl;
		}

		if(empty($errors))
		{
			$method = $add ? 'insert_category' : 'update_category';
			$lang_val = $add ? 'ougc_blog_success_add' : 'ougc_blog_success_edit';

			$ougc_blog->{$method}(array(
				'name'			=> $mybb->get_input('name'),
				'url'			=> $url
			), $mybb->get_input('cid', 1));
			$ougc_blog->update_cache();
			$ougc_blog->log_action();
			$ougc_blog->redirect($lang->{$lang_val});
		}
		else
		{
			$page->output_inline_error($errors);
		}
	}

	$form = new Form($ougc_blog->build_url(($add ? 'action=add' : array('action' => 'edit', 'cid' => $category['cid']))), 'post');
	$form_container = new FormContainer($sub_tabs['ougc_blog_'.($add ? 'cat_add' : 'edit')]['description']);

	$form_container->output_row($lang->ougc_blog_form_name.' <em>*</em>', $lang->ougc_blog_form_name_desc, $form->generate_text_box('name', $mybb->get_input('name')));
	$form_container->output_row($lang->ougc_blog_form_url.' <em>*</em>', $lang->ougc_blog_form_url_desc, $form->generate_text_box('url', $mybb->get_input('url')));

	$form_container->end();
	$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_blog_button_submit), $form->generate_reset_button($lang->reset)));
	$form->end();
	$page->output_footer();
}
elseif($mybb->get_input('action') == 'delete')
{
	if(!$ougc_blog->get_category($mybb->get_input('cid', 1)))
	{
		$ougc_blog->redirect($lang->ougc_blog_error_invalidcategory, true);
	}

	if($mybb->request_method == 'post')
	{
		if(!verify_post_check($mybb->get_input('my_post_key'), true))
		{
			$ougc_blog->redirect($lang->invalid_post_verify_key2, true);
		}

		!$mybb->get_input('no') or $ougc_blog->redirect();

		$ougc_blog->delete_page_category($mybb->get_input('cid', 1));
		$ougc_blog->log_action();
		$ougc_blog->update_cache();
		$ougc_blog->redirect($lang->ougc_blog_success_delete);
	}

	$page->output_confirm_action($ougc_blog->build_url(array('action' => 'delete', 'cid' => $mybb->get_input('cid', 1))));
}
else
{
	$page->add_breadcrumb_item($sub_tabs['ougc_blog_cat_view']['title'], $ougc_blog->build_url());
	$page->output_header($lang->ougc_blog_manage);
	$page->output_nav_tabs($sub_tabs, 'ougc_blog_cat_view');

	$table = new Table;
	$table->construct_header($lang->ougc_blog_form_name, array('width' => '60%'));
	$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

	$ougc_blog->build_limit();

	$query = $db->simple_select('ougc_blog_categories', 'COUNT(cid) AS categories');
	$count = (int)$db->fetch_field($query, 'categories');

	$multipage = $ougc_blog->build_multipage($count);
	
	if(!$count)
	{
		$table->construct_cell('<div align="center">'.$lang->ougc_blog_view_empty.'</div>', array('colspan' => 4));
		$table->construct_row();

		$table->output($sub_tabs['ougc_blog_cat_view']['title']);
	}
	else
	{
		echo $multipage;

		$query = $db->simple_select('ougc_blog_categories', '*', '', array('limit_start' => $ougc_blog->query_start, 'limit' => $ougc_blog->query_limit));

		while($category = $db->fetch_array($query))
		{
			$manage_link = $ougc_blog->build_url(array('manage' => 'blog', 'cid' => $category['cid']));
			$category['name'] = htmlspecialchars_uni($category['name']);

			$table->construct_cell('<a href="'.$manage_link.'"><strong>'.$category['name'].'</strong></a> <span style="font-size: 90%">('.$ougc_blog->build_category_link($lang->ougc_blog_view_page, $category['cid']).')</span>');

			$popup = new PopupMenu('category_'.$category['cid'], $lang->options);
			$popup->add_item($lang->ougc_blog_manage, $manage_link);
			$popup->add_item($lang->edit, $ougc_blog->build_url(array('action' => 'edit', 'cid' => $category['cid'])));
			$popup->add_item($lang->delete, $ougc_blog->build_url(array('action' => 'delete', 'cid' => $category['cid'])));
			$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

			$table->construct_row();
		}

		$table->output($sub_tabs['ougc_blog_cat_view']['title']);
	}

	$page->output_footer();
}