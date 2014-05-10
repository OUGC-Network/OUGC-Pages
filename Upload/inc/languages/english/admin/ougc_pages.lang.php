<?php

/***************************************************************************
 *
 *	OUGC Pages plugin (/inc/languages/english/admin/ougc_pages.lang.php)
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

// Plugin API
$l['setting_group_ougc_pages'] = 'OUGC Pages';
$l['setting_group_ougc_pages_desc'] = 'Create additional pages directly from the ACP.';

// Settings
$l['setting_ougc_pages_seo'] = 'Use SEO URLs';
$l['setting_ougc_pages_seo_desc'] = 'Do you want to make use of the SEO URLs for categories and pages.';
$l['setting_ougc_pages_seo_none'] = 'None';
$l['setting_ougc_pages_seo_mybb'] = 'MyBB Style';
$l['setting_ougc_pages_seo_google'] = 'Google Like';
$l['setting_ougc_pages_perpage'] = 'Items Per Page';
$l['setting_ougc_pages_perpage_desc'] = 'Maximun number of items to show per page in the ACP list.';

// ACP
$l['ougc_pages_manage'] = 'Manage Pages';
$l['ougc_pages_manage_desc'] = 'This section allows you to update your pages.';
$l['ougc_pages_tab_add'] = 'Add New Page';
$l['ougc_pages_tab_add_desc'] = 'Here you can add a new page.';
$l['ougc_pages_tab_import'] = 'Import New Page';
$l['ougc_pages_tab_import_desc'] = 'Here you can import a new page.';
$l['ougc_pages_tab_edit'] = 'Edit Page';
$l['ougc_pages_tab_edit_desc'] = 'Here you can edit a page.';
$l['ougc_pages_tab_edit_cat'] = 'Edit Category';
$l['ougc_pages_tab_edit_cat_desc'] = 'Here you can edit a page category.';
$l['ougc_pages_tab_cat'] = 'Manage Categories';
$l['ougc_pages_tab_cat_desc'] = 'This section allows you to update your page categories.';
$l['ougc_pages_tab_cat_add'] = 'Add New Category';
$l['ougc_pages_tab_cat_add_desc'] = 'Here you can add a new page category.';
$l['ougc_pages_view_empty'] = 'There are currently no pages to show.';
$l['ougc_pages_form_name'] = 'Name';
$l['ougc_pages_form_name_desc'] = 'Insert the name for this category.';
$l['ougc_pages_form_url'] = 'Unique URL';
$l['ougc_pages_form_url_desc'] = 'Insert the unique URL identifier for this category.';
$l['ougc_pages_form_import'] = 'Local File';
$l['ougc_pages_form_import_desc'] = 'Select the XML page file to import from your computer.';
$l['ougc_pages_form_import_url'] = 'URL File';
$l['ougc_pages_form_import_url_desc'] = 'Insert the XML page URL to import.';
$l['ougc_pages_form_category'] = 'Category';
$l['ougc_pages_form_disabled'] = 'Disabled';
$l['ougc_pages_form_disabled_desc'] = 'Disabled';
$l['ougc_pages_form_visible'] = 'Active';
$l['ougc_pages_form_visible_desc'] = 'Whether if this category is active or disabled.';
$l['ougc_pages_form_breadcrumb'] = 'Show in Breadcrumb';
$l['ougc_pages_form_breadcrumb_desc'] = 'Whether if to show this category in the navigation breadcrumb.';
$l['ougc_pages_form_php'] = 'PHP Code';
$l['ougc_pages_form_php_desc'] = 'Whether if eval this page as plain PHP code or use the MyBB template system instead.';
$l['ougc_pages_form_wol'] = 'Show In Who Is Online List';
$l['ougc_pages_form_wol_desc'] = 'Whether if show this page within the WOL list.';
$l['ougc_pages_form_template'] = 'Template';
$l['ougc_pages_form_template_desc'] = 'Insert the page template below.';
$l['ougc_pages_form_disporder'] = 'Display Order';
$l['ougc_pages_form_disporder_desc'] = 'Display order on which this category will be proccessed.';
$l['ougc_pages_form_groups'] = 'Groups';
$l['ougc_pages_form_groups_desc'] = 'Select the groups that can view this category. (none for all)';
$l['ougc_pages_button_disponder'] = 'Update Display Orders';
$l['ougc_pageds_button_submit'] = 'Submit';
$l['ougc_pages_error_invalidcategory'] = 'The selected category is invalid.';
$l['ougc_pages_error_invalidurl'] = 'The selected file is invalid.';
$l['ougc_pages_error_invalidurl'] = 'The inserted unique URL is invalid.';
$l['ougc_pages_form_export'] = 'Export';

// ACP Module: Messages
$l['ougc_pages_error_add'] = 'There was a error while creating a new category';
$l['ougc_pages_success_add'] = 'The category was created successfully.';
$l['ougc_pages_success_edit'] = 'The category/user was edited successfully.';
$l['ougc_pages_error_invalidaward'] = 'The selected category is invalid.';
$l['ougc_pages_error_invaliduser'] = 'The selected user is invalid.';
$l['ougc_pages_error_invalidname'] = 'The inserted name is too short.';
$l['ougc_pages_error_invaliddesscription'] = 'The inserted description is too long.';
$l['ougc_pages_error_invalidimage'] = 'The inserted image is too long.';
$l['ougc_pages_error_give'] = 'The selected user either already has or doesn\'t have the selected category.';
$l['ougc_pages_error_giveperm'] = 'You don\'t have permission to edit the selected user.';
$l['ougc_pages_success_give'] = 'User awarded successfully.';
$l['ougc_pages_error_revoke'] = 'The selected user doesn\'t exist or it doesn\'t have this category.';
$l['ougc_pages_success_revoke'] = 'Awards was revoked from the selected user successfully.';
$l['ougc_pages_success_delete'] = 'The category was deleted successfully.';
$l['ougc_pages_success_cache'] = 'The cache was rebuild successfully.';

// Admin Permissions
$l['ougc_pages_config_permissions'] = 'Can manage pages?';

// PluginLibrary
$l['ougc_pages_pl_required'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
$l['ougc_pages_pl_old'] = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';


































	$l['pages_info_name']='Page Manager';
	$l['pages_info_description']='Allows you to manage additional pages.';
	$l['pages_main_title']='Manage Pages';
	$l['pages_main_description']='This section allows you to edit and delete additional pages.';
	$l['pages_main_table']='Additional Pages';
	$l['pages_main_table_enabled']='Enabled';
	$l['pages_main_table_disabled']='Disabled';
	$l['pages_main_table_id']='ID';
	$l['pages_main_table_framework']='MyBB Template?';
	$l['pages_main_table_online']='Show online?';
	$l['pages_main_table_modified']='Modified';
	$l['pages_main_table_dateline']='{1}, {2}';
	$l['pages_main_table_no_pages']='No additional pages exist at this time.';
	$l['pages_main_control_edit']='Edit Page';
	$l['pages_main_control_export']='Export Page';
	$l['pages_main_control_enable']='Enable Page';
	$l['pages_main_control_disable']='Disable Page';
	$l['pages_main_control_delete']='Delete Page';
	$l['pages_main_control_delete_question']='Are you sure you wish to delete this page?';
	$l['pages_add_title']='Add New Page';
	$l['pages_add_description']='Here you can create a new additional page.';
	$l['pages_add_form']='Add New Page';
	$l['pages_add_success']='The page has been created successfully.';
	$l['pages_import_title']='Import Page';
	$l['pages_import_description']='Here you can import new pages.';
	$l['pages_import_form']='Import Page';
	$l['pages_import_form_file']='Local file';
	$l['pages_import_form_file_description']='Select a file to import.';
	$l['pages_import_form_name']='Name';
	$l['pages_import_form_name_description']='Type a name for the imported page. If left blank, the name in the page file will be used.';
	$l['pages_import_form_manual']='Manual import?';
	$l['pages_import_form_manual_description']='By default pages are installed directly. Here you can activate manual import.';
	$l['pages_import_form_version']='Ignore version?';
	$l['pages_import_form_version_description']='Should this page be installed regardless of the version of Page Manager it was created for?';
	$l['pages_import_form_action']='Import Page';
	$l['pages_import_success']='The selected page has been imported successfully. Please note that imported pages are disabled by default.';
	$l['pages_import_error_no_file']='No file was uploaded.';
	$l['pages_import_error_php']='PHP returned error code {1} while uploading file. Please contact your server administrator with this error.';
	$l['pages_import_error_lost']='The file could not be found on the server.';
	$l['pages_import_error_no_contents']='Could not find an importable page with the file you uploaded. Please check the file is the correct and is not corrupt.';
	$l['pages_import_error_version']='This page has been written for another version of Page Manager. Please use option "Ignore version" to ignore this error.';
	$l['pages_edit_title']='Edit Page';
	$l['pages_edit_description']='Here you can edit an additional page.';
	$l['pages_edit_form']='Edit Page';
	$l['pages_edit_form_name']='Name';
	$l['pages_edit_form_name_description']='The name of your additional page.';
	$l['pages_edit_form_url']='URI parameter';
	$l['pages_edit_form_url_description']='This parameter will be used to point to your page. <strong>It is recommended to use alphanumeric characters only.</strong>';
	$l['pages_edit_form_framework']='Use MyBB Template?';
	$l['pages_edit_form_framework_description']='Set this option to yes, if you want to include MyBB header and footer automatically. <strong>This will disable the possibility to use PHP in page content!</strong>';
	$l['pages_edit_form_template']='Page content';
	$l['pages_edit_form_template_description']='Type your page content here.';
	$l['pages_edit_form_online']='Show in "Who is Online"?';
	$l['pages_edit_form_online_description']='Set this option to no, if you want to hide this page in "Who is Online"';
	$l['pages_edit_form_enable']='Page enabled?';
	$l['pages_edit_form_enable_description']='If you wish to disable this page, set this option to no.';
	$l['pages_edit_form_continue']='Save and Continue Editing';
	$l['pages_edit_form_close']='Save and Return to Listing';
	$l['pages_edit_success']='The selected page has been updated successfully.';
	$l['pages_edit_success_nothing']='The selected page has been updated successfully. But nothing changed.';
	$l['pages_edit_error_name']='Name can not be empty';
	$l['pages_edit_error_url']='URI parameter can not be empty';
	$l['pages_edit_error_url_duplicate']='URI parameter is already taken';
	$l['pages_edit_error_template']='Page content can not be empty';
	$l['pages_enable_success']='The selected page has been enabled successfully.';
	$l['pages_disable_success']='The selected page has been disabled successfully.';
	$l['pages_delete_success']='The selected page has been deleted successfully.';
	$l['pages_invalid_page']='The specified page does not exist.';
	$l['pages_install_error']='Your installation of Page Manager is out of date or corrupt. If possible export all pages and install the plugin again.';
	$l['pages_can_manage_pages']='Can manage additional pages?';
	$l['pages_online']='Viewing <a href="misc.php?page={1}">{2}</a>';