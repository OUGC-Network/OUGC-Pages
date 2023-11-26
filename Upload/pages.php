<?php

/***************************************************************************
 *
 *    OUGC Pages plugin (/pages.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2014 - 2023 Omar Gonzalez
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

// Boring stuff..
use function OUGCPages\Core\initRun;
use function OUGCPages\Core\initShow;
use function OUGCPages\Core\loadLanguage;
use function OUGCPages\Core\runHooks;

define('IN_MYBB', true);
define('THIS_SCRIPT', 'pages.php');

$workingDirectory = dirname(__FILE__);

if (!$workingDirectory) {
    $workingDirectory = '.';
}

$shutdown_queries = $shutdown_functions = [];

require_once $workingDirectory . '/inc/init.php';

if (!function_exists('OUGCPages\\Core\\initRun')) {
    error_no_permission();
}

if (isset($templatelist)) {
    $templatelist .= ',';
} else {
    $templatelist = '';
}

$templatelist .= 'ougcpages_category_list_item, ougcpages_category_list, ougcpages_wrapper, usercp_nav_messenger, usercp_nav_messenger_tracking, usercp_nav_messenger_compose, usercp_nav_messenger_folder, usercp_nav_changename, usercp_nav_editsignature, usercp_nav_profile, usercp_nav_attachments, usercp_nav_misc, ougcpages_wrapper_ucp_nav_item, ougcpages_wrapper_ucp_nav, usercp_nav_home, usercp_nav, ougcpages_wrapper_ucp, ougcpages, ougcpages_category_list_empty';

initRun();

require_once $workingDirectory . '/global.php';

loadLanguage();

runHooks('Start');

global $lang;

if (defined('OUGC_PAGES_STATUS_CATEGORY_INVALID')) {
    error($lang->ougc_pages_error_category_invalid);
} elseif (defined('OUGC_PAGES_STATUS_PAGE_INVALID')) {
    error($lang->ougc_pages_error_page_invalid);
} elseif (defined('OUGC_PAGES_STATUS_CATEGORY_NO_PERMISSION') || defined('OUGC_PAGES_STATUS_PAGE_NO_PERMISSION')) {
    error_no_permission();
} elseif (defined('OUGC_PAGES_STATUS_IS_CATEGORY') || defined('OUGC_PAGES_STATUS_IS_PAGE')) {
    initShow();
}

runHooks('End');

error_no_permission();


/*--START SETTINGS--*/

$profilefields_display = '1,2,3'; // -1 for all

$groups_display = -1; // '1,2,3' for specific

$perpage = 10;

/*--END SETTINGS--*/

$category['visible'] or error($lang->ougc_pages_error_invalidçategory);

if ($category['groups'] != '') {
    $ougc_pages->init_session();

    is_member($category['groups']) or error_no_permission();
}

$templates->cache(
    'ougcpages_listuserfields, ougcpages_listuserfields_multipage, ougcpages_listuserfields_thead, ougcpages_listuserfields_user, ougcpages_listuserfields_user_field, ougcpages_listuserfields_empty, ougcpages_listuserfields_filter, ougcpages_listuserfields_filter_field, postbit_profilefield_multiselect_value, postbit_profilefield_multiselect, multipage_page_current, multipage_page, multipage_end, multipage_nextpage, multipage_jump_page, multipage'
);

$templates->cache['ougcpages_listuserfields'] or $templates->cache['ougcpages_listuserfields'] = <<<EOF
<table border="0" cellspacing="{\$theme['borderwidth']}" cellpadding="{\$theme['tablespace']}" class="tborder">
	<tr>
		<td class="thead" colspan="{\$colspan}"><strong>{\$page['name']}</strong></td>
	</tr>
	<tr>
		<td class="tcat">User</td>
		{\$thead_rows}
	</tr>
	{\$user_list}
</table>
<br />
{\$multipage}
{\$filter}
EOF;

$templates->cache['ougcpages_listuserfields_multipage'] or $templates->cache['ougcpages_listuserfields_multipage'] = <<<EOF
{\$multipage}
<br />
EOF;

$templates->cache['ougcpages_listuserfields_thead'] or $templates->cache['ougcpages_listuserfields_thead'] = <<<EOF
<td class="tcat">{\$name}</td>
EOF;

$templates->cache['ougcpages_listuserfields_user'] or $templates->cache['ougcpages_listuserfields_user'] = <<<EOF
<tr>
	<td class="{\$trow}" width="15%">
		{\$profilelink}
	</td>
	{\$user_fields}
</tr>
EOF;

$templates->cache['ougcpages_listuserfields_user_field'] or $templates->cache['ougcpages_listuserfields_user_field'] = <<<EOF
<td class="{\$trow}" width="{\$width}">
	{\$user_value}
