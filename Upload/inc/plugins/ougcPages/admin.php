<?php

/***************************************************************************
 *
 *    OUGC Pages Location (/inc/plugins/ougcPages/admin.php)
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

namespace OUGCPages\Admin;

function pluginInfo(): array
{
    global $lang;

    \OUGCPages\Core\loadLanguage();

    return [
        'name' => 'OUGC Pages',
        'description' => $lang->setting_group_ougc_pages_desc,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.33',
        'versioncode' => 1833,
        'compatibility' => '183*',
        'codename' => 'ougc_pages',
        'pl' => [
            'version' => 13,
            'url' => 'http://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function pluginActivate(): true
{
    global $PL, $lang, $cache, $db;

    \OUGCPages\Core\loadPluginLibrary();

    // Add settings
    $settingsContents = \file_get_contents( OUGC_PAGES_ROOT . '/settings.json' );

    $settingsData = \json_decode( $settingsContents, true );

    foreach ( $settingsData as $settingKey => &$settingData ) {
        $settingData[ 'title' ] = $lang->{"setting_ougc_pages_{$settingKey}"};
        $settingData[ 'description' ] = $lang->{"setting_ougc_pages_{$settingKey}_desc"};
    }

    $PL->settings( 'ougc_pages', $lang->setting_group_ougc_pages, $lang->setting_group_ougc_pages_desc, $settingsData );

    // Add templates
    $templatesDirIterator = new \DirectoryIterator( OUGC_PAGES_ROOT . '/templates' );

    $templates = [];

    foreach ( $templatesDirIterator as $template ) {
        if ( !$template->isFile() ) {
            continue;
        }

        $pathName = $template->getPathname();

        $pathInfo = \pathinfo( $pathName );

        if ( $pathInfo[ 'extension' ] === 'html' ) {
            $templates[ $pathInfo[ 'filename' ] ] = \file_get_contents( $pathName );
        }
    }

    if ( $templates ) {
        $PL->templates( 'ougcpages', 'OUGC Pages', $templates );
    }

    // Insert/update version into cache
    $plugins = (array) $cache->read( 'ougc_plugins' );

    if ( !$plugins ) {
        $plugins = [];
    }

    if ( !isset( $plugins[ 'pages' ] ) ) {
        $plugins[ 'pages' ] = pluginInfo()[ 'versioncode' ];
    }

    VerifyStylesheet();

    /*~*~* RUN UPDATES START *~*~*/

    if ( $plugins[ 'pages' ] <= 1819 ) {
        $db->update_query( 'ougc_pages', [ 'visible' => 0 ], "groups=''" );
        $db->update_query( 'ougc_pages_categories', [ 'visible' => '' ], "groups=''" );

        $db->update_query( 'ougc_pages', [ 'groups' => 0 ], "groups='-1'" );
        $db->update_query( 'ougc_pages_categories', [ 'groups' => '' ], "groups='-1'" );
    }

    if ( $plugins[ 'pages' ] <= 1833 ) {
        if ( $db->field_exists( 'groups', 'ougc_pages' ) ) {
            $db->rename_column(
                'ougc_pages',
                'groups',
                'allowedGroups',
                dbTables()[ 'ougc_pages' ][ 'allowedGroups' ]
            );
        }
        if ( $db->field_exists( 'groups', 'ougc_pages_categories' ) ) {
            $db->rename_column(
                'ougc_pages_categories',
                'groups',
                'allowedGroups',
                dbTables()[ 'ougc_pages_categories' ][ 'allowedGroups' ]
            );
        }
    }

    /*~*~* RUN UPDATES END *~*~*/

    dbVerifyTables();

    $plugins[ 'pages' ] = pluginInfo()[ 'versioncode' ];

    $cache->update( 'ougc_plugins', $plugins );

    // Update administrator permissions
    change_admin_permission( 'config', 'ougc_pages' );

    return true;
}

function pluginDeactivate(): true
{
    \OUGCPages\Core\loadPluginLibrary();

    // Update administrator permissions
    change_admin_permission( 'config', 'ougc_pages', 0 );

    return true;
}

function pluginIsInstalled(): bool
{
    global $db;

    static $pluginIsInstalled = null;

    if ( $pluginIsInstalled === null ) {
        foreach ( dbTables() as $table => $fields ) {
            $pluginIsInstalled = (bool) $db->table_exists( $table );

            break;
        }
    }

    return $pluginIsInstalled;
}

function pluginUninstall(): true
{
    global $db, $PL, $cache;

    \OUGCPages\Core\loadPluginLibrary();

    // Drop DB entries
    foreach ( dbTables() as $name => $table ) {
        $db->drop_table( $name );
    }

    verifyStylesheet( true );

    $PL->cache_delete( 'ougc_pages' );
    $PL->settings_delete( 'ougc_pages' );
    $PL->templates_delete( 'ougcpages' );

    // Delete version from cache
    $plugins = (array) $cache->read( 'ougc_plugins' );

    if ( isset( $plugins[ 'pages' ] ) ) {
        unset( $plugins[ 'pages' ] );
    }

    if ( !empty( $plugins ) ) {
        $cache->update( 'ougc_plugins', $plugins );
    } else {
        $PL->cache_delete( 'ougc_plugins' );
    }

    // Remove administrator permissions
    change_admin_permission( 'config', 'ougc_pages', -1 );

    return true;
}


