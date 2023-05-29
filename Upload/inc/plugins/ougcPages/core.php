<?php

/***************************************************************************
 *
 *    OUGC Pages (/inc/plugins/ougcPages/core.php)
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

namespace OUGCPages\Core;

const URL = 'index.php?module=config-ougc_pages';

const QUERY_LIMIT = 10;

const QUERY_START = 0;

const EXECUTION_HOOK_INIT = 1;

const EXECUTION_HOOK_GLOBAL_START = 2;

const EXECUTION_HOOK_GLOBAL_INTERMEDIATE = 3;

const EXECUTION_HOOK_GLOBAL_END = 4;

function loadLanguage(): true
{
    global $lang;

    if (!isset($lang->setting_group_ougc_pages)) {
        if (defined('IN_ADMINCP')) {
            $lang->load('config_ougc_pages');
        } else {
            $lang->load('ougc_pages');
        }
    }

    return true;
}

function pluginLibraryRequirements(): object
{
    return (object)\OUGCPages\Admin\pluginInfo()['pl'];
}

function loadPluginLibrary(bool $doCheck = true): bool
{
    global $PL, $lang;

    loadLanguage();

    if ($fileExists = file_exists(PLUGINLIBRARY)) {
        ($PL instanceof PluginLibrary) or require_once PLUGINLIBRARY;
    }

    if (!$doCheck) {
        return false;
    }

    if (!$fileExists || $PL->version < pluginLibraryRequirements()->version) {
        \flash_message(
            $lang->sprintf(
                $lang->ougc_pages_pl_required,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        \admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function addHooks(string $namespace): true
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            $plugins->add_hook($hookName, $callable, $priority);
        }
    }

    return true;
}

function runHooks(string $hookName): bool
{
    global $plugins;

    if (!($plugins instanceof \pluginSystem)) {
        return false;
    }

    $plugins->run_hooks($hookName);

    return true;
}

function templateGetName(string $templateSuffix): string
{
    return "ougcpages_{$templateSuffix}";
}

function getSetting(string $settingKey = ''): string
{
    global $mybb;

    $string = 'OUGC_PAGES_' . strtoupper($settingKey);

    return defined($string) ? constant($string) : (string)$mybb->settings['ougc_pages_' . $settingKey];
}

function sanitizeIntegers(array $dataObject, bool $implodeResult = false): array|string
{
    foreach ($dataObject as $objectKey => &$objectValue) {
        $objectValue = (int)$objectValue;
    }

    $dataObject = array_filter($dataObject);

    if ($implodeResult) {
        $dataObject = implode(',', $dataObject);
    }

    return $dataObject;
}

function getQueryLimit(int $newLimit = 0): int
{
    static $setLimit = QUERY_LIMIT;

    if ($newLimit > 0) {
        $setLimit = $newLimit;
    }
    return $setLimit;
}

function setQueryLimit(int $newLimit): int
{
    return getQueryLimit($newLimit);
}

function getQueryStart(int $newStart = 0): int
{
    static $setLimit = QUERY_START;

    if ($newStart > 0) {
        $setLimit = $newStart;
    }
    return $setLimit;
}

function setQueryStart(int $newStart): int
{
    return getQueryStart($newStart);
}

function url(string $newUrl = ''): string
{
    static $setUrl = URL;

    if (($newUrl = trim($newUrl))) {
        $setUrl = $newUrl;
    }

    return $setUrl;
}

function urlSet(string $newUrl): true
{
    url($newUrl);

    return true;
}

function urlGet(): string
{
    return url();
}

function urlBuild(array $urlAppend = [], bool $fetchImportUrl = false): string
{
    global $PL;

    if (!is_object($PL)) {
        $PL or require_once PLUGINLIBRARY;
    }

    if ($fetchImportUrl === false) {
        if ($urlAppend && !is_array($urlAppend)) {
            $urlAppend = explode('=', $urlAppend);
            $urlAppend = [$urlAppend[0] => $urlAppend[1]];
        }
    }/* else {
        $urlAppend = $this->fetch_input_url( $fetchImportUrl );
    }*/

    return $PL->url_append(urlGet(), $urlAppend, '&amp;', true);
}

