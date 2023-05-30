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

const FIELDS_DATA_CATEGORIES = [
    'cid' => [
        'type' => 'INT',
        'unsigned' => true,
        'auto_increment' => true,
        'primary_key' => true
    ],
    'name' => [
        'type' => 'VARCHAR',
        'formType' => 'textBox',
        'size' => 100,
        'cache' => true,
        'required' => true
    ],
    'description' => [
        'type' => 'VARCHAR',
        'formType' => 'textBox',
        'size' => 255,
        'required' => true
    ],
    'url' => [
        'type' => 'VARCHAR',
        'formType' => 'textBox',
        'size' => 100,
        'unique' => true,
        'cache' => true,
        'required' => true
    ],
    'allowedGroups' => [
        'type' => 'VARCHAR',
        'formType' => 'groupSelect',
        'size' => 255,
        'cache' => true
    ],
    'disporder' => [
        'type' => 'SMALLINT',
        'unsigned' => true,
        'default' => 0
    ],
    'visible' => [
        'type' => 'TINYINT',
        'unsigned' => true,
        'default' => 1
    ],
    'breadcrumb' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 1
    ],
    'displayNavigation' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 1
    ],
    'buildMenu' => [
        'type' => 'TINYINT',
        //'formType' => 'basicSelect',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 1,
        'cache' => true
    ],
    'wrapucp' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 0,
        'cache' => true
    ]
];

const FIELDS_DATA_PAGES = [
    'pid' => [
        'type' => 'INT',
        'unsigned' => true,
        'auto_increment' => true,
        'primary_key' => true
    ],
    'cid' => [
        'type' => 'INT',
        'formType' => 'basicSelect',
        'unsigned' => true,
        'cache' => true
    ],
    'name' => [
        'type' => 'VARCHAR',
        'formType' => 'textBox',
        'size' => 100,
        'cache' => true,
        'required' => true
    ],
    'description' => [
        'type' => 'VARCHAR',
        'formType' => 'textBox',
        'size' => 255,
        'required' => true
    ],
    'url' => [
        'type' => 'VARCHAR',
        'formType' => 'textBox',
        'size' => 100,
        'unique' => true,
        'cache' => true,
        'required' => true
    ],
    'allowedGroups' => [
        'type' => 'VARCHAR',
        'formType' => 'groupSelect',
        'size' => 255,
        'cache' => true
    ],
    'disporder' => [
        'type' => 'SMALLINT',
        'unsigned' => true,
        'default' => 0
    ],
    'visible' => [
        'type' => 'TINYINT',
        'unsigned' => true,
        'default' => 1
    ],
    'menuItem' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 1,
        'cache' => true
    ],
    'wrapper' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 1
    ],
    'wol' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 1
    ],
    'php' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 0
    ],
    'classicTemplate' => [
        'type' => 'TINYINT',
        'formType' => 'yesNo',
        'unsigned' => true,
        'default' => 0
    ],
    'init' => [
        'type' => 'TINYINT',
        'formType' => 'basicSelect',
        'unsigned' => true,
        'default' => \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_END,
        'cache' => true
    ],
    'template' => [
        'type' => 'MEDIUMTEXT',
        'formType' => 'textArea',
        'null' => true
    ],
    'dateline' => [
        'type' => 'INT',
        'unsigned' => true,
        'default' => 0
    ]
];

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

