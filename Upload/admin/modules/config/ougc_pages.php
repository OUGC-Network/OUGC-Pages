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

$page->add_breadcrumb_item($lang->ougc_pages_manage, $ougc_pages->build_url());

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
		if(!empty($admin_options['codepress']))
		{
			$page->extra_header .= '
<link href="./jscripts/codemirror/lib/codemirror.css" rel="stylesheet">
<link href="./jscripts/codemirror/theme/mybb.css" rel="stylesheet">
<script src="./jscripts/codemirror/lib/codemirror.js"></script>
<script src="./jscripts/codemirror/mode/xml/xml.js"></script>
<script src="./jscripts/codemirror/mode/javascript/javascript.js"></script>
<script src="./jscripts/codemirror/mode/css/css.js"></script>
<script src="./jscripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="./jscripts/codemirror/mode/php/php.js"></script>
<link href="./jscripts/codemirror/addon/dialog/dialog-mybb.css" rel="stylesheet" >
<script src="./jscripts/codemirror/addon/dialog/dialog.js"></script>
<script src="./jscripts/codemirror/addon/search/searchcursor.js"></script>
<script src="./jscripts/codemirror/addon/search/search.js"></script>
<script src="./jscripts/codemirror/addon/edit/matchbrackets.js"></script>
<script src="./jscripts/codemirror/mode/clike/clike.js"></script>
';
		}

		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));

		if(!($add = $mybb->get_input('action') == 'add'))
		{
			if(!($pages = $ougc_pages->get_page($mybb->get_input('pid', 1))))
			{
				$ougc_pages->redirect($lang->ougc_pages_error_invalidpage, true);
			}

			$page->add_breadcrumb_item(strip_tags($pages['name']));
		}

		foreach(array('category', 'cid', 'name', 'description', 'url', 'groups', 'php', 'wol', 'disporder', 'wrapper', 'init', 'template', 'visible') as $key)
		{
			if(!isset($mybb->input[$key]) && isset($pages[$key]))
			{
				if(isset($pages[$key]))
				{
					$mybb->input[$key] = $pages[$key];
				}
				else
				{
					$mybb->input[$key] = '';
				}
			}
			unset($key);
		}

		$group_checked = array('all' => '', 'custom' => '', 'none' => '');
		if($mybb->get_input('groups_type') == 'all' || $mybb->get_input('groups') == -1)
		{
			$mybb->input['groups_type'] = 'all';
			$mybb->input['groups'] = -1;
			$group_checked['all'] = 'checked="checked"';
		}
		elseif($mybb->get_input('groups_type') == 'none' || $mybb->get_input('groups') == '' && !$mybb->get_input('groups', 2))
		{
			$mybb->input['groups_type'] = 'none';
			$mybb->input['groups'] = '';
			$group_checked['none'] = 'checked="checked"';
		}
		else
		{
			$mybb->input['groups_type'] = 'custom';
			$mybb->input['groups'] = $ougc_pages->clean_ints($mybb->input['groups']);
			$group_checked['custom'] = 'checked="checked"';
		}

		$page->output_header($lang->ougc_pages_manage);
		$page->output_nav_tabs($sub_tabs, $add ? 'ougc_pages_add' : 'ougc_pages_edit');

		if($mybb->request_method == 'post')
		{
			$errors = array();
			if(!$mybb->get_input('name') || isset($mybb->input['name']{100}))
			{
				$errors[] = $lang->ougc_pages_error_invalidname;
			}

			if(!$mybb->get_input('description') || isset($mybb->input['description']{255}))
			{
				$errors[] = $lang->ougc_pages_error_invaliddescription;
			}

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

				if(!$ougc_pages->get_category($mybb->get_input('category', 1)))
				{
					$mybb->input['category'] = (int)$category['cid'];
				}

				$ougc_pages->{$method}(array(
					'cid'			=> $mybb->get_input('category', 1),
					'name'			=> $mybb->get_input('name'),
					'description'	=> $mybb->get_input('description'),
					'url'			=> $url,
					'groups'		=> $ougc_pages->clean_ints($mybb->input['groups'], true),
					'php'			=> $mybb->get_input('php', 1),
					'wol'			=> $mybb->get_input('wol', 1),
					'disporder'		=> $mybb->get_input('disporder', 1),
					'visible'		=> $mybb->get_input('visible', 1),
					'wrapper'			=> $mybb->get_input('wrapper', 1),
					'init'			=> $mybb->get_input('init', 1),
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
		$form_container = new FormContainer($sub_tabs['ougc_pages_'.($add ? 'add' : 'edit')]['description']);

		$form_container->output_row($lang->ougc_pages_form_category, $lang->ougc_pages_form_category_desc, $ougc_pages->generate_category_select('category', $mybb->get_input('cid', 1)));
		$form_container->output_row($lang->ougc_pages_form_name.' <em>*</em>', $lang->ougc_pages_form_name_desc, $form->generate_text_box('name', $mybb->get_input('name')));
		$form_container->output_row($lang->ougc_pages_form_description.' <em>*</em>', $lang->ougc_pages_form_description_desc, $form->generate_text_box('description', $mybb->get_input('description')));
		$form_container->output_row($lang->ougc_pages_form_url.' <em>*</em>', $lang->ougc_pages_form_url_desc, $form->generate_text_box('url', $mybb->get_input('url')));

		ougc_print_selection_javascript();

		$groups_select = "
		<dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
			<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"all\" {$group_checked['all']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_groups}</strong></label></dt>
			<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"custom\" {$group_checked['custom']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_groups}</strong></label></dt>
			<dd style=\"margin-top: 4px;\" id=\"groups_forums_groups_custom\" class=\"groups_forums_groups\">
				<table cellpadding=\"4\">
					<tr>
						<td valign=\"top\"><small>{$lang->groups_colon}</small></td>
						<td>".$form->generate_group_select('groups[]', $mybb->get_input('groups', 2), array('multiple' => true, 'size' => 5))."</td>
					</tr>
				</table>
			</dd>
			<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"none\" {$group_checked['none']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->none}</strong></label></dt>
		</dl>
		<script type=\"text/javascript\">
			checkAction('groups');
		</script>";

		$form_container->output_row($lang->ougc_pages_form_groups, $lang->ougc_pages_form_groups_desc, $groups_select, '', array(), array('id' => 'row_groups'));

		$form_container->output_row($lang->ougc_pages_form_php, $lang->ougc_pages_form_php_desc, $form->generate_yes_no_radio('php', $mybb->get_input('php', 1)));
		$form_container->output_row($lang->ougc_pages_form_wol, $lang->ougc_pages_form_wol_desc, $form->generate_yes_no_radio('wol', $mybb->get_input('wol', 1)));
		$form_container->output_row($lang->ougc_pages_form_visible, $lang->ougc_pages_form_visible_desc, $form->generate_yes_no_radio('visible', $mybb->get_input('visible', 1)));
		$form_container->output_row($lang->ougc_pages_form_wrapper, $lang->ougc_pages_form_wrapper_desc, $form->generate_yes_no_radio('wrapper', $mybb->get_input('wrapper', 1)));
		$form_container->output_row($lang->ougc_pages_form_init, $lang->ougc_pages_form_init_desc, $form->generate_yes_no_radio('init', $mybb->get_input('init', 1)));
		$form_container->output_row($lang->ougc_pages_form_disporder, $lang->ougc_pages_form_disporder_desc, $form->generate_text_box('disporder', $mybb->get_input('disporder', 1), array('style' => 'text-align: center; width: 30px;" maxlength="5')));
		$form_container->output_row($lang->ougc_pages_form_template, $lang->ougc_pages_form_template_desc, $form->generate_text_area('template', $mybb->get_input('template'), array('rows' => 5, 'id' => 'template', 'class' => '', 'style' => 'width: 100%; height: 500px;')));

		$form_container->end();
		$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pages_button_submit), $form->generate_reset_button($lang->reset)));
		$form->end();

		if(!empty($admin_options['codepress']))
		{
			echo '<script type="text/javascript">
				var editor = CodeMirror.fromTextArea(document.getElementById("template"), {
					lineNumbers: true,
					lineWrapping: true,
					tabMode: "indent",
					theme: "mybb",
					mode: "application/x-httpd-php",
					matchBrackets: true,
				});
			</script>';
		}

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
			'description'	=> $page['description'],
			'url'			=> $page['url'],
			'groups'		=> $page['groups'],
			'php'			=> $page['php'],
			'wol'			=> $page['wol'],
			'visible'		=> $page['visible'],
			'wrapper'		=> $page['wrapper'],
			'init'			=> $page['init'],
			'template'		=> $page['template'],
			'versioncode'	=> $info['versioncode']
		), 'OUGC_Pages_'.$page['name'].'_'.$info['versioncode']);
	}
	elseif($mybb->get_input('action') == 'import')
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));
		$page->output_header($lang->ougc_pages_manage);
		$page->output_nav_tabs($sub_tabs, 'ougc_pages_import');

		if($mybb->request_method == 'post')
		{
			$errors = array();
			if($mybb->get_input('file_url'))
			{
				if(!($contents = fetch_remote_file($mybb->get_input('file_url'))))
				{
					$errors[] = $lang->error_local_file;
				}
			}
			elseif($_FILES['local_file'] && $_FILES['local_file']['error'] != 4)
			{
				// Find out if there was an error with the uploaded file
				if($_FILES['local_file']['error'] != 0)
				{
					$errors[] = $lang->error_uploadfailed.$lang->error_uploadfailed_detail;
					switch($_FILES['local_file']['error'])
					{
						case 1: // UPLOAD_ERR_INI_SIZE
							$errors[] = $lang->error_uploadfailed_php1;
							break;
						case 2: // UPLOAD_ERR_FORM_SIZE
							$errors[] = $lang->error_uploadfailed_php2;
							break;
						case 3: // UPLOAD_ERR_PARTIAL
							$errors[] = $lang->error_uploadfailed_php3;
							break;
						case 6: // UPLOAD_ERR_NO_TMP_DIR
							$errors[] = $lang->error_uploadfailed_php6;
							break;
						case 7: // UPLOAD_ERR_CANT_WRITE
							$errors[] = $lang->error_uploadfailed_php7;
							break;
						default:
							$errors[] = $lang->sprintf($lang->error_uploadfailed_phpx, $_FILES['local_file']['error']);
							break;
					}
				}

				if(empty($errors))
				{
					// Was the temporary file found?
					if(!is_uploaded_file($_FILES['local_file']['tmp_name']))
					{
						$errors[] = $lang->error_uploadfailed_lost;
					}

					// Get the contents
					if(!($contents = trim(file_get_contents($_FILES['local_file']['tmp_name']))))
					{
						$errors[] = $lang->error_uploadfailed_nocontents;
					}
	
					// Delete the temporary file if possible
					unlink($_FILES['local_file']['tmp_name']);
				}
			}
			else
			{
				// UPLOAD_ERR_NO_FILE
				$errors[] = $lang->error_uploadfailed_php4;
			}

			if(empty($errors))
			{
				$xml_import = array();

				$valid_version = true;
				if($xml_import = $PL->xml_import($contents))
				{
					if(!$mybb->get_input('ignore_version', 1))
					{
						$info = ougc_pages_info();
						$valid_version = (float)$xml_import['versioncode'] == $info['versioncode'];
					}

					if(!$valid_version)
					{
						unset($xml_import);
					}
				}
				else
				{
					// try to get this as a "Page Manager" page
					require_once MYBB_ROOT.'inc/class_xml.php';
					$xml_parser = new XMLParser($contents);
					$tree = $xml_parser->get_tree();

					if(!$mybb->get_input('ignore_version', 1))
					{
						$valid_version = (float)$tree['pagemanager']['attributes']['version'] == '1.5.2';
					}

					if(!$valid_version)
					{
						unset($tree);
					}

					if(isset($tree['pagemanager']) && $valid_version &&
					is_array($tree['pagemanager']) && is_array($tree['pagemanager']['page']))
					{
						#if(!($template = base64_decode($tree['pagemanager']['page']['template']['value'], isset($tree['pagemanager']['page']['checksum']['value']) ? false : true)))
						if(!($template = base64_decode($tree['pagemanager']['page']['template']['value'])))
						{
							$template = $tree['pagemanager']['page']['template']['value'];
						}

						$xml_import = array(
							'name'			=> (string)$tree['pagemanager']['page']['name']['value'],
							'description'	=> '',
							'url'			=> (string)$tree['pagemanager']['page']['url']['value'],
							'groups'		=> -1,
							'php'			=> !isset($tree['pagemanager']['page']['framework']['value']) || !(int)$tree['pagemanager']['page']['framework']['value'] ? 1 : 0,
							'wol'			=> !isset($tree['pagemanager']['page']['online']['value']) || (int)$tree['pagemanager']['page']['online']['value'] ? 1 : 0,
							'visible'		=> (int)$tree['pagemanager']['page']['enabled']['value'],
							'wrapper'		=> 0, // It runs without a wrapper, similar here
							'init'			=> 0, // It runs at misc_stats, similar here
							'template'		=> (string)trim($template)
						);
					}
				}

				if(!$xml_import)
				{
					$errors[] = !$valid_version ? $lang->ougc_pages_error_invalidversion : $lang->ougc_pages_error_invalidimport;
				}

				if(empty($errors))
				{
					$query = $db->simple_select('ougc_pages', 'MAX(disporder) as max_disporder', 'cid=\''.(int)$category['cid'].'\'');
					$max_disporder = (int)$db->fetch_field($query, 'max_disporder');

					$ougc_pages->insert_page(array(
						'cid'			=> $category['cid'],
						'name'			=> $xml_import['name'],
						'description'	=> $xml_import['description'],
						'groups'		=> $xml_import['groups'],
						'php'			=> $xml_import['php'],
						'url'			=> uniqid(),
						'wol'			=> $xml_import['wol'],
						'disporder'		=> ++$max_disporder,
						'visible'		=> $xml_import['visible'],
						'wrapper'		=> $xml_import['wrapper'],
						'init'			=> $xml_import['init'],
						'template'		=> $xml_import['template']
					));

					$ougc_pages->update_page(array(
						'url'			=> $ougc_pages->get_import_url($xml_import['name'], $xml_import['url'])
					), $ougc_pages->pid);

					$ougc_pages->update_cache();
					$ougc_pages->log_action();
					$ougc_pages->redirect($lang->ougc_pages_success_add);
				}
			}

			empty($errors) or $page->output_inline_error($errors);
		}

		$form = new Form($ougc_pages->build_url('action=import'), 'post', '', true);
		$form_container = new FormContainer($sub_tabs['ougc_pages_import']['description']);

		$form_container->output_row($lang->ougc_pages_form_import, $lang->ougc_pages_form_import_desc, $form->generate_file_upload_box('local_file', $mybb->get_input('local_file')));
		$form_container->output_row($lang->ougc_pages_form_import_url, $lang->ougc_pages_form_import_url_desc, $form->generate_text_box('file_url', $mybb->get_input('file_url')));
		$form_container->output_row($lang->ougc_pages_form_import_ignore_version, $lang->ougc_pages_form_import_ignore_version_desc, $form->generate_yes_no_radio('ignore_version', $mybb->get_input('ignore_version', 1)));

		$form_container->end();
		$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pages_button_submit), $form->generate_reset_button($lang->reset)));
		$form->end();
		$page->output_footer();
	}
	else
	{
		$page->add_breadcrumb_item(htmlspecialchars_uni($category['name']));
		$page->output_header($lang->ougc_pages_manage);
		$page->output_nav_tabs($sub_tabs, 'ougc_pages_view');

		$table = new Table;
		$table->construct_header($lang->ougc_pages_form_name, array('width' => '60%'));
		$table->construct_header($lang->ougc_pages_form_disporder, array('width' => '15%', 'class' => 'align_center'));
		$table->construct_header($lang->ougc_pages_form_visible, array('width' => '10%', 'class' => 'align_center'));
		$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

		$ougc_pages->build_limit();

		$query = $db->simple_select('ougc_pages', 'COUNT(cid) AS pages', 'cid=\''.(int)$category['cid'].'\'');
		$count = (int)$db->fetch_field($query, 'pages');

		$multipage = $ougc_pages->build_multipage($count, $ougc_pages->build_url());
	
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

			$query = $db->simple_select('ougc_pages', '*', 'cid=\''.(int)$category['cid'].'\'', array('limit_start' => $ougc_pages->query_start, 'limit' => $ougc_pages->query_limit, 'order_by' => 'disporder'));

			echo $multipage;

			$form = new Form($ougc_pages->build_url('action=updatedisporder'), 'post');

			while($pages = $db->fetch_array($query))
			{
				$edit_link = $ougc_pages->build_url(array('action' => 'edit', 'pid' => $pages['pid']));
				$pages['name'] = htmlspecialchars_uni($pages['name']);

				$pages['visible'] or $pages['name'] = '<em>'.$pages['name'].'</em>';

				$table->construct_cell('<a href="'.$edit_link.'"><strong>'.$pages['name'].'</strong></a> <span style="font-size: 90%">('.$ougc_pages->build_page_link($lang->ougc_pages_view_page, $pages['pid']).')</span>');
				$table->construct_cell($form->generate_text_box('disporder['.$pages['pid'].']', (int)$pages['disporder'], array('style' => 'text-align: center; width: 30px;')), array('class' => 'align_center'));
				$table->construct_cell('<a href="'.$ougc_pages->build_url(array('action' => 'update', 'pid' => $pages['pid'], 'my_post_key' => $mybb->post_code)).'"><img src="styles/default/images/icons/bullet_o'.(!$pages['visible'] ? 'ff' : 'n').'.png" alt="" title="'.(!$pages['visible'] ? $lang->ougc_pages_form_disabled : $lang->ougc_pages_form_visible).'" /></a>', array('class' => 'align_center'));

				$popup = new PopupMenu('page_'.$pages['pid'], $lang->options);
				$popup->add_item($lang->edit, $edit_link);
				$popup->add_item($lang->ougc_pages_form_export, $ougc_pages->build_url(array('action' => 'export', 'pid' => $pages['pid'], 'my_post_key' => $mybb->post_code)));
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
	if(!($add = $mybb->get_input('action') == 'add'))
	{
		if(!($category = $ougc_pages->get_category($mybb->get_input('cid', 1))))
		{
			$ougc_pages->redirect($lang->ougc_pages_error_invalidcategory, true);
		}

		$page->add_breadcrumb_item(strip_tags($category['name']));
	}

	foreach(array('name', 'description', 'groups', 'url', 'disporder'/*, 'breadcrumb', 'navigation'*/, 'visible') as $key)
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

	$group_checked = array('all' => '', 'custom' => '', 'none' => '');
	if($mybb->get_input('groups_type') == 'all' || $mybb->get_input('groups') == -1)
	{
		$mybb->input['groups_type'] = 'all';
		$mybb->input['groups'] = -1;
		$group_checked['all'] = 'checked="checked"';
	}
	elseif($mybb->get_input('groups_type') == 'none' || $mybb->get_input('groups') == '' && !$mybb->get_input('groups', 2))
	{
		$mybb->input['groups_type'] = 'none';
		$mybb->input['groups'] = '';
		$group_checked['none'] = 'checked="checked"';
	}
	else
	{
		$mybb->input['groups_type'] = 'custom';
		$mybb->input['groups'] = $ougc_pages->clean_ints($mybb->input['groups']);
		$group_checked['custom'] = 'checked="checked"';
	}

	$page->output_header($lang->ougc_pages_manage);
	$page->output_nav_tabs($sub_tabs, $add ? 'ougc_pages_cat_add' : 'ougc_pages_edit');

	if($mybb->request_method == 'post')
	{
		$errors = array();
		if(!$mybb->get_input('name') || isset($mybb->input['name']{100}))
		{
			$errors[] = $lang->ougc_pages_error_invalidname;
		}

		if(!$mybb->get_input('description') || isset($mybb->input['description']{255}))
		{
			$errors[] = $lang->ougc_pages_error_invaliddescription;
		}

		if(!$mybb->get_input('url'))
		{
			$errors[] = $lang->ougc_pages_error_invalidurl;
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
				'description'	=> $mybb->get_input('description'),
				'url'			=> $url,
				'groups'		=> $ougc_pages->clean_ints($mybb->input['groups'], true),
				'disporder'		=> $mybb->get_input('disporder', 1),
				'visible'		=> $mybb->get_input('visible', 1),
				/*'breadcrumb'	=> $mybb->get_input('breadcrumb', 1),
				'navigation'	=> $mybb->get_input('navigation', 1)*/
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
	$form_container->output_row($lang->ougc_pages_form_description.' <em>*</em>', $lang->ougc_pages_form_description_desc, $form->generate_text_box('description', $mybb->get_input('description')));
	$form_container->output_row($lang->ougc_pages_form_url.' <em>*</em>', $lang->ougc_pages_form_url_desc, $form->generate_text_box('url', $mybb->get_input('url')));

	ougc_print_selection_javascript();

	$groups_select = "
	<dl style=\"margin-top: 0; margin-bottom: 0; width: 100%\">
		<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"all\" {$group_checked['all']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->all_groups}</strong></label></dt>
		<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"custom\" {$group_checked['custom']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->select_groups}</strong></label></dt>
		<dd style=\"margin-top: 4px;\" id=\"groups_forums_groups_custom\" class=\"groups_forums_groups\">
			<table cellpadding=\"4\">
				<tr>
					<td valign=\"top\"><small>{$lang->groups_colon}</small></td>
					<td>".$form->generate_group_select('groups[]', $mybb->get_input('groups', 2), array('multiple' => true, 'size' => 5))."</td>
				</tr>
			</table>
		</dd>
		<dt><label style=\"display: block;\"><input type=\"radio\" name=\"groups_type\" value=\"none\" {$group_checked['none']} class=\"groups_forums_groups_check\" onclick=\"checkAction('groups');\" style=\"vertical-align: middle;\" /> <strong>{$lang->none}</strong></label></dt>
	</dl>
	<script type=\"text/javascript\">
		checkAction('groups');
	</script>";

	$form_container->output_row($lang->ougc_pages_form_groups, $lang->ougc_pages_form_groups_desc, $groups_select, '', array(), array('id' => 'row_groups'));

	$form_container->output_row($lang->ougc_pages_form_visible, $lang->ougc_pages_form_visible_desc, $form->generate_yes_no_radio('visible', $mybb->get_input('visible', 1)));
	#$form_container->output_row($lang->ougc_pages_form_breadcrumb, $lang->ougc_pages_form_breadcrumb_desc, $form->generate_yes_no_radio('breadcrumb', $mybb->get_input('breadcrumb', 1)));
	#$form_container->output_row($lang->ougc_pages_form_navigation, $lang->ougc_pages_form_navigation_desc, $form->generate_yes_no_radio('navigation', $mybb->get_input('navigation', 1)));
	$form_container->output_row($lang->ougc_pages_form_disporder, $lang->ougc_pages_form_disporder_desc, $form->generate_text_box('disporder', $mybb->get_input('disporder', 1), array('style' => 'text-align: center; width: 30px;" maxlength="5')));

	$form_container->end();
	$form->output_submit_wrapper(array($form->generate_submit_button($lang->ougc_pages_button_submit), $form->generate_reset_button($lang->reset)));
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

		$ougc_pages->delete_category($mybb->get_input('cid', 1));
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
	$page->output_header($lang->ougc_pages_manage);
	$page->output_nav_tabs($sub_tabs, 'ougc_pages_cat_view');

	$table = new Table;
	$table->construct_header($lang->ougc_pages_form_name, array('width' => '60%'));
	$table->construct_header($lang->ougc_pages_form_disporder, array('width' => '15%', 'class' => 'align_center'));
	$table->construct_header($lang->ougc_pages_form_visible, array('width' => '10%', 'class' => 'align_center'));
	$table->construct_header($lang->options, array('width' => '15%', 'class' => 'align_center'));

	$ougc_pages->build_limit();

	$query = $db->simple_select('ougc_pages_categories', 'COUNT(cid) AS categories');
	$count = (int)$db->fetch_field($query, 'categories');

	$multipage = $ougc_pages->build_multipage($count, $ougc_pages->build_url());
	
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
			$manage_link = $ougc_pages->build_url(array('manage' => 'pages', 'cid' => $category['cid']));
			$category['name'] = htmlspecialchars_uni($category['name']);

			$category['visible'] or $category['name'] = '<em>'.$category['name'].'</em>';

			$table->construct_cell('<a href="'.$manage_link.'"><strong>'.$category['name'].'</strong></a> <span style="font-size: 90%">('.$ougc_pages->build_category_link($lang->ougc_pages_view_page, $category['cid']).')</span>');
			$table->construct_cell($form->generate_text_box('disporder['.$category['cid'].']', (int)$category['disporder'], array('style' => 'text-align: center; width: 30px;')), array('class' => 'align_center'));
			$table->construct_cell('<a href="'.$ougc_pages->build_url(array('action' => 'update', 'cid' => $category['cid'], 'my_post_key' => $mybb->post_code)).'"><img src="styles/default/images/icons/bullet_o'.(!$category['visible'] ? 'ff' : 'n').'.png" alt="" title="'.(!$category['visible'] ? $lang->ougc_pages_form_disabled : $lang->ougc_pages_form_visible).'" /></a>', array('class' => 'align_center'));

			$popup = new PopupMenu('category_'.$category['cid'], $lang->options);
			$popup->add_item($lang->ougc_pages_manage, $manage_link);
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