function parseUrl(string $urlString): string
{
    global $settings;

    $urlString = \ougc_getpreview($urlString);

    $pattern = \preg_replace(
        '/[\\\\\\^\\-\\[\\]\\/]/u',
        '\\\\\\0',
        '!"#$%&\'( )*+,-./:;<=>?@[\]^_`{|}~'
    );

    $urlString = \preg_replace(
        '/^[' . $pattern . ']+|[' . $pattern . ']+$/u',
        '',
        $urlString
    );

    $urlString = \preg_replace(
        '/[' . $pattern . ']+/u',
        '-',
        $urlString
    );

    return \my_strtolower($urlString);
}

function importGetUrl(string $importName, string $importUrl = ''): string
{
    if (empty($importUrl)) {
        $importUrl = $importName;
    }

    $importUrl = parseUrl($importUrl);

    if (pageGetByUrl($importUrl)) {
        return importGetUrl('', \uniqid());
    }

    return $importUrl;
}

function cacheUpdate(): true
{
    require_once OUGC_PAGES_ROOT . '/admin.php';

    global $db, $cache;

    $cacheData = [
        'categories' => [],
        'pages' => [],
    ];

    $whereClause = ["visible='1'", "allowedGroups!=''"];

    // Update categories
    $dbQuery = $db->simple_select(
        'ougc_pages_categories',
        '*',
        implode(' AND ', $whereClause),
        ['order_by' => 'disporder']
    );

    while ($categoryData = $db->fetch_array($dbQuery)) {
        foreach (\OUGCPages\Admin\FIELDS_DATA_CATEGORIES as $fieldKey => $fieldData) {
            if (!isset($categoryData[$fieldKey]) || empty($fieldData['cache'])) {
                continue;
            }

            if (in_array($fieldData['type'], ['VARCHAR'])) {
                $cacheData['categories'][(int)$categoryData['cid']][$fieldKey] = $categoryData[$fieldKey];
            } else if (in_array($fieldData['type'], ['INT', 'SMALLINT', 'TINYINT'])) {
                $cacheData['categories'][(int)$categoryData['cid']][$fieldKey] = (int)$categoryData[$fieldKey];
            }
        }

        unset($fieldKey, $fieldData);
    }

    $db->free_result($dbQuery);

    if (!empty($cacheData['categories'])) {
        $categoriesIDs = implode("', '", array_keys($cacheData['categories']));

        $whereClause[] = "cid IN ('{$categoriesIDs}')";

        // Update pages
        $dbQuery = $db->simple_select(
            'ougc_pages',
            '*',
            implode(' AND ', $whereClause),
            ['order_by' => 'cid, disporder']
        );

        while ($pageData = $db->fetch_array($dbQuery)) {
            $pageID = (int)$pageData['pid'];
            $categoryID = (int)$pageData['cid'];

            foreach (\OUGCPages\Admin\FIELDS_DATA_PAGES as $fieldKey => $fieldData) {
                if (!isset($pageData[$fieldKey]) || empty($fieldData['cache'])) {
                    continue;
                }

                if (in_array($fieldData['type'], ['VARCHAR'])) {
                    $cacheData['pages'][$pageID][$fieldKey] = $pageData[$fieldKey];
                } else if (in_array($fieldData['type'], ['INT', 'SMALLINT', 'TINYINT'])) {
                    $cacheData['pages'][$pageID][$fieldKey] = (int)$pageData[$fieldKey];
                }
            }

            $cacheData['pages'][$pageID]['previousPageID'] = $cacheData['pages'][$pageID]['nextPageID'] = 0;

            if (isset($currentCategoryID) && $currentCategoryID === $categoryID) {
                if (isset($previousPageID) && $cacheData['pages'][$previousPageID]['cid'] === $categoryID) {
                    $cacheData['pages'][$previousPageID]['nextPageID'] = $pageID;

                    $cacheData['pages'][$pageID]['previousPageID'] = $previousPageID;
                }
            }

            unset($fieldKey, $fieldData);

            $currentCategoryID = $categoryID;

            $previousPageID = $pageID;
        }

        $db->free_result($dbQuery);
    }

    $cache->update('ougc_pages', $cacheData);

    return true;
}

