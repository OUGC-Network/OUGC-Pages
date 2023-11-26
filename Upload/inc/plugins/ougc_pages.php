<?php

/***************************************************************************
 *
 *    OUGC Pages plugin (/inc/plugins/ougc_pages.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2014 Omar Gonzalez
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
use function ougc\Pages\Admin\pluginActivate;
use function ougc\Pages\Admin\pluginDeactivate;
use function ougc\Pages\Admin\pluginInfo;
use function ougc\Pages\Admin\pluginIsInstalled;
use function ougc\Pages\Admin\pluginUninstall;
use function ougc\Pages\Core\addHooks;
use function ougc\Pages\Core\cacheUpdate;

defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

const OUGC_PAGES_ROOT = MYBB_ROOT . 'inc/plugins/ougc/Pages';

// Plugin Settings
define('ougc\Pages\Core\SETTINGS', [
    'enableEval' => false
]);

require_once OUGC_PAGES_ROOT . '/core.php';

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

// Add our hooks
if (defined('IN_ADMINCP')) {
    require_once OUGC_PAGES_ROOT . '/admin.php';
    require_once OUGC_PAGES_ROOT . '/adminHooks.php';

    addHooks('ougc\Pages\adminHooks');
} else {
    require_once OUGC_PAGES_ROOT . '/forumHooks.php';

    addHooks('ougc\Pages\ForumHooks');
}

// Plugin API
function ougc_pages_info(): array
{
    return pluginInfo();
}

// _activate() routine
function ougc_pages_activate()
{
    pluginActivate();
}

// _deactivate() routine
function ougc_pages_deactivate()
{
    pluginDeactivate();
}

// _install() routine
function ougc_pages_install()
{
    pluginUninstall();
}

// _is_installed() routine
function ougc_pages_is_installed(): bool
{
    return pluginIsInstalled();
}

// _uninstall() routine
function ougc_pages_uninstall()
{
    pluginUninstall();
}

// Tools -> Cache update helper
function update_ougc_pages()
{
    cacheUpdate();
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
                $parser = new postParser();
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
        $message = trim(
            preg_replace(['~ {2,}~', "~\n{2,}~"],
                [' ', "\n"],
                strtr($message, ["\xA0" => ' ', "\r" => '', "\t" => ' ']))
        );

        // newline fix for browsers which don't support them
        $message = preg_replace("~ ?\n ?~", " \n", $message);

        // Shorten the message if too long
        if (my_strlen($message) > $maxlen) {
            $message = my_substr($message, 0, $maxlen - 1) . '...';
        }

        return htmlspecialchars_uni($message);
    }
}