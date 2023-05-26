<?php

/***************************************************************************
 *
 *    OUGC Hide Administrator Location (/inc/plugins/ougcPages/forumHooks.php)
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

namespace OUGCPages\ForumHooks;

use const OUGCPages\Core\EXECUTION_HOOK_GLOBAL_END;
use const OUGCPages\Core\EXECUTION_HOOK_GLOBAL_START;

function fetch_wol_activity_end(&$activityObjects): array
{
    if ($activityObjects['activity'] != 'unknown') {
        return $activityObjects;
    }

    if (my_strpos($activityObjects['location'], 'pages.php') === false) {
        return $activityObjects;
    }

    $activityObjects['activity'] = 'ougc_pages';

    return $activityObjects;
}

function build_friendly_wol_location_end(&$locationObjetcs): array|bool
{
    global $ougc_pages, $lang, $settings;
    $ougc_pages->lang_load();

    if ($locationObjetcs['user_activity']['activity'] == 'ougc_pages') {
        global $cache;

        $pagecache = $cache->read('ougc_pages');

        $location = parse_url($locationObjetcs['user_activity']['location']);
        $location['query'] = html_entity_decode($location['query']);
        $location['query'] = explode('&', (string)$location['query']);

        if (empty($location['query'])) {
            return false;
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
                $locationObjetcs['user_activity']['location'] = '/';
                return $locationObjetcs;
            }

            $locationObjetcs['location_name'] = $lang->sprintf($lang->ougc_pages_wol, \OUGCPages\Core\pageGetLink($pagecache['pages'][$url]), htmlspecialchars_uni($page['name']));
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
                $locationObjetcs['location_name'] = $lang->sprintf($lang->ougc_pages_wol_cat, $ougc_pages->get_category_link($cid), htmlspecialchars_uni($category['name']));
            }
        }
    }

    return $locationObjetcs;
}

function usercp_menu60(): bool // maybe later allow custom priorities
{
    global $cache, $db, $ougc_pages, $templates, $mybb, $usercpmenu, $collapsed, $theme, $collapsedimg, $collapsed, $collapse;

    $categoriesCache = \OUGCPages\Core\cacheGetCategories();

    if (empty($categoriesCache)) {
        return false;
    }

    foreach ($categoriesCache as $cid => $categoryData) {
        if (!$categoryData['wrapucp'] || !\is_member($categoryData['allowedGroups'])) {
            continue;
        }

        $pageCache = \OUGCPages\Core\cacheGetPages();

        $pageList = '';

        foreach ($pageCache as $pid => $pageData) {
            if ($cid !== $pageData['cid'] || !\is_member($pageData['allowedGroups'])) {
                continue;
            }

            $pageName = \htmlspecialchars_uni($pageData['name']);

            $pageLink = \OUGCPages\Core\pageGetLink($pid);

            $pageList .= eval($templates->render('ougcpages_wrapper_ucp_nav_item'));
        }

        if (!$pageList) {
            continue;
        }

        $categoryName = \htmlspecialchars_uni($categoryData['name']);

        $collapseID = 'usercpougcpages' . $cid;

        $collapse || $collapse = [];

        $expanderText = (in_array($collapseID, $collapse)) ? '[+]' : '[-]';

        if (!isset($collapsedImage[$collapseID])) {
            $collapsedImage[$collapseID] = '';
        }

        if (!isset($collapsed[$collapseID . '_e'])) {
            $collapsed[$collapseID . '_e'] = '';
        }

        $collapseImage = $collapsedImage[$collapseID];

        $collapsedE = $collapsed[$collapseID . '_e'];

        $usercpmenu .= eval($templates->render('ougcpages_wrapper_ucp_nav'));
    }

    return true;
}

function global_start(): true
{
    if (
        \OUGCPages\Core\executeStatusGet() !== \OUGCPages\Core\EXECUTION_STATUS_DISABLED &&
        \OUGCPages\Core\executeHookGet() === \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_START
    ) {
        \OUGCPages\Core\initExecute(
            \OUGCPages\Core\executeGetCurrentPageID()
        );
    }

    return true;
}

function global_intermediate(): true
{
    if (\OUGCPages\Core\executeHookCheck(\OUGCPages\Core\EXECUTION_HOOK_GLOBAL_INTERMEDIATE)) {
        \OUGCPages\Core\initExecute(
            \OUGCPages\Core\executeGetCurrentPageID()
        );
    }

    return true;
}

function global_end(): true
{
    \OUGCPages\Core\cacheUpdate();

    if (
        \OUGCPages\Core\executeStatusGet() !== \OUGCPages\Core\EXECUTION_STATUS_DISABLED &&
        \OUGCPages\Core\executeHookGet() === \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_END
    ) {
        \OUGCPages\Core\initExecute(
            \OUGCPages\Core\executeGetCurrentPageID()
        );
    }

    return true;
}