function cacheGetPages(): array
{
    global $mybb;

    $cacheData = $mybb->cache->read('ougc_pages');

    if (!empty($cacheData['pages'])) {
        return $cacheData['pages'];
    }

    return [];
}

function cacheGetCategories(): array
{
    global $mybb;

    $cacheData = $mybb->cache->read('ougc_pages');

    if (!empty($cacheData['categories'])) {
        return $cacheData['categories'];
    }

    return [];
}

function redirect(string $redirectMessage = '', bool $isError = false): never
{
    if (defined('IN_ADMINCP')) {
        if ($redirectMessage) {
            \flash_message($redirectMessage, ($isError ? 'error' : 'success'));
        }

        \admin_redirect(urlBuild());
    } else {
        redirectBase(urlBuild(), $redirectMessage);
    }

    exit;
}

function redirectBase(string $url, string $message = '', string $title = '', bool $forceRedirect = false): void
{
    \redirect($url, $message, $title, $forceRedirect);
}

function logAction(int $objectID): true
{
    if ($objectID) {
        \log_admin_action($objectID);
    }

    return true;
}

function multipageBuild(int $itemsCount, string $paginationUrl = ''/*, bool $checkUrl = false*/): string
{
    global $mybb;

    /*if ( $checkUrl ) {
        $input = explode( '=', $params );
        if ( isset( $mybb->input[ $input[ 0 ] ] ) && $mybb->input[ $input[ 0 ] ] != $input[ 1 ] ) {
            $mybb->input[ 'page' ] = 0;
        }
    }*/

    if ($mybb->get_input('page', \MyBB::INPUT_INT) > 0) {
        if ($mybb->get_input('page', \MyBB::INPUT_INT) > ceil($itemsCount / getQueryLimit())) {
            $mybb->input['page'] = 1;
        } else {
            setQueryStart(($mybb->get_input('page', \MyBB::INPUT_INT) - 1) * getQueryLimit());
        }
    } else {
        $mybb->input['page'] = 1;
    }

    if (defined('IN_ADMINCP')) {
        return (string)\draw_admin_pagination(
            $mybb->get_input('page', \MyBB::INPUT_INT),
            getQueryLimit(),
            $itemsCount,
            $paginationUrl
        );
    }

    return (string)\multipage(
        $itemsCount,
        getQueryLimit(),
        $mybb->get_input('page', \MyBB::INPUT_INT),
        $paginationUrl
    );
}

function initExecute(int $pageID): never
{
    global $mybb, $lang, $db, $plugins, $cache, $parser, $settings;
    global $templates, $headerinclude, $header, $theme, $footer;
    global $templatelist, $session, $maintimer, $permissions;
    global $categoriesCache, $pagesCache, $isCategory, $isPage, $categoryID, $pageID, $categoryData, $pageData;

    runHooks('ougc_pages_execution_init');

    if (getSetting('disable_eval')) {
        echo pageGetTemplate($pageID);
    } else {
        eval('?>' . pageGetTemplate($pageID));
    }

    exit;
}

function initSession(): true
{
    global $session;

    if (!isset($session)) {
        require_once \MYBB_ROOT . 'inc/class_session.php';

        $session = new \session;

        $session->init();
    }

    return true;
}

