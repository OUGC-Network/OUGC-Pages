<?php

/***************************************************************************
 *
 *    OUGC Pages plugin (/inc/plugins/ougc_pages.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2014 - 2020 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Create additional pages directly from the ACP.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Run/Add Hooks
if (defined('IN_ADMINCP')) {
    $plugins->add_hook('admin_config_menu', 'ougc_pages_config_menu');
    $plugins->add_hook('admin_config_action_handler', 'ougc_pages_config_action_handler');
    $plugins->add_hook('admin_config_permissions', 'ougc_pages_config_permissions');
    $plugins->add_hook('admin_page_output_header', 'ougc_pages_output_header');

    $plugins->add_hook(
        'admin_config_settings_start',
        create_function('&$args', 'global $ougc_pages;	$ougc_pages->lang_load();')
    );
    $plugins->add_hook(
        'admin_config_settings_change',
        create_function('&$args', 'global $ougc_pages;	$ougc_pages->lang_load();')
    );
} else {
    $plugins->add_hook('fetch_wol_activity_end', 'ougc_pages_fetch_wol_activity_end');
    $plugins->add_hook('build_friendly_wol_location_end', 'ougc_pages_build_friendly_wol_location_end');
    $plugins->add_hook('usercp_menu', 'ougc_pages_usercp_menu', 40);
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_pages_info()
{
    global $lang, $ougc_pages;
    $ougc_pages->lang_load();

    return [
        'name' => 'OUGC Pages',
        'description' => $lang->setting_group_ougc_pages_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.21',
        'versioncode' => 1821,
        'compatibility' => '18*',
        'codename' => 'ougc_pages',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573',
        ],
    ];
}

// _activate() routine
function ougc_pages_activate()
{
    global $PL, $lang, $cache, $db, $ougc_pages;
    ougc_pages_pl_check();

    // Add settings group
    $PL->settings('ougc_pages', $lang->setting_group_ougc_pages, $lang->setting_group_ougc_pages_desc, [
        /*'portal'				=> array(
		   'title'			=> $lang->setting_ougc_pages_portal,
		   'description'	=> $lang->setting_ougc_pages_portal_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		),*/
        'seo' => [
            'title' => $lang->setting_ougc_pages_seo,
            'description' => $lang->setting_ougc_pages_seo_desc,
            'optionscode' => 'yesno',
            'value' => 0,
        ],
        'seo_scheme' => [
            'title' => $lang->setting_ougc_pages_seo_scheme,
            'description' => $lang->setting_ougc_pages_seo_scheme_desc,
            'optionscode' => 'text',
            'value' => 'page-{url}',
        ],
        'seo_scheme_categories' => [
            'title' => $lang->setting_ougc_pages_seo_scheme_categories,
            'description' => $lang->setting_ougc_pages_seo_scheme_categories_desc,
            'optionscode' => 'text',
            'value' => 'category-{url}',
        ],
        'perpage' => [
            'title' => $lang->setting_ougc_pages_perpage,
            'description' => $lang->setting_ougc_pages_perpage_desc,
            'optionscode' => 'numeric',
            'value' => 20,
        ],
    ]);

    // Add template group
    $PL->templates('ougcpages', '<lang:setting_group_ougc_pages>', [
        '' => '<html>
	<head>
		<title>{$title} - {$mybb->settings[\'bbname\']}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		{$content}
		{$footer}
	</body>
</html>',
        'category_list_item' => '<li><a href="{$page_link}" title="{$page[\'name\']}">{$page[\'name\']}</a></li>',
        'category_list_empty' => '<i>{$lang->ougc_pages_category_list_empty}</i>',
        'category_list' => '<ol>
	{$page_list}
</ol>',
        /*'navigation'	=> '{$content}{$navigation[\'previous\']}{$navigation[\'next\']}',*/
        /*'navigation_next' => '<div class="float_right">
	<strong><a href="{$next_link}">{$lang->ougc_pages_navigation_next}</a> &raquo;</strong>
</div>',*/
        /*'navigation_previous' => '<div class="float_left">
	<strong>&laquo; <a href="{$previous_link}">{$lang->ougc_pages_navigation_previous}</a></strong>
</div>',*/
        'wrapper' => '<table cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder tfixed">
<tr>
<td class="thead"><strong>{$title}</strong></td>
</tr>
<tr>
<td class="tcat smalltext"><strong>{$description}</strong></td>
</tr>
<tr>
<td class="trow1">
{$content}
</td>
</tr>
</table>
<br class="clear" />',
        'wrapper_ucp' => '<table width="100%" border="0" align="center">
	<tr>
		{$usercpnav}
		<td valign="top">
			{$content}
		</td>
	</tr>
</table>',
        'wrapper_ucp_nav' => '<tbody>
<tr>
	<td class="tcat tcat_menu tcat_collapse{$img}">
		<div class="expcolimage"><img src="{$theme[\'imgdir\']}/collapse{$img}.png" id="{$collapse_id}_img" class="expander" alt="{$expaltext}" title="{$expaltext}" /></div>
		<div><span class="smalltext"><strong>{$category[\'name\']}</strong></span></div>
	</td>
</tr>
</tbody>
<tbody style="{$_e}" id="{$collapse_id}_e">
	{$navs}
</tbody>',
        'wrapper_ucp_nav_item' => '<tr>
	<td class="trow1 smalltext"><a href="{$page_link}" class="usercp_nav_item usercp_nav_drafts">{$page[\'name\']}</a></td>
</tr>',
    ]);

    // Update administrator permissions
    change_admin_permission('config', 'ougc_pages');

    // Insert/update version into cache
    $plugins = $cache->read('ougc_plugins');
    if (!$plugins) {
        $plugins = [];
    }

    $info = ougc_pages_info();

    if (!isset($plugins['pages'])) {
        $plugins['pages'] = $info['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/
    $ougc_pages->_db_verify_tables();
    $ougc_pages->_db_verify_columns();
    $ougc_pages->_db_verify_indexes();

    $ougc_pages->_verify_stylesheet();

    if ($plugins['pages'] <= 1819) {
        $db->update_query('ougc_pages', ['visible' => 0], "groups=''");
        $db->update_query('ougc_pages_categories', ['visible' => ''], "groups=''");

        $db->update_query('ougc_pages', ['groups' => 0], "groups='-1'");
        $db->update_query('ougc_pages_categories', ['groups' => ''], "groups='-1'");
    }

    /*~*~* RUN UPDATES END *~*~*/

    $plugins['pages'] = $info['versioncode'];
    $cache->update('ougc_plugins', $plugins);
    //find_replace_templatesets('index', '#'.preg_quote('{$forums}').'#', "{\$hello}\n{\$forums}");
}

// _deactivate() routine
function ougc_pages_deactivate()
{
    ougc_pages_pl_check();

    // Update administrator permissions
    change_admin_permission('config', 'ougc_pages', 0);
}

// _install() routine
function ougc_pages_install()
{
    global $ougc_pages;

    // Create our table(s)
    $ougc_pages->_db_verify_tables();
    $ougc_pages->_db_verify_columns();
    $ougc_pages->_db_verify_indexes();
    /*$dir = dirname(__FILE__).'/ougc_pages/';

	if($directory = opendir($dir))
	{
		while(false !== ($page = readdir($directory)))
		{
			if($page != '.' && $page != '..')
			{
				if($contents = trim(file_get_contents($dir.$page)))
				{
				}
			}
		}

		closedir($directory);
	}*/
}

// _is_installed() routine
function ougc_pages_is_installed()
{
    global $db, $ougc_pages;

    foreach ($ougc_pages->_db_tables() as $name => $table) {
        $installed = $db->table_exists($name);
        break;
    }

    return $installed;
}

// _uninstall() routine
function ougc_pages_uninstall()
{
    global $db, $PL, $cache, $ougc_pages;
    ougc_pages_pl_check();

    // Drop DB entries
    foreach ($ougc_pages->_db_tables() as $name => $table) {
        $db->drop_table($name);
    }
    foreach ($ougc_pages->_db_columns() as $table => $columns) {
        foreach ($columns as $name => $definition) {
            !$db->field_exists($name, $table) || $db->drop_column($table, $name);
        }
    }

    $ougc_pages->_remove_from_stylesheet();

    $PL->cache_delete('ougc_pages');
    $PL->settings_delete('ougc_pages');
    $PL->templates_delete('ougcpages');

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['pages'])) {
        unset($plugins['pages']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $PL->cache_delete('ougc_plugins');
    }

    // Remove administrator permissions
    change_admin_permission('config', 'ougc_pages', -1);
}

// PluginLibrary dependency check & load
function ougc_pages_pl_check()
{
    global $lang, $ougc_pages;
    $ougc_pages->lang_load();
    $info = ougc_pages_info();

    if (!file_exists(PLUGINLIBRARY)) {
        flash_message(
            $lang->sprintf($lang->ougc_pages_pl_required, $info['pl']['url'], $info['pl']['version']),
            'error'
        );
        admin_redirect('index.php?module=config-plugins');
        exit;
    }

    global $PL;

    $PL or require_once PLUGINLIBRARY;

    if ($PL->version < $info['pl']['version']) {
        flash_message(
            $lang->sprintf($lang->ougc_pages_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version),
            'error'
        );
        admin_redirect('index.php?module=config-plugins');
        exit;
    }
}

// Add menu to ACP
function ougc_pages_config_menu(&$args)
{
    global $ougc_pages, $lang;
    $ougc_pages->lang_load();

    $args[] = [
        'id' => 'ougc_pages',
        'title' => $lang->ougc_pages_manage,
        'link' => 'index.php?module=config-ougc_pages',
    ];
}

// Add action handler to config module
function ougc_pages_config_action_handler(&$args)
{
    $args['ougc_pages'] = ['visible' => 'ougc_pages', 'file' => 'ougc_pages.php'];
}

// Insert plugin into the admin permissions page
function ougc_pages_config_permissions(&$args)
{
    global $ougc_pages, $lang;
    $ougc_pages->lang_load();

    $args['ougc_pages'] = $lang->ougc_pages_config_permissions;
}

// Show a flash message if plug-in requires updating
function ougc_pages_output_header()
{
    global $cache;

    $plugins = $cache->read('ougc_plugins');
    if (!$plugins) {
        $plugins = [];
    }

    $info = ougc_pages_info();

    if (!isset($plugins['pages'])) {
        $plugins['pages'] = $info['versioncode'];
    }

    if ($info['versioncode'] != $plugins['pages']) {
        global $page, $ougc_pages, $lang;
        $ougc_pages->lang_load();

        $page->extra_messages['ougc_pages'] = ['message' => $lang->ougc_pages_error_update, 'type' => 'error'];
    }
}

// Cache manager
function update_ougc_pages()
{
    global $ougc_pages;

    $ougc_pages->update_cache();
}

// WOL support
function ougc_pages_fetch_wol_activity_end(&$args)
{
    if ($args['activity'] != 'unknown') {
        return;
    }

    if (my_strpos($args['location'], 'pages.php') === false) {
        return;
    }

    $args['activity'] = 'ougc_pages';
}

function ougc_pages_build_friendly_wol_location_end(&$args)
{
    global $ougc_pages, $lang, $settings;
    $ougc_pages->lang_load();

    if ($args['user_activity']['activity'] == 'ougc_pages') {
        global $cache;

        $pagecache = $cache->read('ougc_pages');

        $location = parse_url($args['user_activity']['location']);
        $location['query'] = html_entity_decode($location['query']);
        $location['query'] = explode('&', (string)$location['query']);

        if (empty($location['query'])) {
            return;
        }

        foreach ($location['query'] as $query) {
            $param = explode('=', $query);

            $type = $param[0];

            if ($type == 'page' || $type == 'category') {
                $url = $param[1];
            }
        }

        if ($type == 'page' && !empty($pagecache['pages'][$url])) {
            $page = $ougc_pages->get_page($pagecache['pages'][$url]);

            if (!$page['wol']) {
                $args['user_activity']['location'] = '/';

                return;
            }

            $args['location_name'] = $lang->sprintf(
                $lang->ougc_pages_wol,
                $ougc_pages->get_page_link($pagecache['pages'][$url]),
                htmlspecialchars_uni($page['name'])
            );
        }

        if ($type == 'category') {
            $category = null;
            foreach ($pagecache['categories'] as $cid => $cat) {
                if ($cat['url'] == $url) {
                    $category = $cat;
                    break;
                }
            }

            if ($category !== null) {
                $args['location_name'] = $lang->sprintf(
                    $lang->ougc_pages_wol_cat,
                    $ougc_pages->get_category_link($cid),
                    htmlspecialchars_uni($category['name'])
                );
            }
        }
    }
}

// Show the page
function ougc_pages_show(/*$portal=false*/)
{
    global $db, $ougc_pages, $lang, $templates, $mybb, $footer, $headerinclude, $header, $theme, $page, $category;

    // Load lang
    $ougc_pages->lang_load();

    !$ougc_pages->invalid_page or error($lang->ougc_pages_error_invalidpage);

    !$ougc_pages->invalid_category or error($lang->ougc_pages_error_invalidçategory);

    !$ougc_pages->no_permission or error_no_permission();

    // Load custom page language file if exists
    $lang->load('ougc_pages_'.$category['cid'], false, true);
    $lang->load('ougc_pages_'.$page['pid'], false, true);

    $category['name'] = htmlspecialchars_uni($category['name']);

    if ($category['wrapucp']) {
        $lang->load('usercp');

        add_breadcrumb($lang->nav_usercp, "usercp.php");
    }

    if ($category['breadcrumb']) {
        add_breadcrumb($category['name'], $ougc_pages->get_category_link($category['cid']));
    }

    $gids = explode(',', $mybb->user['additionalgroups']);
    $gids[] = $mybb->user['usergroup'];
    $gids = array_filter(array_unique($gids));

    $sqlwhere = 'visible=\'1\' AND cid=\''.(int)$category['cid'].'\' AND (groups=\'\'';
    switch ($db->type) {
        case 'pgsql':
        case 'sqlite':
            foreach ($gids as $gid) {
                $gid = (int)$gid;
                $sqlwhere .= ' OR \',\'||groups||\',\' LIKE \'%,'.$gid.',%\'';
            }
            break;
        default:
            foreach ($gids as $gid) {
                $gid = (int)$gid;
                $sqlwhere .= ' OR CONCAT(\',\',groups,\',\') LIKE \'%,'.$gid.',%\'';
            }
            break;
    }
    $sqlwhere .= ')';

    /*$navigation = array('previous' => '', 'right' => 'next');*/

    if (!empty($page)) {
        $title = $page['name'] = htmlspecialchars_uni($page['name']);
        $description = $page['description'] = htmlspecialchars_uni($page['description']);

        add_breadcrumb($page['name'], $ougc_pages->get_page_link($page['pid']));

        /*if($category['navigation'])
		{
			$sqlwhere .= 'AND php!=\'1\' AND disporder';
			$where = '<\''.(int)$page['disporder'].'\'';
			$query = $db->simple_select('ougc_pages', 'pid', $sqlwhere.$where, array('order_by' => 'disporder, name', 'limit' => 1));
			$previous_page_id = (int)$db->fetch_field($query, 'pid');

			if($previous_page_id)
			{
				$previous_link = $ougc_pages->get_page_link($previous_page_id);
				eval('$navigation[\'previous\'] = "'.$templates->get('ougcpages_navigation_previous').'";');
			}

			$where = '>\''.(int)$page['disporder'].'\'';
			$query = $db->simple_select('ougc_pages', 'pid', $sqlwhere.$where, array('order_by' => 'disporder, name', 'limit' => 1));
			$next_page_id = (int)$db->fetch_field($query, 'pid');

			if($next_page_id)
			{
				$next_link = $ougc_pages->get_page_link($next_page_id);
				eval('$navigation[\'next\'] = "'.$templates->get('ougcpages_navigation_next').'";');
			}
		}*/

        $templates->cache['ougcpages_temporary_tmpl'] = $page['template'];

        #TODO: Add "Las updated on DATELINE..." to page

        eval('$content = "'.$templates->get('ougcpages_temporary_tmpl').'";');

        if ($page['wrapper']) {
            eval('$content = "'.$templates->get('ougcpages_wrapper').'";');
        }
    } else {
        $title = $category['name'] = htmlspecialchars_uni($category['name']);
        $description = $category['description'] = htmlspecialchars_uni($category['description']);

        $query = $db->simple_select('ougc_pages', '*', $sqlwhere, ['order_by' => 'disporder']);

        $page_list = '';
        while ($page = $db->fetch_array($query)) {
            $page['name'] = htmlspecialchars_uni($page['name']);
            $page_link = $ougc_pages->get_page_link($page['pid']);

            eval('$page_list .= "'.$templates->get('ougcpages_category_list_item').'";');
        }

        if (!$page_list) {
            eval('$content = "'.$templates->get('ougcpages_category_list_empty').'";');
        } else {
            eval('$content = "'.$templates->get('ougcpages_category_list').'";');
        }

        eval('$content = "'.$templates->get('ougcpages_wrapper').'";');
    }

    /*if($category['navigation'])
	{
		eval('$content = "'.$templates->get('ougcpages_navigation').'";');
	}*/

    /*if($portal)
	{
		return $content;
	}*/

    if ($category['wrapucp']) {
        global $usercpnav;

        require_once MYBB_ROOT.'inc/functions_user.php';

        usercp_menu();

        eval('$content = "'.$templates->get('ougcpages_wrapper_ucp').'";');
    }

    eval('$page = "'.$templates->get('ougcpages').'";');

    output_page($page);
    exit;
}

// Hijack the portal start
/*function ougc_pages_portal_start()
{
	global $mybb;

	if($mybb->get_input('page') && !$mybb->get_input('page', 1) || $mybb->get_input('category'))
	{
		$mybb->settings['portal_announcementsfid'] = false;
	}
}*/

// Hijack the portal announcements
/*function ougc_pages_portal_end()
{
	global $settings, $announcements;

	$settings['portal_announcementsfid'] or $announcements = ougc_pages_show(true);
}*/

// Execute PHP pages
function ougc_pages_execute()
{
    global $mybb, $lang, $db, $plugins, $cache, $parser, $settings;
    global $templates, $headerinclude, $header, $theme, $footer;
    global $templatelist, $session, $maintimer, $permissions;
    global $ougc_pages, $category, $page;

    #eval('? >'.$page['template'].'<?php');
    eval('?>'.$page['template']);
    exit;
}

// Initialize the plugin magic
function ougc_pages_init()
{
    global $mybb;
    global $templatelist, $ougc_pages;
    global $category, $page, $session;
    global $plugins;

    /*if(THIS_SCRIPT == 'portal.php' && !$mybb->settings['ougc_pages_portal'] || THIS_SCRIPT == 'pages.php' && $mybb->settings['ougc_pages_portal'])
	{
		return;
	}*/

    if (defined('IN_ADMINCP') || (defined(THIS_SCRIPT) && THIS_SCRIPT == 'pages.php')) {
        return;
    }

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= 'ougcpages, ougcpages_wrapper, ougcpages_navigation, ougcpages_category_list_item, ougcpages_category_list, ougcpages_navigation_previous, ougcpages_navigation_next';

    // should be fixed as well
    if (strpos(
            $mybb->settings['ougc_pages_seo_scheme'],
            '?'
        ) !== false && isset($mybb->input['page']) && empty($mybb->input['page']) && count((array)$mybb->input) > 1) {
        $pick = null;
        foreach ($mybb->input as $k => $v) {
            if ($k == 'page') {
                $pick = true;
                continue;
            }

            if ($pick === true) {
                $mybb->input['page'] = $k; // we assume second input to be the page
                break;
            }
        }
        unset($pick);
    }

    $mybb->cache->read('ougc_pages'); // TODO FIX THIS SHIT

    if ($is_page = !empty($mybb->input['page'])) {
        !empty(
        $mybb->cache->cache['ougc_pages']['pages'][$mybb->get_input(
            'page'
        )]
        ) or $ougc_pages->invalid_page = true;
    }

    if ($is_page) {
        if (!empty($mybb->cache->cache['ougc_pages']['pages'][$mybb->get_input('page')])) {
            if ($page = $ougc_pages->get_page_by_url($mybb->get_input('page'))) {
                #$templatelist .= ', ougcpages_page'.$page['pid'];

                if ($category = $ougc_pages->get_category($page['cid'])) {
                    $category['visible'] or $ougc_pages->invalid_category = true;
                    #$templatelist .= ', ougcpages_category'.$category['cid'];
                } else {
                    $ougc_pages->invalid_category = true;
                }
            } else {
                $ougc_pages->invalid_page = true;
            }
        } else {
            $ougc_pages->invalid_page = true;
        }
    } else {
        if (!empty($mybb->input['category'])) {
            // should be fixed as well
            if (strpos(
                    $mybb->settings['ougc_pages_seo_scheme_categories'],
                    '?'
                ) !== false && empty($mybb->input['category']) && count((array)$mybb->input) > 1) {
                $pick = null;
                foreach ($mybb->input as $k => $v) {
                    if ($k == 'category') {
                        $pick = true;
                        continue;
                    }

                    if ($pick === true) {
                        $mybb->input['category'] = $k; // we assume second input to be the category
                        break;
                    }
                }
                unset($pick);
            }

            if ($category = $ougc_pages->get_category_by_url($mybb->get_input('category'))) {
                $category['visible'] or $ougc_pages->invalid_category = true;
                #$templatelist .= ', ougcpages_category'.$category['cid'];
            } else {
                $ougc_pages->invalid_category = true;
            }
        }
    }

    if (!empty($category)) {
        // Save three queries if no permission check is necessary
        if ($category['groups'] != '') {
            $ougc_pages->init_session();

            is_member($category['groups']) or $ougc_pages->no_permission = true;
        }
    }

    if (!empty($page)) {
        if (!$page['wol'] && !defined('NO_ONLINE')) {
            define('NO_ONLINE', 1);
        }

        // Save three queries if no permission check is necessary
        if (!$ougc_pages->no_permission) {
            if ($page['groups'] != '') {
                $ougc_pages->init_session();

                is_member($page['groups']) or $ougc_pages->no_permission = true;
            }
        }

        if ($page['php'] && !$ougc_pages->no_permission) {
            if ($page['init']) {
                ougc_pages_execute();
            }

            $plugins->add_hook('global_end', 'ougc_pages_execute');
        }
    }
    /*if(THIS_SCRIPT == 'portal.php')
	{
		$plugins->add_hook('portal_start', 'ougc_pages_portal_start', 999999999);
		$plugins->add_hook('portal_end', 'ougc_pages_portal_end');
	}*/
}

function ougc_pages_usercp_menu()
{
    global $cache, $db, $ougc_pages, $templates, $mybb, $usercpmenu, $collapsed, $theme, $collapsedimg, $collapsed, $collapse;

    $pages_cache = $cache->read('ougc_pages');

    if (empty($pages_cache['categories'])) {
        return;
    }

    foreach ($pages_cache['categories'] as $cid => $category) {
        if (!$category['wrapucp']) {
            continue;
        }

        $gids = explode(',', $mybb->user['additionalgroups']);
        $gids[] = $mybb->user['usergroup'];
        $gids = array_filter(array_unique($gids));

        $sqlwhere = 'visible=\'1\' AND cid=\''.(int)$cid.'\' AND (groups=\'\'';
        switch ($db->type) {
            case 'pgsql':
            case 'sqlite':
                foreach ($gids as $gid) {
                    $gid = (int)$gid;
                    $sqlwhere .= ' OR \',\'||groups||\',\' LIKE \'%,'.$gid.',%\'';
                }
                break;
            default:
                foreach ($gids as $gid) {
                    $gid = (int)$gid;
                    $sqlwhere .= ' OR CONCAT(\',\',groups,\',\') LIKE \'%,'.$gid.',%\'';
                }
                break;
        }
        $sqlwhere .= ')';

        $query = $db->simple_select('ougc_pages', '*', $sqlwhere, ['order_by' => 'disporder']);

        $navs = '';

        while ($page = $db->fetch_array($query)) {
            $page['name'] = htmlspecialchars_uni($page['name']);
            $page_link = $ougc_pages->get_page_link($page['pid']);

            $navs .= eval($templates->render('ougcpages_wrapper_ucp_nav_item'));
        }

        if (!$navs) {
            continue;
        }

        $category['name'] = htmlspecialchars_uni($category['name']);

        $collapse_id = 'usercpougcpages'.$cid;

        $expaltext = (in_array($collapse_id, $collapse)) ? "[+]" : "[-]";

        if (!isset($collapsedimg[$collapse_id])) {
            $collapsedimg[$collapse_id] = '';
        }

        if (!isset($collapsed[$collapse_id.'_e'])) {
            $collapsed[$collapse_id.'_e'] = '';
        }

        $img = $collapsedimg[$collapse_id];
        $_e = $collapsed[$collapse_id.'_e'];

        $usercpmenu .= eval($templates->render('ougcpages_wrapper_ucp_nav'));
    }
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
if (!function_exists('control_object')) {
    function control_object(&$obj, $code)
    {
        static $cnt = 0;
        $newname = '_objcont_'.(++$cnt);
        $objserial = serialize($obj);
        $classname = get_class($obj);
        $checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
        $checkstr_len = strlen($checkstr);
        if (substr($objserial, 0, $checkstr_len) == $checkstr) {
            $vars = [];
            // grab resources/object etc, stripping scope info from keys
            foreach ((array)$obj as $k => $v) {
                if ($p = strrpos($k, "\0")) {
                    $k = substr($k, $p + 1);
                }
                $vars[$k] = $v;
            }
            if (!empty($vars)) {
                $code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
            }
            eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
            $obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
            if (!empty($vars)) {
                $obj->___setvars($vars);
            }
        }
        // else not a valid object or PHP serialize has changed
    }
}

if (!function_exists('ougc_getpreview')) {
    /**
     * Shorts a message to look like a preview.
     * Based off Zinga Burga's "Thread Tooltip Preview" plugin threadtooltip_getpreview() function.
     *
     * @param  string Message to short.
     * @param  int Maximum characters to show.
     * @param  bool Strip MyCode Quotes from message.
     * @param  bool Strip MyCode from message.
     * @return string Shortened message
     **/
    function ougc_getpreview($message, $maxlen = 100, $stripquotes = true, $stripmycode = true)
    {
        // Attempt to remove quotes, skip if going to strip MyCode
        if ($stripquotes && !$stripmycode) {
            $message = preg_replace([
                '#\[quote=([\"\']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"\']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi',
                '#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si',
                '#\[quote\]#si',
                '#\[\/quote\]#si',
            ], '', $message);
        }

        // Attempt to remove any MyCode
        if ($stripmycode) {
            global $parser;
            if (!is_object($parser)) {
                require_once MYBB_ROOT.'inc/class_parser.php';
                $parser = new postParser;
            }

            $message = $parser->parse_message($message, [
                'allow_html' => 0,
                'allow_mycode' => 1,
                'allow_smilies' => 0,
                'allow_imgcode' => 1,
                'filter_badwords' => 1,
                'nl2br' => 0,
            ]);

            // before stripping tags, try converting some into spaces
            $message = preg_replace([
                '~\<(?:img|hr).*?/\>~si',
                '~\<li\>(.*?)\</li\>~si',
            ], [' ', "\n* $1"], $message);

            $message = unhtmlentities(strip_tags($message));
        }

        // convert \xA0 to spaces (reverse &nbsp;)
        $message = trim(
            preg_replace(['~ {2,}~', "~\n{2,}~"],
                [' ', "\n"],
                strtr($message, ["\xA0" => ' ', "\r" => '', "\t" => ' ']))
        );

        // newline fix for browsers which don't support them
        $message = preg_replace("~ ?\n ?~", " \n", $message);

        // Shorten the message if too long
        if (my_strlen($message) > $maxlen) {
            $message = my_substr($message, 0, $maxlen - 1).'...';
        }

        return htmlspecialchars_uni($message);
    }
}

// Our awesome class
class OUGC_Pages
{
    // Define our ACP url
    public $url = 'index.php?module=config-plugins';

    // Maximum number of rows to return, for SQL queries and mulpage build
    public $query_limit = 10;

    // From what DB row start receiving what.eve.r, for SQL queries and mulpage build
    public $query_start = 0;

    // Init helper
    public $invalid_page = false;

    // Init helper
    public $invalid_category = false;

    // Init helper
    public $no_permission = false;

    // Build the class
    function __construct()
    {
        global $settings;

        $this->query_limit = isset($limit) ? (int)$limit : (int)$settings['ougc_pages_perpage'];
    }

    // Loads language strings
    function lang_load()
    {
        global $lang;

        isset($lang->setting_group_ougc_pages) or $lang->load((defined('IN_ADMINCP') ? 'config_' : '').'ougc_pages');
    }

    // Clean input
    function clean_ints($val, $implode = false)
    {
        if (!is_array($val)) {
            $val = (array)explode(',', $val);
        }

        foreach ($val as $k => &$v) {
            $v = (int)$v;
        }

        $val = array_filter($val);

        if ($implode) {
            $val = (string)implode(',', $val);
        }

        return $val;
    }

    // List of tables
    function _db_tables()
    {
        global $db;

        $collation = $db->build_create_table_collation();

        $tables = [
            'ougc_pages' => [
                'pid' => "int UNSIGNED NOT NULL AUTO_INCREMENT",
                'cid' => "int UNSIGNED NOT NULL DEFAULT '0'",
                'name' => "varchar(100) NOT NULL DEFAULT ''",
                'description' => "varchar(255) NOT NULL DEFAULT ''",
                'url' => "varchar(100) NOT NULL DEFAULT ''",
                'groups' => "text NOT NULL",
                'php' => "tinyint(1) NOT NULL DEFAULT '0'",
                'wol' => "tinyint(1) NOT NULL DEFAULT '1'",
                'disporder' => "tinyint(5) NOT NULL DEFAULT '0'",
                'visible' => "tinyint(1) NOT NULL DEFAULT '1'",
                'wrapper' => "tinyint(1) NOT NULL DEFAULT '1'",
                'init' => "tinyint(1) NOT NULL DEFAULT '1'",
                'template' => "MEDIUMTEXT NOT NULL",
                'dateline' => "int(10) NOT NULL DEFAULT '0'",
                'prymary_key' => "pid",
            ],
            'ougc_pages_categories' => [
                'cid' => "int UNSIGNED NOT NULL AUTO_INCREMENT",
                'name' => "varchar(100) NOT NULL DEFAULT ''",
                'description' => "varchar(255) NOT NULL DEFAULT ''",
                'url' => "varchar(100) NOT NULL DEFAULT ''",
                'groups' => "text NOT NULL",
                'disporder' => "tinyint(5) NOT NULL DEFAULT '0'",
                'visible' => "tinyint(1) NOT NULL DEFAULT '1'",
                'breadcrumb' => "tinyint(1) NOT NULL DEFAULT '1'",
                'navigation' => "tinyint(1) NOT NULL DEFAULT '1'",
                'wrapucp' => "tinyint(1) NOT NULL DEFAULT '0'",
                'prymary_key' => "cid",
            ],
        ];

        return $tables;
    }

    // List of columns
    function _db_columns()
    {
        $tables = [];

        return $tables;
    }

    // Verify DB indexes
    function _db_verify_indexes()
    {
        global $db;

        if (!$db->index_exists('ougc_pages', 'url')) {
            $db->write_query("ALTER TABLE ".TABLE_PREFIX."ougc_pages ADD UNIQUE KEY `url` (`url`)");
        }
        if (!$db->index_exists('ougc_pages_categories', 'url')) {
            $db->write_query("ALTER TABLE ".TABLE_PREFIX."ougc_pages_categories ADD UNIQUE KEY `url` (`url`)");
        }
    }

    // Verify DB tables
    function _db_verify_tables()
    {
        global $db;

        $collation = $db->build_create_table_collation();
        foreach ($this->_db_tables() as $table => $fields) {
            if ($db->table_exists($table)) {
                foreach ($fields as $field => $definition) {
                    if ($field == 'prymary_key') {
                        continue;
                    }

                    if ($db->field_exists($field, $table)) {
                        $db->modify_column($table, "`{$field}`", $definition);
                    } else {
                        $db->add_column($table, $field, $definition);
                    }
                }
            } else {
                $query = "CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."{$table}` (";
                foreach ($fields as $field => $definition) {
                    if ($field == 'prymary_key') {
                        $query .= "PRIMARY KEY (`{$definition}`)";
                    } else {
                        $query .= "`{$field}` {$definition},";
                    }
                }
                $query .= ") ENGINE=MyISAM{$collation};";
                $db->write_query($query);
            }
        }
    }

    // Verify DB columns
    function _db_verify_columns()
    {
        global $db;

        foreach ($this->_db_columns() as $table => $columns) {
            foreach ($columns as $field => $definition) {
                if ($db->field_exists($field, $table)) {
                    $db->modify_column($table, "`{$field}`", $definition);
                } else {
                    $db->add_column($table, $field, $definition);
                }
            }
        }
    }

    // Edit the master template to add our page to the core style sheet only
    function _verify_stylesheet($remove = false)
    {
        global $db;

        $query = $db->simple_select(
            "themestylesheets",
            "sid, attachedto",
            "name='usercp.css' AND tid= '1'"
        );

        $updated = false;

        while ($stylesheet = $db->fetch_array($query)) {
            if (!$remove && my_strpos($stylesheet['attachedto'], '|pages.php') === false) {
                $db->update_query("themestylesheets", [
                    "attachedto" => $stylesheet['attachedto'].'|pages.php',
                    "lastmodified" => TIME_NOW,
                ], "sid = '".(int)$stylesheet['sid']."'");
                $updated = true;
            }

            if ($remove && my_strpos($stylesheet['attachedto'], '|pages.php') !== false) {
                $db->update_query("themestylesheets", [
                    "attachedto" => str_replace('|pages.php', '', $stylesheet['attachedto']),
                    "lastmodified" => TIME_NOW,
                ], "sid = '".(int)$stylesheet['sid']."'");
                $updated = true;
            }
        }

        if ($updated) {
            $query = $db->simple_select("themes", "tid");
            require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
            while ($tid = $db->fetch_field($query, "tid")) {
                update_theme_stylesheet_list($tid);
            }
        }
    }

    function _remove_from_stylesheet()
    {
        $this->_verify_stylesheet(true);
    }

    // Update pages cache
    function update_cache()
    {
        global $db, $cache;

        $update = [];

        // Update categories
        $query = $db->simple_select('ougc_pages_categories', '*', 'visible=\'1\'', ['order_by' => 'disporder']);

        $update = ['categories' => [], 'pages' => []];

        while ($category = $db->fetch_array($query)) {
            $update['categories'][(int)$category['cid']] = [
                'name' => (string)$category['name'],
                'description' => (string)$category['description'],
                'url' => (string)$category['url'],
                'groups' => (string)$category['groups'],
                'breadcrumb' => (bool)$category['breadcrumb'],
                'wrapucp' => (bool)$category['wrapucp'],
                /*'navigation'	=> (bool)$category['navigation']*/
            ];
        }

        $db->free_result($query);

        // Update pages
        $query = $db->simple_select(
            'ougc_pages',
            'pid, url',
            'visible=\'1\''.(!empty($update['categories']) ? ' AND cid NOT IN (\''.implode(
                    '\', \'',
                    $update['categories']
                ).'\')' : ''),
            ['order_by' => 'disporder']
        );

        while ($page = $db->fetch_array($query)) {
            $update['pages'][(string)$page['url']] = (int)$page['pid'];
        }

        $db->free_result($query);

        $cache->update('ougc_pages', $update);
    }

    // Set url
    function set_url($url)
    {
        if (($url = trim($url))) {
            $this->url = $url;
        }
    }

    // Build an url parameter
    function build_url($urlappend = [], $fetch_input_url = false)
    {
        global $PL;

        if (!is_object($PL)) {
            $PL or require_once PLUGINLIBRARY;
        }

        if ($fetch_input_url === false) {
            if ($urlappend && !is_array($urlappend)) {
                $urlappend = explode('=', $urlappend);
                $urlappend = [$urlappend[0] => $urlappend[1]];
            }
        } else {
            $urlappend = $this->fetch_input_url($fetch_input_url);
        }

        return $PL->url_append($this->url, $urlappend, '&amp;', true);
    }

    // Cleans the unique URL
    // Thanks Google SEO!
    function clean_url($url)
    {
        global $settings;

        $url = ougc_getpreview($url);

        $pattern = preg_replace('/[\\\\\\^\\-\\[\\]\\/]/u', '\\\\\\0', '!"#$%&\'( )*+,-./:;<=>?@[\]^_`{|}~');

        $url = preg_replace('/^['.$pattern.']+|['.$pattern.']+$/u', '', $url);

        $url = preg_replace('/['.$pattern.']+/u', '-', $url);

        return my_strtolower($url);
    }

    // Get an unique URL for import process
    function get_import_url($name, $url = '')
    {
        global $db;

        !empty($url) or $url = $name;

        $url = $this->clean_url($url);

        $query = $db->simple_select(
            'ougc_pages',
            'pid',
            'url=\''.$db->escape_string($url).'\' AND pid!=\''.$this->pid.'\'',
            ['limit' => 1]
        );
        $duplicate_url = (int)$db->fetch_field($query, 'pid');

        if ($duplicate_url) {
            return $this->get_import_url(null, $url.'-'.$this->pid);
        }

        return $url;
    }

    // Create the user session
    function init_session()
    {
        global $session;

        if (!isset($session)) {
            require_once MYBB_ROOT.'inc/class_session.php';
            $session = new session;
            $session->init();
        }
    }

    // Redirect admin help function
    function redirect($message = '', $error = false)
    {
        if (defined('IN_ADMINCP')) {
            !$message or flash_message($message, ($error ? 'error' : 'success'));

            admin_redirect($this->build_url());
        } else {
            redirect($this->build_url(), $message);
        }

        exit;
    }

    // Log admin action
    function log_action()
    {
        if ($this->aid) {
            log_admin_action($this->aid);
        }
    }

    // Build a multipage.
    function build_multipage($count, $url = '', $check = false)
    {
        global $mybb, $multipage;

        if ($check) {
            $input = explode('=', $params);
            if (isset($mybb->input[$input[0]]) && $mybb->input[$input[0]] != $input[1]) {
                $mybb->input['page'] = 0;
            }
        }

        if ($mybb->get_input('page', 1) > 0) {
            $this->query_start = ($mybb->get_input('page', 1) - 1) * $this->query_limit;
            if ($mybb->get_input('page', 1) > ceil($count / $this->query_limit)) {
                $this->query_start = 0;
                $mybb->input['page'] = 1;
            }
        } else {
            $this->query_start = 0;
            $mybb->input['page'] = 1;
        }

        if (defined('IN_ADMINCP')) {
            return (string)draw_admin_pagination($mybb->get_input('page', 1), $this->query_limit, $count, $url);
        }

        return (string)multipage($count, $this->query_limit, $mybb->get_input('page', 1), $url);
    }

    // Get a category from the DB
    function get_category($cid, $url = false)
    {
        global $cache;

        if (!isset($this->cache['categories'][$cid])) {
            global $db;
            $this->cache['categories'][$cid] = false;

            $where = ($url === false ? 'cid=\''.(int)$cid.'\'' : 'url=\''.$db->escape_string($url).'\'');

            $query = $db->simple_select('ougc_pages_categories', '*', $where);
            $category = $db->fetch_array($query);

            if (isset($category['cid'])) {
                $this->cache['categories'][$cid] = $category;
            }
        }

        return $this->cache['categories'][$cid];
    }

    // Get PID by url input
    function get_category_by_url($url)
    {
        return $this->get_category(null, $url);
    }

    // Get the category link.
    function get_category_link($cid)
    {
        global $db, $settings;

        $cid = (int)$cid;

        $query = $db->simple_select('ougc_pages_categories', 'url', 'cid=\''.$cid.'\'');
        $url = $db->fetch_field($query, 'url');

        if ($settings['ougc_pages_seo'] && my_strpos(
                $settings['ougc_pages_seo_scheme_categories'],
                '{url}'
            ) !== false) {
            $url = str_replace('{url}', $url, $settings['ougc_pages_seo_scheme_categories']);
        } else {
            if ($settings['ougc_pages_portal']) {
                $url = 'portal.php?action=pages&category='.$url;
            } else {
                $url = 'pages.php?category='.$url;
            }
        }

        return $settings['bburl'].'/'.htmlspecialchars_uni($url);
    }

    // Build the page link.
    function build_category_link($name, $pid)
    {
        return '<a href="'.$this->get_category_link($pid).'">'.htmlspecialchars_uni($name).'</a>';
    }

    // Get a page from the DB
    function get_page($pid, $url = false)
    {
        if (!isset($this->cache['pages'][$pid])) {
            global $db;
            $this->cache['pages'][$pid] = false;

            $where = ($url === false ? 'pid=\''.(int)$pid.'\'' : 'url=\''.$db->escape_string($url).'\'');

            $query = $db->simple_select('ougc_pages', '*', $where);
            $page = $db->fetch_array($query);

            if (isset($page['pid'])) {
                $this->cache['pages'][$pid] = $page;
            }
        }

        return $this->cache['pages'][$pid];
    }

    // Get PID by url input
    function get_page_by_url($url)
    {
        return $this->get_page(null, $url);
    }

    // Get the page link.
    function get_page_link($pid)
    {
        global $db, $settings;

        $pid = (int)$pid;

        $query = $db->simple_select('ougc_pages', 'url', 'pid=\''.$pid.'\'');
        $url = $db->fetch_field($query, 'url');

        if ($settings['ougc_pages_seo'] && my_strpos($settings['ougc_pages_seo_scheme'], '{url}') !== false) {
            $url = str_replace('{url}', $url, $settings['ougc_pages_seo_scheme']);
        } else {
            /*if($settings['ougc_pages_portal'])
			{
				$url = 'portal.php?action=pages&page='.$url;
			}
			else
			{
				$url = 'pages.php?page='.$url;
			}*/
            $url = 'pages.php?page='.$url;
        }

        return $settings['bburl'].'/'.htmlspecialchars_uni($url);
    }

    // Build the category link.
    function build_page_link($name, $pid)
    {
        return '<a href="'.$this->get_page_link($pid).'">'.htmlspecialchars_uni($name).'</a>';
    }

    // Insert a new page to the DB
    function insert_page($data = [], $update = false, $pid = 0)
    {
        global $db;

        $insert_data = [];

        if (isset($data['cid'])) {
            $insert_data['cid'] = (int)$data['cid'];
        }

        if (isset($data['name'])) {
            $insert_data['name'] = $db->escape_string($data['name']);
        }

        if (isset($data['description'])) {
            $insert_data['description'] = $db->escape_string($data['description']);
        }

        if (isset($data['url'])) {
            $insert_data['url'] = $db->escape_string($data['url']);
        }

        if (isset($data['groups'])) {
            $insert_data['groups'] = $db->escape_string($data['groups']);
        } else {
            if (!$update) {
                $insert_data['groups'] = '';
            }
        }

        if (isset($data['php'])) {
            $insert_data['php'] = (int)$data['php'];
        }

        if (isset($data['wol'])) {
            $insert_data['wol'] = (int)$data['wol'];
        }

        if (isset($data['disporder'])) {
            $insert_data['disporder'] = (int)$data['disporder'];
        }

        if (isset($data['visible'])) {
            $insert_data['visible'] = (int)$data['visible'];
        }

        if (isset($data['wrapper'])) {
            $insert_data['wrapper'] = (int)$data['wrapper'];
        }

        if (isset($data['init'])) {
            $insert_data['init'] = (int)$data['init'];
        }

        if (isset($data['template'])) {
            $insert_data['template'] = $db->escape_string($data['template']);
        } else {
            if (!$update) {
                $insert_data['template'] = '';
            }
        }

        if (isset($data['dateline'])) {
            $insert_data['dateline'] = (int)$data['dateline'];
        } else {
            $insert_data['dateline'] = TIME_NOW;
        }

        if ($insert_data) {
            global $plugins;

            if ($update) {
                $this->pid = (int)$pid;
                $db->update_query('ougc_pages', $insert_data, 'pid=\''.$this->pid.'\'');
            } else {
                $this->pid = (int)$db->insert_query('ougc_pages', $insert_data);
            }

            $plugins->run_hooks('ouc_pages_'.($update ? 'update' : 'insert').'_page', $this);
        }
    }

    // Update espesific page.
    function update_page($data = [], $pid = 0)
    {
        $this->insert_page($data, true, $pid);
    }

    // Delete page from DB
    function delete_page($pid)
    {
        global $db;

        $this->pid = (int)$pid;

        $db->delete_query('ougc_pages', 'pid=\''.$this->pid.'\'');

        return $this->pid;
    }

    // Insert a new category to the DB
    function insert_category($data = [], $update = false, $cid = 0)
    {
        global $db;

        $insert_data = [];

        if (isset($data['name'])) {
            $insert_data['name'] = $db->escape_string($data['name']);
        }

        if (isset($data['description'])) {
            $insert_data['description'] = $db->escape_string($data['description']);
        }

        if (isset($data['url'])) {
            $insert_data['url'] = $db->escape_string($data['url']);
        }

        if (isset($data['groups'])) {
            $insert_data['groups'] = $db->escape_string($data['groups']);
        }

        if (isset($data['disporder'])) {
            $insert_data['disporder'] = (int)$data['disporder'];
        }

        if (isset($data['visible'])) {
            $insert_data['visible'] = (int)$data['visible'];
        }

        if (isset($data['breadcrumb'])) {
            $insert_data['breadcrumb'] = (int)$data['breadcrumb'];
        }

        if (isset($data['wrapucp'])) {
            $insert_data['wrapucp'] = (int)$data['wrapucp'];
        }

        if (isset($data['navigation'])) {
            /*$insert_data['navigation'] = (int)$data['navigation'];*/
        }

        if ($insert_data) {
            global $plugins;

            if ($update) {
                $this->cid = (int)$cid;
                $db->update_query('ougc_pages_categories', $insert_data, 'cid=\''.$this->cid.'\'');
            } else {
                $this->cid = (int)$db->insert_query('ougc_pages_categories', $insert_data);
            }

            $plugins->run_hooks('ouc_pages_'.($update ? 'update' : 'insert').'_category', $this);
        }
    }

    // Update espesific category
    function update_category($data = [], $cid = 0)
    {
        $this->insert_category($data, true, $cid);
    }

    // Delete category from DB
    function delete_category($cid)
    {
        global $db;

        $this->cid = (int)$cid;

        $db->delete_query('ougc_pages_categories', 'cid=\''.$this->cid.'\'');

        return $this->cid;
    }

    // Generate a category selection box.
    function generate_category_select($name, $selected = [], $options = [])
    {
        global $db;

        is_array($selected) or $selected = [$selected];

        $select = '<select name="'.$name.'"';

        if (isset($options['multiple'])) {
            $select .= ' multiple="multiple"';
        }

        if (isset($options['class'])) {
            $select .= ' class="'.$options['class'].'"';
        }

        if (isset($options['id'])) {
            $select .= ' id="'.$options['id'].'"';
        }

        if (isset($options['size'])) {
            $select .= ' size="'.$options['size'].'"';
        }

        $select .= '>';

        $query = $db->simple_select('ougc_pages_categories', 'cid, name', '', ['order_by' => 'disporder']);

        while ($category = $db->fetch_array($query)) {
            $s = '';
            if (in_array($category['cid'], $selected)) {
                $s = ' selected="selected"';
            }
            $select .= '<option value="'.$category['cid'].'"'.$s.'>'.htmlspecialchars_uni(
                    $category['name']
                ).'</option>';
        }

        $select .= '</select>';

        return $select;
    }
}

$GLOBALS['ougc_pages'] = new OUGC_Pages;

ougc_pages_init();