function dbVerifyTables(): true
{
    global $db;

    $collation = $db->build_create_table_collation();

    foreach ( dbTables() as $table => $fields ) {
        if ( $db->table_exists( $table ) ) {
            foreach ( $fields as $field => $definition ) {
                if ( $field == 'primary_key' || $field == 'unique_key' ) {
                    continue;
                }

                if ( $db->field_exists( $field, $table ) ) {
                    $db->modify_column( $table, "`{$field}`", $definition );
                } else {
                    $db->add_column( $table, $field, $definition );
                }
            }
        } else {
            $query = "CREATE TABLE IF NOT EXISTS `{$db->table_prefix}{$table}` (";

            foreach ( $fields as $field => $definition ) {
                if ( $field == 'primary_key' ) {
                    $query .= "PRIMARY KEY (`{$definition}`)";
                } else if ( $field != 'unique_key' ) {
                    $query .= "`{$field}` {$definition},";
                }
            }

            $query .= ") ENGINE=MyISAM{$collation};";

            $db->write_query( $query );
        }
    }

    dbVerifyIndexes();

    return true;
}

function dbVerifyIndexes(): true
{
    global $db;

    foreach ( dbTables() as $table => $fields ) {
        if ( !$db->table_exists( $table ) ) {
            continue;
        }

        if ( isset( $fields[ 'unique_key' ] ) ) {
            foreach ( $fields[ 'unique_key' ] as $k => $v ) {
                if ( $db->index_exists( $table, $k ) ) {
                    continue;
                }

                $db->write_query( "ALTER TABLE {$db->table_prefix}{$table} ADD UNIQUE KEY {$k} ({$v})" );
            }
        }
    }

    return true;
}


function dbTables(): array
{
    return [
        'ougc_pages' => [
            'pid' => "int UNSIGNED NOT NULL AUTO_INCREMENT",
            'cid' => "int UNSIGNED NOT NULL DEFAULT '0'",
            'name' => "varchar(100) NOT NULL DEFAULT ''",
            'description' => "varchar(255) NOT NULL DEFAULT ''",
            'url' => "varchar(100) NOT NULL DEFAULT ''",
            'allowedGroups' => "varchar(100) NOT NULL DEFAULT ''",
            'php' => "tinyint(1) NOT NULL DEFAULT '0'",
            'wol' => "tinyint(1) NOT NULL DEFAULT '1'",
            'disporder' => "tinyint(5) NOT NULL DEFAULT '0'",
            'visible' => "tinyint(1) NOT NULL DEFAULT '1'",
            'navigation' => "tinyint(1) NOT NULL DEFAULT '1'", // TODO
            'menuitem' => "tinyint(1) NOT NULL DEFAULT '1'", // TODO
            'wrapper' => "tinyint(1) NOT NULL DEFAULT '1'",
            'init' => "tinyint(1) NOT NULL DEFAULT '1'",
            'template' => "MEDIUMTEXT NOT NULL",
            'dateline' => "int(10) NOT NULL DEFAULT '0'",
            'primary_key' => "pid",
            'unique_key' => [ 'url' => 'url' ]
        ],
        'ougc_pages_categories' => [
            'cid' => "int UNSIGNED NOT NULL AUTO_INCREMENT",
            'name' => "varchar(100) NOT NULL DEFAULT ''",
            'description' => "varchar(255) NOT NULL DEFAULT ''",
            'url' => "varchar(100) NOT NULL DEFAULT ''",
            'allowedGroups' => "text NOT NULL",
            'disporder' => "tinyint(5) NOT NULL DEFAULT '0'",
            'visible' => "tinyint(1) NOT NULL DEFAULT '1'",
            'breadcrumb' => "tinyint(1) NOT NULL DEFAULT '1'",
            'navigation' => "tinyint(1) NOT NULL DEFAULT '1'",
            'menuitem' => "tinyint(1) NOT NULL DEFAULT '1'",
            'classicTemplate' => "tinyint(1) NOT NULL DEFAULT '1'", // TODO
            'wrapucp' => "tinyint(1) NOT NULL DEFAULT '0'",
            'primary_key' => "cid",
            'unique_key' => [ 'url' => 'url' ]
        ]
    ];
}

function verifyStylesheet( $removeStylesheet = false ): true
{
    global $db;

    $dbQuery = $db->simple_select(
        'themestylesheets',
        'sid, attachedto',
        "name='usercp.css' AND tid= '1'"
    );

    $updateResult = false;

    while ( $stylesheet = $db->fetch_array( $dbQuery ) ) {
        $sheetID = (int) $stylesheet[ 'sid' ];

        if ( !$removeStylesheet && my_strpos( $stylesheet[ 'attachedto' ], '|pages.php' ) === false ) {
            $db->update_query( 'themestylesheets', [
                'attachedto' => $stylesheet[ 'attachedto' ] . '|pages.php',
                'lastmodified' => TIME_NOW
            ], "sid = '{$sheetID}'" );
            $updateResult = true;
        }

        if ( $removeStylesheet && my_strpos( $stylesheet[ 'attachedto' ], '|pages.php' ) !== false ) {
            $db->update_query( 'themestylesheets', [
                'attachedto' => str_replace( '|pages.php', '', $stylesheet[ 'attachedto' ] ),
                'lastmodified' => TIME_NOW
            ], "sid = '{$sheetID}'" );
            $updateResult = true;
        }
    }

    if ( $updateResult ) {
        $dbQuery = $db->simple_select( 'themes', 'tid' );

        require_once MYBB_ADMIN_DIR . 'inc/functions_themes.php';

        while ( $tid = $db->fetch_field( $dbQuery, 'tid' ) ) {
            update_theme_stylesheet_list( $tid );
        }
    }

    return true;
}