function initRun(): bool
{
    global $mybb, $templatelist, $navbits;

    // we share this to the global scope for administrators to use but the plugin shouldn't rely on them a bit
    global $categoriesCache, $pagesCache, $isCategory, $isPage, $categoryID, $pageID, $categoryData, $pageData;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= '';

    if (
        defined('IN_ADMINCP') ||
        (defined(THIS_SCRIPT) && THIS_SCRIPT !== 'pages.php')
    ) {
        return false;
    }

    if (empty($navbits)) {
        $navbits = [
            0 => [
                'name' => $mybb->settings['bbname_orig'],
                'url' => $mybb->settings['bburl'] . '/index.php'
            ]
        ];
    }

    $categoriesCache = cacheGetCategories();

    $pagesCache = cacheGetPages();

    $isCategory = $isPage = false;

    $categoryID = $pageID = 0;

    $usingQuestionMark = \my_strpos(getSetting('seo_scheme_categories'), '?') !== false;

    if (isset($mybb->input['category'])) {
        $isCategory = true;

        // should be improved but works, by now
        if ($usingQuestionMark && count((array)$mybb->input) > 1) {
            $guessPick = false;

            foreach ($mybb->input as $inputKey => $inputValue) {
                if ($inputKey == 'category') {
                    $guessPick = true;

                    continue;
                }

                if ($guessPick) {
                    $mybb->input['category'] = $inputKey; // we assume second input to be the category

                    break;
                }
            }
        }

        $categoryInput = \my_strtolower($mybb->get_input('category'));

        foreach ($categoriesCache as $cid => $categoryData) {
            if ($categoryData['url'] === $categoryInput) {
                $categoryID = $cid;

                break;
            }
        }
    } else if (isset($mybb->input['page'])) {
        $isPage = true;

        // should be improved but works, by now
        if ($usingQuestionMark && count((array)$mybb->input) > 1) {
            $guessPick = false;

            foreach ($mybb->input as $inputKey => $inputValue) {
                if ($inputKey == 'page') {
                    $guessPick = true;

                    continue;
                }

                if ($guessPick) {
                    $mybb->input['page'] = $inputKey; // we assume second input to be the page

                    break;
                }
            }
        }

        $pageInput = \my_strtolower($mybb->get_input('page'));

        foreach ($pagesCache as $pid => $pageData) {
            if ($pageData['url'] === $pageInput) {
                $pageID = $pid;

                $categoryID = $pageData['cid'];

                $categoryData = $categoriesCache[$categoryID];

                break;
            }
        }
    }

    $categoryData = categoryGet($categoryID);

    $pageData = pageGet($pageID);

    // maybe do some case-sensitive comparison and redirect to one unique case url

    if (
        ($isCategory && !$categoryID && !$categoryData) ||
        ($isPage && !$categoryID && !$categoryData && !$pageID && !$pageData)
    ) {
        if ($isCategory) {
            define('OUGC_PAGES_STATUS_CATEGORY_INVALID', true);
        } else {
            define('OUGC_PAGES_STATUS_PAGE_INVALID', true);
        }

        return false;
    }

    // url correction needs work, this covers the basics
    $categoryUrl = categoryGetLinkBase($categoryID);

    if ($isPage) {
        $pageUrl = pageGetLinkBase($pageID);
    }

    $locationPath = \parse_url($_SERVER['REQUEST_URI'])['path'];

    if ($usingQuestionMark) {

        if ($isPage) {
            $locationPath .= "?{$pageData['url']}";
        } else {
            $locationPath .= "?{$categoryData['url']}";
        }
    }

    if ($isCategory && \my_strpos($locationPath, $categoryUrl) === false) {
        $mybb->settings['redirects'] = 0;

        redirectBase(categoryGetLink($categoryID));
    } else if ($isPage && \my_strpos($locationPath, $pageUrl) === false) {
        $mybb->settings['redirects'] = 0;

        redirectBase(pageGetLink($pageID));
    }

    $templatelist .= "ougcpages_category{$categoryID}, ougcpages_page{$pageID}";

    if ($categoryData['allowedGroups'] === '') {
        define('OUGC_PAGES_STATUS_CATEGORY_NO_PERMISSION', true);

        return false;
    } else if ((int)$categoryData['allowedGroups'] !== -1) {
        initSession();

        if (!\is_member($categoryData['allowedGroups'], $mybb->user)) {
            define('OUGC_PAGES_STATUS_CATEGORY_NO_PERMISSION', true);

            return false;
        }
    }

    if ($isCategory) {
        define('OUGC_PAGES_STATUS_IS_CATEGORY', $categoryID);

        return true;
    }

    define('OUGC_PAGES_STATUS_IS_PAGE', $pageID);

    if ($pageData['allowedGroups'] === '') {
        define('OUGC_PAGES_STATUS_PAGE_NO_PERMISSION', true);

        return false;
    } else if ((int)$pageData['allowedGroups'] !== -1) {
        initSession();

        if (!\is_member($pageData['allowedGroups'], $mybb->user)) {
            define('OUGC_PAGES_STATUS_PAGE_NO_PERMISSION', true);

            return false;
        }
    }

    if (!$pageData['wol'] && !defined('NO_ONLINE')) {
        define('NO_ONLINE', true);
    }

    if ($pageData['php']) {
        if ($pageData['init'] === EXECUTION_HOOK_INIT) {
            initExecute($pageData['pid']);
        } else if ($pageData['init'] === EXECUTION_HOOK_GLOBAL_START) {
            define('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_START', $pageData['pid']);

        } else if ($pageData['init'] === EXECUTION_HOOK_GLOBAL_INTERMEDIATE) {
            define('OUGC_PAGES_STATUS_PAGE_INIT_GLOBAL_INTERMEDIATE', $pageData['pid']);
        }

        // we no longer load at 'global_end' (small lie), we instead load at 'ougc_pages_start' to make sure the page loads within the plugin's pages.php file
    }

    return true;
}

