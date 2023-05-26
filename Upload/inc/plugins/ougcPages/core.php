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

const PAGE_STATUS_VALID = 1;

const PAGE_STATUS_INVALID = 0;

const CATEGORY_STATUS_VALID = 1;

const PERMISSION_STATUS_ALLOW = 1;

const PERMISSION_STATUS_DISALLOW = 0;

const EXECUTION_STATUS_DISABLED = 0; // pageID if not 0

const EXECUTION_HOOK_INIT = 1;

const EXECUTION_HOOK_GLOBAL_START = 2;

const EXECUTION_HOOK_GLOBAL_INTERMEDIATE = 3;

const EXECUTION_HOOK_GLOBAL_END = 4;

const EXECUTION_HOOK_CORE_START = 5;

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
        flash_message(
            $lang->sprintf(
                $lang->ougc_pages_pl_required,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
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

function importGetUrl(string $importName, string $importUrl = '', int $pageID = 0): string
{
    global $db;

    if (empty($importUrl)) {
        $importUrl = $importName;
    }

    $importUrl = parseUrl($importUrl);

    $importUrlEscaped = $db->escape_string($importUrl);

    $dbQuery = $db->simple_select(
        'ougc_pages',
        'pid',
        "url='{$importUrlEscaped}' AND pid!='{$pageID}'",
        ['limit' => 1]
    );

    if ($db->num_rows($dbQuery)) {
        return importGetUrl('', "{$importUrl} - {$pageID}");
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

    $whereClause = ["visible='1'"];

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
            ['order_by' => 'disporder']
        );

        while ($pageData = $db->fetch_array($dbQuery)) {
            foreach (\OUGCPages\Admin\FIELDS_DATA_PAGES as $fieldKey => $fieldData) {
                if (!isset($pageData[$fieldKey]) || empty($fieldData['cache'])) {
                    continue;
                }

                if (in_array($fieldData['type'], ['VARCHAR'])) {
                    $cacheData['pages'][(int)$pageData['pid']][$fieldKey] = $pageData[$fieldKey];
                } else if (in_array($fieldData['type'], ['INT', 'SMALLINT', 'TINYINT'])) {
                    $cacheData['pages'][(int)$pageData['pid']][$fieldKey] = (int)$pageData[$fieldKey];
                }
            }

            unset($fieldKey, $fieldData);
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
        \redirect(urlBuild(), $redirectMessage);
    }

    exit;
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
    global $ougc_pages, $category, $page, $plugins;

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
    global $mybb;
    global $templates, $templatelist, $ougc_pages;
    global $session;
    global $plugins, $navbits;
    //global $category, $page, $pageData; TODO

    //$page = &$pageData; // TODO
    //$category = &$categoryData; // TODO

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= '';

    if (empty($navbits)) {
        $navbits = [
            0 => [
                'name' => $mybb->settings['bbname_orig'],
                'url' => $mybb->settings['bburl'] . '/index.php'
            ]
        ];
    }

    /*if (
        defined( 'IN_ADMINCP' ) ||
        ( defined( THIS_SCRIPT ) && THIS_SCRIPT == 'pages.php' )
    ) {
        return false;
    }*/

    // should be fixed as well
    /*if (
        strpos(getSetting('seo_scheme'), '?') !== false &&
        isset($mybb->input['page']) &&
        !empty($mybb->input['page']) &&
        count((array)$mybb->input) > 1
    ) {
        foreach ($mybb->input as $inputKey => $inputValue) {
            if ($inputKey == 'page') {
                $mybb->input['page'] = $inputKey; // we assume second input to be the page

                break;
            }
        }
    }*/

    $categoriesCache = \OUGCPages\Core\cacheGetCategories();

    $pagesCache = \OUGCPages\Core\cacheGetPages();

    $categoryID = $pageID = 0;

    $isCategory = $isPage = false;

    if (isset($mybb->input['page'])) {
        $isPage = true;

        foreach ($pagesCache as $pid => $pageData) {
            if ($pageData['url'] === $mybb->get_input('page')) {
                $pageID = $pid;
                $categoryID = $pageData['cid'];
                $categoryData = $categoriesCache[$categoryID];
                break;
            }
        }
    } else if (isset($mybb->input['category'])) {
        $isCategory = true;

        foreach ($categoriesCache as $cid => $categoryData) {
            if ($categoryData['url'] === $mybb->get_input('category')) {
                $categoryID = $cid;
                break;
            }
        }
    }

    // maybe do some case-sensitive comparison and redirect to one unique case url

    if (($isCategory && !$categoryID && !$categoryData) || ($isPage && !$categoryID && !$pageID && !$categoryData && !$pageData)) {
        pageStatusSet(PAGE_STATUS_INVALID);

        return false;
    }

    categoryCurrentSet($categoryID);

    $templatelist .= "ougcpages_category{$categoryID}, ougcpages_page{$pageID}";

    if ($categoryData['allowedGroups'] === '') {
        permissionStatusSet(PERMISSION_STATUS_DISALLOW);

        return false;
    } else if ((int)$categoryData['allowedGroups'] !== -1) {
        initSession();

        if (!\is_member($categoryData['allowedGroups'])) {
            permissionStatusSet(PERMISSION_STATUS_DISALLOW);

            return false;
        }
    }

    if ($isPage) {
        if ($pageData['allowedGroups'] === '') {
            permissionStatusSet(PERMISSION_STATUS_DISALLOW);

            return false;
        } else if ((int)$pageData['allowedGroups'] !== -1) {
            initSession();

            if (!\is_member($pageData['allowedGroups'])) {
                permissionStatusSet(PERMISSION_STATUS_DISALLOW);

                return false;
            }
        }

        /*
        if (!empty($mybb->cache->cache['ougc_pages']['pages'][$mybb->get_input('page')])) {
            if ($pageData = pageGetByUrl($mybb->get_input('page'))) {

                if ($categoryData = categoryGet($categoryID)) {
                    if (!$categoryData['visible']) {
                        categoryStatusSet(PAGE_STATUS_INVALID);
                    } else {
                        categoryCurrentSet($categoryID);
                    }
                } else {
                    categoryStatusSet(PAGE_STATUS_INVALID);
                }
            } else {
                pageStatusSet(PAGE_STATUS_INVALID);
            }
        } else {
            pageStatusSet(PAGE_STATUS_INVALID);
        }*/
    }/* else if (!empty($mybb->input['category'])) {
        // should be fixed as well
        if (strpos(getSetting('seo_scheme_categories'), '?') !== false && empty($mybb->input['category']) && count((array)$mybb->input) > 1) {
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

        if ($categoryData = categoryGetByUrl($mybb->get_input('category'))) {
            if (!$categoryData['visible']) {
                categoryStatusSet(PAGE_STATUS_INVALID);
            } else {
                categoryCurrentSet($categoryID);
            }
        } else {
            categoryStatusSet(PAGE_STATUS_INVALID);
        }
    }

    if (!empty($categoryData)) {
        // Save three queries if no permission check is necessary
        if ($categoryData['allowedGroups'] != '') {
            initSession();

            if (!\is_member($categoryData['allowedGroups'])) {
                permissionStatusSet(PERMISSION_STATUS_DISALLOW);
            }
        }
    }*/

    if ($isPage) {
        $pageData = pageGet($pageID); // not all page data is cached

        if (!$pageData['wol'] && !defined('NO_ONLINE')) {
            define('NO_ONLINE', 1);
        }

        /*
        // Save three queries if no permission check is necessary
        if (permissionStatusGet() === PERMISSION_STATUS_ALLOW) {
            if ($pageData['allowedGroups'] != '') {
                initSession();

                if (!is_member($pageData['allowedGroups'])) {
                    permissionStatusSet(PERMISSION_STATUS_DISALLOW);
                }
            }
        }*/

        executeStatusSet($pageID);

        if ($pageData['php'] && permissionStatusGet() === PERMISSION_STATUS_ALLOW) {
            switch ((int)$pageData['init']) {
                case EXECUTION_HOOK_INIT:
                    executeHookSet(EXECUTION_HOOK_INIT);
                    break;
                case EXECUTION_HOOK_GLOBAL_START:
                    executeHookSet(EXECUTION_HOOK_GLOBAL_START);
                    break;
                case EXECUTION_HOOK_GLOBAL_INTERMEDIATE:
                    executeHookSet(EXECUTION_HOOK_GLOBAL_INTERMEDIATE);
                    break;
                default:
                    executeHookSet(EXECUTION_HOOK_GLOBAL_END);
                    break;
            }

            if (\OUGCPages\Core\executeHookCheck(EXECUTION_HOOK_INIT)) {
                initExecute(executeGetCurrentPageID());
            }
        } else {
            executeHookSet(EXECUTION_HOOK_CORE_START);
        }
    }

    return true;
}

function initShow(): never
{
    global $db, $lang, $templates, $mybb, $footer, $headerinclude, $header, $theme;

    loadLanguage();

    if (pageStatusGet() === PAGE_STATUS_INVALID) {
        \error($lang->ougc_pages_error_invalidpage);
    }

    if (categoryStatusGet() === PAGE_STATUS_INVALID) {
        error($lang->ougc_pages_error_invalidcategory);
    }

    if (permissionStatusGet() === PERMISSION_STATUS_DISALLOW) {
        error_no_permission();
    }

    $categoryData = categoryGet(categoryCurrentGet());

    $pageData = pageGet(executeGetCurrentPageID());

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

    if ($categoryData['breadcrumb']) {
        \add_breadcrumb($categoryData['name'], categoryGetLink($categoryData['cid']));
    }

    $navigation = ['previous' => '', 'next' => ''];

    if (!empty($pageData)) {
        $title = $pageData['name'] = htmlspecialchars_uni($pageData['name']);

        $description = $pageData['description'] = htmlspecialchars_uni($pageData['description']);

        add_breadcrumb($pageData['name'], pageGetLink($pageData['pid']));

        /*if($categoryData['navigation'])
        {
            implode( ' AND ', $whereClause ) .= 'AND php!=\'1\' AND disporder';
            $where = '<\''.(int)$pageData['disporder'].'\'';
            $query = $db->simple_select('ougc_pages', 'pid', implode( ' AND ', $whereClause ).$where, array('order_by' => 'disporder, name', 'limit' => 1));
            $previous_page_id = (int)$db->fetch_field($query, 'pid');

            if($previous_page_id)
            {
                $previous_link = pageGetLink($previous_page_id);
                $navigation['previous'] = eval( $templates->render( 'ougcpages_navigation_previous' ) );
            }

            $where = '>\''.(int)$pageData['disporder'].'\'';
            $query = $db->simple_select('ougc_pages', 'pid', implode( ' AND ', $whereClause ).$where, array('order_by' => 'disporder, name', 'limit' => 1));
            $next_page_id = (int)$db->fetch_field($query, 'pid');

            if($next_page_id)
            {
                $next_link = pageGetLink($next_page_id);
                $navigation['next'] = eval( $templates->render( 'ougcpages_navigation_next' ) );
            }
        }*/

        $templates->cache['ougcpages_temporary_tmpl'] = $pageData['template'];

        #TODO: Add "Las updated on DATELINE..." to page

        $content = eval($templates->render('ougcpages_temporary_tmpl'));

        if ($pageData['wrapper']) {
            $content = eval($templates->render('ougcpages_wrapper'));
        }

        /*if($categoryData['navigation'])
        {
            $content = eval( $templates->render( 'ougcpages_navigation' ) );
        }*/
    } else {
        $title = \htmlspecialchars_uni($categoryData['name']);

        $description = \htmlspecialchars_uni($categoryData['description']);

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

function permissionStatusGet(bool $setNewStatus = false, int $newStatus = PERMISSION_STATUS_ALLOW): int
{
    static $setStatus = PERMISSION_STATUS_ALLOW;

    if ($setNewStatus) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function permissionStatusSet(int $newStatus = PERMISSION_STATUS_ALLOW): int
{
    return permissionStatusGet(true, $newStatus);
}

function executeStatusGet(bool $setNewStatus = false, int $newStatus = EXECUTION_STATUS_DISABLED): int
{
    static $cachedStatus = EXECUTION_STATUS_DISABLED;

    if ($setNewStatus) {
        $cachedStatus = $newStatus;
    }

    return $cachedStatus;
}

function executeStatusSet(int $newStatus): int
{
    return executeStatusGet(true, $newStatus);
}

function executeGetCurrentPageID(): int
{
    return executeStatusGet();
}

function categoryCurrentSet(null|int $categoryID = null): null|int
{
    static $currentCategoryID = null;

    if ($categoryID !== null) {
        $currentCategoryID = (int)$categoryID;
    }

    return $currentCategoryID;
}

function categoryCurrentGet(): int
{
    return categoryCurrentSet();
}

function executeHookGet(bool $setNewHook = false, int $newHook = EXECUTION_HOOK_GLOBAL_END): int
{
    static $cachedHook = EXECUTION_HOOK_GLOBAL_END;

    if ($setNewHook) {
        $cachedHook = $newHook;
    }

    return $cachedHook;
}

function executeHookSet(int $newHook): int
{
    return executeHookGet(true, $newHook);
}

function executeHookCheck(int $ifHook): bool
{
    return (
        \OUGCPages\Core\executeStatusGet() !== \OUGCPages\Core\EXECUTION_STATUS_DISABLED &&
        \OUGCPages\Core\executeHookGet() === $ifHook
    );
}

function categoryStatusGet(bool $setNewStatus = false, int $newStatus = CATEGORY_STATUS_VALID): int
{
    static $setStatus = CATEGORY_STATUS_VALID;

    if ($setNewStatus) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function categoryStatusSet(int $newStatus = CATEGORY_STATUS_VALID): int
{
    return categoryStatusGet(true, $newStatus);
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

function categoryDelete(int $categoryID): int
{
    global $db;

    $db->delete_query('ougc_pages_categories', "cid='{$categoryID}'");

    return $categoryID;
}

function categoryGet(int $cid, bool|string $url = false): array
{
    global $cache;

    static $cacheObject = [];

    if (!isset($cacheObject[$cid])) {
        global $db;
        $cacheObject[$cid] = [];

        $where = ($url === false ? 'cid=\'' . $cid . '\'' : 'url=\'' . $db->escape_string($url) . '\'');

        $query = $db->simple_select('ougc_pages_categories', '*', $where);
        $category = $db->fetch_array($query);

        if (isset($category['cid'])) {
            $cacheObject[$cid] = $category;
        }
    }

    return $cacheObject[$cid];
}

function categoryQuery(array $fieldList = ['*'], array $whereConditions = ['1=1'], array $queryOptions = []): bool|array
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

    return false;
}

function categoryGetByUrl(bool|string $url): array
{
    return categoryGet(0, $url);
}

function categoryGetLink(int $cid): string
{
    global $db, $settings;

    $query = $db->simple_select('ougc_pages_categories', 'url', 'cid=\'' . $cid . '\'');
    $url = $db->fetch_field($query, 'url');

    if ($settings['ougc_pages_seo'] && \my_strpos($settings['ougc_pages_seo_scheme_categories'], '{url}') !== false) {
        $url = str_replace('{url}', $url, $settings['ougc_pages_seo_scheme_categories']);
    } else {
        $url = 'pages.php?category=' . $url;
    }

    return $settings['bburl'] . '/' . \htmlspecialchars_uni($url);
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
        $selectItems[$categoryData['cid']] = htmlspecialchars_uni($categoryData['name']);
    }

    return $selectItems;
}

function pageStatusGet(bool $setNewStatus = false, int $newStatus = PAGE_STATUS_VALID): int
{
    static $setStatus = PAGE_STATUS_VALID;

    if ($setNewStatus) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function pageStatusSet(int $newStatus = PAGE_STATUS_VALID): int
{
    return pageStatusGet(true, $newStatus);
}

function pageInsert(array $pageData = [], int $pageID = 0, bool $update = false): int
{
    global $db;

    $insertData = [];

    if (!$update) {
        foreach (['allowedGroups', 'template'] as $columnKey) {
            if (!isset($pageData[$columnKey])) {
                $insertData[$columnKey] = '';
            }
        }

        if (!isset($pageData['dateline'])) {
            $insertData['dateline'] = \TIME_NOW;
        }
    }

    foreach (['name', 'description', 'url', 'allowedGroups', 'template'] as $columnKey) {
        if (isset($pageData[$columnKey])) {
            $insertData[$columnKey] = $db->escape_string($pageData[$columnKey]);
        }
    }

    foreach (['cid', 'php', 'wol', 'disporder', 'visible', 'wrapper', 'init', 'dateline'] as $columnKey) {
        if (isset($pageData[$columnKey])) {
            $insertData[$columnKey] = (int)$pageData[$columnKey];
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

function pageGet(int $pageID, bool|string $url = false): array
{
    static $cacheObject = [];

    if (!isset($cacheObject[$pageID])) {
        global $db;
        $cacheObject[$pageID] = [];

        $where = ($url === false ? 'pid=\'' . (int)$pageID . '\'' : 'url=\'' . $db->escape_string($url) . '\'');

        $query = $db->simple_select('ougc_pages', '*', $where);
        $page = $db->fetch_array($query);

        if (isset($page['pid'])) {
            $cacheObject[$pageID] = $page;
        }
    }

    return $cacheObject[$pageID];
}

function pageQuery(array $fieldList = ['*'], array $whereConditions = ['1=1'], array $queryOptions = []): bool|array
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

    return false;
}

function pageGetTemplate(int $pageID): string
{
    $pageData = pageGet($pageID);

    if (!isset($pageData['template'])) {
        return '';
    }

    return $pageData['template'];
}

function pageGetByUrl(bool|string $url): array
{
    return pageGet(0, $url);
}

function pageGetLink(int $pageID): string
{
    global $db, $settings;

    $query = $db->simple_select('ougc_pages', 'url', 'pid=\'' . $pageID . '\'');
    $url = $db->fetch_field($query, 'url');

    if ($settings['ougc_pages_seo'] && my_strpos($settings['ougc_pages_seo_scheme'], '{url}') !== false) {
        $url = str_replace('{url}', $url, $settings['ougc_pages_seo_scheme']);
    } else {
        $url = 'pages.php?page=' . $url;
    }

    return $settings['bburl'] . '/' . htmlspecialchars_uni($url);
}

function pageBuildLink(string $pageName, int $pageID): string
{
    $pageLink = pageGetLink($pageID);

    $pageName = \htmlspecialchars_uni($pageName);

    return "<a href=\"{$pageLink}\">{$pageName}</a>";
}