function pluginActivate(): void
{
    global $PL, $lang, $cache, $db;

    \OUGCPages\Core\loadPluginLibrary();

    // Add settings
    $settingsContents = \file_get_contents(OUGC_PAGES_ROOT . '/settings.json');

    $settingsData = \json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougc_pages_{$settingKey}"})) {
            continue;
        }

        $settingData['title'] = $lang->{"setting_ougc_pages_{$settingKey}"};
        $settingData['description'] = $lang->{"setting_ougc_pages_{$settingKey}_desc"};
    }

    $PL->settings('ougc_pages', $lang->setting_group_ougc_pages, $lang->setting_group_ougc_pages_desc, $settingsData);

    // Add templates
    $templatesDirIterator = new \DirectoryIterator(OUGC_PAGES_ROOT . '/templates');

    $templates = [];

    foreach ($templatesDirIterator as $template) {
        if (!$template->isFile()) {
            continue;
        }

        $pathName = $template->getPathname();

        $pathInfo = \pathinfo($pathName);

        if ($pathInfo['extension'] === 'html') {
            $templates[$pathInfo['filename']] = \file_get_contents($pathName);
        }
    }

    if ($templates) {
        $PL->templates('ougcpages', 'OUGC Pages', $templates);
    }

    // Insert/update version into cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (!$plugins) {
        $plugins = [];
    }

    if (!isset($plugins['pages'])) {
        $plugins['pages'] = pluginInfo()['versioncode'];
    }

    verifyStylesheet();

    /*~*~* RUN UPDATES START *~*~*/

    if ($plugins['pages'] <= 1819) {
        $db->update_query('ougc_pages', ['visible' => 0], "groups=''");
        $db->update_query('ougc_pages_categories', ['visible' => ''], "groups=''");

        $db->update_query('ougc_pages', ['groups' => 0], "groups='-1'");
        $db->update_query('ougc_pages_categories', ['groups' => ''], "groups='-1'");
    }

    if ($plugins['pages'] <= 1833) {
        if ($db->field_exists('groups', 'ougc_pages')) {
            $db->rename_column(
                'ougc_pages',
                'groups',
                'allowedGroups',
                dbTables()['ougc_pages']['allowedGroups']
            );

            $db->update_query('ougc_pages', ['allowedGroups' => -1], "allowedGroups=''");
        }
        if ($db->field_exists('groups', 'ougc_pages_categories')) {
            $db->rename_column(
                'ougc_pages_categories',
                'groups',
                'allowedGroups',
                dbTables()['ougc_pages_categories']['allowedGroups']
            );

            $db->update_query('ougc_pages_categories', ['allowedGroups' => -1], "allowedGroups=''");
        }
    }

    /*~*~* RUN UPDATES END *~*~*/

    dbVerifyTables();

    $plugins['pages'] = pluginInfo()['versioncode'];

    $cache->update('ougc_plugins', $plugins);

    // Update administrator permissions
    \change_admin_permission('config', 'ougc_pages');

    \OUGCPages\Core\cacheUpdate();
}

function pluginDeactivate(): void
{
    \OUGCPages\Core\loadPluginLibrary();

    // Update administrator permissions
    \change_admin_permission('config', 'ougc_pages', 0);
}

function pluginIsInstalled(): bool
{
    global $db;

    static $pluginIsInstalled = null;

    if ($pluginIsInstalled === null) {
        foreach (dbTables() as $table => $fields) {
            $pluginIsInstalled = (bool)$db->table_exists($table);

            break;
        }
    }

    return $pluginIsInstalled;
}

