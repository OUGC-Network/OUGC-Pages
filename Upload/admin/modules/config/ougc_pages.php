<?php

/***************************************************************************
 *
 *	OUGC Pages plugin (/admin/modules/config/ougc_pages.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Create additional pages directly from the ACP.
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
ougc_pages_pl_check();

// Set url to use
$ougc_pages->set_url('index.php?module=config-ougc_pages');

$ougc_pages->lang_load();
ougc_pages_activate();
$sub_tabs['ougc_pages_cat_view'] = array(
	'title'			=> $lang->ougc_pages_tab_cat,
	'link'			=> $ougc_pages->build_url(array('action' => 'categories')),
	'description'	=> $lang->ougc_pages_tab_cat_desc
);
if($mybb->get_input('manage') != 'pages')
{
	$sub_tabs['ougc_pages_cat_add'] = array(
		'title'			=> $lang->ougc_pages_tab_cat_add,
		'link'			=> $ougc_pages->build_url(array('action' => 'add')),
		'description'	=> $lang->ougc_pages_tab_cat_add_desc
	);
}
if($mybb->get_input('action') == 'edit' && $mybb->get_input('manage') != 'pages')
{
	$sub_tabs['ougc_pages_edit'] = array(
		'title'			=> $lang->ougc_pages_tab_edit_cat,
		'link'			=> $ougc_pages->build_url(array('action' => 'edit', 'cid' => $mybb->get_input('cid', 1))),
		'description'	=> $lang->ougc_pages_tab_edit_cat_desc,
	);
}

if($mybb->get_input('manage') == 'pages')
{
	if(!($category = $ougc_pages->get_category($mybb->get_input('cid', 1))))
	{
		$ougc_pages->redirect($lang->ougc_pages_error_invalidcategory, true);
	}

	// Set url to use
	$ougc_pages->set_url($ougc_pages->build_url(array('manage' => 'pages', 'cid' => $category['cid'])));

	$sub_tabs['ougc_pages_view'] = array(
		'title'			=> $lang->ougc_pages_manage,
		'link'			=> $ougc_pages->build_url(),
		'description'	=> $lang->ougc_pages_manage_desc
	);
	$sub_tabs['ougc_pages_add'] = array(
		'title'			=> $lang->ougc_pages_tab_add,
		'link'			=> $ougc_pages->build_url(array('action' => 'add')),
		'description'	=> $lang->ougc_pages_tab_add_desc
	);
	if($mybb->get_input('action') == 'edit')
	{
		$sub_tabs['ougc_pages_edit'] = array(
			'title'			=> $lang->ougc_pages_tab_edit,
			'link'			=> $ougc_pages->build_url(array('action' => 'edit', 'pid' => $mybb->get_input('pid', 1))),
			'description'	=> $lang->ougc_pages_tab_edit_desc,
		);
	}
	$sub_tabs['ougc_pages_import'] = array(
		'title'			=> $lang->ougc_pages_tab_import,
		'link'			=> $ougc_pages->build_url(array('action' => 'import')),
		'description'	=> $lang->ougc_pages_tab_import_desc
	);

	if($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));
		$page->add_breadcrumb_item($lang->ougc_pages_acp_nav, $ougc_pages->build_url());

		if(!($add = $mybb->get_input('action') == 'add'))
		{
			if(!($pages = $ougc_pages->get_page($mybb->get_input('pid', 1))))
			{
				$ougc_pages->redirect($lang->ougc_pages_error_invalidpage, true);
			}

			$page->add_breadcrumb_item(strip_tags($pages['name']));
		}

		foreach(array('category', 'name', 'url', 'groups', 'php', 'wol', 'disporder', 'template', 'visible') as $key)
		{
			$mybb->input[$key] = isset($mybb->input[$key]) ? $mybb->input[$key] : ($add ? '' : $pages[$key]);
		}

		if($admin_options['codepress'])
		{
			$page->extra_header .= '<link type="text/css" href="./jscripts/codepress/languages/codepress-mybb.css" rel="stylesheet" id="cp-lang-style" />
<script type="text/javascript" src="./jscripts/codepress/codepress.js"></script>
<script type="text/javascript">
CodePress.language = \'mybb\';
</script>';
		}

		$page->output_header($lang->ougc_pages_acp_nav);
		$page->output_nav_tabs($sub_tabs, $add ? 'ougc_pages_cat_add' : 'ougc_pages_edit');

		if($mybb->request_method == 'post')
		{
			$errors = array();
			if(!$mybb->get_input('name') || isset($mybb->input['name']{100}))
			{
				$errors[] = $lang->ougc_pages_error_invalidname;
			}

			!isset($mybb->input['description']{255}) or $errors[] = $lang->ougc_pages_error_invaliddesscription;

			$url = $ougc_pages->clean_url($mybb->get_input('url'));
			$query = $db->simple_select('ougc_pages', 'pid', 'url=\''.$db->escape_string($url).'\''.($add ? '' : ' AND pid!=\''.$mybb->get_input('pid', 1).'\''), array('limit' => 1));

			if($db->num_rows($query))
			{
				$errors[] = $lang->ougc_pages_error_invalidurl;
			}

			if(empty($errors))
			{
				$method = $add ? 'insert_page' : 'update_page';
				$lang_val = $add ? 'ougc_pages_success_add' : 'ougc_pages_success_edit';

				$ougc_pages->{$method}(array(
					'cid'			=> $mybb->get_input('category'),
					'name'			=> $mybb->get_input('name'),
					'url'			=> $url,
					'groups'		=> $ougc_pages->clean_ints($mybb->get_input('groups', 2), true),
					'php'			=> $mybb->get_input('php', 1),
					'wol'			=> $mybb->get_input('wol', 1),
					'disporder'		=> $mybb->get_input('disporder', 1),
					'visible'		=> $mybb->get_input('visible', 1),
					'template'		=> $mybb->get_input('template')
				), $mybb->get_input('pid', 1));
				$ougc_pages->update_cache();
				$ougc_pages->log_action();
				$ougc_pages->redirect($lang->{$lang_val});
			}
			else
			{
				$page->output_inline_error($errors);
			}
		}

		$form = new Form($ougc_pages->build_url(($add ? 'action=add' : array('action' => 'edit', 'pid' => $pages['pid']))), 'post');
		$form_container = new FormContainer($sub_tabs['ougc_pages_'.($add ? 'cat_add' : 'edit')]['description']);

		$form_container->output_row($lang->ougc_pages_form_groups, $lang->ougc_pages_form_groups_desc, $ougc_pages->generate_category_select('category', $mybb->get_input('category')));
		$form_container->output_row($lang->ougc_pages_form_name.' <em>*</em>', $lang->ougc_pages_form_name_desc, $form->generate_text_box('name', $mybb->get_input('name')));
		$form_container->output_row($lang->ougc_pages_form_url.' <em>*</em>', $lang->ougc_pages_form_url_desc, $form->generate_text_box('url', $mybb->get_input('url')));
		$form_container->output_row($lang->ougc_pages_form_groups, $lang->ougc_pages_form_groups_desc, $form->generate_group_select('groups[]', $ougc_pages->clean_ints($mybb->get_input('groups')), array('multiple' => 1, 'size' => 5)));
		$form_container->output_row($lang->ougc_pages_form_php, $lang->ougc_pages_form_php_desc, $form->generate_yes_no_radio('php', $mybb->get_input('php', 1)));
		$form_container->output_row($lang->ougc_pages_form_wol, $lang->ougc_pages_form_wol_desc, $form->generate_yes_no_radio('wol', $mybb->get_input('wol', 1)));
		$form_container->output_row($lang->ougc_pages_form_visible, $lang->ougc_pages_form_visible_desc, $form->generate_yes_no_radio('visible', $mybb->get_input('visible', 1)));
		$form_container->output_row($lang->ougc_pages_form_disporder, $lang->ougc_pages_form_disporder_desc, $form->generate_text_box('disporder', $mybb->get_input('disporder', 1), array('style' => 'text-align: center; width: 30px;" maxlength="5')));
		$form_container->output_row($lang->ougc_pages_form_template, $lang->ougc_pages_form_template_desc, $form->generate_text_area('template', $mybb->get_input('template'), array('rows' => 50, 'id' => 'template', 'class' => 'codepress mybb', 'style' => 'width: 100%; height: 500px;')));

		$form_container->end();
		$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pageds_button_submit), $form->generate_reset_button($lang->reset)));
		$form->end();
		$page->output_footer();
	}
	elseif($mybb->get_input('action') == 'delete')
	{
		if(!$ougc_pages->get_page($mybb->get_input('pid', 1)))
		{
			$ougc_pages->redirect($lang->ougc_pages_error_invalidpage, true);
		}

		if($mybb->request_method == 'post')
		{
			if(!verify_post_check($mybb->get_input('my_post_key'), true))
			{
				$ougc_pages->redirect($lang->invalid_post_verify_key2, true);
			}

			!$mybb->get_input('no') or $ougc_pages->redirect();

			$ougc_pages->delete_page($mybb->get_input('pid', 1));
			$ougc_pages->log_action();
			$ougc_pages->update_cache();
			$ougc_pages->redirect($lang->ougc_pages_success_delete);
		}

		$page->output_confirm_action($ougc_pages->build_url(array('action' => 'delete', 'pid' => $mybb->get_input('pid', 1))));
	}
	elseif($mybb->get_input('action') == 'update')
	{
		if(!($page = $ougc_pages->get_page($mybb->get_input('pid', 1))))
		{
			$ougc_pages->redirect($lang->ougc_pages_error_invalidpage, true);
		}

		if(!verify_post_check($mybb->get_input('my_post_key'), true))
		{
			$ougc_pages->redirect($lang->invalid_post_verify_key2, true);
		}

		$ougc_pages->update_page(array('visible' => (int)!(bool)$page['visible']), $mybb->get_input('pid', 1));
		$ougc_pages->log_action();
		$ougc_pages->update_cache();
		$ougc_pages->redirect();
	}
	elseif($mybb->get_input('action') == 'export')
	{
		if(!($page = $ougc_pages->get_page($mybb->get_input('pid', 1))))
		{
			$ougc_pages->redirect($lang->ougc_pages_error_invalidpage, true);
		}

		$info = ougc_pages_info();

		$file = $PL->xml_export(array(
			'name'			=> $page['name'],
			'php'			=> $page['php'],
			'wol'			=> $page['wol'],
			'template'		=> $page['template']
		), 'OUGC_Pages_'.$page['name'].'_'.$info['versioncode']);
	}
	elseif($mybb->get_input('action') == 'import')
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));
		$page->add_breadcrumb_item($lang->ougc_pages_acp_nav, $ougc_pages->build_url());
		$page->output_header($lang->ougc_pages_acp_nav);
		$page->output_nav_tabs($sub_tabs, 'ougc_pages_import');

		if($mybb->request_method == 'post')
		{
			$lang->load('style_themes');

			$errors = array();
			if(!$_FILES['localfile'] && !$mybb->get_input('urlfile'))
			{
				#$errors[] = $lang->ougc_pages_error_invalidurl;
			}

			if($mybb->get_input('file_url'))
			{
				if(!($contents = fetch_remote_file($mybb->get_input('file_url'))))
				{
					$errors[] = $lang->error_local_file;
				}
			}
			else
			{
				// UPLOAD_ERR_NO_FILE
				$errors[] = $lang->error_uploadfailed_php4;
			}

			if(empty($errors))
			{
				$xml_import = $PL->xml_import($contents);

				$query = $db->simple_select('ougc_pages', 'MAX(disporder) as max_disporder');
				$max_disporder = (int)$db->fetch_field($query, 'max_disporder');

				_dump(slug_url($xml_import['name'], $pid));
				$ougc_pages->insert_page(array(
					'cid'			=> $category['cid'],
					'name'			=> $xml_import['name'],
					'php'			=> $xml_import['php'],
					'wol'			=> $xml_import['wol'],
					'disporder'		=> ++$max_disporder,
					'template'		=> $xml_import['template']
				));

				$ougc_pages->update_cache();
				$ougc_pages->log_action();
				$ougc_pages->redirect($lang->ougc_pages_success_add);
			}
			else
			{
				$page->output_inline_error($errors);
			}
		}

		$form = new Form($ougc_pages->build_url('action=import'), 'post');
		$form_container = new FormContainer($sub_tabs['ougc_pages_import']['description']);

		$form_container->output_row($lang->ougc_pages_form_import, $lang->ougc_pages_form_import_desc, $form->generate_file_upload_box('localfile', $mybb->get_input('localfile')));
		$form_container->output_row($lang->ougc_pages_form_import_url, $lang->ougc_pages_form_import_url_desc, $form->generate_text_box('file_url', $mybb->get_input('file_url')));

		$form_container->end();
		$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pageds_button_submit), $form->generate_reset_button($lang->reset)));
		$form->end();
		$page->output_footer();
	}
	else
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));
		$page->add_breadcrumb_item($sub_tabs['ougc_pages_view']['title'], $ougc_pages->build_url());
		$page->output_header($lang->ougc_pages_acp_nav);
		$page->output_nav_tabs($sub_tabs, 'ougc_pages_view');

		$table = new Table;
		$table->construct_header($lang->ougc_pages_form_name, array('width' => '30%'));
			$table->construct_header($lang->ougc_pages_form_category, array('width' => '30%'));
		$table->construct_header($lang->ougc_pages_form_disporder, array('width' => '15%', 'class' => 'align_center'));
		$table->construct_header($lang->ougc_pages_form_visible, array('width' => '10%', 'class' => 'align_center'));
		$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

		$ougc_pages->build_limit();

		$query = $db->simple_select('ougc_pages', 'COUNT(cid) AS pages');
		$count = (int)$db->fetch_field($query, 'pages');

		$multipage = $ougc_pages->build_multipage($count);
	
		if(!$count)
		{
			$table->construct_cell('<div align="center">'.$lang->ougc_pages_view_empty.'</div>', array('colspan' => 5));
			$table->construct_row();

			$table->output($sub_tabs['ougc_pages_view']['title']);
		}
		else
		{
			if($mybb->request_method == 'post' && $mybb->get_input('action') == 'updatedisporder')
			{
				foreach($mybb->get_input('disporder', 2) as $pid => $disporder)
				{
					$ougc_pages->update_page(array('disporder' => $disporder), $pid);
				}
				$ougc_pages->update_cache();
				$ougc_pages->redirect();
			}

			$query = $db->simple_select('ougc_pages', '*', '', array('limit_start' => $ougc_pages->query_start, 'limit' => $ougc_pages->query_limit, 'order_by' => 'disporder'));

			echo $multipage;

			$form = new Form($ougc_pages->build_url('action=updatedisporder'), 'post');

			while($pages = $db->fetch_array($query))
			{
				$category = $ougc_pages->get_category($pages['cid']);

				$table->construct_cell('<b>'.htmlspecialchars_uni($pages['name']).'</b><br /><i>'.$ougc_pages->get_page_link($pages['pid']).'</i>');
				$table->construct_cell(htmlspecialchars_uni($category['name']).'<br /><i>'.$ougc_pages->get_category_link($category['cid']).'</i>');
				$table->construct_cell($form->generate_text_box('disporder['.$pages['pid'].']', (int)$pages['disporder'], array('style' => 'text-align: center; width: 30px;')), array('class' => 'align_center'));
				$table->construct_cell('<a href="'.$ougc_pages->build_url(array('action' => 'update', 'pid' => $pages['pid'], 'my_post_key' => $mybb->post_code)).'"><img src="styles/default/images/icons/bullet_o'.(!$pages['visible'] ? 'ff' : 'n').'.gif" alt="" title="'.(!$pages['visible'] ? $lang->ougc_pages_form_disabled : $lang->ougc_pages_form_visible).'" /></a>', array('class' => 'align_center'));

				$popup = new PopupMenu('page_'.$pages['pid'], $lang->options);
				$popup->add_item($lang->ougc_pages_form_export, $ougc_pages->build_url(array('action' => 'export', 'pid' => $pages['pid'], 'my_post_key' => $mybb->post_code)));
				$popup->add_item($lang->edit, $ougc_pages->build_url(array('action' => 'edit', 'pid' => $pages['pid'])));
				$popup->add_item($lang->delete, $ougc_pages->build_url(array('action' => 'delete', 'pid' => $pages['pid'])));
				$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

				$table->construct_row();
			}

			$table->output($sub_tabs['ougc_pages_view']['title']);

			$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pages_button_disponder), $form->generate_reset_button($lang->reset)));
			$form->end();
		}

		$page->output_footer();
	}
}
elseif($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit')
{
	$page->add_breadcrumb_item($lang->ougc_pages_acp_nav, $ougc_pages->build_url());

	if(!($add = $mybb->get_input('action') == 'add'))
	{
		if(!($category = $ougc_pages->get_category($mybb->get_input('cid', 1))))
		{
			$ougc_pages->redirect($lang->ougc_pages_error_invalidcategory, true);
		}

		$page->add_breadcrumb_item(strip_tags($category['name']));
	}

	$mybb->get_input('groups_type') or $mybb->input['groups_type'] = 'all';

	foreach(array('name', 'groups', 'url', 'disporder', 'breadcrumb', 'visible') as $key)
	{
		$mybb->input[$key] = isset($mybb->input[$key]) ? $mybb->input[$key] : ($add ? '' : $category[$key]);
		unset($key);
	}

	$page->output_header($lang->ougc_pages_acp_nav);
	$page->output_nav_tabs($sub_tabs, $add ? 'ougc_pages_cat_add' : 'ougc_pages_edit');

	if($mybb->request_method == 'post')
	{
		$errors = array();
		if(!$mybb->get_input('name') || isset($mybb->input['name']{100}))
		{
			$errors[] = $lang->ougc_pages_error_invalidname;
		}

		if(!$mybb->get_input('url'))
		{
			$errors[] = $lang->ougc_pages_error_invalidname;
		}

		$url = $ougc_pages->clean_url($mybb->get_input('url'));
		$query = $db->simple_select('ougc_pages_categories', 'cid', 'url=\''.$db->escape_string($url).'\''.($add ? '' : ' AND cid!=\''.$mybb->get_input('cid', 1).'\''), array('limit' => 1));

		if($db->num_rows($query))
		{
			$errors[] = $lang->ougc_pages_error_invalidurl;
		}

		if(empty($errors))
		{
			$method = $add ? 'insert_category' : 'update_category';
			$lang_val = $add ? 'ougc_pages_success_add' : 'ougc_pages_success_edit';

			$ougc_pages->{$method}(array(
				'name'			=> $mybb->get_input('name'),
				'url'			=> $url,
				'groups'		=> $ougc_pages->clean_ints($mybb->get_input('groups', 2), true),
				'disporder'		=> $mybb->get_input('disporder', 1),
				'visible'		=> $mybb->get_input('visible', 1),
				'breadcrumb'	=> $mybb->get_input('breadcrumb', 1)
			), $mybb->get_input('cid', 1));
			$ougc_pages->update_cache();
			$ougc_pages->log_action();
			$ougc_pages->redirect($lang->{$lang_val});
		}
		else
		{
			$page->output_inline_error($errors);
		}
	}

	$form = new Form($ougc_pages->build_url(($add ? 'action=add' : array('action' => 'edit', 'cid' => $category['cid']))), 'post');
	$form_container = new FormContainer($sub_tabs['ougc_pages_'.($add ? 'cat_add' : 'edit')]['description']);

	$form_container->output_row($lang->ougc_pages_form_name.' <em>*</em>', $lang->ougc_pages_form_name_desc, $form->generate_text_box('name', $mybb->get_input('name')));
	$form_container->output_row($lang->ougc_pages_form_url.' <em>*</em>', $lang->ougc_pages_form_url_desc, $form->generate_text_box('url', $mybb->get_input('url')));

	$selected_values = '';
	if($mybb->get_input('groups_type') == 'custom')
	{
		$selected_values = $ougc_pages->clean_ints($mybb->get_input('groups', 2));
	}

	$group_checked = array('all' => '', 'custom' => '', 'none' => '');
	if($mybb->get_input('groups_type') == 'all')
	{
		$group_checked['all'] = 'checked="checked"';
	}
	elseif($mybb->get_input('groups_type') == 'none')
	{
		$group_checked['none'] = 'checked="checked"';
	}
	else
	{
		$group_checked['custom'] = 'checked="checked"';
	}

	print_selection_javascript();

	$setting_code = "
	<dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
		<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"all\" {$group_checked['all']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_groups}</strong></label></dt>
		<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"custom\" {$group_checked['custom']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_groups}</strong></label></dt>
		<dd style=\"margin-top: 4px;\" id=\"groups_forums_groups_custom\" class=\"groups_forums_groups\">
			<table cellpadding=\"4\">
				<tr>
					<td valign=\"top\"><small>{$lang->groups_colon}</small></td>
					<td>".$form->generate_group_select('groups[]', $selected_values, array('multiple' => true, 'size' => 5))."</td>
				</tr>
			</table>
		</dd>
		<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"none\" {$group_checked['none']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->none}</strong></label></dt>
	</dl>
	<script type=\"text/javascript\">
		checkAction('groups');
	</script>";

	$form_container->output_row($lang->ougc_pages_form_groups, $lang->ougc_pages_form_groups_desc, $setting_code, '', array(), array('id' => 'row_groups'));

	$form_container->output_row($lang->ougc_pages_form_visible, $lang->ougc_pages_form_visible_desc, $form->generate_yes_no_radio('visible', $mybb->get_input('visible', 1)));
	$form_container->output_row($lang->ougc_pages_form_breadcrumb, $lang->ougc_pages_form_breadcrumb_desc, $form->generate_yes_no_radio('breadcrumb', $mybb->get_input('breadcrumb', 1)));
	$form_container->output_row($lang->ougc_pages_form_disporder, $lang->ougc_pages_form_disporder_desc, $form->generate_text_box('disporder', $mybb->get_input('disporder', 1), array('style' => 'text-align: center; width: 30px;" maxlength="5')));

	$form_container->end();
	$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pageds_button_submit), $form->generate_reset_button($lang->reset)));
	$form->end();
	$page->output_footer();
}
elseif($mybb->get_input('action') == 'delete')
{
	if(!$ougc_pages->get_category($mybb->get_input('cid', 1)))
	{
		$ougc_pages->redirect($lang->ougc_pages_error_invalidcategory, true);
	}

	if($mybb->request_method == 'post')
	{
		if(!verify_post_check($mybb->get_input('my_post_key'), true))
		{
			$ougc_pages->redirect($lang->invalid_post_verify_key2, true);
		}

		!$mybb->get_input('no') or $ougc_pages->redirect();

		$ougc_pages->delete_page_category($mybb->get_input('cid', 1));
		$ougc_pages->log_action();
		$ougc_pages->update_cache();
		$ougc_pages->redirect($lang->ougc_pages_success_delete);
	}

	$page->output_confirm_action($ougc_pages->build_url(array('action' => 'delete', 'cid' => $mybb->get_input('cid', 1))));
}
elseif($mybb->get_input('action') == 'update')
{
	if(!($category = $ougc_pages->get_category($mybb->get_input('cid', 1))))
	{
		$ougc_pages->redirect($lang->ougc_pages_error_invalidcategory, true);
	}

	if(!verify_post_check($mybb->get_input('my_post_key'), true))
	{
		$ougc_pages->redirect($lang->invalid_post_verify_key2, true);
	}

	$ougc_pages->update_category(array('visible' => (int)!(bool)$category['visible']), $mybb->get_input('cid', 1));
	$ougc_pages->log_action();
	$ougc_pages->update_cache();
	$ougc_pages->redirect();
}
else
{
	$page->add_breadcrumb_item($sub_tabs['ougc_pages_cat_view']['title'], $ougc_pages->build_url());
	$page->output_header($lang->ougc_pages_acp_nav);
	$page->output_nav_tabs($sub_tabs, 'ougc_pages_cat_view');

	$table = new Table;
	$table->construct_header($lang->ougc_pages_form_name, array('width' => '60%'));
	$table->construct_header($lang->ougc_pages_form_disporder, array('width' => '15%', 'class' => 'align_center'));
	$table->construct_header($lang->ougc_pages_form_visible, array('width' => '10%', 'class' => 'align_center'));
	$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

	$ougc_pages->build_limit();

	$query = $db->simple_select('ougc_pages_categories', 'COUNT(cid) AS categories');
	$count = (int)$db->fetch_field($query, 'categories');

	$multipage = $ougc_pages->build_multipage($count);
	
	if(!$count)
	{
		$table->construct_cell('<div align="center">'.$lang->ougc_pages_view_empty.'</div>', array('colspan' => 4));
		$table->construct_row();

		$table->output($sub_tabs['ougc_pages_cat_view']['title']);
	}
	else
	{
		if($mybb->request_method == 'post' && $mybb->get_input('action') == 'updatedisporder')
		{
			foreach($mybb->get_input('disporder', 2) as $cid => $disporder)
			{
				$ougc_pages->update_category(array('disporder' => $disporder), $cid);
			}
			$ougc_pages->update_cache();
			$ougc_pages->redirect();
		}

		echo $multipage;

		$query = $db->simple_select('ougc_pages_categories', '*', '', array('limit_start' => $ougc_pages->query_start, 'limit' => $ougc_pages->query_limit, 'order_by' => 'disporder'));

		$form = new Form($ougc_pages->build_url('action=updatedisporder'), 'post');

		while($category = $db->fetch_array($query))
		{
			$table->construct_cell('<b>'.htmlspecialchars_uni($category['name']).'</b><br /><i>'.$ougc_pages->get_category_link($category['cid']).'</i>');
			$table->construct_cell($form->generate_text_box('disporder['.$category['cid'].']', (int)$category['disporder'], array('style' => 'text-align: center; width: 30px;')), array('class' => 'align_center'));
			$table->construct_cell('<a href="'.$ougc_pages->build_url(array('action' => 'update', 'cid' => $category['cid'], 'my_post_key' => $mybb->post_code)).'"><img src="styles/default/images/icons/bullet_o'.(!$category['visible'] ? 'ff' : 'n').'.png" alt="" title="'.(!$category['visible'] ? $lang->ougc_pages_form_disabled : $lang->ougc_pages_form_visible).'" /></a>', array('class' => 'align_center'));

			$popup = new PopupMenu('category_'.$category['pid'], $lang->options);
			$popup->add_item($lang->ougc_pages_manage, $ougc_pages->build_url(array('manage' => 'pages', 'cid' => $category['cid'])));
			$popup->add_item($lang->edit, $ougc_pages->build_url(array('action' => 'edit', 'cid' => $category['cid'])));
			$popup->add_item($lang->delete, $ougc_pages->build_url(array('action' => 'delete', 'cid' => $category['cid'])));
			$table->construct_cell($popup->fetch(), array('class' => 'align_center'));

			$table->construct_row();
		}

		$table->output($sub_tabs['ougc_pages_cat_view']['title']);

		$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pages_button_disponder), $form->generate_reset_button($lang->reset)));
		$form->end();
	}

	$page->output_footer();
}