function initShow(): never
{
    global $db, $lang, $templates, $mybb, $footer, $headerinclude, $header, $theme;

    loadLanguage();

    $categoriesCache = cacheGetCategories();

    $pagesCache = cacheGetPages();

    $isCategory = $isPage = false;

    $categoryID = $pageID = 0;

    if (defined('OUGC_PAGES_STATUS_IS_CATEGORY')) {
        $isCategory = true;

        $categoryID = OUGC_PAGES_STATUS_IS_CATEGORY;

        $categoryData = categoryGet($categoryID);
    } else {
        $isPage = true;

        $pageID = OUGC_PAGES_STATUS_IS_PAGE;

        $pageData = pageGet($pageID);

        $categoryData = categoryGet($pageData['cid']);

        if ($pageData['php']) {
            initExecute($pageData['pid']);
        }
    }

    // Load custom page language file if exists
    $lang->load("ougc_pages_{$categoryData['cid']}", false, true);

    if (!empty($pageData)) {
        $lang->load("ougc_pages_{$pageData['pid']}", false, true);
    }

    $categoryData['name'] = \htmlspecialchars_uni($categoryData['name']);

    if ($categoryData['wrapucp']) {
        $lang->load('usercp');

        if ($mybb->user['uid'] && $mybb->usergroup['canusercp']) {
            \add_breadcrumb($lang->nav_usercp, "usercp.php");
        }
    }

    if (!$isPage || $categoryData['breadcrumb']) {
        \add_breadcrumb($categoryData['name'], categoryGetLink($categoryData['cid']));
    }

    $navigation = ['previousPage' => '', 'nextPage' => ''];

    $lastEditedMessage = '';

    if (!empty($pageData)) {
        $title = $pageData['name'] = \htmlspecialchars_uni($pageData['name']);

        $description = $pageData['description'] = \htmlspecialchars_uni($pageData['description']);

        $canonicalUrl = pageGetLink($pageID);

        \add_breadcrumb($pageData['name'], pageGetLink($pageData['pid']));

        if ($categoryData['navigation']) {
            if (!empty($pagesCache[$pageID]) && !empty($pagesCache[$pageID]['previousPageID'])) {
                $previousPageLink = pageGetLink($pagesCache[$pageID]['previousPageID']);
                $previousPageName = \htmlspecialchars_uni($pagesCache[$pagesCache[$pageID]['previousPageID']]['name']);

                $navigation['previousPage'] = eval($templates->render('ougcpages_navigation_previous'));
            }
            if (!empty($pagesCache[$pageID]) && !empty($pagesCache[$pageID]['nextPageID'])) {
                $nextPageLink = pageGetLink($pagesCache[$pageID]['nextPageID']);
                $nextPageName = \htmlspecialchars_uni($pagesCache[$pagesCache[$pageID]['nextPageID']]['name']);

                $navigation['nextPage'] = eval($templates->render('ougcpages_navigation_next'));
            }
        }

        $templates->cache['ougcpages_temporary_tmpl'] = $pageData['template'];

        if (!empty($pageData['dateline'])) {
            $editDateNormal = \my_date('normal', $pageData['dateline']);

            $editDateRelative = \my_date('relative', $pageData['dateline']);

            $lastEditedMessage = eval($templates->render('ougcpages_wrapper_edited'));
        }

        $content = eval($templates->render('ougcpages_temporary_tmpl'));

        if ($pageData['wrapper']) {
            $content = eval($templates->render('ougcpages_wrapper'));
        }
    } else {
        $title = \htmlspecialchars_uni($categoryData['name']);

        $description = \htmlspecialchars_uni($categoryData['description']);

        $canonicalUrl = categoryGetLink($categoryID);

        $pageCache = cacheGetPages();

        $pageList = '';

        foreach ($pageCache as $pid => $pageData) {
            if (
                $categoryData['cid'] !== $pageData['cid'] ||
                !\is_member($pageData['allowedGroups'])
            ) {
                continue;
            }

            $pageName = \htmlspecialchars_uni($pageData['name']);

            $pageLink = pageGetLink($pid);

            $pageList .= eval($templates->render('ougcpages_category_list_item'));
        }

        if (!$pageList) {
            $content = eval($templates->render('ougcpages_category_list_empty'));
        } else {
            $content = eval($templates->render('ougcpages_category_list'));
        }

        $content = eval($templates->render('ougcpages_wrapper'));
    }

    if ($categoryData['wrapucp']) {
        global $usercpnav;

        require_once MYBB_ROOT . 'inc/functions_user.php';

        \usercp_menu();

        $content = eval($templates->render('ougcpages_wrapper_ucp'));
    }

    $pageContent = eval($templates->render('ougcpages'));

    \output_page($pageContent);

    exit;
}

