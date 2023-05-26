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

$templatelist .= 'ougcpages_category_list_item, ougcpages_category_list, ougcpages_wrapper, usercp_nav_messenger, usercp_nav_messenger_tracking, usercp_nav_messenger_compose, usercp_nav_messenger_folder, usercp_nav_changename, usercp_nav_editsignature, usercp_nav_profile, usercp_nav_attachments, usercp_nav_misc, ougcpages_wrapper_ucp_nav_item, ougcpages_wrapper_ucp_nav, usercp_nav_home, usercp_nav, ougcpages_wrapper_ucp, ougcpages';

\OUGCPages\Core\initRun();

require_once $workingDirectory . '/global.php';

\OUGCPages\Core\runHooks('ougc_pages_start');

if (\OUGCPages\Core\executeHookCheck(\OUGCPages\Core\EXECUTION_HOOK_CORE_START)) {
    \OUGCPages\Core\initShow();
}

error_no_permission();