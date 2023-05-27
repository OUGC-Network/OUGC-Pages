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

function fetch_wol_activity_end(&$activityObjects): array
{
    if ($activityObjects['activity'] !== 'unknown' || \my_strpos($activityObjects['location'], 'pages.php') === false) {
        return $activityObjects;
    }

    $activityObjects['activity'] = 'ougc_pages';

    return $activityObjects;
}

function build_friendly_wol_location_end(&$locationObjects): array|bool
{
    if ($locationObjects['user_activity']['activity'] !== 'ougc_pages') {
        return $locationObjects;
    }

    global $lang;

    $pagesCache = \OUGCPages\Core\cacheGetPages();

    $categoriesCache = \OUGCPages\Core\cacheGetCategories();

    $location = \parse_url($locationObjects['user_activity']['location']);

    if (empty($location['query'])) {
        return $locationObjects;
    }

    $location['query'] = \html_entity_decode($location['query']);

    $location['query'] = \explode('&', (string)$location['query']);

    if (empty($location['query'])) {
        return $locationObjects;
    }

    $isCategory = $isPage = false;

    foreach ($location['query'] as $query) {
        $param = explode('=', $query);

        if ($param[0] === 'category') {
            $isCategory = true;
        } else if ($param[0] === 'page') {
            $isPage = true;
        }

        if ($isCategory || $isPage) {
            $url = $param[1];

            break;
        }
    }

    \OUGCPages\Core\loadlanguage();

    if ($isCategory) {
        $categoryData = \OUGCPages\Core\categoryGetByUrl($url);

        if (!empty($categoryData)) {
            $locationObjects['location_name'] = $lang->sprintf(
                $lang->ougc_pages_wol_category,
                \OUGCPages\Core\categoryGetLink($categoryData['cid']),
                \htmlspecialchars_uni($categoryData['name'])
            );
        }
    }

    if ($isPage) {
        $pageData = \OUGCPages\Core\pageGetByUrl($url);

        if (!$pageData['wol']) {
            $locationObjects['user_activity']['location'] = '/';

            return $locationObjects;
        }

        if (!empty($pageData)) {
            $locationObjects['location_name'] = $lang->sprintf(
                $lang->ougc_pages_wol_page,
                \OUGCPages\Core\pageGetLink($pageData['pid']),
                \htmlspecialchars_uni($pageData['name'])
            );
        }
    }

    return $locationObjects;
}

function usercp_menu10(): void
{
    if ((int)\OUGCPages\Core\getSetting('usercp_priority') !== 10) {
        return;
    }

    usercp_menu40(true);
}

function usercp_menu20(): void
{
    if ((int)\OUGCPages\Core\getSetting('usercp_priority') !== 20) {
        return;
    }

    usercp_menu40(true);
}

function usercp_menu30(): void
{
    if ((int)\OUGCPages\Core\getSetting('usercp_priority') !== 30) {
        return;
    }

    usercp_menu40(true);
}

function usercp_menu40(bool $forceRun = false): void // maybe later allow custom priorities
{
    if (!$forceRun && (int)\OUGCPages\Core\getSetting('usercp_priority') !== 40) {
        return;
    }

    global $cache, $db, $templates, $mybb, $usercpmenu, $collapsed, $theme, $collapsedimg, $collapsed, $collapse, $ucp_nav_home;

    $categoriesCache = \OUGCPages\Core\cacheGetCategories();

    if (empty($categoriesCache)) {
        return;
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
}

function global_start(): void
{
    if (defined('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_START')) {

        \OUGCPages\Core\initExecute(OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_START);
    }
}

function global_intermediate(): void
{
    if (defined('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_INTERMEDIATE')) {

        \OUGCPages\Core\initExecute(OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_INTERMEDIATE);
    }
}