function categoryInsert(array $categoryData = [], int $categoryID = 0, bool $update = false): int
{
    global $db;

    $insertData = [];

    foreach (\OUGCPages\Admin\FIELDS_DATA_CATEGORIES as $fieldKey => $fieldData) {
        if (!isset($categoryData[$fieldKey])) {
            continue;
        }

        if (in_array($fieldData['type'], ['VARCHAR', 'MEDIUMTEXT'])) {
            $insertData[$fieldKey] = $db->escape_string($categoryData[$fieldKey]);
        } else if (in_array($fieldData['type'], ['INT', 'SMALLINT', 'TINYINT'])) {
            $insertData[$fieldKey] = (int)$categoryData[$fieldKey];
        }
    }

    unset($fieldKey, $fieldData);

    $insertID = $categoryID;

    if ($insertData) {
        if ($update) {
            $db->update_query('ougc_pages_categories', $insertData, "cid='{$categoryID}'");

            runHooks('ouc_pages_update_category');
        } else {
            $insertID = (int)$db->insert_query('ougc_pages_categories', $insertData);

            runHooks('ouc_pages_insert_category');
        }
    }

    return $insertID;
}

function categoryUpdate(array $data = [], int $cid = 0): int
{
    return categoryInsert($data, $cid, true);
}