</td>
EOF;

$templates->cache['ougcpages_listuserfields_empty'] or $templates->cache['ougcpages_listuserfields_empty'] = <<<EOF
<tr>
	<td class="{\$trow}" colspan="{\$colspan}">
		There are currently no users to display.
	</td>
</tr>
EOF;

$templates->cache['ougcpages_listuserfields_filter'] or $templates->cache['ougcpages_listuserfields_filter'] = <<<EOF
<form action="{\$page_url}" method="post">
	<table border="0" cellspacing="{\$theme['borderwidth']}" cellpadding="{\$theme['tablespace']}" class="tborder">
		<tr>
			<td class="thead" colspan="2"><strong>Filter & Sort</strong></td>
		</tr>
		<tr>
			<td class="trow1" width="25%"><strong>Sort direction:</strong></td>
			<td class="trow1" width="75%">
				<select name="orderdir">
					<option value="asc">ASC</option>
					<option value="desc" {\$selected['desc']}>DESC</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="trow1" width="25%"><strong>Users per page:</strong></td>
			<td class="trow1" width="75%">
				<input type="number" name="perpage" value="{\$perpage}" class="textbox" />
			</td>
		</tr>
		<tr>
			<td class="trow1" width="25%"><strong>Profile field:</strong></td>
			<td class="trow1" width="75%">
				<select name="fields[]" multiple="multiple">
					{\$fields}
				</select>
			</td>
		</tr>
	</table>
	<br />
	<div align="center">
		{\$gobutton}
	</div>
</form>
EOF;

$templates->cache['ougcpages_listuserfields_filter_field'] or $templates->cache['ougcpages_listuserfields_filter_field'] = <<<EOF
<option value="{\$fid}" {\$_selected}>{\$name}</option>
EOF;

global $PL;

$PL or require_once PLUGINLIBRARY;

$url_backup = $ougc_pages->build_url();

$ougc_pages->set_url($ougc_pages->get_page_link($page['pid']));

$build_url = [];

$selected = [
    'desc' => '',
];

if ($mybb->get_input('fields', MyBB::INPUT_ARRAY)) {
    $fids = array_filter(array_unique(array_map('intval', $mybb->get_input('fields', MyBB::INPUT_ARRAY))));

    foreach ($fids as $fid) {
        $build_url["fields[{$fid}]"] = $fid;

        $selected["fid{$fid}"] = ' selected="selected"';
    }

    $profilefields_display = implode(',', $fids);
}

$query_fields = ['uf.*'];

$pfcache = $cache->read('profilefields');

if (is_array($pfcache) && (int)$profilefields_display != -1) {
    $query_fields = [];

    // Then loop through the profile fields.
    foreach ($pfcache as $key => $profilefield) {
        $fid = (int)$profilefield['fid'];

        if (!isset($selected["fid{$fid}"])) {
            $selected["fid{$fid}"] = '';
        }

        if (!$fid || !is_member($profilefields_display, ['usergroup' => $fid])) {
            unset($pfcache[$key]);
        } else {
            $query_fields[] = "uf.fid{$fid}";
        }
    }
}

$multipage = $filter = '';

$colspan = 1 + count($pfcache);

$width = 100 / $colspan - 1;

global $gobutton;

if ($mybb->get_input('perpage', MyBB::INPUT_INT)) {
    $perpage = $build_url['perpage'] = $mybb->get_input('perpage', MyBB::INPUT_INT);
}

if (!$perpage) {
    $perpage = 10;
}