function pluginUninstall(): void
{
    global $db, $PL, $cache;

    \OUGCPages\Core\loadPluginLibrary();

    // Drop DB entries
    foreach (dbTables() as $name => $table) {
        $db->drop_table($name);
    }

    verifyStylesheet(true);

    $PL->cache_delete('ougc_pages');
    $PL->settings_delete('ougc_pages');
    $PL->templates_delete('ougcpages');

    // Delete version from cache
    $plugins = (array)$cache->read('ougc_plugins');

    if (isset($plugins['pages'])) {
        unset($plugins['pages']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $PL->cache_delete('ougc_plugins');
    }

    // Remove administrator permissions
    \change_admin_permission('config', 'ougc_pages', -1);
}


function dbVerifyTables(): void
{
    global $db;

    $collation = $db->build_create_table_collation();

    foreach (dbTables() as $table => $fields) {
        if ($db->table_exists($table)) {
            foreach ($fields as $field => $definition) {
                if ($field == 'primary_key' || $field == 'unique_key') {
                    continue;
                }

                if ($db->field_exists($field, $table)) {
                    $db->modify_column($table, "`{$field}`", $definition);
                } else {
                    $db->add_column($table, $field, $definition);
                }
            }
        } else {
            $query = "CREATE TABLE IF NOT EXISTS `{$db->table_prefix}{$table}` (";

            foreach ($fields as $field => $definition) {
                if ($field == 'primary_key') {
                    $query .= "PRIMARY KEY (`{$definition}`)";
                } else if ($field != 'unique_key') {
                    $query .= "`{$field}` {$definition},";
                }
            }

            $query .= ") ENGINE=MyISAM{$collation};";

            $db->write_query($query);
        }
    }

    dbVerifyIndexes();
}

function dbVerifyIndexes(): void
{
    global $db;

    foreach (dbTables() as $table => $fields) {
        if (!$db->table_exists($table)) {
            continue;
        }

        if (isset($fields['unique_key'])) {
            foreach ($fields['unique_key'] as $k => $v) {
                if ($db->index_exists($table, $k)) {
                    continue;
                }

                $db->write_query("ALTER TABLE {$db->table_prefix}{$table} ADD UNIQUE KEY {$k} ({$v})");
            }
        }
    }
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
            'menuItem' => "tinyint(1) NOT NULL DEFAULT '1'",
            'wrapper' => "tinyint(1) NOT NULL DEFAULT '1'",
            'classicTemplate' => "tinyint(1) NOT NULL DEFAULT '1'",
            'template' => "MEDIUMTEXT NOT NULL",
            'init' => "tinyint(1) NOT NULL DEFAULT '4'",
            'dateline' => "int(10) NOT NULL DEFAULT '0'",
            'primary_key' => "pid",
            'unique_key' => ['url' => 'url']
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
            'displayNavigation' => "tinyint(1) NOT NULL DEFAULT '1'",
            'buildMenu' => "tinyint(1) NOT NULL DEFAULT '1'",
            //'classicTemplate' => "tinyint(1) NOT NULL DEFAULT '1'", // TODO
            'wrapucp' => "tinyint(1) NOT NULL DEFAULT '0'",
            'primary_key' => "cid",
            'unique_key' => ['url' => 'url']
        ]
    ];

    $tablesData = [];

    foreach ([
                 'ougc_pages_categories' => FIELDS_DATA_CATEGORIES,
                 'ougc_pages' => FIELDS_DATA_PAGES,
             ] as $tableName => $fieldsData) {
        foreach ($fieldsData as $fieldName => $fieldData) {
            $fieldDefinition = '';

            if (!isset($fieldData['type'])) {
                continue;
            }

            $fieldDefinition .= $fieldData['type'];

            if (isset($fieldData['size'])) {
                $fieldDefinition .= "({$fieldData['size']})";
            }

            if (isset($fieldData['unsigned'])) {
                $fieldDefinition .= ' UNSIGNED';
            }

            if (!isset($fieldData['null'])) {
                $fieldDefinition .= ' NOT';
            }

            $fieldDefinition .= ' NULL';

            if (isset($fieldData['auto_increment'])) {
                $fieldDefinition .= ' AUTO_INCREMENT';
            }

            if (isset($fieldData['default'])) {
                $fieldDefinition .= " DEFAULT '{$fieldData['default']}'";
            }

            $tablesData[$tableName][$fieldName] = $fieldDefinition;
        }

        foreach ($fieldsData as $fieldName => $fieldData) {
            if (isset($fieldData['primary_key'])) {
                $tablesData[$tableName]['primary_key'] = $fieldName;
            }
        }
    }

    return $tablesData;
}

