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

function fetch_wol_activity_end( &$activityObjects ): array
{
    if ( $activityObjects[ 'activity' ] != 'unknown' ) {
        return $activityObjects;
    }

    if ( my_strpos( $activityObjects[ 'location' ], 'pages.php' ) === false ) {
        return $activityObjects;
    }

    $activityObjects[ 'activity' ] = 'ougc_pages';

    return $activityObjects;
}

function build_friendly_wol_location_end( &$locationObjetcs ): array|bool
{
    global $ougc_pages, $lang, $settings;
    $ougc_pages->lang_load();

    if ( $locationObjetcs[ 'user_activity' ][ 'activity' ] == 'ougc_pages' ) {
        global $cache;

        $pagecache = $cache->read( 'ougc_pages' );

        $location = parse_url( $locationObjetcs[ 'user_activity' ][ 'location' ] );
        $location[ 'query' ] = html_entity_decode( $location[ 'query' ] );
        $location[ 'query' ] = explode( '&', (string) $location[ 'query' ] );

        if ( empty( $location[ 'query' ] ) ) {
            return false;
        }

        foreach ( $location[ 'query' ] as $query ) {
            $param = explode( '=', $query );

            $type = $param[ 0 ];

            if ( $type == 'page' || $type == 'category' ) {
                $url = $param[ 1 ];
            }
        }

        if ( $type == 'page' && !empty( $pagecache[ 'pages' ][ $url ] ) ) {
            $page = $ougc_pages->get_page( $pagecache[ 'pages' ][ $url ] );

            if ( !$page[ 'wol' ] ) {
                $locationObjetcs[ 'user_activity' ][ 'location' ] = '/';
                return $locationObjetcs;
            }

            $locationObjetcs[ 'location_name' ] = $lang->sprintf( $lang->ougc_pages_wol, \OUGCPages\Core\pageGetLink( $pagecache[ 'pages' ][ $url ] ), htmlspecialchars_uni( $page[ 'name' ] ) );
        }

        if ( $type == 'category' ) {
            $category = null;
            foreach ( $pagecache[ 'categories' ] as $cid => $cat ) {
                if ( $cat[ 'url' ] == $url ) {
                    $category = $cat;
                    break;
                }
            }

            if ( $category !== null ) {
                $locationObjetcs[ 'location_name' ] = $lang->sprintf( $lang->ougc_pages_wol_cat, $ougc_pages->get_category_link( $cid ), htmlspecialchars_uni( $category[ 'name' ] ) );
            }
        }
    }

    return $locationObjetcs;
}

function usercp_menu40(): bool
{
    global $cache, $db, $ougc_pages, $templates, $mybb, $usercpmenu, $collapsed, $theme, $collapsedimg, $collapsed, $collapse;

    $pages_cache = $cache->read( 'ougc_pages' );

    if ( empty( $pages_cache[ 'categories' ] ) ) {
        return false;
    }

    foreach ( $pages_cache[ 'categories' ] as $cid => $category ) {
        if ( !$category[ 'wrapucp' ] ) {
            continue;
        }

        $gids = explode( ',', $mybb->user[ 'additionalgroups' ] );
        $gids[] = $mybb->user[ 'usergroup' ];
        $gids = array_filter( array_unique( array_map( 'intval', $gids ) ) );

        $cid = (int) $cid;

        $whereClause = [
            "visible='1'",
            "cid='{$cid}'",
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

        $query = $db->simple_select(
            'ougc_pages',
            '*',
            implode( ' AND ', $whereClause ),
            [ 'order_by' => 'disporder' ]
        );

        $navs = '';

        while ( $page = $db->fetch_array( $query ) ) {
            $page[ 'name' ] = \htmlspecialchars_uni( $page[ 'name' ] );
            $page_link = \OUGCPages\Core\pageGetLink( $page[ 'pid' ] );

            $navs .= eval( $templates->render( 'ougcpages_wrapper_ucp_nav_item' ) );
        }

        if ( !$navs ) {
            continue;
        }

        $category[ 'name' ] = htmlspecialchars_uni( $category[ 'name' ] );

        $collapse_id = 'usercpougcpages' . $cid;

        $collapse || $collapse = [];

        $expaltext = ( in_array( $collapse_id, $collapse ) ) ? "[+]" : "[-]";

        if ( !isset( $collapsedimg[ $collapse_id ] ) ) {
            $collapsedimg[ $collapse_id ] = '';
        }

        if ( !isset( $collapsed[ $collapse_id . '_e' ] ) ) {
            $collapsed[ $collapse_id . '_e' ] = '';
        }

        $img = $collapsedimg[ $collapse_id ];
        $_e = $collapsed[ $collapse_id . '_e' ];

        $usercpmenu .= eval( $templates->render( 'ougcpages_wrapper_ucp_nav' ) );
    }

    return true;
}

function global_start(): true
{
    if (
        \OUGCPages\Core\executeStatusGet() !== \OUGCPages\Core\EXECUTION_STATUS_DISABLED &&
        \OUGCPages\Core\executeHookGet() === \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_START
    ) {
        \OUGCPages\Core\initExecute( \OUGCPages\Core\executeStatusGet() );
    }

    return true;
}

function global_intermediate(): true
{
    if (
        \OUGCPages\Core\executeStatusGet() !== \OUGCPages\Core\EXECUTION_STATUS_DISABLED &&
        \OUGCPages\Core\executeHookGet() === \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_INTERMEDIATE
    ) {
        \OUGCPages\Core\initExecute( \OUGCPages\Core\executeStatusGet() );
    }

    return true;
}

function global_end(): true
{
    if (
        \OUGCPages\Core\executeStatusGet() !== \OUGCPages\Core\EXECUTION_STATUS_DISABLED &&
        \OUGCPages\Core\executeHookGet() === \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_END
    ) {
        \OUGCPages\Core\initExecute( \OUGCPages\Core\executeStatusGet() );
    }

    return true;
}