if (is_array($pfcache)) {
    if (!is_object($parser)) {
        require_once MYBB_ROOT . 'inc/class_parser.php';

        $parser = new postParser;
    }

    $where = ['1=1'];

    $query_fields = implode(',', $query_fields);

    if ((int)$groups_display != -1) {
        $gids = array_filter(array_unique(array_map('intval', explode(',', $groups_display))));

        $where2 = ["u.usergroup IN ('" . implode("','", $gids) . "')"];

        switch ($db->type) {
            case 'pgsql':
            case 'sqlite':

                foreach ($gids as $gid) {
                    $where2[] = "','||additionalgroups||',' LIKE '%,{$gid},%'";
                }

                break;
            default:

                foreach ($gids as $gid) {
                    $where2[] = "CONCAT(',',`additionalgroups`,',') LIKE '%,{$gid},%'";
                }

                break;
        }

        $where[] = '(' . implode(' OR ', $where2) . ')';

        unset($where2);
    }

    $where = implode(' AND ', $where);

    $query = $db->simple_select('users', 'COUNT(uid) AS total_users', $where);

    $total_users = $db->fetch_field($query, 'total_users');

    if ($total_users) {
        $_page = $mybb->get_input('_page', \MyBB::INPUT_INT);

        $pages = $total_users / $perpage;

        $pages = ceil($pages);

        if ($_page > $pages || $_page <= 0) {
            $_page = 1;
        }

        if ($_page) {
            $start = ($_page - 1) * $perpage;
        } else {
            $start = 0;

            $_page = 1;
        }

        $order_dir = 'asc';

        if ($mybb->get_input('orderdir') == 'desc') {
            $build_url['orderdir'] = 'desc';

            $order_dir = 'desc';

            $selected['desc'] = ' selected="selected"';
        }

        $multipage = (string)multipage(
            $total_users,
            $perpage,
            $_page,
            str_replace(
                'PALCEHOLDER',
                '{page}',
                $ougc_pages->build_url(
                    array_merge($build_url, ['_page' => 'PALCEHOLDER'])
                )
            )
        );

        $query = $db->simple_select(
            "users u LEFT JOIN {$db->table_prefix}userfields uf ON (u.uid=uf.ufid)",
            "u.*,{$query_fields}",
            $where,
            [
                'limit' => $perpage,
                'limit_start' => $start,
                'order_by' => 'u.username',
                'order_dir' => $order_dir,
            ]
        );

        $trow = alt_trow(true);

        while ($user = $db->fetch_array($query)) {
            $user['username'] = htmlspecialchars_uni($user['username']);

            $username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

            $profilelink = build_profile_link($username, $user['uid']);

            $user_fields = '';

            foreach ($pfcache as $field) {
                $fieldfid = "fid{$field['fid']}";

                $user_value = '';

                if (!empty($user[$fieldfid])) {
                    $user['fieldname'] = htmlspecialchars_uni($field['name']);

                    $thing = explode("\n", $field['type'], "2");
                    $type = trim($thing[0]);
                    $useropts = explode("\n", $user[$fieldfid]);

                    if (is_array($useropts) && ($type == "multiselect" || $type == "checkbox")) {
                        $fieldvalue_option = '';

                        foreach ($useropts as $val) {
                            if ($val) {
                                $fieldvalue_option = eval($templates->render('postbit_profilefield_multiselect_value'));
                            }
                        }

                        if ($fieldvalue_option) {
                            $user_value = eval($templates->render('postbit_profilefield_multiselect'));
                        }
                    } else {
                        $field_parser_options = [
                            "allow_html" => $field['allowhtml'],
                            "allow_mycode" => $field['allowmycode'],
                            "allow_smilies" => $field['allowsmilies'],
                            "allow_imgcode" => $field['allowimgcode'],
                            "allow_videocode" => $field['allowvideocode'],
                            #"nofollow_on" => 1,
                            "filter_badwords" => 1,
                        ];

                        if ($field['type'] == "textarea") {
                            $field_parser_options['me_username'] = $user['username'];
                        } else {
                            $field_parser_options['nl2br'] = 0;
                        }

                        if ($mybb->user['showimages'] != 1 && $mybb->user['uid'] != 0 || $mybb->settings['guestimages'] != 1 && $mybb->user['uid'] == 0) {
                            $field_parser_options['allow_imgcode'] = 0;
                        }

                        $user_value = $parser->parse_message($user[$fieldfid], $field_parser_options);
                    }
                }

                $user_fields .= eval($templates->render('ougcpages_listuserfields_user_field'));
            }

            $user_list .= eval($templates->render('ougcpages_listuserfields_user'));

            $trow = alt_trow();
        }
    }
}

if ($mybb->usergroup['cancp']) {
    $page_url = $ougc_pages->build_url($build_url);

    $fields = '';

    foreach ($cache->read('profilefields') as $profilefield) {
        $fid = (int)$profilefield['fid'];

        $name = htmlspecialchars_uni($profilefield['name']);

        $_selected = $selected["fid{$fid}"];

        $fields .= eval($templates->render('ougcpages_listuserfields_filter_field'));
    }

    $filter = eval($templates->render('ougcpages_listuserfields_filter'));
}

if ($multipage) {
    $multipage = eval($templates->render('ougcpages_listuserfields_multipage'));
}

if (!$user_list) {
    $user_list = eval($templates->render('ougcpages_listuserfields_empty'));
}

$thead_rows = '';

foreach ($pfcache as $field) {
    $name = htmlspecialchars_uni($field['name']);

    $thead_rows .= eval($templates->render('ougcpages_listuserfields_thead'));
}

$message = eval($templates->render('ougcpages_listuserfields'));

$ougc_pages->set_url($url_backup);

// Set-up page
$page['template'] = $message;
ougc_pages_show(true);