<?php

/***************************************************************************
 *
 *    OUGC Pages plugin (/inc/plugins/ougc_pages.php)
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

const OUGC_PAGES_ROOT = MYBB_ROOT . 'inc/plugins/ougcPages';
const OUGC_PAGES_DISABLE_EVAL = false;

require_once OUGC_PAGES_ROOT . '/core.php';

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

// Add our hooks
if (defined('IN_ADMINCP')) {
    require_once OUGC_PAGES_ROOT . '/admin.php';
    require_once OUGC_PAGES_ROOT . '/adminHooks.php';

    \OUGCPages\Core\addHooks('OUGCPages\adminHooks');
} else {
    require_once OUGC_PAGES_ROOT . '/forumHooks.php';

    \OUGCPages\Core\addHooks('OUGCPages\ForumHooks');
}

// Plugin API
function ougc_pages_info(): array
{
    return \OUGCPages\Admin\pluginInfo();
}

// _activate() routine
function ougc_pages_activate(): true
{
    return \OUGCPages\Admin\pluginActivate();
}

// _deactivate() routine
function ougc_pages_deactivate(): true
{
    return \OUGCPages\Admin\pluginDeactivate();
}

// _install() routine
function ougc_pages_install(): true
{
    return \OUGCPages\Admin\pluginUninstall();
}

// _is_installed() routine
function ougc_pages_is_installed(): bool
{
    return \OUGCPages\Admin\pluginIsInstalled();
}

// _uninstall() routine
function ougc_pages_uninstall(): true
{
    return \OUGCPages\Admin\pluginUninstall();
}

// PluginLibrary dependency check & load
function pluginLibraryRequirements(): bool
{
    die('pluginLibraryRequirements');
    return \OUGCPages\Core\loadPluginLibrary();
}

// Add menu to ACP
function ougc_pages_config_menu(array &$args): array
{
    die('ougc_pages_config_menu');
    return \OUGCPages\adminHooks\admin_config_menu($args);
}

// Add action handler to config module
function ougc_pages_config_action_handler(array &$args): array
{
    die('ougc_pages_config_action_handler');
    return \OUGCPages\adminHooks\admin_config_action_handler($args);
}

// Insert plugin into the admin permissions page
function ougc_pages_config_permissions(array &$args): array
{
    die('ougc_pages_config_permissions');
    return \OUGCPages\adminHooks\admin_config_permissions($args);
}

// Show a flash message if plug-in requires updating
function ougc_pages_output_header(): true
{
    die('ougc_pages_output_header');
    return \OUGCPages\adminHooks\admin_page_output_header();
}

// Cache manager
function update_ougc_pages()
{
    die('update_ougc_pages');
    return \OUGCPages\Core\cacheUpdate();
}

// WOL support
function ougc_pages_fetch_wol_activity_end(array &$args): array
{
    die('ougc_pages_fetch_wol_activity_end');
    return \OUGCPages\ForumHooks\fetch_wol_activity_end($args);
}

function ougc_pages_build_friendly_wol_location_end(array &$args): array|bool
{
    die('ougc_pages_build_friendly_wol_location_end');
    return \OUGCPages\ForumHooks\build_friendly_wol_location_end($args);
}

// Show the page
function ougc_pages_show(): never
{
    die('ougc_pages_show');
    \OUGCPages\Core\initShow();
}

// Execute PHP pages
function ougc_pages_execute(): never
{
    die('ougc_pages_execute');
    \OUGCPages\Core\initExecute();
}

// Initialize the plugin magic
function ougc_pages_init(): bool
{
    die('ougc_pages_init');
    return \OUGCPages\Core\initRun();
}

function ougc_pages_usercp_menu(): true
{
    die('ougc_pages_usercp_menu');
    return \OUGCPages\ForumHooks\usercp_menu40();
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
if (!function_exists('control_object')) {
    function control_object(&$obj, $code)
    {
        static $cnt = 0;
        $newname = '_objcont_' . (++$cnt);
        $objserial = serialize($obj);
        $classname = get_class($obj);
        $checkstr = 'O:' . strlen($classname) . ':"' . $classname . '":';
        $checkstr_len = strlen($checkstr);
        if (substr($objserial, 0, $checkstr_len) == $checkstr) {
            $vars = [];
            // grab resources/object etc, stripping scope info from keys
            foreach ((array)$obj as $k => $v) {
                if ($p = strrpos($k, "\0")) {
                    $k = substr($k, $p + 1);
                }
                $vars[$k] = $v;
            }
            if (!empty($vars)) {
                $code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
            }
            eval('class ' . $newname . ' extends ' . $classname . ' {' . $code . '}');
            $obj = unserialize('O:' . strlen($newname) . ':"' . $newname . '":' . substr($objserial, $checkstr_len));
            if (!empty($vars)) {
                $obj->___setvars($vars);
            }
        }
        // else not a valid object or PHP serialize has changed
    }
}

if (!function_exists('ougc_getpreview')) {
    /**
     * Shorts a message to look like a preview.
     * Based off Zinga Burga's "Thread Tooltip Preview" plugin threadtooltip_getpreview() function.
     *
     * @param string Message to short.
     * @param int Maximum characters to show.
     * @param bool Strip MyCode Quotes from message.
     * @param bool Strip MyCode from message.
     * @return string Shortened message
     **/
    function ougc_getpreview($message, $maxlen = 100, $stripquotes = true, $stripmycode = true)
    {
        // Attempt to remove quotes, skip if going to strip MyCode
        if ($stripquotes && !$stripmycode) {
            $message = preg_replace([
                '#\[quote=([\"\']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"\']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi',
                '#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si',
                '#\[quote\]#si',
                '#\[\/quote\]#si'
            ], '', $message);
        }

        // Attempt to remove any MyCode
        if ($stripmycode) {
            global $parser;
            if (!is_object($parser)) {
                require_once MYBB_ROOT . 'inc/class_parser.php';
                $parser = new postParser;
            }

            $message = $parser->parse_message($message, [
                'allow_html' => 0,
                'allow_mycode' => 1,
                'allow_smilies' => 0,
                'allow_imgcode' => 1,
                'filter_badwords' => 1,
                'nl2br' => 0
            ]);

            // before stripping tags, try converting some into spaces
            $message = preg_replace([
                '~\<(?:img|hr).*?/\>~si',
                '~\<li\>(.*?)\</li\>~si'
            ], [' ', "\n* $1"], $message);

            $message = unhtmlentities(strip_tags($message));
        }

        // convert \xA0 to spaces (reverse &nbsp;)
        $message = trim(preg_replace(['~ {2,}~', "~\n{2,}~"], [' ', "\n"], strtr($message, ["\xA0" => ' ', "\r" => '', "\t" => ' '])));

        // newline fix for browsers which don't support them
        $message = preg_replace("~ ?\n ?~", " \n", $message);

        // Shorten the message if too long
        if (my_strlen($message) > $maxlen) {
            $message = my_substr($message, 0, $maxlen - 1) . '...';
        }

        return htmlspecialchars_uni($message);
    }
}

require_once OUGC_PAGES_ROOT . '/class.php';

$GLOBALS['ougc_pages'] = new OUGC_Pages;

//\OUGCPages\Core\initRun();