function categoryDelete(int $categoryID): bool
{
    global $db;

    $db->delete_query('ougc_pages_categories', "cid='{$categoryID}'");

    $db->delete_query('ougc_pages', "cid='{$categoryID}'");

    return true;
}

function categoryGet(int $categoryID, string $categoryUrl = ''): array
{
    static $cacheObject = [];

    if (!isset($cacheObject[$categoryID])) {
        global $db;

        $cacheObject[$categoryID] = [];

        $whereConditions = ['1=1'];

        if ($categoryUrl === '') {
            $whereConditions[] = "cid='{$categoryID}'";
        } else {
            $whereConditions[] = "url='{$db->escape_string($categoryUrl)}'";
        }

        $categoryData = categoryQuery(['*'], $whereConditions, ['limit' => 1]);

        if ($categoryData && isset($categoryData[0]['cid'])) {
            $cacheObject[$categoryID] = $categoryData[0];
        }
    }

    return $cacheObject[$categoryID];
}

function categoryQuery(array $fieldList = ['*'], array $whereConditions = ['1=1'], array $queryOptions = []): array
{
    global $db;

    $dbQuery = $db->simple_select(
        'ougc_pages_categories',
        implode(', ', $fieldList),
        implode(' AND ', $whereConditions),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        $returnObjects = [];

        while ($categoryData = $db->fetch_array($dbQuery)) {
            $returnObjects[] = $categoryData;
        }

        return $returnObjects;
    }

    return [];
}

function categoryGetByUrl(bool|string $url): array
{
    return categoryGet(0, $url);
}

function categoryGetLink(int $categoryID): string
{
    global $settings;

    return $settings['bburl'] . '/' . \htmlspecialchars_uni(categoryGetLinkBase($categoryID));
}

function categoryGetLinkBase(int $categoryID): string
{
    static $cacheObject = [];

    if (!isset($cacheObject[$categoryID])) {
        $cacheObject[$categoryID] = '';

        $categoriesCache = cacheGetCategories();

        if (!empty($categoriesCache[$categoryID]['url'])) {
            if (getSetting('seo') && \my_strpos(getSetting('seo_scheme_categories'), '{url}') !== false) {
                $cacheObject[$categoryID] = str_replace('{url}', $categoriesCache[$categoryID]['url'], getSetting('seo_scheme_categories'));
            } else {
                $cacheObject[$categoryID] = "pages.php?page={$categoriesCache[$categoryID]['url']}";
            }
        }
        // maybe get from DB otherwise ...
    }

    return $cacheObject[$categoryID];
}

function categoryBuildLink(string $categoryName, int $categoryID): string
{
    $categoryLink = categoryGetLink($categoryID);

    $categoryName = \htmlspecialchars_uni($categoryName);

    return "<a href=\"{$categoryLink}\">{$categoryName}</a>";
}

function categoryBuildSelect(): array
{
    $selectItems = [];

    foreach (categoryQuery(['cid', 'name'], ['1=1'], ['order_by' => 'name']) as $categoryData) {
        $selectItems[$categoryData['cid']] = \htmlspecialchars_uni($categoryData['name']);
    }

    return $selectItems;
}

