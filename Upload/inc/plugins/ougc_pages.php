<?php

/***************************************************************************
 *
 *	OUGC Pages plugin (/inc/plugins/ougc_pages.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Create additional pages directly from the ACP.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Run/Add Hooks
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_menu', 'ougc_pages_config_menu');
	$plugins->add_hook('admin_config_action_handler', 'ougc_pages_config_action_handler');
	$plugins->add_hook('admin_config_permissions', 'ougc_pages_config_permissions');

	$plugins->add_hook('admin_config_settings_start', create_function('&$args', 'global $ougc_pages;	$ougc_pages->lang_load();'));
	$plugins->add_hook('admin_config_settings_change', create_function('&$args', 'global $ougc_pages;	$ougc_pages->lang_load();'));
}
else
{
	$plugins->add_hook('build_friendly_wol_location_end', 'ougc_pages_wol');
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_pages_info()
{
	global $lang, $ougc_pages;
	$ougc_pages->lang_load();

	return array(
		'name'			=> 'OUGC Pages',
		'description'	=> $lang->setting_group_ougc_pages_desc,
		'website'		=> 'http://mods.mybb.com/view/ougc-pages',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '0.1',
		'versioncode'	=> '0100',
		'compatibility'	=> '17*',
		'guid'			=> '',
		'pl'			=> array(
			'version'	=> 12,
			'url'		=> 'http://mods.mybb.com/view/pluginlibrary'
		)
	);
}

// _activate() routine
function ougc_pages_activate()
{
	global $PL, $lang, $cache;
	ougc_pages_deactivate();

	// Add settings group
	$PL->settings('ougc_pages', $lang->setting_group_ougc_pages, $lang->setting_group_ougc_pages_desc, array(
		'seo'					=> array(
		   'title'			=> $lang->setting_ougc_pages_seo,
		   'description'	=> $lang->setting_ougc_pages_seo_desc,
		   'optionscode'	=> 'select
0='.$lang->setting_ougc_pages_seo_none.'
1='.$lang->setting_ougc_pages_seo_mybb.'
2='.$lang->setting_ougc_pages_seo_google,
		   'value'			=> 0
		),
		'seo_scheme'			=> array(
		   'title'			=> $lang->setting_ougc_pages_seo_scheme,
		   'description'	=> $lang->setting_ougc_pages_seo_scheme_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'page-{$url}.html',
		),
		'seo_scheme_categories'	=> array(
		   'title'			=> $lang->setting_ougc_pages_seo_scheme_categories,
		   'description'	=> $lang->setting_ougc_pages_seo_scheme_categories_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'category-{$url}.html',
		),
		'perpage'				=> array(
		   'title'			=> $lang->setting_ougc_pages_perpage,
		   'description'	=> $lang->setting_ougc_pages_perpage_desc,
		   'optionscode'	=> 'text',
			'value'			=>	20,
		),
		'portal'				=> array(
		   'title'			=> $lang->setting_ougc_pages_portal,
		   'description'	=> $lang->setting_ougc_pages_portal_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		)
	));

	// Add template group
	$PL->templates('ougcpages', '<lang:setting_group_ougc_pages>', array(
		''	=> '<html>
	<head>
		<title>{$pages[\'name\']} - {$settings[\'bbname\']}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		{$pages[\'code\']}
		{$footer}
	</body>
</html>'
	));

	// Update administrator permissions
	change_admin_permission('config', 'ougc_pages');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_pages_info();

	if(!isset($plugins['pages']))
	{
		$plugins['pages'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['pages'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_pages_deactivate()
{
	ougc_pages_pl_check();

	// Update administrator permissions
	change_admin_permission('config', 'ougc_pages', 0);
}

// _install() routine
function ougc_pages_install()
{
	global $db;

	$collation = $db->build_create_table_collation();

	// Create our table(s)
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."ougc_pages` (
			`pid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`cid` int NOT NULL DEFAULT '0',
			`name` varchar(100) NOT NULL DEFAULT '',
			`url` varchar(100) NOT NULL DEFAULT '',
			`groups` text NOT NULL,
			`php` tinyint(1) NOT NULL DEFAULT '0',
			`wol` tinyint(1) NOT NULL DEFAULT '1',
			`disporder` tinyint(1) NOT NULL DEFAULT '0',
			`visible` tinyint(1) NOT NULL DEFAULT '1',
			`template` text NOT NULL,
			`dateline` int(10) NOT NULL DEFAULT '0',
			PRIMARY KEY (`pid`),
			UNIQUE KEY `url` (`url`)
		) ENGINE=MyISAM{$collation};"
	);

	$db->write_query("CREATE TABLE `".TABLE_PREFIX."ougc_pages_categories` (
			`cid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT '',
			`url` varchar(100) NOT NULL DEFAULT '',
			`groups` text NOT NULL,
			`disporder` smallint NOT NULL DEFAULT '0',
			`visible` tinyint(1) NOT NULL DEFAULT '1',
			`breadcrumb` tinyint(1) NOT NULL DEFAULT '1',
			PRIMARY KEY (`cid`),
			UNIQUE KEY `url` (`url`)
		) ENGINE=MyISAM{$collation};"
	);
}

// _is_installed() routine
function ougc_pages_is_installed()
{
	global $db;

	return $db->table_exists('ougc_pages');
}

// _uninstall() routine
function ougc_pages_uninstall()
{
	global $db, $PL, $cache;
	ougc_pages_pl_check();

	// Drop DB entries
	$db->drop_table('ougc_pages');
	$db->drop_table('ougc_pages_categories');

	$PL->cache_delete('ougc_pages');
	$PL->settings_delete('ougc_pages');
	$PL->templates_delete('ougcpages');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['pages']))
	{
		unset($plugins['pages']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}

	// Remove administrator permissions
	change_admin_permission('config', 'ougc_pages', -1);
}

// PluginLibrary dependency check & load
function ougc_pages_pl_check()
{
	global $lang, $ougc_pages;
	$ougc_pages->lang_load();
	$info = ougc_pages_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_pages_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_pages_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Add menu to ACP
function ougc_pages_config_menu(&$args)
{
	global $ougc_pages, $lang;
	$ougc_pages->lang_load();

	$args[] = array(
		'id'	=> 'ougc_pages',
		'title'	=> $lang->ougc_pages_manage,
		'link'	=> 'index.php?module=config-ougc_pages'
	);
}

// Add action handler to config module
function ougc_pages_config_action_handler(&$args)
{
	$args['ougc_pages'] = array('visible' => 'ougc_pages', 'file' => 'ougc_pages.php');
}

// Insert plugin into the admin permissions page
function ougc_pages_config_permissions(&$args)
{
	global $ougc_pages, $lang;
	$ougc_pages->lang_load();

	$args['ougc_pages'] = $lang->ougc_pages_config_permissions;
}

// Cache manager
function update_ougc_pages()
{
	global $ougc_pages;

	$ougc_pages->cache_update();
}

// WOL support
function ougc_pages_wol(&$args)
{
	if(!(in_array($args['user_activity']['activity'], array('portal', 'pages')) && my_strpos($args['user_activity']['location'], 'page=')))
	{
		return;
	}

	global $cache;

	$pagecache = $cache->read('ougc_pages');

	$location = parse_url($args['user_activity']['location']);
	$location['query'] = html_entity_decode($location['query']);
	$location['query'] = explode('&', (string)$location['query']);

	if(!empty($location['query']))
	{

		foreach($location['query'] as $query)
		{
			$param = explode('=', $query);
			if($param[0] == 'page')
			{
				$page = $param[1];
			}
		}

		if(!empty($pagecache['pages'][$page]))
		{
			global $ougc_pages, $lang, $settings;
			$ougc_pages->lang_load();

			$pages = $ougc_pages->get_page($pagecache['pages'][$page]);

			$args['location_name'] = $lang->sprintf($lang->ougc_pages_wol, $settings['bburl'], $ougc_pages->get_page_link($pagecache['pages'][$page]), htmlspecialchars_uni($pages['name']));
		}
	}
}

// Show the page
function ougc_pages_show($portal=false)
{
	global $ougc_pages, $lang, $templates;

	// Load lang
	$ougc_pages->lang_load();

	!$ougc_pages->invalid_page or error($lang->ougc_pages_error_invalidpage);

	!$ougc_pages->no_permission or error_no_permission();

	// Load custom page language file if exists
	$lang->load('ougc_pages_'.$pages['url'], false, true);

	$pages = $ougc_pages->get_page_by_url($mybb->get_input('page'));
	$category = $ougc_pages->get_category($pages['cid'], true);

	$ougc_pages->set_url($ougc_pages->get_category_link($pages['cid']));

	if($category['breadcrumb'])
	{
		add_breadcrumb(htmlspecialchars_uni($category['name']), $ougc_pages->build_url());
	}
	add_breadcrumb(htmlspecialchars_uni($pages['name']));

	if(!isset($templates->cache['ougcpages']))
	{
		$templates->cache['ougcpages'] = '<html>
	<head>
		<title>{$pages[\'name\']} - {$settings[\'bbname\']}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		{$pages[\'template\']}
		{$footer}
		{$footer}
	</body>
	</html>';
	}

	if(!isset($templates->cache['ougcpages_portal']))
	{
		$templates->cache['ougcpages_portal'] = '{$pages[\'template\']}';
	}

	eval('$page = "'.$templates->get('ougcpages'.($portal ? '_portal' : '')).'";');

	if($portal)
	{
		return $page;
	}

	output_page($page);
	exit;
}

// Hijack the portal start
function ougc_pages_portal_start()
{
	global $settings;

	$settings['portal_announcementsfid'] = false;
}

// Hijack the portal announcements
function ougc_pages_portal_end()
{
	global $announcements;

	$announcements = ougc_pages_show(true);
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
if(!function_exists('control_object'))
{
	function control_object(&$obj, $code)
	{
		static $cnt = 0;
		$newname = '_objcont_'.(++$cnt);
		$objserial = serialize($obj);
		$classname = get_class($obj);
		$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
		$checkstr_len = strlen($checkstr);
		if(substr($objserial, 0, $checkstr_len) == $checkstr)
		{
			$vars = array();
			// grab resources/object etc, stripping scope info from keys
			foreach((array)$obj as $k => $v)
			{
				if($p = strrpos($k, "\0"))
				{
					$k = substr($k, $p+1);
				}
				$vars[$k] = $v;
			}
			if(!empty($vars))
			{
				$code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
			}
			eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
			$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
			if(!empty($vars))
			{
				$obj->___setvars($vars);
			}
		}
		// else not a valid object or PHP serialize has changed
	}
}

if(!function_exists('ougc_getpreview'))
{
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
	function ougc_getpreview($message, $maxlen=100, $stripquotes=true, $stripmycode=true)
	{
		// Attempt to remove quotes, skip if going to strip MyCode
		if($stripquotes && !$stripmycode)
		{
			$message = preg_replace(array(
			'#\[quote=([\"\']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"\']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi',
			'#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si',
			'#\[quote\]#si',
			'#\[\/quote\]#si'
			), '', $message);
		}

		// Attempt to remove any MyCode
		if($stripmycode)
		{
			global $parser;
			if(!is_object($parser))
			{
			require_once MYBB_ROOT.'inc/class_parser.php';
			$parser = new postParser;
			}

			$message = $parser->parse_message($message, array(
			'allow_html'		=>	0,
			'allow_mycode'		=>	1,
			'allow_smilies'		=>	0,
			'allow_imgcode'		=>	1,
			'filter_badwords'	=>	1,
			'nl2br'				=>	0
			));

			// before stripping tags, try converting some into spaces
			$message = preg_replace(array(
			'~\<(?:img|hr).*?/\>~si',
			'~\<li\>(.*?)\</li\>~si'
			), array(' ', "\n* $1"), $message);

			$message = unhtmlentities(strip_tags($message));
		}

		// convert \xA0 to spaces (reverse &nbsp;)
		$message = trim(preg_replace(array('~ {2,}~', "~\n{2,}~"), array(' ', "\n"), strtr($message, array("\xA0" => ' ', "\r" => '', "\t" => ' '))));

		// newline fix for browsers which don't support them
		$message = preg_replace("~ ?\n ?~", " \n", $message);

		// Shorten the message if too long
		if(my_strlen($message) > $maxlen)
		{
			$message = my_substr($message, 0, $maxlen-1).'...';
		}

		return htmlspecialchars_uni($message);
	}
}

if(!function_exists('print_selection_javascript'))
{
	function print_selection_javascript()
	{
		static $already_printed = false;

		if($already_printed)
		{
			return;
		}

		$already_printed = true;

		echo "<script type=\"text/javascript\">
		function checkAction(id)
		{
			var checked = '';

			$('.'+id+'_forums_groups_check').each(function(e, val)
			{
				if($(this).prop('checked') == true)
				{
					checked = $(this).val();
				}
			});

			$('.'+id+'_forums_groups').each(function(e)
			{
				$(this).hide();
			});

			if($('#'+id+'_forums_groups_'+checked))
			{
				$('#'+id+'_forums_groups_'+checked).show();
			}
		}
	</script>";
	}
}

// Our awesome class
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
	public $no_permission = false;

	// Build the class
	function __construct()
	{
	}

	// Loads language strings
	function lang_load()
	{
		global $lang;

		isset($lang->setting_group_ougc_pages) or $lang->load('ougc_pages');
	}

	// $PL->is_member(); helper
	function is_member($gids, $user=false)
	{
		global $PL;
		$PL or require_once PLUGINLIBRARY;

		return (bool)$PL->is_member((string)$gids, $user);
	}

	// Clean input
	function clean_ints($val, $implode=false)
	{
		if(!is_array($val))
		{
			$val = (array)explode(',', $val);
		}

		foreach($val as $k => &$v)
		{
			$v = (int)$v;
		}

		$val = array_filter($val);

		if($implode)
		{
			$val = (string)implode(',', $val);
		}

		return $val;
	}

	// Get PID by url input
	function get_cid_by_url($url)
	{
		global $db;

		$query = $db->simple_select('ougc_pages_categories', 'cid', 'url=\''.$db->escape_string($url).'\'');

		return (int)$db->fetch_field($query, 'cid');
	}

	// Update pages cache
	function update_cache()
	{
		global $db, $cache;

		$update = array();

		// Update categories
		$query = $db->simple_select('ougc_pages_categories', '*', 'visible=\'1\'', array('order_by' => 'disporder'));

		$update = array('categories' => array(), 'pages' => array());

		while($category = $db->fetch_array($query))
		{
			$update['categories'][(int)$category['cid']] = array(
				'name'			=> (string)$category['name'],
				'url'			=> (string)$category['url'],
				'groups'		=> (string)$category['groups'],
				'breadcrumb'	=> (bool)$category['breadcrumb']
			);
		}

		$db->free_result($query);

		// Update pages
		$query = $db->simple_select('ougc_pages', 'pid, url', 'visible=\'1\''.(!empty($update['categories']) ? ' AND cid NOT IN (\''.implode('\', \'', $update['categories']).'\')' : ''), array('order_by' => 'disporder'));

		while($pages = $db->fetch_array($query))
		{
			$update['pages'][(string)$pages['url']] = (int)$pages['pid'];
		}

		$db->free_result($query);

		$cache->update('ougc_pages', $update);
	}

	// Set url
	function set_url($url)
	{
		if(($url = trim($url)))
		{
			$this->url = $url;
		}
	}

	// Build an url parameter
	function build_url($urlappend=array(), $fetch_input_url=false)
	{
		global $PL;

		if(!is_object($PL))
		{
			return $this->url;
		}

		if($fetch_input_url === false)
		{
			if($urlappend && !is_array($urlappend))
			{
				$urlappend = explode('=', $urlappend);
				$urlappend = array($urlappend[0] => $urlappend[1]);
			}
		}
		else
		{
			$urlappend = $this->fetch_input_url($fetch_input_url);
		}

		return $PL->url_append($this->url, $urlappend, '&amp;', true);
	}

	// Build $limit and $start for queries
	function build_limit($limit=null, $spcl=1)
	{
		global $settings;

		$this->query_limit = isset($limit) ? (int)$limit : (int)$settings['ougc_pages_perpage'];
		$this->query_limit = $this->query_limit > 100 ? 100 : ($this->query_limit < 1 && $this->query_limit != $spcl ? 1 : $this->query_limit);
	}

	// Build a multipage.
	function build_multipage($count, $params=array(), $check=false)
	{
		global $mybb, $multipage;
	
		if($check)
		{
			$input = explode('=', $params);
			if(isset($mybb->input[$input[0]]) && $mybb->input[$input[0]] != $input[1])
			{
				$mybb->input['page'] =  0;
			}
		}

		if($mybb->get_input('page', 1) > 0)
		{
			$this->query_start = ($mybb->get_input('page', 1) - 1)*$this->query_limit;
			if($mybb->get_input('page', 1) > ceil($count/$this->query_limit))
			{
				$this->query_start = 0;
				$mybb->input['page'] = 1;
			}
		}
		else
		{
			$this->query_start = 0;
			$mybb->input['page'] = 1;
		}

		if(defined('IN_ADMINCP'))
		{
			$multipage = (string)draw_admin_pagination($mybb->get_input('page', 1), $this->query_limit, $count, $this->build_url($params));
		}
		else
		{
			$multipage = (string)multipage($count, $this->query_limit, $mybb->get_input('page', 1), $this->build_url($params));
		}
	}

	// Build the category link.
	function build_page_link($name, $cid)
	{
		$home = $this->build_url();

		return '<a href="'.$home.'/'.$this->get_category_link($cid).'">'.htmlspecialchars_uni($name).'</a>';
	}

	// Get the category link.
	function get_category_link($cid)
	{
		global $db, $settings;

		$cid = (int)$cid;

		$query = $db->simple_select('ougc_pages_categories', 'url', 'cid=\''.$cid.'\'');
		$url = $db->fetch_field($query, 'url');

		if($settings['ougc_pages_seo'])
		{
			$link = str_replace('{url}', $url, $sesttings['ougc_pages_seo_scheme_categories']);
		}
		else
		{
			$link = ($settings['ougc_pages_portal'] ? 'portal' : 'pages').'.php?category='.$url;
		}

		;
		return htmlspecialchars_uni($link);
	}

	// Build the page link.
	function build_category_link($name, $pid)
	{
		$home = $this->build_url();

		return '<a href="'.$home.'/'.$this->get_page_link($pid).'">'.htmlspecialchars_uni($name).'</a>';
	}

	// Get the page link.
	function get_page_link($pid)
	{
		global $db, $settings;

		$pid = (int)$pid;

		$query = $db->simple_select('ougc_pages', 'url', 'pid=\''.$pid.'\'');
		$url = $db->fetch_field($query, 'url');

		if($settings['ougc_pages_seo'])
		{
			$link = str_replace('{url}', $url, $sesttings['ougc_pages_seo_scheme']);
		}
		else
		{
			$link = ($settings['ougc_pages_portal'] ? 'portal' : 'pages').'.php?page='.$url;
		}

		return htmlspecialchars_uni($link);
	}

	// Get a category from the DB
	function get_category($cid, $cached=false)
	{
		if($cached)
		{
			global $cache;

			return $cache->cache['ougc_pages']['categories'][$cid];
		}

		if(!isset($this->cache['categories'][$cid]))
		{
			global $db;
			$this->cache['categories'][$cid] = false;

			$query = $db->simple_select('ougc_pages_categories', '*', 'cid=\''.(int)$cid.'\'');
			$category = $db->fetch_array($query);

			if(isset($category['cid']))
			{
				$this->cache['categories'][$cid] = $category;
			}
		}

		return $this->cache['categories'][$cid];
	}

	// Get a page from the DB
	function get_page($pid, $url=false)
	{
		if(!isset($this->cache['pages'][$pid]))
		{
			global $db;
			$this->cache['pages'][$pid] = false;

			$where = ($url === false ? 'pid=\''.(int)$pid.'\'' : 'url=\''.$db->escape_string($url).'\'');

			$query = $db->simple_select('ougc_pages', '*', $where);
			$pages = $db->fetch_array($query);

			if(isset($pages['pid']))
			{
				$this->cache['pages'][$pid] = $pages;
			}
		}

		return $this->cache['pages'][$pid];
	}

	// Get PID by url input
	function get_page_by_url($url)
	{
		return $this->get_page(null, $url);
	}

	// Redirect admin help function
	function redirect($message='', $error=false)
	{
		if(defined('IN_ADMINCP'))
		{
			!$message or flash_message($message, ($error ? 'error' : 'success'));

			admin_redirect($this->build_url());
		}
		else
		{
			redirect($this->build_url(), $message);
		}

		exit;
	}

	// Get bar from DB or cache
	function delete_page_category($cid)
	{
		global $db;

		$this->cid = (int)$cid;

		$db->delete_query('ougc_pages_categories', 'cid=\''.$this->cid.'\'');

		return $this->cid;
	}

	// Log admin action
	function log_action()
	{
		if($this->aid)
		{
			log_admin_action($this->aid);
		}
	}

	// Insert a new category to the DB
	function insert_category($data=array(), $update=false, $cid=0)
	{
		global $db;

		$insert_data = array();

		if(isset($data['name']))
		{
			$insert_data['name'] = $db->escape_string($data['name']);
		}

		if(isset($data['url']))
		{
			$insert_data['url'] = $db->escape_string($data['url']);
		}

		if(isset($data['groups']))
		{
			$insert_data['groups'] = $db->escape_string($data['groups']);
		}

		if(isset($data['disporder']))
		{
			$insert_data['disporder'] = (int)$data['disporder'];
		}

		if(isset($data['visible']))
		{
			$insert_data['visible'] = (int)$data['visible'];
		}

		if(isset($data['breadcrumb']))
		{
			$insert_data['breadcrumb'] = (int)$data['breadcrumb'];
		}

		if($insert_data)
		{
			global $plugins;

			if($update)
			{
				$this->cid = (int)$cid;
				$db->update_query('ougc_pages_categories', $insert_data, 'cid=\''.$this->cid.'\'');
			}
			else
			{
				$this->cid = (int)$db->insert_query('ougc_pages_categories', $insert_data);
			}

			$plugins->run_hooks('ouc_pages_'.($update ? 'update' : 'insert').'_category', $this);
		}
	}

	// Update espesific category
	function update_category($data=array(), $cid=0)
	{
		$this->insert_category($data, true, $cid);
	}

	// Insert a new page to the DB
	function insert_page($data=array(), $update=false, $pid=0)
	{
		global $db;

		$insert_data = array();

		if(isset($data['cid']))
		{
			$insert_data['cid'] = (int)$data['cid'];
		}

		if(isset($data['name']))
		{
			$insert_data['name'] = $db->escape_string($data['name']);
		}

		if(isset($data['url']))
		{
			$insert_data['url'] = $db->escape_string($data['url']);
		}

		if(isset($data['groups']))
		{
			$insert_data['groups'] = $db->escape_string($data['groups']);
		}

		if(isset($data['php']))
		{
			$insert_data['php'] = (int)$data['php'];
		}

		if(isset($data['wol']))
		{
			$insert_data['wol'] = (int)$data['wol'];
		}

		if(isset($data['disporder']))
		{
			$insert_data['disporder'] = (int)$data['disporder'];
		}

		if(isset($data['visible']))
		{
			$insert_data['visible'] = (int)$data['visible'];
		}

		if(isset($data['template']))
		{
			$insert_data['template'] = $db->escape_string($data['template']);
		}

		if(isset($data['dateline']))
		{
			$insert_data['dateline'] = (int)$data['dateline'];
		}
		else
		{
			$insert_data['dateline'] = TIME_NOW;
		}

		if($insert_data)
		{
			global $plugins;

			if($update)
			{
				$this->pid = (int)$pid;
				$db->update_query('ougc_pages', $insert_data, 'pid=\''.$this->pid.'\'');
			}
			else
			{
				$this->pid = (int)$db->insert_query('ougc_pages', $insert_data);
			}

			$plugins->run_hooks('ouc_pages_'.($update ? 'update' : 'insert').'_page', $this);
		}
	}

	// Update espesific page.
	function update_page($data=array(), $pid=0)
	{
		$this->insert_page($data, true, $pid);
	}
	
	// Generate a category selection box.
	function generate_category_select($name, $selected=array(), $options=array())
	{
		global $db;

		is_array($selected) or $selected = array($selected);

		$select = '<select name="'.$name.'"';
		
		if(isset($options['multiple']))
		{
			$select .= ' multiple="multiple"';
		}
		
		if(isset($options['class']))
		{
			$select .= ' class="'.$options['class'].'"';
		}
		
		if(isset($options['id']))
		{
			$select .= ' id="'.$options['id'].'"';
		}
		
		if(isset($options['size']))
		{
			$select .= ' size="'.$options['size'].'"';
		}
		
		$select .= '>';
		
		$query = $db->simple_select('ougc_pages_categories', 'cid, name', '', array('order_by' => 'disporder'));

		while($category = $db->fetch_array($query))
		{
			$s = '';
			if(in_array($category['cid'], $selected))
			{
				$s = ' selected="selected"';
			}
			$select .= '<option value="'.$category['cid'].'"'.$s.'>'.htmlspecialchars_uni($category['name']).'</option>';
		}
		
		$select .= '</select>';
		
		return $select;
	}

	// Cleans the unique url
	// Thanks Google SEO!
	function clean_url($url)
	{
		global $settings;

		$url = ougc_getpreview($url);

		$pattern = preg_replace('/[\\\\\\^\\-\\[\\]\\/]/u', '\\\\\\0', '!"#$%&\'( )*+,-./:;<=>?@[\]^_`{|}~');

		$url = preg_replace('/^['.$pattern.']+|['.$pattern.']+$/u', '', $url);

		$url = preg_replace('/['.$pattern.']+/u', '-', $url);

		return my_strtolower($url);
	}
}

$GLOBALS['ougc_pages'] = new OUGC_Pages;

// Initialize the plugin magic
if(THIS_SCRIPT == 'portal.php' || THIS_SCRIPT == 'pages.php')
{
	global $mybb;

	if(THIS_SCRIPT == 'portal.php' && !$mybb->settings['ougc_pages_portal'])
	{
		return;
	}

	global $templatelist, $ougc_pages;

	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	else
	{
		$templatelist = '';
	}

	$templatelist .= 'ougcpages, ougcpages_portal';

	if($mybb->get_input('page') && !empty($mybb->cache->cache['ougc_pages']['pages'][$mybb->get_input('page')]))
	{
		if(THIS_SCRIPT == 'portal.php')
		{
			global $plugins;

			$plugins->add_hook('portal_start', 'ougc_pages_portal_start');
			$plugins->add_hook('portal_end', 'ougc_pages_portal_end');
		}

		if($pages = $ougc_pages->get_page_by_url($mybb->get_input('page')))
		{
			$category = $ougc_pages->get_category($pages['cid'], true);
			if(!empty($category))
			{
				if(!$pages['wol'] && !defined('NO_ONLINE'))
				{
					define('NO_ONLINE', 1);
				}

				// Save three queries if no permission check is necessary
				if($category['groups'] || $pages['groups'])
				{
					// Create the session
					require_once MYBB_ROOT.'inc/class_session.php';
					$session = new session;
					$session->init();

					$ougc_pages->is_member($category['groups']) or $ougc_pages->no_permission = true;
					$ougc_pages->is_member($pages['groups']) or $ougc_pages->no_permission = true;
				}

				if($pages['php'] && !$ougc_pages->no_permission)
				{
					global $db, $templates, $plugins, $cache, $settings, $lang, $maintimer;

					#eval('? >'.$pages['template'].'<?php');
					eval('?>'.$pages['template']);
					exit;
				}
			}
			else
			{
				$ougc_pages->invalid_page = true;
			}
		}
		else
		{
			$ougc_pages->invalid_page = true;
		}
	}
}