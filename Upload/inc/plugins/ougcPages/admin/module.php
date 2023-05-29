<?php

/***************************************************************************
 *
 *    OUGC Pages (/inc/plugins/ougcPages/admin/module.php)
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

// Die if IN_MYBB is not defined, for security reasons.

defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Set url to use
\OUGCPages\Core\urlSet('index.php?module=config-ougc_pages');

\OUGCPages\Core\loadLanguage();

$moduleTabs = [
    'categoryView' => [
        'title' => $lang->ougc_pages_tab_category,
        'link' => \OUGCPages\Core\urlBuild(['action' => 'categories']),
        'description' => $lang->ougc_pages_tab_category_desc
    ]
];

if ($mybb->get_input('manage') != 'pages') {
    $moduleTabs['categoryNew'] = [
        'title' => $lang->ougc_pages_tab_category_add,
        'link' => \OUGCPages\Core\urlBuild(['action' => 'add']),
        'description' => $lang->ougc_pages_tab_category_add_desc
    ];

    if ($mybb->get_input('action') == 'edit') {
        $moduleTabs['categoryEdit'] = [
            'title' => $lang->ougc_pages_tab_category_edit,
            'link' => \OUGCPages\Core\urlBuild(['action' => 'edit', 'cid' => $mybb->get_input('cid', \MyBB::INPUT_INT)]),
            'description' => $lang->ougc_pages_tab_category_edit_desc,
        ];
    }
}

$page->add_breadcrumb_item($lang->ougc_pages_manage, \OUGCPages\Core\urlBuild());

if ($mybb->get_input('manage') == 'pages') {
    if (!($categoryData = \OUGCPages\Core\categoryGet($mybb->get_input('cid', \MyBB::INPUT_INT)))) {
        \OUGCPages\Core\redirect($lang->ougc_pages_error_category_invalid, true);
    }

    // Set url to use
    \OUGCPages\Core\urlSet(
        \OUGCPages\Core\urlBuild(['manage' => 'pages', 'cid' => $categoryData['cid']])
    );

    $page->add_breadcrumb_item(\strip_tags($categoryData['name']), \OUGCPages\Core\urlBuild());

    $moduleTabs['pageView'] = [
        'title' => $lang->ougc_pages_manage,
        'link' => \OUGCPages\Core\urlBuild(),
        'description' => $lang->ougc_pages_manage_desc
    ];

    $moduleTabs['pageAdd'] = [
        'title' => $lang->ougc_pages_tab_page_add,
        'link' => \OUGCPages\Core\urlBuild(['action' => 'add']),
        'description' => $lang->ougc_pages_tab_page_add_desc
    ];

    if ($mybb->get_input('action') == 'edit') {
        $moduleTabs['pageEdit'] = [
            'title' => $lang->ougc_pages_tab_page_edit,
            'link' => \OUGCPages\Core\urlBuild(['action' => 'edit', 'pid' => $mybb->get_input('pid', \MyBB::INPUT_INT)]),
            'description' => $lang->ougc_pages_tab_page_edit_desc,
        ];
    }

    $moduleTabs['pageImport'] = [
        'title' => $lang->ougc_pages_tab_page_import,
        'link' => \OUGCPages\Core\urlBuild(['action' => 'import']),
        'description' => $lang->ougc_pages_tab_page_import_desc
    ];

    if ($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit') {
        if (!($newPage = $mybb->get_input('action') === 'add')) {
            if (!($pageData = \OUGCPages\Core\pageGet($mybb->get_input('pid', \MyBB::INPUT_INT)))) {
                \OUGCPages\Core\redirect($lang->ougc_pages_error_page_invalid, true);
            }

            $page->add_breadcrumb_item(\strip_tags($pageData['name']));

            \OUGCPages\Admin\pageFormSetFields($pageData);
        } else {
            $page->add_breadcrumb_item($lang->ougc_pages_tab_page_add);

            \OUGCPages\Admin\pageFormSetFields();
        }

        if (!empty($admin_options['codepress'])) {
            $page->extra_header .= eval($templates->render(
                \OUGCPages\Core\templateGetName('adminCodeMirror'),
                true,
                false
            ));
        }

        $page->output_header($lang->ougc_pages_manage);

        $navTabsString = 'pageAdd';

        if (!$newPage) {
            $navTabsString = 'pageEdit';
        }

        $page->output_nav_tabs($moduleTabs, $navTabsString);

        if ($mybb->request_method == 'post') {
            $errors = [];

            $mybb->input['url'] = \OUGCPages\Core\parseUrl($mybb->get_input('url'));

            $whereConditions = ["url='{$db->escape_string($mybb->get_input('url'))}'"];

            if (!$newPage) {
                $whereConditions[] = "pid!='{$pageData['pid']}'";
            }

            if (\OUGCPages\Core\pageQuery(['pid'], $whereConditions, ['limit' => 1])) {
                $errors[] = $lang->ougc_pages_error_page_duplicated_url;
            }

            // if this is a non-php page then check its contents
            if (!$mybb->get_input('php', \MyBB::INPUT_INT) && \check_template($mybb->get_input('template'))) {
                $errors[] = $lang->ougc_pages_error_page_invalid_template;
            }

            \OUGCPages\Admin\pageFormCheckFields($errors);

            if (empty($errors)) {
                $formData = [];

                if ($newPage) {
                    $higherOrder = \OUGCPages\Core\pageQuery(
                        ['MAX(disporder) as maxOrder'],
                        ["cid='{$categoryData['cid']}'"]
                    );

                    $formData['disporder'] = 1;

                    if (!empty($higherOrder[0]['maxOrder'])) {
                        $formData['disporder'] += $higherOrder[0]['maxOrder'];
                    }
                }

                \OUGCPages\Admin\pageFormParseFields($formData);

                if ($newPage) {
                    $pageID = \OUGCPages\Core\pageInsert($formData);

                    $redirectText = 'ougc_pages_success_page_add';
                } else {
                    $pageID = \OUGCPages\Core\pageUpdate($formData, $pageData['pid']);

                    $redirectText = 'ougc_pages_success_page_updated';
                }

                \OUGCPages\Core\cacheUpdate();

                \OUGCPages\Core\logAction($pageID);

                if ($mybb->get_input('continue')) {
                    \OUGCPages\Core\urlSet(
                        \OUGCPages\Core\urlBuild(['action' => 'edit', 'pid' => $pageID])
                    );
                }

                \OUGCPages\Core\redirect($lang->{$redirectText});
            } else {
                $page->output_inline_error($errors);
            }
        }

        $navTabsString = $formContainerTitle = 'pageAdd';

        $formUrlFields = ['action' => 'add'];

        if (!$newPage) {
            $navTabsString = $formContainerTitle = 'pageEdit';

            $formUrlFields = ['action' => 'edit', 'pid' => $pageData['pid']];
        }

        $formObject = new \Form(
            \OUGCPages\Core\urlBuild($formUrlFields),
            'post'
        );

        $formContainer = new \FormContainer(
            $moduleTabs[$formContainerTitle]['description']
        );

        $basicSelectItems = [
            'cid' => \OUGCPages\Core\categoryBuildSelect(),
            'init' => [
                \OUGCPages\Core\EXECUTION_HOOK_INIT => $lang->ougc_pages_form_page_init_init,
                \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_START => $lang->ougc_pages_form_page_init_start,
                \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_INTERMEDIATE => $lang->ougc_pages_form_page_init_intermediate,
                \OUGCPages\Core\EXECUTION_HOOK_GLOBAL_END => $lang->ougc_pages_form_page_init_end,
            ]
        ];

        \OUGCPages\Admin\pageFormBuildFields($formContainer, $formObject, $basicSelectItems);

        $formContainer->end();

        $formObject->output_submit_wrapper([
            $formObject->generate_submit_button($lang->ougc_pages_button_continue, ['name' => 'continue']),
            $formObject->generate_submit_button($lang->ougc_pages_button_submit),
            $formObject->generate_submit_button($lang->reset)
        ]);

        $formObject->end();

        if (!empty($admin_options['codepress'])) {
            echo eval($templates->render(
                \OUGCPages\Core\templateGetName('adminCodeMirrorFooter'),
                true,
                false
            ));
        }

        $page->output_footer();
    } else if ($mybb->get_input('action') == 'delete') {
        if (!($pageData = \OUGCPages\Core\categoryGet($mybb->get_input('pid', \MyBB::INPUT_INT)))) {
            \OUGCPages\Core\redirect($lang->ougc_pages_error_page_invalid, true);
        }

        if ($mybb->request_method == 'post') {
            if (!\verify_post_check($mybb->get_input('my_post_key'), true)) {
                \OUGCPages\Core\redirect($lang->invalid_post_verify_key2, true);
            }

            if ($mybb->get_input('no')) {
                \OUGCPages\Core\redirect();
            }

            \OUGCPages\Core\pageDelete($pageData['pid']);

            \OUGCPages\Core\logAction($pageData['pid']);

            \OUGCPages\Core\cacheUpdate();

            \OUGCPages\Core\redirect($lang->ougc_pages_success_page_deleted);
        }

        $page->output_confirm_action(
            \OUGCPages\Core\urlBuild(['action' => 'delete', 'pid' => $pageData['pid']])
        );
    } else if ($mybb->get_input('action') == 'update') {
        if (!($pageData = \OUGCPages\Core\pageGet($mybb->get_input('pid', \MyBB::INPUT_INT)))) {
            \OUGCPages\Core\redirect($lang->ougc_pages_error_page_invalid, true);
        }

        $statusUpdate = 1;

        $updateText = 'ougc_pages_success_page_enabled';

        if ($pageData['visible']) {
            $statusUpdate = 0;

            $updateText = 'ougc_pages_success_page_disabled';
        }

        \OUGCPages\Core\pageUpdate(
            ['visible' => $statusUpdate],
            $pageData['pid']
        );

        \OUGCPages\Core\logAction($pageData['pid']);

        \OUGCPages\Core\cacheUpdate();

        \OUGCPages\Core\redirect($lang->{$updateText});
    } else if ($mybb->get_input('action') == 'export') {
        if (!($page = \OUGCPages\Core\pageGet($mybb->get_input('pid', \MyBB::INPUT_INT)))) {
            \OUGCPages\Core\redirect($lang->ougc_pages_error_invalidpage, true);
        }

        $info = ougc_pages_info();

        $file = $PL->xml_export([
            'name' => $page['name'],
            'description' => $page['description'],
            'url' => $page['url'],
            'allowedGroups' => $page['allowedGroups'],
            'php' => $page['php'],
            'wol' => $page['wol'],
            'visible' => $page['visible'],
            'wrapper' => $page['wrapper'],
            'init' => $page['init'],
            'template' => $page['template'],
            'versioncode' => $info['versioncode']
        ], 'OUGC_Pages_' . $page['name'] . '_' . $info['versioncode'] . '.xml');
    } else if ($mybb->get_input('action') == 'import') {
        $page->add_breadcrumb_item(\htmlspecialchars_uni($categoryData['name']));

        $page->output_header($lang->ougc_pages_manage);

        $page->output_nav_tabs($moduleTabs, 'pageImport');

        if ($mybb->request_method == 'post') {
            $errors = [];

            if ($mybb->get_input('file_url')) {
                if (!($contents = \fetch_remote_file($mybb->get_input('file_url')))) {
                    $errors[] = $lang->error_local_file;
                }
            } else if ($_FILES['local_file'] && $_FILES['local_file']['error'] != 4) {
                // Find out if there was an error with the uploaded file
                if ($_FILES['local_file']['error'] != 0) {
                    $errors[] = $lang->error_uploadfailed . $lang->error_uploadfailed_detail;

                    switch ($_FILES['local_file']['error']) {
                        case 1: // UPLOAD_ERR_INI_SIZE
                            $errors[] = $lang->error_uploadfailed_php1;
                            break;
                        case 2: // UPLOAD_ERR_FORM_SIZE
                            $errors[] = $lang->error_uploadfailed_php2;
                            break;
                        case 3: // UPLOAD_ERR_PARTIAL
                            $errors[] = $lang->error_uploadfailed_php3;
                            break;
                        case 6: // UPLOAD_ERR_NO_TMP_DIR
                            $errors[] = $lang->error_uploadfailed_php6;
                            break;
                        case 7: // UPLOAD_ERR_CANT_WRITE
                            $errors[] = $lang->error_uploadfailed_php7;
                            break;
                        default:
                            $errors[] = $lang->sprintf($lang->error_uploadfailed_phpx, $_FILES['local_file']['error']);
                            break;
                    }
                }

                if (empty($errors)) {
                    // Was the temporary file found?
                    if (!is_uploaded_file($_FILES['local_file']['tmp_name'])) {
                        $errors[] = $lang->error_uploadfailed_lost;
                    }

                    // Get the contents
                    if (!($contents = trim(file_get_contents($_FILES['local_file']['tmp_name'])))) {
                        $errors[] = $lang->error_uploadfailed_nocontents;
                    }

                    // Delete the temporary file if possible
                    unlink($_FILES['local_file']['tmp_name']);
                }
            } else {
                // UPLOAD_ERR_NO_FILE
                $errors[] = $lang->error_uploadfailed_php4;
            }

            if (empty($errors)) {
                $xmlImport = [];

                $isValidVersion = true;

                if ($xmlImportPL = @$PL->xml_import($contents)) {
                    if (!$mybb->get_input('ignore_version', \MyBB::INPUT_INT)) {
                        $isValidVersion = (float)$xmlImportPL['versioncode'] == \OUGCPages\Admin\pluginInfo()['versioncode'];
                    }

                    if ($isValidVersion) {
                        $xmlImport = $xmlImportPL;

                        unset($xmlImportPL);
                    }
                } else {
                    // try to get this as a "Page Manager" page
                    require_once MYBB_ROOT . 'inc/class_xml.php';

                    $xmlParser = new XMLParser($contents);

                    $treeContents = $xmlParser->get_tree();

                    if (!empty($treeContents['pagemanager'])) {
                        $pageManagerFile = $treeContents['pagemanager'];

                        unset($treeContents);

                        if (!$mybb->get_input('ignore_version', \MyBB::INPUT_INT)) {
                            $isValidVersion = (float)$pageManagerFile['attributes']['version'] == '1.5.2';
                        }

                        if ($isValidVersion) {
                            $pageManagerContents = $pageManagerFile['page'];
                        }

                        unset($pageManagerFile);

                        if (!empty($pageManagerContents)) {
                            if (!($template = base64_decode($pageManagerContents['template']['value']))) {
                                $template = $pageManagerContents['template']['value'];
                            }

                            $xmlImport = [
                                'name' => (string)$pageManagerContents['name']['value'],
                                'description' => '',
                                'url' => (string)$pageManagerContents['url']['value'],
                                'allowedGroups' => -1,
                                'php' => empty($pageManagerContents['framework']['value']) ? 0 : 1,
                                'wol' => empty($pageManagerContents['online']['value']) ? 0 : 1,
                                'visible' => empty($pageManagerContents['enabled']['value']) ? 0 : 1,
                                'wrapper' => 0, // It runs without a wrapper, similar here
                                'init' => 4, // It runs at misc_stats, so we run at global_end
                                'template' => (string)trim($template)
                            ];
                        }
                    }
                }

                if (empty($xmlImport)) {
                    if ($isValidVersion) {
                        $errors[] = $lang->ougc_pages_error_import_invalid;
                    } else {
                        $errors[] = $lang->ougc_pages_error_import_invalid_version;
                    }
                }

                if (empty($errors)) {
                    $xmlImport['cid'] = $categoryData['cid'];

                    $higherOrder = \OUGCPages\Core\pageQuery(
                        ['MAX(disporder) as maxOrder'],
                        ["cid='{$categoryData['cid']}'"]
                    );

                    $xmlImport['disporder'] = 1;

                    if (!empty($higherOrder[0]['maxOrder'])) {
                        $xmlImport['disporder'] += $higherOrder[0]['maxOrder'];
                    }

                    $xmlImport['url'] = \OUGCPages\Core\importGetUrl($xmlImport['name'], $xmlImport['url']);

                    $pageData = [];

                    foreach (\OUGCPages\Admin\FIELDS_DATA_PAGES as $fieldKey => $fieldData) {
                        if (!isset($xmlImport[$fieldKey])) {
                            continue;
                        }

                        $pageData[$fieldKey] = $xmlImport[$fieldKey];
                    }

                    unset($fieldKey, $fieldData, $xmlImport);

                    $pageID = \OUGCPages\Core\pageInsert($pageData);

                    \OUGCPages\Core\cacheUpdate();

                    \OUGCPages\Core\logAction($pageID);

                    \OUGCPages\Core\redirect($lang->ougc_pages_success_imported);
                }
            }

            if (!empty($errors)) {
                $page->output_inline_error($errors);
            }
        }

        $form = new \Form(\OUGCPages\Core\urlBuild(['action' => 'import']), 'post', '', true);

        $formContainer = new \FormContainer($moduleTabs['pageImport']['description']);

        $formContainer->output_row(
            $lang->ougc_pages_form_import,
            $lang->ougc_pages_form_import_desc,
            $form->generate_file_upload_box('local_file', $mybb->get_input('local_file'))
        );

        $formContainer->output_row(
            $lang->ougc_pages_form_import_url,
            $lang->ougc_pages_form_import_url_desc,
            $form->generate_text_box('file_url', $mybb->get_input('file_url'))
        );

        $formContainer->output_row(
            $lang->ougc_pages_form_import_ignore_version,
            $lang->ougc_pages_form_import_ignore_version_desc,
            $form->generate_yes_no_radio('ignore_version', $mybb->get_input('ignore_version', \MyBB::INPUT_INT))
        );

        $formContainer->end();

        $form->output_submit_wrapper([
            $form->generate_submit_button($lang->ougc_pages_button_import),
            $form->generate_reset_button($lang->reset)
        ]);

        $form->end();

        $page->output_footer();
    } else {
        $page->add_breadcrumb_item(\htmlspecialchars_uni($categoryData['name']));

        $page->output_header($lang->ougc_pages_manage);

        $page->output_nav_tabs($moduleTabs, 'pageView');

        $tableObject = new \Table;

        $tableObject->construct_header($lang->ougc_pages_category_name);
        $tableObject->construct_header($lang->ougc_pages_category_order, ['width' => '15%', 'class' => 'align_center']);
        $tableObject->construct_header($lang->ougc_pages_category_status, ['width' => '15%', 'class' => 'align_center']);
        $tableObject->construct_header($lang->options, ['width' => '15%', 'class' => 'align_center']);

        if (!($totalPages = \OUGCPages\Core\pageQuery(['COUNT(pid) AS pages'], ["cid='{$categoryData['cid']}'"])[0]['pages'])) {
            $tableObject->construct_cell($lang->ougc_pages_category_empty, ['colspan' => 4, 'class' => 'align_center']);

            $tableObject->construct_row();

            $tableObject->output($moduleTabs['pageView']['title']);
        } else {
            $multiPage = \OUGCPages\Core\multipageBuild($totalPages, \OUGCPages\Core\urlBuild());

            if ($mybb->request_method == 'post') {
                if (!\verify_post_check($mybb->get_input('my_post_key'), true)) {
                    \OUGCPages\Core\redirect($lang->invalid_post_verify_key2, true);
                }

                foreach ($mybb->get_input('disporder', \MyBB::INPUT_ARRAY) as $pageID => $newOrder) {
                    \OUGCPages\Core\pageUpdate(
                        ['disporder' => $newOrder],
                        $pageID
                    );
                }

                \OUGCPages\Core\cacheUpdate();

                \OUGCPages\Core\redirect($lang->ougc_pages_success_page_updated_order);
            }

            echo $multiPage;

            $pagesList = \OUGCPages\Core\pageQuery(['*'], ["cid='{$categoryData['cid']}'"], [
                'limit_start' => \OUGCPages\Core\getQueryStart(),
                'limit' => \OUGCPages\Core\getQueryLimit(),
                'order_by' => 'disporder'
            ]);

            $formObject = new \Form(\OUGCPages\Core\urlBuild(), 'post');

            foreach ($pagesList as $pageData) {
                $manageUrl = \OUGCPages\Core\urlBuild(['manage' => 'pages', 'action' => 'edit', 'pid' => $pageData['pid']]);

                $pageName = \htmlspecialchars_uni($pageData['name']);

                $pageLink = '---';

                if ($pageData['visible'] && $pageData['allowedGroups'] !== '') {
                    $pageLink = \OUGCPages\Core\pageBuildLink(
                        $lang->ougc_pages_page_view,
                        $pageData['pid']
                    );
                }

                $tableObject->construct_cell(eval($templates->render(
                    \OUGCPages\Core\templateGetName('adminPageName'),
                    true,
                    false
                )));

                $tableObject->construct_cell(
                    $formObject->generate_text_box(
                        "disporder[{$pageData['pid']}]",
                        $pageData['disporder'],
                        ['style' => 'text-align: center; width: 30px;']
                    ),
                    ['class' => 'align_center']
                );

                $updateStatusUrl = \OUGCPages\Core\urlBuild([
                    'action' => 'update',
                    'pid' => $pageData['pid'],
                    'my_post_key' => $mybb->post_code
                ]);

                $updateStatusBullet = 'off';

                $updateStatusText = $lang->ougc_pages_category_disabled;

                if ($pageData['visible']) {
                    $updateStatusBullet = 'on';

                    $updateStatusText = $lang->ougc_pages_category_enabled;
                }

                $tableObject->construct_cell(eval($templates->render(
                    \OUGCPages\Core\templateGetName('adminCategoryStatus'),
                    true,
                    false
                )), ['class' => 'align_center']);

                $popup = new \PopupMenu('page_' . $pageData['pid'], $lang->options);

                $popup->add_item($lang->edit, \OUGCPages\Core\urlBuild(['action' => 'edit', 'pid' => $pageData['pid']]));

                $popup->add_item($lang->delete, \OUGCPages\Core\urlBuild(['action' => 'delete', 'pid' => $pageData['pid']]));

                $popup->add_item($lang->ougc_pages_page_export, \OUGCPages\Core\urlBuild([
                    'action' => 'export',
                    'pid' => $pageData['pid'],
                    'my_post_key' => $mybb->post_code
                ]));

                $tableObject->construct_cell($popup->fetch(), ['class' => 'align_center']);

                $tableObject->construct_row();
            }

            /* while ($pages = $db->fetch_array($dbQuery)) {
                 $edit_link = \OUGCPages\Core\urlBuild(['action' => 'edit', 'pid' => $pages['pid']]);
                 $pages['name'] = htmlspecialchars_uni($pages['name']);

                 $pages['visible'] or $pages['name'] = '<em>' . $pages['name'] . '</em>';

                 $tableObject->construct_cell('<a href="' . $edit_link . '"><strong>' . $pages['name'] . '</strong></a> <span style="font-size: 90%">(' . \OUGCPages\Core\pageBuildLink($lang->ougc_pages_view_page, $pages['pid']) . ')</span>');
                 $tableObject->construct_cell($form->generate_text_box('disporder[' . $pages['pid'] . ']', (int)$pages['disporder'], ['style' => 'text-align: center; width: 30px;']), ['class' => 'align_center']);
                 $tableObject->construct_cell('<a href="' . \OUGCPages\Core\urlBuild(['action' => 'update', 'pid' => $pages['pid'], 'my_post_key' => $mybb->post_code]) . '"><img src="styles/default/images/icons/bullet_o' . (!$pages['visible'] ? 'ff' : 'n') . '.png" alt="" title="' . (!$pages['visible'] ? $lang->ougc_pages_form_disabled : $lang->ougc_pages_form_visible) . '" /></a>', ['class' => 'align_center']);
             }*/

            $tableObject->output($moduleTabs['categoryView']['title']);

            $formObject->output_submit_wrapper([
                $formObject->generate_submit_button($lang->ougc_pages_button_update_order),
                $formObject->generate_reset_button($lang->reset)
            ]);

            $formObject->end();
        }

        $page->output_footer();
    }
} else if ($mybb->get_input('action') == 'add' || $mybb->get_input('action') == 'edit') {
    if (!($newCategory = $mybb->get_input('action') === 'add')) {
        if (!($categoryData = \OUGCPages\Core\categoryGet($mybb->get_input('cid', \MyBB::INPUT_INT)))) {
            \OUGCPages\Core\redirect($lang->ougc_pages_error_category_invalid, true);
        }

        \OUGCPages\Admin\categoryFormSetFields($categoryData);

        $page->add_breadcrumb_item(\strip_tags($categoryData['name']));
    } else {
        \OUGCPages\Admin\categoryFormSetFields();
    }

    $page->output_header($lang->ougc_pages_manage);

    $navTabsString = $formContainerTitle = 'categoryNew';

    $formUrlFields = ['action' => 'add'];

    if (!$newCategory) {
        $navTabsString = $formContainerTitle = 'categoryEdit';

        $formUrlFields = ['action' => 'edit', 'cid' => $categoryData['cid']];
    }

    $page->output_nav_tabs($moduleTabs, $navTabsString);

    if ($mybb->request_method == 'post') {
        $errors = [];

        $mybb->input['url'] = \OUGCPages\Core\parseUrl($mybb->get_input('url'));

        $whereConditions = ["url='{$db->escape_string($mybb->get_input('url'))}'"];

        if (!$newCategory) {
            $whereConditions[] = "cid!='{$categoryData['cid']}'";
        }

        if (\OUGCPages\Core\categoryQuery(['cid'], $whereConditions, ['limit' => 1])) {
            $errors[] = $lang->ougc_pages_error_category_duplicated_url;
        }

        \OUGCPages\Admin\categoryFormCheckFields($errors);

        if (empty($errors)) {
            $formData = [];

            if ($newCategory) {
                $higherOrder = \OUGCPages\Core\categoryQuery(
                    ['MAX(disporder) as maxOrder']
                );

                $formData['disporder'] = 1;

                if (!empty($higherOrder[0]['maxOrder'])) {
                    $formData['disporder'] += $higherOrder[0]['maxOrder'];
                }
            }

            \OUGCPages\Admin\categoryFormParseFields($formData);

            if ($newCategory) {
                $categoryID = \OUGCPages\Core\categoryInsert($formData);
            } else {
                $categoryID = \OUGCPages\Core\categoryUpdate($formData, $categoryData['cid']);
            }

            \OUGCPages\Core\cacheUpdate();

            \OUGCPages\Core\logAction($categoryID);

            if ($newCategory) {
                \OUGCPages\Core\redirect($lang->ougc_pages_success_category_add);
            }

            \OUGCPages\Core\redirect($lang->ougc_pages_success_category_updated);
        } else {
            $page->output_inline_error($errors);
        }
    }

    $formObject = new \Form(
        \OUGCPages\Core\urlBuild($formUrlFields),
        'post'
    );

    $formContainer = new \FormContainer(
        $moduleTabs[$formContainerTitle]['description']
    );

    /*$basicSelectItems = [
      'buildMenu' => [
           0 => $lang->ougc_pages_form_category_buildMenu_none,
           1 => $lang->ougc_pages_form_category_buildMenu_header,
           2 => $lang->ougc_pages_form_category_buildMenu_footer
       ]
   ];*/

    \OUGCPages\Admin\categoryFormBuildFields($formContainer, $formObject/*, $basicSelectItems*/);

    $formContainer->end();

    $formObject->output_submit_wrapper([
        $formObject->generate_submit_button($lang->ougc_pages_button_submit),
        $formObject->generate_reset_button($lang->reset)
    ]);

    $formObject->end();

    $page->output_footer();
} else if ($mybb->get_input('action') == 'delete') {
    if (!($categoryData = \OUGCPages\Core\categoryGet($mybb->get_input('cid', \MyBB::INPUT_INT)))) {
        \OUGCPages\Core\redirect($lang->ougc_pages_error_category_invalid, true);
    }

    if ($mybb->request_method == 'post') {
        if (!\verify_post_check($mybb->get_input('my_post_key'), true)) {
            \OUGCPages\Core\redirect($lang->invalid_post_verify_key2, true);
        }

        if ($mybb->get_input('no')) {
            \OUGCPages\Core\redirect();
        }

        \OUGCPages\Core\categoryDelete($categoryData['cid']);

        \OUGCPages\Core\logAction($categoryData['cid']);

        \OUGCPages\Core\cacheUpdate();

        \OUGCPages\Core\redirect($lang->ougc_pages_success_category_deleted);
    }

    $page->output_confirm_action(
        \OUGCPages\Core\urlBuild(['action' => 'delete', 'cid' => $categoryData['cid']])
    );
} else if ($mybb->get_input('action') == 'update') {
    if (!($categoryData = \OUGCPages\Core\categoryGet($mybb->get_input('cid', \MyBB::INPUT_INT)))) {
        \OUGCPages\Core\redirect($lang->ougc_pages_error_category_invalid, true);
    }

    if (!\verify_post_check($mybb->get_input('my_post_key'), true)) {
        \OUGCPages\Core\redirect($lang->invalid_post_verify_key2, true);
    }

    $statusUpdate = 1;

    $updateText = 'ougc_pages_success_category_enabled';

    if ($categoryData['visible']) {
        $statusUpdate = 0;

        $updateText = 'ougc_pages_success_category_disabled';
    }

    \OUGCPages\Core\categoryUpdate(
        ['visible' => $statusUpdate],
        $categoryData['cid']
    );

    \OUGCPages\Core\logAction($categoryData['cid']);

    \OUGCPages\Core\cacheUpdate();

    \OUGCPages\Core\redirect($lang->{$updateText});
} else {
    $page->add_breadcrumb_item($moduleTabs['categoryView']['title'], \OUGCPages\Core\urlBuild());
    $page->output_header($lang->ougc_pages_manage);
    $page->output_nav_tabs($moduleTabs, 'categoryView');

    $tableObject = new \Table;

    $tableObject->construct_header($lang->ougc_pages_category_name);
    $tableObject->construct_header($lang->ougc_pages_category_order, ['width' => '15%', 'class' => 'align_center']);
    $tableObject->construct_header($lang->ougc_pages_category_status, ['width' => '15%', 'class' => 'align_center']);
    $tableObject->construct_header($lang->options, ['width' => '15%', 'class' => 'align_center']);

    if (!($totalCategories = \OUGCPages\Core\categoryQuery(['COUNT(cid) AS categories'])[0]['categories'])) {
        $tableObject->construct_cell($lang->ougc_pages_category_empty, ['colspan' => 4, 'class' => 'align_center']);

        $tableObject->construct_row();

        $tableObject->output($moduleTabs['categoryView']['title']);
    } else {
        $multiPage = \OUGCPages\Core\multipageBuild($totalCategories, \OUGCPages\Core\urlBuild());

        if ($mybb->request_method == 'post') {
            if (!\verify_post_check($mybb->get_input('my_post_key'), true)) {
                \OUGCPages\Core\redirect($lang->invalid_post_verify_key2, true);
            }

            foreach ($mybb->get_input('disporder', \MyBB::INPUT_ARRAY) as $categoryID => $newOrder) {
                \OUGCPages\Core\categoryUpdate(
                    ['disporder' => $newOrder],
                    $categoryID
                );
            }

            \OUGCPages\Core\cacheUpdate();

            \OUGCPages\Core\redirect($lang->ougc_pages_success_category_updated_order);
        }

        echo $multiPage;

        $categoriesList = \OUGCPages\Core\categoryQuery(['*'], ['1=1'], [
            'limit_start' => \OUGCPages\Core\getQueryStart(),
            'limit' => \OUGCPages\Core\getQueryLimit(),
            'order_by' => 'disporder'
        ]);

        $formObject = new \Form(\OUGCPages\Core\urlBuild(), 'post');

        foreach ($categoriesList as $categoryData) {
            $manageUrl = \OUGCPages\Core\urlBuild(['manage' => 'pages', 'cid' => $categoryData['cid']]);

            $categoryName = \htmlspecialchars_uni($categoryData['name']);

            $categoryLink = '---';

            if ($categoryData['visible'] && $categoryData['allowedGroups'] !== '') {
                $categoryLink = \OUGCPages\Core\categoryBuildLink(
                    $lang->ougc_pages_category_view,
                    $categoryData['cid']
                );
            }

            $tableObject->construct_cell(eval($templates->render(
                \OUGCPages\Core\templateGetName('adminCategoryName'),
                true,
                false
            )));

            $tableObject->construct_cell(
                $formObject->generate_text_box(
                    "disporder[{$categoryData['cid']}]",
                    $categoryData['disporder'],
                    ['style' => 'text-align: center; width: 30px;']
                ),
                ['class' => 'align_center']
            );

            $updateStatusUrl = \OUGCPages\Core\urlBuild([
                'action' => 'update',
                'cid' => $categoryData['cid'],
                'my_post_key' => $mybb->post_code
            ]);

            $updateStatusBullet = 'off';

            $updateStatusText = $lang->ougc_pages_category_disabled;

            if ($categoryData['visible']) {
                $updateStatusBullet = 'on';

                $updateStatusText = $lang->ougc_pages_category_enabled;
            }

            $tableObject->construct_cell(eval($templates->render(
                \OUGCPages\Core\templateGetName('adminCategoryStatus'),
                true,
                false
            )), ['class' => 'align_center']);

            $popup = new \PopupMenu('category_' . $categoryData['cid'], $lang->options);

            $popup->add_item($lang->edit, \OUGCPages\Core\urlBuild(['action' => 'edit', 'cid' => $categoryData['cid']]));

            $popup->add_item($lang->delete, \OUGCPages\Core\urlBuild(['action' => 'delete', 'cid' => $categoryData['cid']]));

            $tableObject->construct_cell($popup->fetch(), ['class' => 'align_center']);

            $tableObject->construct_row();
        }

        $tableObject->output($moduleTabs['categoryView']['title']);

        $formObject->output_submit_wrapper([
            $formObject->generate_submit_button($lang->ougc_pages_button_update_order),
            $formObject->generate_reset_button($lang->reset)
        ]);

        $formObject->end();
    }

    $page->output_footer();
}