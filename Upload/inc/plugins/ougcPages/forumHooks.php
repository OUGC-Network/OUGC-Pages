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

namespace ougc\Pages\ForumHooks;

use function explode;
use function html_entity_decode;
use function htmlspecialchars_uni;
use function is_member;
use function my_strpos;
use function ougc\Pages\Core\cacheGetCategories;
use function ougc\Pages\Core\cacheGetPages;
use function ougc\Pages\Core\categoryGetByUrl;
use function ougc\Pages\Core\categoryGetLink;
use function ougc\Pages\Core\getSetting;
use function ougc\Pages\Core\initExecute;
use function ougc\Pages\Core\loadlanguage;
use function ougc\Pages\Core\pageGetByUrl;
use function ougc\Pages\Core\pageGetLink;
use function ougc\Pages\Core\pageGetLinkBase;
use function ougc\Pages\Core\runHooks;
use function parse_url;
use function str_replace;

function fetch_wol_activity_end(&$activityObjects): array
{
    if ($activityObjects['activity'] !== 'unknown' || my_strpos($activityObjects['location'], 'pages.php') === false) {
        return $activityObjects;
    }

    $activityObjects['activity'] = 'ougc_pages';

    return $activityObjects;
}

function build_friendly_wol_location_end(&$locationObjects): array
{
    if ($locationObjects['user_activity']['activity'] !== 'ougc_pages') {
        return $locationObjects;
    }

    global $lang;

    $pagesCache = cacheGetPages();

    $categoriesCache = cacheGetCategories();

    $location = parse_url($locationObjects['user_activity']['location']);

    if (empty($location['query'])) {
        return $locationObjects;
    }

    $location['query'] = html_entity_decode($location['query']);

    $location['query'] = explode('&', (string)$location['query']);

    if (empty($location['query'])) {
        return $locationObjects;
    }

    $isCategory = $isPage = false;

    foreach ($location['query'] as $query) {
        $param = explode('=', $query);

        if ($param[0] === 'category') {
            $isCategory = true;
        } elseif ($param[0] === 'page') {
            $isPage = true;
        }

        if ($isCategory || $isPage) {
            $url = $param[1];

            break;
        }
    }

    loadlanguage();

    if ($isCategory) {
        $categoryData = categoryGetByUrl($url);

        if (!empty($categoryData)) {
            $locationObjects['location_name'] = $lang->sprintf(
                $lang->ougc_pages_wol_category,
                categoryGetLink($categoryData['cid']),
                htmlspecialchars_uni($categoryData['name'])
            );
        }
    }

    if ($isPage) {
        $pageData = pageGetByUrl($url);

        if (!$pageData['wol']) {
            $locationObjects['user_activity']['location'] = '/';

            return $locationObjects;
        }

        if (!empty($pageData)) {
            $locationObjects['location_name'] = $lang->sprintf(
                $lang->ougc_pages_wol_page,
                pageGetLink($pageData['pid']),
                htmlspecialchars_uni($pageData['name'])
            );
        }
    }

    return $locationObjects;
}

function usercp_menu10()
{
    if ((int)getSetting('usercp_priority') !== 10) {
        return;
    }

    usercp_menu40(true);
}

function usercp_menu20()
{
    if ((int)getSetting('usercp_priority') !== 20) {
        return;
    }

    usercp_menu40(true);
}

function usercp_menu30()
{
    if ((int)getSetting('usercp_priority') !== 30) {
        return;
    }

    usercp_menu40(true);
}

function usercp_menu40(bool $forceRun = false) // maybe later allow custom priorities
{
    if (!$forceRun && (int)getSetting('usercp_priority') !== 40) {
        return;
    }

    global $cache, $db, $templates, $mybb, $usercpmenu, $collapsed, $theme, $collapsedimg, $collapsed, $collapse, $ucp_nav_home;

    $categoriesCache = cacheGetCategories();

    if (empty($categoriesCache)) {
        return;
    }

    foreach ($categoriesCache as $cid => $categoryData) {
        if (!$categoryData['wrapucp'] || !is_member($categoryData['allowedGroups'])) {
            continue;
        }

        $pageCache = cacheGetPages();

        $pageList = '';

        foreach ($pageCache as $pid => $pageData) {
            if ((int)$cid !== (int)$pageData['cid'] || !is_member($pageData['allowedGroups'])) {
                continue;
            }

            $pageName = htmlspecialchars_uni($pageData['name']);

            $pageLink = pageGetLink($pid);

            $pageList .= eval($templates->render('ougcpages_wrapper_ucp_nav_item'));
        }

        if (!$pageList) {
            continue;
        }

        $categoryName = htmlspecialchars_uni($categoryData['name']);

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
}

function global_start()
{
    global $templatelist;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= 'ougcpages_menu_item, ougcpages_menu, ougcpages_menu_css';

    if (defined('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_START')) {
        global $templates;

        $templates->cache($templatelist);

        runHooks('ExecutionGlobalStart');

        initExecute(OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_START);
    }
}

function global_intermediate()
{
    if (defined('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_INTERMEDIATE')) {
        runHooks('ExecutionGlobalIntermediate');

        initExecute(OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_INTERMEDIATE);
    }
}

function oucPagesStart()
{
    if (defined('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_END')) {
        runHooks('ExecutionGlobalEnd');

        initExecute(OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_END);
    }
}

function pre_output_page(string &$pageContents): string
{
    if (my_strpos($pageContents, '<!--OUGC_PAGES_FOOTER-->') === false) {
        return $pageContents;
    }

    global $templates;

    $categoriesCache = cacheGetCategories();

    $pagesCache = cacheGetPages();

    $menuList = '';

    foreach ($categoriesCache as $categoryID => $categoryData) {
        if (!$categoryData['buildMenu']) {
            continue;
        }

        if ((int)$categoryData['allowedGroups'] !== -1 && !is_member($categoryData['allowedGroups'])) {
            continue;
        }

        $categoryName = htmlspecialchars_uni($categoryData['name']);

        $menuItems = '';

        foreach ($pagesCache as $pageID => $pageData) {
            if ((int)$categoryID !== (int)$pageData['cid']) {
                continue;
            }

            if (empty($pageData['menuItem']) || (int)$pageData['allowedGroups'] !== -1 && !is_member(
                    $pageData['allowedGroups']
                )) {
                continue;
            }

            $pageName = htmlspecialchars_uni($pageData['name']);

            $pageUrl = pageGetLinkBase($pageID);

            $menuItems .= eval($templates->render('ougcpages_menu_item'));
        }

        if (!$menuItems) {
            continue;
        }

        $menuList .= eval($templates->render('ougcpages_menu'));
    }

    if ($menuList) {
        $menuList .= eval($templates->render('ougcpages_menu_css'));
    }

    $pageContents = str_replace('<!--OUGC_PAGES_FOOTER-->', $menuList, $pageContents);

    return $pageContents;
}