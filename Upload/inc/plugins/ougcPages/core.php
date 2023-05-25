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
const INVALID_PAGE = false;
const INVALID_CATEGORY = false;
const NO_PERMISSION = false;

const PAGE_STATUS_VALID = 1;

const PAGE_STATUS_INVALID = 0;

const CATEGORY_STATUS_VALID = 1;

const CATEGORY_STATUS_INVALID = 0;

const PERMISSION_STATUS_ALLOW = 1;

const PERMISSION_STATUS_DISALLOW = 0;

const EXECUTION_STATUS_DISABLED = 0; // pageID if not 0

const EXECUTION_HOOK_INIT = 1;

const EXECUTION_HOOK_GLOBAL_START = 2;

const EXECUTION_HOOK_GLOBAL_INTERMEDIATE = 3;

const EXECUTION_HOOK_GLOBAL_END = 4;

function loadLanguage(): true
{
    global $lang;

    if ( !isset( $lang->setting_group_ougc_pages ) ) {
        if ( defined( 'IN_ADMINCP' ) ) {
            $lang->load( 'config_ougc_pages' );
        } else {
            $lang->load( 'ougc_pages' );
        }
    }

    return true;
}

function pluginLibraryRequirements(): object
{
    return (object) \OUGCPages\Admin\pluginInfo()[ 'pl' ];
}

