<?php

/***************************************************************************
 *
 *    OUGC Pages (/inc/plugins/ougcPages/class.php)
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

// Our awesome class is deprecated, to be removed later on
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

        //$this->query_limit = isset($limit) ? (int) $limit : (int) $settings['ougc_pages_perpage'];
    }

    // Loads language strings
    function lang_load(): true
    {
        return \OUGCPages\Core\loadLanguage();
    }

    // Clean input
    function clean_ints( array|string $dataObject, $implodeResult = false ): string
    {
        die( 'clean_ints' );
        return \OUGCPages\Core\sanitizeIntegers( $dataObject, $implodeResult );
    }

    // List of tables
    function _db_tables(): array
    {
        return \OUGCPages\Admin\dbTables();
    }

    // List of columns
    function _db_columns(): array
    {
        die( '_db_columns' );
        return [];
    }

    // Verify DB indexes
    function _db_verify_indexes(): true
    {
        die( '_db_verify_indexes' );
        return \OUGCPages\Admin\dbVerifyIndexes();
    }

    // Verify DB tables
    function _db_verify_tables(): true
    {
        die( '_db_verify_tables' );
        return \OUGCPages\Admin\dbVerifyTables();
    }

    // Verify DB columns
    function _db_verify_columns(): true
    {
        die( '_db_verify_columns' );
        return true;
    }

    // Edit the master template to add our page to the core style sheet only
    function _verify_stylesheet( bool $removeStylesheet = false ): true
    {
        die( '_verify_stylesheet' );
        return \OUGCPages\Admin\verifyStylesheet( $removeStylesheet );
    }

    function _remove_from_stylesheet(): true
    {
        die( '_remove_from_stylesheet' );
        return \OUGCPages\Admin\verifyStylesheet( true );
    }

    // Update pages cache
    function update_cache(): true
    {
        die( 'update_cache' );
        return \OUGCPages\Core\cacheUpdate();
    }

    // Set url
    function set_url( string $url ): true
    {
        die( 'set_url' );
        return \OUGCPages\Core\urlSet( $url );
    }

    // Build an url parameter
    function build_url( array $urlAppend = [], bool $fetchImportUrl = false ): string
    {
        die( 'build_url' );
        return \OUGCPages\Core\urlBuild( $urlAppend, $fetchImportUrl );
    }

    // Cleans the unique URL
    // Thanks Google SEO!
    function clean_url( string $urlString ): string
    {
        die( 'clean_url' );
        return \OUGCPages\Core\parseUrl( $urlString );
    }

    // Get an unique URL for import process
    function get_import_url( string $name, string $url = '', int $pageID = 0 ): string
    {
        die( 'get_import_url' );
        return \OUGCPages\Core\importGetUrl( $name, $url, $pageID );
    }

    // Create the user session
    function init_session(): true
    {
        die( 'init_session' );
        return \OUGCPages\Core\initSession();
    }

    // Redirect admin help function
    function redirect( string $message = '', bool $error = false ): never
    {
        die( 'redirect' );
        \OUGCPages\Core\redirect( $message, $error );
    }

    // Log admin action
    function log_action( int $objectID = 0 ): true
    {
        die( 'log_action' );
        return \OUGCPages\Core\logAction( $objectID );
    }

    // Build a multipage.
    function build_multipage( int $count, string $url = '', bool $check = false ): string
    {
        die( 'build_multipage' );
        return \OUGCPages\Core\multipageBuild( $count, $url, $check );
    }

    // Get a category from the DB
    function get_category( int $cid, bool|string $url = false ): array
    {
        die( 'get_category' );
        return \OUGCPages\Core\categoryGet( $cid, $url );
    }

    // Get PID by url input
    function get_category_by_url( bool|string $url ): array
    {
        die( 'get_category_by_url' );
        return \OUGCPages\Core\categoryGetByUrl( $url );
    }

    // Get the category link.
    function get_category_link( int $cid ): string
    {
        die( 'get_category_link' );
        return \OUGCPages\Core\categoryGetLink( $cid );
    }

    // Build the page link.
    function build_category_link( string $categoryName, int $categoryID ): string
    {
        die( 'build_category_link' );
        return \OUGCPages\Core\categoryBuildLink( $categoryName, $categoryID );
    }

    // Get a page from the DB
    function get_page( int $pid, bool|string $url = false ): array
    {
        die( 'get_page' );
        return \OUGCPages\Core\pageGet( $pid, $url );
    }

    // Get PID by url input
    function get_page_by_url( string $url ): array
    {
        die( 'get_page_by_url' );
        return \OUGCPages\Core\pageGetByUrl( $url );
    }

    // Get the page link.
    function get_page_link( int $pid ): string
    {
        die( 'get_page_link' );
        return \OUGCPages\Core\pageGetLink( $pid );
    }

    // Build the category link.
    function build_page_link( string $name, int $pid ): string
    {
        die( 'build_page_link' );
        return \OUGCPages\Core\pageBuildLink( $name, $pid );
    }

    // Insert a new page to the DB
    function insert_page( array $data = [], bool $update = false, int $pid = 0 ): int
    {
        die( 'insert_page' );
        return \OUGCPages\Core\pageInsert( $data, $pid, $update );
    }

    // Update specific page.
    function update_page( array $data = [], int $pid = 0 ): int
    {
        die( 'update_page' );
        return \OUGCPages\Core\pageUpdate( $data, $pid );
    }

    // Delete page from DB
    function delete_page( int $pid ): int
    {
        die( 'delete_page' );
        return \OUGCPages\Core\pageDelete( $pid );
    }

    // Insert a new category to the DB
    function insert_category( array $data = [], bool $update = false, int $cid = 0 ): int
    {
        die( 'insert_category' );
        return \OUGCPages\Core\categoryInsert( $data, $cid, $update );
    }

    // Update specific category
    function update_category( array $data = [], int $cid = 0 ): int
    {
        die( 'update_category' );
        return \OUGCPages\Core\categoryUpdate( $data, $cid, true );
    }

    // Delete category from DB
    function delete_category( int $cid ): int
    {
        die( 'delete_category' );
        return \OUGCPages\Core\categoryDelete( $cid );
    }

    // Generate a category selection box.
    function generate_category_select( string $name, array|int $selected = [], array $options = [] ): string
    {
        die( 'generate_category_select' );
        return \OUGCPages\Core\categoryBuildSelect( $name, $selected, $options );
    }
}