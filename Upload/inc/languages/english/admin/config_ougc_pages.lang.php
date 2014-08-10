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
$l['setting_ougc_pages_perpage_desc'] = 'Maximum number of items to show per page in the ACP list.';

// ACP
$l['ougc_pages_manage'] = 'Manage Pages';
$l['ougc_pages_manage_desc'] = 'This section allows you to update your pages.';
$l['ougc_pages_tab_add'] = 'Add New Page';
$l['ougc_pages_tab_add_desc'] = 'Here you can add a new page.';
$l['ougc_pages_tab_import'] = 'Import Page';
$l['ougc_pages_tab_import_desc'] = 'Here you can import a new page.';
$l['ougc_pages_tab_edit'] = 'Edit Page';
$l['ougc_pages_tab_edit_desc'] = 'Here you can edit a page.';
$l['ougc_pages_tab_edit_cat'] = 'Edit Category';
$l['ougc_pages_tab_edit_cat_desc'] = 'Here you can edit a page category.';
$l['ougc_pages_tab_cat'] = 'Categories';
$l['ougc_pages_tab_cat_desc'] = 'This section allows you to update your page categories.';
$l['ougc_pages_tab_cat_add'] = 'Add New Category';
$l['ougc_pages_tab_cat_add_desc'] = 'Here you can add a new page category.';
$l['ougc_pages_view_empty'] = 'There are currently no pages to show.';
$l['ougc_pages_form_category'] = 'Page Category';
$l['ougc_pages_form_category_desc'] = 'Select the page category where this page goes in.';
$l['ougc_pages_form_name'] = 'Name';
$l['ougc_pages_form_name_desc'] = 'Insert the name for this category/page.';
$l['ougc_pages_form_description'] = 'Description';
$l['ougc_pages_form_description_desc'] = 'Insert the description for this category/page.';
$l['ougc_pages_form_url'] = 'Unique URL';
$l['ougc_pages_form_url_desc'] = 'Insert the unique URL identifier for this category/page.';
$l['ougc_pages_form_import'] = 'Local File';
$l['ougc_pages_form_import_desc'] = 'Select the XML page file to import from your computer.';
$l['ougc_pages_form_import_url'] = 'URL File';
$l['ougc_pages_form_import_url_desc'] = 'Insert the XML page URL to import.';
$l['ougc_pages_form_import_ignore_version'] = 'Ignore Version Compatibility';
$l['ougc_pages_form_import_ignore_version_desc'] = 'Should this page be imported regardless of the version of OUGC Pages / Page Manager it was created for?';
$l['ougc_pages_form_category'] = 'Category';
$l['ougc_pages_form_disabled'] = 'Disabled';
$l['ougc_pages_form_disabled_desc'] = 'Disabled';
$l['ougc_pages_form_visible'] = 'Active';
$l['ougc_pages_form_visible_desc'] = 'Whether if this category/page is active or disabled.';
$l['ougc_pages_form_breadcrumb'] = 'Show in Breadcrumb';
$l['ougc_pages_form_breadcrumb_desc'] = 'Whether if to show this category in the navigation breadcrumb.';
$l['ougc_pages_form_navigation'] = 'Show Navigation';
$l['ougc_pages_form_breadcrumb_desc'] = 'Whether if to show a previous/next pagination in this category in pages.';
$l['ougc_pages_form_php'] = 'PHP Code';
$l['ougc_pages_form_php_desc'] = 'Whether if process this page as plain PHP code or use the MyBB template system instead.';
$l['ougc_pages_form_wol'] = 'Show In Who Is On-line List';
$l['ougc_pages_form_wol_desc'] = 'Whether if show this page within the WOL list.';
$l['ougc_pages_form_template'] = 'Template';
$l['ougc_pages_form_template_desc'] = 'Insert the page template below.';
$l['ougc_pages_form_disporder'] = 'Display Order';
$l['ougc_pages_form_disporder_desc'] = 'Display order on which this category/page will be processed.';
$l['ougc_pages_form_groups'] = 'Allowed Groups';
$l['ougc_pages_form_groups_desc'] = 'Select the groups that can view this category/page.';
$l['ougc_pages_button_disponder'] = 'Update Display Orders';
$l['ougc_pageds_button_submit'] = 'Submit';
$l['ougc_pages_error_invalidcategory'] = 'The selected category is invalid.';
$l['ougc_pages_error_invalidurl'] = 'The selected file is invalid.';
$l['ougc_pages_error_invalidurl'] = 'The inserted unique URL is invalid.';
$l['ougc_pages_form_export'] = 'Export';

// ACP Module: Messages
$l['ougc_pages_error_update'] = 'OUGC Pages requires updating. Please deactivate and re-activate the plug-in to fix this issue.';
$l['ougc_pages_error_add'] = 'There was a error while creating a new category';
$l['ougc_pages_success_add'] = 'The category was created successfully.';
$l['ougc_pages_success_edit'] = 'The category/user was edited successfully.';
$l['ougc_pages_error_invalidname'] = 'The inserted name is invalid.';
$l['ougc_pages_error_invalidname'] = 'The inserted description is invalid.';
$l['ougc_pages_error_invalidurl'] = 'The inserted unique URL is invalid.';
$l['ougc_pages_error_invaliduploadurl'] = 'The inserted upload URL is invalid.';
$l['ougc_pages_error_invaliddesscription'] = 'The inserted description is too long.';
$l['ougc_pages_error_invalidimage'] = 'The inserted image is too long.';
$l['ougc_pages_error_invalidimport'] = 'The page content seems to be invalid.';
$l['ougc_pages_error_invalidversion'] = 'The page content seems to be from an invalid plug-in version.';
$l['ougc_pages_error_give'] = 'The selected user either already has or doesn\'t have the selected category.';
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