function verifyStylesheet($removeStylesheet = false): void
{
    global $db;

    $dbQuery = $db->simple_select(
        'themestylesheets',
        'sid, attachedto',
        "name='usercp.css' AND tid= '1'"
    );

    $updateResult = false;

    while ($stylesheet = $db->fetch_array($dbQuery)) {
        $sheetID = (int)$stylesheet['sid'];

        if (!$removeStylesheet && my_strpos($stylesheet['attachedto'], '|pages.php') === false) {
            $db->update_query('themestylesheets', [
                'attachedto' => $stylesheet['attachedto'] . '|pages.php',
                'lastmodified' => TIME_NOW
            ], "sid = '{$sheetID}'");
            $updateResult = true;
        }

        if ($removeStylesheet && my_strpos($stylesheet['attachedto'], '|pages.php') !== false) {
            $db->update_query('themestylesheets', [
                'attachedto' => str_replace('|pages.php', '', $stylesheet['attachedto']),
                'lastmodified' => TIME_NOW
            ], "sid = '{$sheetID}'");
            $updateResult = true;
        }
    }

    if ($updateResult) {
        $dbQuery = $db->simple_select('themes', 'tid');

        require_once MYBB_ADMIN_DIR . 'inc/functions_themes.php';

        while ($tid = $db->fetch_field($dbQuery, 'tid')) {
            \update_theme_stylesheet_list($tid);
        }
    }
}

function categoryFormCheckFields(array &$errors, string $errorIdentifier = 'category', array $fieldsData = FIELDS_DATA_CATEGORIES): void
{
    pageFormCheckFields($errors, $errorIdentifier, $fieldsData);
}

function pageFormCheckFields(array &$errors, string $errorIdentifier = 'page', array $fieldsData = FIELDS_DATA_PAGES): void
{
    global $mybb, $lang;

    foreach ($fieldsData as $fieldKey => $fieldData) {
        if (!isset($fieldData['formType'])) {
            continue;
        }

        if ($fieldData['formType'] == 'textBox') {
            $inputLength = \my_strlen($mybb->get_input($fieldKey));

            if ($inputLength < 1 || $inputLength > $fieldData['size']) {
                $errors[] = $lang->sprintf(
                    $lang->{"ougc_pages_error_{$errorIdentifier}_invalid_{$fieldKey}"},
                    \my_number_format($fieldData['size'])
                );
            }
        } elseif ($fieldData['formType'] == 'groupSelect') {
            if ($mybb->get_input("{$fieldKey}Select") === 'all') {
                $mybb->input[$fieldKey] = -1;
            } elseif ($mybb->get_input("{$fieldKey}Select") === 'custom') {
                $mybb->input[$fieldKey] = implode(',', \OUGCPages\Core\sanitizeIntegers(
                    $mybb->get_input($fieldKey, \MyBB::INPUT_ARRAY)
                ));
            }
        }

        unset($fieldKey, $fieldData);
    }
}

function categoryFormBuildFields(object &$formContainer, object &$formObject, array $basicSelectItems = [], string $errorIdentifier = 'category', array $fieldsData = FIELDS_DATA_CATEGORIES): void
{
    pageFormBuildFields($formContainer, $formObject, $basicSelectItems, $errorIdentifier, $fieldsData);
}