function loadPluginLibrary( bool $doCheck = true ): bool
{
    global $PL, $lang;

    loadLanguage();

    if ( $fileExists = file_exists( PLUGINLIBRARY ) ) {
        ( $PL instanceof PluginLibrary ) or require_once PLUGINLIBRARY;
    }

    if ( !$doCheck ) {
        return false;
    }

    if ( !$fileExists || $PL->version < pluginLibraryRequirements()->version ) {
        flash_message(
            $lang->sprintf(
                $lang->ougc_pages_pl_required,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        admin_redirect( 'index.php?module=config-plugins' );
    }

    return true;
}

function addHooks( string $namespace ): true
{
    global $plugins;

    $namespaceLowercase = strtolower( $namespace );
    $definedUserFunctions = get_defined_functions()[ 'user' ];

    foreach ( $definedUserFunctions as $callable ) {
        $namespaceWithPrefixLength = strlen( $namespaceLowercase ) + 1;

        if ( substr( $callable, 0, $namespaceWithPrefixLength ) == $namespaceLowercase . '\\' ) {
            $hookName = substr_replace( $callable, '', 0, $namespaceWithPrefixLength );

            $priority = substr( $callable, -2 );

            if ( is_numeric( substr( $hookName, -2 ) ) ) {
                $hookName = substr( $hookName, 0, -2 );
            } else {
                $priority = 10;
            }

            $plugins->add_hook( $hookName, $callable, $priority );
        }
    }

    return true;
}

function runHooks( string $hookName ): bool
{
    global $plugins;

    $plugins->run_hooks( $hookName );

    return true;
}

function getSetting( string $settingKey = '' ): string
{
    global $mybb;

    $string = 'OUGC_PAGES_' . strtoupper( $settingKey );

    return defined( $string ) ? constant( $string ) : (string) $mybb->settings[ 'ougc_pages_' . $settingKey ];
}

function sanitizeIntegers( array $dataObject, bool $implodeResult = false ): array|string
{
    foreach ( $dataObject as $objectKey => &$objectValue ) {
        $objectValue = (int) $objectValue;
    }

    $dataObject = array_filter( $dataObject );

    if ( $implodeResult ) {
        $dataObject = implode( ',', $dataObject );
    }

    return $dataObject;
}

function getQueryLimit( int $newLimit = 0 ): int
{
    static $setLimit = QUERY_LIMIT;

    if ( $newLimit > 0 ) {
        $setLimit = $newLimit;
    }
    return $setLimit;
}

function setQueryLimit( int $newLimit ): int
{
    return getQueryLimit( $newLimit );
}

function getQueryStart( int $newStart = 0 ): int
{
    static $setLimit = QUERY_START;

    if ( $newStart > 0 ) {
        $setLimit = $newStart;
    }
    return $setLimit;
}

function setQueryStart( int $newStart ): int
{
    return getQueryStart( $newStart );
}

function url( string $newUrl = '' ): string
{
    static $setUrl = URL;

    if ( ( $newUrl = trim( $newUrl ) ) ) {
        $setUrl = $newUrl;
    }

    return $setUrl;
}

function urlSet( string $newUrl ): true
{
    url( $newUrl );

    return true;
}

function urlGet(): string
{
    return url();
}

function urlBuild( array $urlAppend = [], bool $fetchImportUrl = false ): string
{
    global $PL;

    if ( !is_object( $PL ) ) {
        $PL or require_once PLUGINLIBRARY;
    }

    if ( $fetchImportUrl === false ) {
        if ( $urlAppend && !is_array( $urlAppend ) ) {
            $urlAppend = explode( '=', $urlAppend );
            $urlAppend = [ $urlAppend[ 0 ] => $urlAppend[ 1 ] ];
        }
    }/* else {
        $urlAppend = $this->fetch_input_url( $fetchImportUrl );
    }*/

    return $PL->url_append( urlGet(), $urlAppend, '&amp;', true );
}

function parseUrl( string $urlString ): string
{
    global $settings;

    $urlString = \ougc_getpreview( $urlString );

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

    return \my_strtolower( $urlString );
}

function importGetUrl( string $importName, string $importUrl = '', int $pageID = 0 ): string
{
    global $db;

    if ( empty( $importUrl ) ) {
        $importUrl = $importName;
    }

    $importUrl = parseUrl( $importUrl );

    $importUrlEscaped = $db->escape_string( $importUrl );

    $dbQuery = $db->simple_select(
        'ougc_pages',
        'pid',
        "url='{$importUrlEscaped}' AND pid!='{$pageID}'",
        [ 'limit' => 1 ]
    );

    if ( $db->num_rows( $dbQuery ) ) {
        return importGetUrl( '', "{$importUrl} - {$pageID}" );
    }

    return $importUrl;
}

function cacheUpdate(): true
{
    global $db, $cache;

    $updateData = [
        'categories' => [],
        'pages' => [],
    ];

    $whereClause = [ "visible='1'" ];

    // Update categories
    $dbQuery = $db->simple_select(
        'ougc_pages_categories',
        '*',
        implode( ' AND ', $whereClause ),
        [ 'order_by' => 'disporder' ]
    );

    while ( $category = $db->fetch_array( $dbQuery ) ) {
        $updateData[ 'categories' ][ (int) $category[ 'cid' ] ] = [
            'name' => (string) $category[ 'name' ],
            'description' => (string) $category[ 'description' ],
            'url' => (string) $category[ 'url' ],
            'allowedGroups' => (string) $category[ 'allowedGroups' ],
            'breadcrumb' => (bool) $category[ 'breadcrumb' ],
            'wrapucp' => (bool) $category[ 'wrapucp' ],
            'navigation' => (bool) $category[ 'navigation' ]
        ];
    }

    $db->free_result( $dbQuery );

    if ( !empty( $updateData[ 'categories' ] ) ) {
        $categoriesIDs = implode( "', '", array_keys( $updateData[ 'categories' ] ) );

        $whereClause[] = "cid IN ('{$categoriesIDs}')";

        // Update pages
        $dbQuery = $db->simple_select(
            'ougc_pages',
            'pid, url',
            implode( ' AND ', $whereClause ),
            [ 'order_by' => 'disporder' ]
        );

        while ( $page = $db->fetch_array( $dbQuery ) ) {
            $updateData[ 'pages' ][ (string) $page[ 'url' ] ] = (int) $page[ 'pid' ];
        }

        $db->free_result( $dbQuery );
    }

    $cache->update( 'ougc_pages', $updateData );

    return true;
}

function redirect( string $redirectMessage = '', bool $isError = false ): never
{
    if ( defined( 'IN_ADMINCP' ) ) {
        if ( $redirectMessage ) {
            \flash_message( $redirectMessage, ( $isError ? 'error' : 'success' ) );
        }

        \admin_redirect( urlBuild() );
    } else {
        \redirect( urlBuild(), $redirectMessage );
    }

    exit;
}

function logAction( int $objectID ): true
{
    if ( $objectID ) {
        \log_admin_action( $objectID );
    }

    return true;
}

function multipageBuild( int $itemsCount, string $paginationUrl = ''/*, bool $checkUrl = false*/ ): string
{
    global $mybb, $multipage;

    /*if ( $checkUrl ) {
        $input = explode( '=', $params );
        if ( isset( $mybb->input[ $input[ 0 ] ] ) && $mybb->input[ $input[ 0 ] ] != $input[ 1 ] ) {
            $mybb->input[ 'page' ] = 0;
        }
    }*/

    if ( $mybb->get_input( 'page', 1 ) > 0 ) {
        if ( $mybb->get_input( 'page', 1 ) > ceil( $itemsCount / getQueryLimit() ) ) {
            $mybb->input[ 'page' ] = 1;
        } else {
            setQueryStart( ( $mybb->get_input( 'page', 1 ) - 1 ) * getQueryLimit() );
        }
    } else {
        $mybb->input[ 'page' ] = 1;
    }

    if ( defined( 'IN_ADMINCP' ) ) {
        return (string) \draw_admin_pagination( $mybb->get_input( 'page', 1 ), getQueryLimit(), $itemsCount, $paginationUrl );
    }

    return (string) \multipage( $itemsCount, getQueryLimit(), $mybb->get_input( 'page', 1 ), $paginationUrl );
}

function initExecute( int $pageID ): never
{
    global $mybb, $lang, $db, $plugins, $cache, $parser, $settings;
    global $templates, $headerinclude, $header, $theme, $footer;
    global $templatelist, $session, $maintimer, $permissions;
    global $ougc_pages, $category, $page, $plugins;

    runHooks( 'ougc_pages_execution_init' );

    if ( getSetting( 'disable_eval' ) ) {
        echo pageGetTemplate( $pageID );
    } else {
        eval( '?>' . pageGetTemplate( $pageID ) );
    }

    exit;
}

function initSession(): true
{
    global $session;

    if ( !isset( $session ) ) {
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

    if ( empty( $navbits ) ) {
        $navbits = [
            0 => [
                'name' => $mybb->settings[ 'bbname_orig' ],
                'url' => $mybb->settings[ 'bburl' ] . '/index.php'
            ]
        ];
    }

    /*if (
        defined( 'IN_ADMINCP' ) ||
        ( defined( THIS_SCRIPT ) && THIS_SCRIPT == 'pages.php' )
    ) {
        return false;
    }*/

    if ( isset( $templatelist ) ) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= 'ougcpages, ougcpages_wrapper, ougcpages_navigation, ougcpages_category_list_item, ougcpages_category_list, ougcpages_navigation_previous, ougcpages_navigation_next';

    // should be fixed as well
    if (
        strpos( $mybb->settings[ 'ougc_pages_seo_scheme' ], '?' ) !== false &&
        isset( $mybb->input[ 'page' ] ) &&
        empty( $mybb->input[ 'page' ] ) &&
        count( (array) $mybb->input ) > 1
    ) {
        foreach ( $mybb->input as $inputKey => $inputValue ) {
            if ( $inputKey == 'page' ) {
                $mybb->input[ 'page' ] = $inputKey; // we assume second input to be the page

                break;
            }
        }
    }

    $mybb->cache->read( 'ougc_pages' ); // TODO FIX THIS SHIT

    if ( $isPage = !empty( $mybb->input[ 'page' ] ) ) {
        if ( empty( $mybb->cache->cache[ 'ougc_pages' ][ 'pages' ][ $mybb->get_input( 'page' ) ] ) ) {
            pageStatusSet( PAGE_STATUS_INVALID );
        }
    }

    if ( $isPage ) {
        if ( !empty( $mybb->cache->cache[ 'ougc_pages' ][ 'pages' ][ $mybb->get_input( 'page' ) ] ) ) {
            if ( $pageData = pageGetByUrl( $mybb->get_input( 'page' ) ) ) {
                #$templatelist .= ', ougcpages_page'.$page['pid']; TODO

                if ( $categoryData = categoryGet( $pageData[ 'cid' ] ) ) {
                    if ( !$categoryData[ 'visible' ] ) {
                        categoryStatusSet( PAGE_STATUS_INVALID );
                    } else {
                        categoryCurrentSet( $categoryData[ 'cid' ] );
                    }
                    #$templatelist .= ', ougcpages_category'.$categoryData['cid']; TODO
                } else {
                    categoryStatusSet( PAGE_STATUS_INVALID );
                }
            } else {
                pageStatusSet( PAGE_STATUS_INVALID );
            }
        } else {
            pageStatusSet( PAGE_STATUS_INVALID );
        }
    } else if ( !empty( $mybb->input[ 'category' ] ) ) {
        // should be fixed as well
        if ( strpos( $mybb->settings[ 'ougc_pages_seo_scheme_categories' ], '?' ) !== false && empty( $mybb->input[ 'category' ] ) && count( (array) $mybb->input ) > 1 ) {
            $pick = null;
            foreach ( $mybb->input as $k => $v ) {
                if ( $k == 'category' ) {
                    $pick = true;
                    continue;
                }

                if ( $pick === true ) {
                    $mybb->input[ 'category' ] = $k; // we assume second input to be the category
                    break;
                }
            }
            unset( $pick );
        }

        if ( $categoryData = categoryGetByUrl( $mybb->get_input( 'category' ) ) ) {
            if ( !$categoryData[ 'visible' ] ) {
                categoryStatusSet( PAGE_STATUS_INVALID );
            } else {
                categoryCurrentSet( $categoryData[ 'cid' ] );
            }
            #$templatelist .= ', ougcpages_category'.$categoryData['cid']; TODO
        } else {
            categoryStatusSet( PAGE_STATUS_INVALID );
        }
    }

    if ( !empty( $categoryData ) ) {
        // Save three queries if no permission check is necessary
        if ( $categoryData[ 'allowedGroups' ] != '' ) {
            initSession();

            if ( !\is_member( $categoryData[ 'allowedGroups' ] ) ) {
                permissionStatusSet( PERMISSION_STATUS_DISALLOW );
            }
        }
    }

    if ( !empty( $pageData ) ) {
        if ( !$pageData[ 'wol' ] && !defined( 'NO_ONLINE' ) ) {
            define( 'NO_ONLINE', 1 );
        }

        // Save three queries if no permission check is necessary
        if ( permissionStatusSet() === PERMISSION_STATUS_ALLOW ) {
            if ( $pageData[ 'allowedGroups' ] != '' ) {
                initSession();

                if ( !is_member( $pageData[ 'allowedGroups' ] ) ) {
                    permissionStatusSet( PERMISSION_STATUS_DISALLOW );
                }
            }
        }

        if ( $pageData[ 'php' ] && permissionStatusSet() === PERMISSION_STATUS_ALLOW ) {
            executeStatusSet( $pageData[ 'pid' ] );

            switch ( (int) $pageData[ 'init' ] ) {
                case EXECUTION_HOOK_INIT:
                    initExecute( executeStatusGet() );
                    break;
                case EXECUTION_HOOK_GLOBAL_START:
                    executeHookSet( EXECUTION_HOOK_GLOBAL_START );
                    break;
                case EXECUTION_HOOK_GLOBAL_INTERMEDIATE:
                    executeHookSet( EXECUTION_HOOK_GLOBAL_INTERMEDIATE );
                    break;
                default:
                    executeHookSet( EXECUTION_HOOK_GLOBAL_END );
                    break;
            }
        }
    }

    return true;
}

function initShow(): never
{
    global $db, $ougc_pages, $lang, $templates, $mybb, $footer, $headerinclude, $header, $theme;
    //, $page, $category;

    // Load lang
    $ougc_pages->lang_load();

    if ( pageStatusGet() === PAGE_STATUS_INVALID ) {
        \error( $lang->ougc_pages_error_invalidpage );
    }

    if ( categoryStatusGet() === PAGE_STATUS_INVALID ) {
        error( $lang->ougc_pages_error_invalidçategory );
    }

    if ( !permissionStatusSet() === PERMISSION_STATUS_DISALLOW ) {
        error_no_permission();
    }

    $categoryData = categoryGet( categoryCurrentGet() );

    $pageData = pageGet( executeStatusGet() );

    // Load custom page language file if exists
    $lang->load( 'ougc_pages_' . $categoryData[ 'cid' ], false, true );

    if ( !empty( $pageData ) ) {
        $lang->load( 'ougc_pages_' . $pageData[ 'pid' ], false, true );
    }

    $categoryData[ 'name' ] = \htmlspecialchars_uni( $categoryData[ 'name' ] );

    if ( $categoryData[ 'wrapucp' ] ) {
        $lang->load( 'usercp' );

        if ( $mybb->user[ 'uid' ] && $mybb->usergroup[ 'canusercp' ] ) {
            \add_breadcrumb( $lang->nav_usercp, "usercp.php" );
        }
    }

    if ( $categoryData[ 'breadcrumb' ] ) {
        \add_breadcrumb( $categoryData[ 'name' ], categoryGetLink( $categoryData[ 'cid' ] ) );
    }

    $gids = explode( ',', $mybb->user[ 'additionalgroups' ] );
    $gids[] = $mybb->user[ 'usergroup' ];
    $gids = array_filter( array_unique( array_map( 'intval', $gids ) ) );

    $categoryData[ 'cid' ] = (int) $categoryData[ 'cid' ];

    $whereClause = [
        "visible='1'",
        "cid='{$categoryData[ 'cid' ]}'",
    ];

    $whereClauseGroups = [ "allowedGroups=''" ];

    switch ( $db->type ) {
        case 'pgsql':
        case 'sqlite':
            foreach ( $gids as $gid ) {
                $gid = (int) $gid;
                $whereClauseGroups[] = "','||allowedGroups||',' LIKE '%,{$gid},%'";
            }
            break;
        default:
            foreach ( $gids as $gid ) {
                $gid = (int) $gid;
                $whereClauseGroups[] = "CONCAT(',',allowedGroups,',') LIKE '%,{$gid},%'";
            }
            break;
    }

    $whereClauseGroups = implode( ' OR ', $whereClauseGroups );

    $whereClause[] = "({$whereClauseGroups})";

    /*$navigation = array('previous' => '', 'right' => 'next');*/

    if ( !empty( $pageData ) ) {
        $title = $pageData[ 'name' ] = htmlspecialchars_uni( $pageData[ 'name' ] );

        $description = $pageData[ 'description' ] = htmlspecialchars_uni( $pageData[ 'description' ] );

        add_breadcrumb( $pageData[ 'name' ], \OUGCPages\Core\pageGetLink( $pageData[ 'pid' ] ) );

        /*if($categoryData['navigation'])
        {
            implode( ' AND ', $whereClause ) .= 'AND php!=\'1\' AND disporder';
            $where = '<\''.(int)$pageData['disporder'].'\'';
            $query = $db->simple_select('ougc_pages', 'pid', implode( ' AND ', $whereClause ).$where, array('order_by' => 'disporder, name', 'limit' => 1));
            $previous_page_id = (int)$db->fetch_field($query, 'pid');

            if($previous_page_id)
            {
                $previous_link = \OUGCPages\Core\pageGetLink($previous_page_id);
                $navigation['previous'] = eval( $templates->render( 'ougcpages_navigation_previous' ) );
            }

            $where = '>\''.(int)$pageData['disporder'].'\'';
            $query = $db->simple_select('ougc_pages', 'pid', implode( ' AND ', $whereClause ).$where, array('order_by' => 'disporder, name', 'limit' => 1));
            $next_page_id = (int)$db->fetch_field($query, 'pid');

            if($next_page_id)
            {
                $next_link = \OUGCPages\Core\pageGetLink($next_page_id);
                $navigation['next'] = eval( $templates->render( 'ougcpages_navigation_next' ) );
            }
        }*/

        $templates->cache[ 'ougcpages_temporary_tmpl' ] = $pageData[ 'template' ];

        #TODO: Add "Las updated on DATELINE..." to page

        $content = eval( $templates->render( 'ougcpages_temporary_tmpl' ) );

        if ( $pageData[ 'wrapper' ] ) {
            $content = eval( $templates->render( 'ougcpages_wrapper' ) );
        }
    } else {
        $title = $categoryData[ 'name' ] = htmlspecialchars_uni( $categoryData[ 'name' ] );

        $description = $categoryData[ 'description' ] = htmlspecialchars_uni( $categoryData[ 'description' ] );

        $query = $db->simple_select(
            'ougc_pages',
            '*',
            implode( ' AND ', $whereClause ),
            [ 'order_by' => 'disporder' ]
        );

        $page_list = '';
        while ( $pageData = $db->fetch_array( $query ) ) {
            $pageData[ 'name' ] = htmlspecialchars_uni( $pageData[ 'name' ] );
            $page_link = \OUGCPages\Core\pageGetLink( $pageData[ 'pid' ] );

            $page_list .= eval( $templates->render( 'ougcpages_category_list_item' ) );
        }

        if ( !$page_list ) {
            $content = eval( $templates->render( 'ougcpages_category_list_empty' ) );
        } else {
            $content = eval( $templates->render( 'ougcpages_category_list' ) );
        }

        $content = eval( $templates->render( 'ougcpages_wrapper' ) );
    }

    /*if($categoryData['navigation'])
    {
        $content = eval( $templates->render( 'ougcpages_navigation' ) );
    }*/

    if ( $categoryData[ 'wrapucp' ] ) {
        global $usercpnav;

        require_once MYBB_ROOT . 'inc/functions_user.php';

        \usercp_menu();

        $content = eval( $templates->render( 'ougcpages_wrapper_ucp' ) );
    }

    $pageContent = eval( $templates->render( 'ougcpages' ) );

    \output_page( $pageContent );

    exit;
}

function permissionStatusGet( bool $setNewStatus = false, int $newStatus = PERMISSION_STATUS_ALLOW ): int
{
    static $setStatus = PERMISSION_STATUS_ALLOW;

    if ( $setNewStatus ) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function permissionStatusSet( int $newStatus = PERMISSION_STATUS_ALLOW ): int
{
    return permissionStatusGet( true, $newStatus );
}

function executeStatusGet( bool $setNewStatus = false, int $newStatus = EXECUTION_STATUS_DISABLED ): int
{
    static $setStatus = EXECUTION_STATUS_DISABLED;

    if ( $setNewStatus ) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function executeStatusSet( int $newStatus = EXECUTION_STATUS_DISABLED ): int
{
    return executeStatusGet( true, $newStatus );
}

function categoryCurrentSet( null|int $categoryID = null ): null|int
{
    static $currentCategoryID = null;

    if ( $categoryID !== null ) {
        $currentCategoryID = (int) $categoryID;
    }

    return $currentCategoryID;
}

function categoryCurrentGet(): int
{
    return categoryCurrentSet();
}

function executeHookGet( bool $setNewHook = false, bool $newHook = EXECUTION_HOOK_GLOBAL_END ): int
{
    static $setHook = EXECUTION_HOOK_GLOBAL_END;

    if ( $setNewHook ) {
        $setHook = $newHook;
    }

    return $setHook;
}

function executeHookSet( int $newHook = EXECUTION_HOOK_GLOBAL_END ): int
{
    return executeHookGet( true, $newHook );
}

function categoryStatusGet( bool $setNewStatus = false, int $newStatus = CATEGORY_STATUS_VALID ): int
{
    static $setStatus = CATEGORY_STATUS_VALID;

    if ( $setNewStatus ) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function categoryStatusSet( int $newStatus = CATEGORY_STATUS_VALID ): int
{
    return categoryStatusGet( true, $newStatus );
}

function categoryInsert( array $categoryData = [], int $categoryID = 0, bool $update = false ): int
{
    global $db;

    $insertData = [];

    foreach ( [ 'name', 'description', 'url', 'allowedGroups' ] as $columnKey ) {
        if ( isset( $categoryData[ $columnKey ] ) ) {
            $insertData[ $columnKey ] = $db->escape_string( $categoryData[ $columnKey ] );
        }
    }

    foreach ( [ 'disporder', 'visible', 'breadcrumb', 'wrapucp', 'navigation' ] as $columnKey ) {
        if ( isset( $categoryData[ $columnKey ] ) ) {
            $insertData[ $columnKey ] = (int) $categoryData[ $columnKey ];
        }
    }

    $insertID = $categoryID;

    if ( $insertData ) {
        global $plugins;

        if ( $update ) {
            $db->update_query( 'ougc_pages_categories', $insertData, "cid='{$categoryID}'" );

            runHooks( 'ouc_pages_update_category' );
        } else {
            $insertID = (int) $db->insert_query( 'ougc_pages_categories', $insertData );

            runHooks( 'ouc_pages_insert_category' );
        }
    }

    return $insertID;
}

function categoryUpdate( array $data = [], int $cid = 0 ): int
{
    return categoryInsert( $data, $cid, true );
}

function categoryDelete( int $categoryID ): int
{
    global $db;

    $db->delete_query( 'ougc_pages_categories', "cid='{$categoryID}'" );

    return $categoryID;
}

function categoryGet( int $cid, bool|string $url = false ): array
{
    global $cache;

    static $cacheObject = [];

    if ( !isset( $cacheObject[ $cid ] ) ) {
        global $db;
        $cacheObject[ $cid ] = [];

        $where = ( $url === false ? 'cid=\'' . $cid . '\'' : 'url=\'' . $db->escape_string( $url ) . '\'' );

        $query = $db->simple_select( 'ougc_pages_categories', '*', $where );
        $category = $db->fetch_array( $query );

        if ( isset( $category[ 'cid' ] ) ) {
            $cacheObject[ $cid ] = $category;
        }
    }

    return $cacheObject[ $cid ];
}

function categoryGetByUrl( bool|string $url ): array
{
    return categoryGet( 0, $url );
}

function categoryGetLink( int $cid ): string
{
    global $db, $settings;

    $query = $db->simple_select( 'ougc_pages_categories', 'url', 'cid=\'' . $cid . '\'' );
    $url = $db->fetch_field( $query, 'url' );

    if ( $settings[ 'ougc_pages_seo' ] && \my_strpos( $settings[ 'ougc_pages_seo_scheme_categories' ], '{url}' ) !== false ) {
        $url = str_replace( '{url}', $url, $settings[ 'ougc_pages_seo_scheme_categories' ] );
    } else {
        $url = 'pages.php?category=' . $url;
    }

    return $settings[ 'bburl' ] . '/' . \htmlspecialchars_uni( $url );
}

function categoryBuildLink( string $categoryName, int $categoryID ): string
{
    $categoryLink = categoryGetLink( $categoryID );

    $categoryName = \htmlspecialchars_uni( $categoryName );

    return "<a href=\"{$categoryLink}\">{$categoryName}</a>";
}

function categoryBuildSelect( string $name, array|int $selected = [], array $options = [] ): string
{
    global $db;

    is_array( $selected ) or $selected = [ $selected ];

    $select = '<select name="' . $name . '"';

    if ( isset( $options[ 'multiple' ] ) ) {
        $select .= ' multiple="multiple"';
    }

    if ( isset( $options[ 'class' ] ) ) {
        $select .= ' class="' . $options[ 'class' ] . '"';
    }

    if ( isset( $options[ 'id' ] ) ) {
        $select .= ' id="' . $options[ 'id' ] . '"';
    }

    if ( isset( $options[ 'size' ] ) ) {
        $select .= ' size="' . $options[ 'size' ] . '"';
    }

    $select .= '>';

    $query = $db->simple_select( 'ougc_pages_categories', 'cid, name', '', [ 'order_by' => 'disporder' ] );

    while ( $category = $db->fetch_array( $query ) ) {
        $s = '';
        if ( in_array( $category[ 'cid' ], $selected ) ) {
            $s = ' selected="selected"';
        }
        $select .= '<option value="' . $category[ 'cid' ] . '"' . $s . '>' . htmlspecialchars_uni( $category[ 'name' ] ) . '</option>';
    }

    $select .= '</select>';

    return $select;
}

function pageStatusGet( bool $setNewStatus = false, int $newStatus = PAGE_STATUS_VALID ): int
{
    static $setStatus = PAGE_STATUS_VALID;

    if ( $setNewStatus ) {
        $setStatus = $newStatus;
    }

    return $setStatus;
}

function pageStatusSet( int $newStatus = PAGE_STATUS_VALID ): int
{
    return pageStatusGet( true, $newStatus );
}

function pageInsert( array $pageData = [], int $pageID = 0, bool $update = false ): int
{
    global $db;

    $insertData = [];

    if ( !$update ) {
        foreach ( [ 'allowedGroups', 'template' ] as $columnKey ) {
            if ( !isset( $pageData[ $columnKey ] ) ) {
                $insertData[ $columnKey ] = '';
            }
        }

        if ( !isset( $pageData[ 'dateline' ] ) ) {
            $insertData[ 'dateline' ] = \TIME_NOW;
        }
    }

    foreach ( [ 'name', 'description', 'url', 'allowedGroups', 'template' ] as $columnKey ) {
        if ( isset( $pageData[ $columnKey ] ) ) {
            $insertData[ $columnKey ] = $db->escape_string( $pageData[ $columnKey ] );
        }
    }

    foreach ( [ 'cid', 'php', 'wol', 'disporder', 'visible', 'wrapper', 'init', 'dateline' ] as $columnKey ) {
        if ( isset( $pageData[ $columnKey ] ) ) {
            $insertData[ $columnKey ] = (int) $pageData[ $columnKey ];
        }
    }

    $insertID = $pageID;

    if ( $insertData ) {
        global $plugins;

        if ( $update ) {
            $db->update_query( 'ougc_pages', $insertData, 'pid=\'' . $insertID . '\'' );

            runHooks( 'ouc_pages_update_page' );
        } else {
            $insertID = (int) $db->insert_query( 'ougc_pages', $insertData );

            runHooks( 'ouc_pages_insert_page' );
        }
    }

    return $insertID;
}

function pageUpdate( array $data = [], int $pageID = 0 ): int
{
    return pageInsert( $data, $pageID, true );
}

function pageDelete( int $pageID ): int
{
    global $db;

    $db->delete_query( 'ougc_pages', "pid='{$pageID}'" );

    return $pageID;
}

function pageGet( int $pageID, bool|string $url = false ): array
{
    static $cacheObject = [];

    if ( !isset( $cacheObject[ $pageID ] ) ) {
        global $db;
        $cacheObject[ $pageID ] = [];

        $where = ( $url === false ? 'pid=\'' . (int) $pageID . '\'' : 'url=\'' . $db->escape_string( $url ) . '\'' );

        $query = $db->simple_select( 'ougc_pages', '*', $where );
        $page = $db->fetch_array( $query );

        if ( isset( $page[ 'pid' ] ) ) {
            $cacheObject[ $pageID ] = $page;
        }
    }

    return $cacheObject[ $pageID ];
}

function pageGetTemplate( int $pageID ): string
{
    $pageData = pageGet( $pageID );

    if ( !isset( $pageData[ 'template' ] ) ) {
        return '';
    }

    return $pageData[ 'template' ];
}

function pageGetByUrl( bool|string $url ): array
{
    return pageGet( 0, $url );
}

function pageGetLink( int $pageID ): string
{
    global $db, $settings;

    $query = $db->simple_select( 'ougc_pages', 'url', 'pid=\'' . $pageID . '\'' );
    $url = $db->fetch_field( $query, 'url' );

    if ( $settings[ 'ougc_pages_seo' ] && my_strpos( $settings[ 'ougc_pages_seo_scheme' ], '{url}' ) !== false ) {
        $url = str_replace( '{url}', $url, $settings[ 'ougc_pages_seo_scheme' ] );
    } else {
        $url = 'pages.php?page=' . $url;
    }

    return $settings[ 'bburl' ] . '/' . htmlspecialchars_uni( $url );
}

function pageBuildLink( string $pageName, int $pageID ): string
{
    $pageLink = pageGetLink( $pageID );

    $pageName = \htmlspecialchars_uni( $pageName );

    return "<a href=\"{$pageLink}\">{$pageName}</a>";
}