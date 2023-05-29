<?php

/***************************************************************************
 *
 *    OUGC Hide Administrator Location (/inc/plugins/ougcPages/adminHooks.php)
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

namespace OUGCPages\AdminHooks;

function admin_config_plugins_deactivate(): bool
{
    global $mybb, $page;

    if (
        $mybb->get_input('action') != 'deactivate' ||
        $mybb->get_input('plugin') != 'ougc_pages' ||
        !$mybb->get_input('uninstall', \MyBB::INPUT_INT)
    ) {
        return false;
    }

    if ($mybb->request_method != 'post') {
        $page->output_confirm_action(
            'index.php?module=config-plugins&amp;action=deactivate&amp;uninstall=1&amp;plugin=ougc_pages'
        );
    }

    if ($mybb->get_input('no')) {
        \admin_redirect('index.php?module=config-plugins');
    }

    return true;
}

function admin_config_menu(&$subMenu): array
{
    global $lang;

    \OUGCPages\Core\loadLanguage();

    $subMenu[] = [
        'id' => 'ougc_pages',
        'title' => $lang->ougc_pages_manage,
        'link' => 'index.php?module=config-ougc_pages'
    ];

    return $subMenu;
}

function admin_config_action_handler(&$handlerActions): array
{
    global $lang;

    \OUGCPages\Core\loadLanguage();

    $handlerActions['ougc_pages'] = [
        'active' => 'ougc_pages',
        'file' => 'manage.php'
    ];

    return $handlerActions;
}

function admin_load(): void
{
    \OUGCPages\Admin\pluginActivate();

    global $modules_dir, $run_module, $action_file, $run_module, $page, $modules_dir_backup, $run_module_backup, $action_file_backup;

    if ($run_module != 'config' || $page->active_action != 'ougc_pages') {
        return;
    }

    $modules_dir_backup = $modules_dir;

    $run_module_backup = $run_module;

    $action_file_backup = $action_file;

    $modules_dir = OUGC_PAGES_ROOT;

    $run_module = 'admin';

    $action_file = 'module.php';
}

function admin_config_permissions(&$permissionActions): array
{
    global $lang;

    \OUGCPages\Core\loadLanguage();

    $permissionActions['ougc_pages'] = $lang->ougc_pages_config_permissions;

    return $permissionActions;
}

function admin_page_output_header(): true
{
    global $cache;

    $plugins = $cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    if (!isset($plugins['pages'])) {
        $plugins['pages'] = \OUGCPages\Admin\pluginInfo()['versioncode'];
    }

    if (\OUGCPages\Admin\pluginInfo()['versioncode'] != $plugins['pages']) {
        global $page, $lang;

        \OUGCPages\Core\loadLanguage();

        $page->extra_messages['ougc_pages'] = [
            'message' => $lang->ougc_pages_error_update,
            'type' => 'error'
        ];
    }

    return true;
}

function admin_config_settings_start(): true
{
    return \OUGCPages\Core\loadLanguage();
}

function admin_config_settings_change(): true
{
    return \OUGCPages\Core\loadLanguage();
}