function pageFormBuildFields(object &$formContainer, object &$formObject, array $basicSelectItems = [], string $errorIdentifier = 'page', array $fieldsData = FIELDS_DATA_PAGES): void
{
    global $mybb, $lang;

    foreach ($fieldsData as $fieldKey => $fieldData) {
        if (!isset($fieldData['formType'])) {
            continue;
        }

        $requiredMark = '';

        if (!empty($fieldData['required'])) {
            $requiredMark = " <em>*</em>";
        }

        $formContainer->output_row(
            $lang->{"ougc_pages_form_{$errorIdentifier}_{$fieldKey}"} . $requiredMark,
            $lang->{"ougc_pages_form_{$errorIdentifier}_{$fieldKey}_desc"},
            call_user_func_array(function (string $fieldKey, string $formType, array $basicSelectItems = []) use (&$formObject): string {
                global $mybb, $lang, $templates;

                if ($formType == 'yesNo') {
                    return $formObject->generate_yes_no_radio(
                        $fieldKey,
                        $mybb->get_input($fieldKey, \MyBB::INPUT_INT),
                        true
                    );
                } else if ($formType == 'groupSelect') {
                    $selectedItems = [];

                    $multiSelectChecked = ['all' => '', 'custom' => '', 'none' => ''];

                    if ($mybb->get_input($fieldKey, \MyBB::INPUT_INT) === -1) {
                        $multiSelectChecked['all'] = 'checked="checked"';
                    } elseif (!empty($mybb->get_input($fieldKey))) {
                        $multiSelectChecked['custom'] = 'checked="checked"';

                        $selectedItems = \OUGCPages\Core\sanitizeIntegers(explode(
                            ',',
                            $mybb->get_input($fieldKey)
                        ));
                    } else {
                        $multiSelectChecked['none'] = 'checked="checked"';
                    }

                    \print_selection_javascript();

                    $groupSelectField = $formObject->generate_group_select(
                        "{$fieldKey}[]",
                        $selectedItems,
                        ['id' => $fieldKey, 'multiple' => true, 'size' => 5]
                    );

                    return eval($templates->render(
                        \OUGCPages\Core\templateGetName('adminGroupSelect'),
                        true,
                        false
                    ));
                } else if ($formType == 'basicSelect') {
                    return $formObject->generate_select_box(
                        $fieldKey,
                        $basicSelectItems[$fieldKey],
                        $mybb->get_input($fieldKey, \MyBB::INPUT_INT),
                        ['id' => $fieldKey]
                    );
                } else if ($formType == 'textArea') {
                    return $formObject->generate_text_area(
                        $fieldKey,
                        $mybb->get_input($fieldKey),
                        ['id' => 'template', 'class' => '', 'style' => 'width: 100%; height: 500px;']
                    );
                } else {
                    return $formObject->generate_text_box(
                        $fieldKey,
                        $mybb->get_input($fieldKey),
                        ['id' => $fieldKey]
                    );
                }
            }, [$fieldKey, $fieldData['formType'], $basicSelectItems]),
            '',
            [],
            ['id' => "row_{$fieldKey}"]
        );

        unset($fieldKey, $fieldData);
    }
}

function categoryFormParseFields(array &$formData, array $fieldsData = FIELDS_DATA_CATEGORIES): void
{
    pageFormParseFields($formData, $fieldsData);
}

function pageFormParseFields(array &$formData, array $fieldsData = FIELDS_DATA_PAGES): void
{
    global $mybb;

    foreach ($fieldsData as $fieldKey => $fieldData) {
        if (!isset($fieldData['formType'])) {
            continue;
        }

        if ($fieldData['formType'] == 'yesNo') {
            $formData[$fieldKey] = $mybb->get_input($fieldKey, \MyBB::INPUT_INT);
        } else {
            $formData[$fieldKey] = $mybb->get_input($fieldKey);
        }

        unset($fieldKey, $fieldData);
    }
}

function categoryFormSetFields(array &$objectData = [], array $fieldsData = FIELDS_DATA_CATEGORIES): void
{
    pageFormSetFields($objectData, $fieldsData);
}

function pageFormSetFields(array &$objectData = [], array $fieldsData = FIELDS_DATA_PAGES): void
{
    global $mybb;

    foreach ($fieldsData as $fieldKey => $fieldData) {
        if (!isset($fieldData['formType'])) {
            continue;
        }

        if (!isset($mybb->input[$fieldKey])) {
            if (isset($objectData[$fieldKey])) {
                $mybb->input[$fieldKey] = $objectData[$fieldKey];
            } else {
                $mybb->input[$fieldKey] = '';
            }
        }

        unset($fieldKey, $fieldData);
    }
}