function pageInsert(array $pageData = [], int $pageID = 0, bool $update = false): int
{
    global $db;

    $insertData = [];

    foreach (\OUGCPages\Admin\FIELDS_DATA_PAGES as $fieldKey => $fieldData) {
        if (!isset($pageData[$fieldKey])) {
            continue;
        }

        if (in_array($fieldData['type'], ['VARCHAR', 'MEDIUMTEXT'])) {
            $insertData[$fieldKey] = $db->escape_string($pageData[$fieldKey]);
        } else if (in_array($fieldData['type'], ['INT', 'SMALLINT', 'TINYINT'])) {
            $insertData[$fieldKey] = (int)$pageData[$fieldKey];
        }
    }

    $insertID = $pageID;

    if ($insertData) {
        global $plugins;

        $insertData['dateline'] = \TIME_NOW;

        if ($update) {
            $db->update_query('ougc_pages', $insertData, 'pid=\'' . $insertID . '\'');

            runHooks('ouc_pages_update_page');
        } else {
            $insertID = (int)$db->insert_query('ougc_pages', $insertData);

            runHooks('ouc_pages_insert_page');
        }
    }

    return $insertID;
}

function pageUpdate(array $data = [], int $pageID = 0): int
{
    return pageInsert($data, $pageID, true);
}

function pageDelete(int $pageID): int
{
    global $db;

    $db->delete_query('ougc_pages', "pid='{$pageID}'");

    return $pageID;
}

function pageGet(int $pageID, string $pageUrl = ''): array
{
    static $cacheObject = [];

    if (!isset($cacheObject[$pageID])) {
        global $db;

        $cacheObject[$pageID] = [];

        $whereConditions = ['1=1'];

        if ($pageUrl === '') {
            $whereConditions[] = "pid='{$pageID}'";
        } else {
            $whereConditions[] = "url='{$db->escape_string($pageUrl)}'";
        }

        $pageData = pageQuery(['*'], $whereConditions, ['limit' => 1]);

        if ($pageData && isset($pageData[0]['pid'])) {
            $cacheObject[$pageID] = $pageData[0];
        }
    }

    return $cacheObject[$pageID];
}

function pageQuery(array $fieldList = ['*'], array $whereConditions = ['1=1'], array $queryOptions = []): array
{
    global $db;

    $dbQuery = $db->simple_select(
        'ougc_pages',
        implode(', ', $fieldList),
        implode(' AND ', $whereConditions),
        $queryOptions
    );

    if ($db->num_rows($dbQuery)) {
        $returnObjects = [];

        while ($categoryData = $db->fetch_array($dbQuery)) {
            $returnObjects[] = $categoryData;
        }

        return $returnObjects;
    }

    return [];
}

function pageGetTemplate(int $pageID): string
{
    global $templates;

    $pageData = pageGet($pageID);

    if (!empty($pageData['classicTemplate']) && isset($templates->cache["ougcpages_page{$pageID}"])) {
        return $templates->cache["ougcpages_page{$pageID}"];
    }

    if (!isset($pageData['template'])) {
        return '';
    }

    return $pageData['template'];
}

function pageGetByUrl(string $url): array
{
    return pageGet(0, $url);
}

function pageGetLink(int $pageID): string
{
    global $settings;

    return $settings['bburl'] . '/' . \htmlspecialchars_uni(pageGetLinkBase($pageID));
}

function pageGetLinkBase(int $pageID): string
{
    static $cacheObject = [];

    if (!isset($cacheObject[$pageID])) {
        $cacheObject[$pageID] = '';

        $pagesCache = cacheGetPages();

        if (!empty($pagesCache[$pageID]['url'])) {
            if (getSetting('seo') && \my_strpos(getSetting('seo_scheme'), '{url}') !== false) {
                $cacheObject[$pageID] = str_replace('{url}', $pagesCache[$pageID]['url'], getSetting('seo_scheme'));
            } else {
                $cacheObject[$pageID] = "pages.php?page={$pagesCache[$pageID]['url']}";
            }
        }
        // maybe get from DB otherwise ...
    }

    return $cacheObject[$pageID];
}

function pageBuildLink(string $pageName, int $pageID): string
{
    $pageLink = pageGetLink($pageID);

    $pageName = \htmlspecialchars_uni($pageName);

    return "<a href=\"{$pageLink}\">{$pageName}</a>";
}