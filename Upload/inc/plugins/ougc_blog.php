<?php

/***************************************************************************
 *
 *	OUGC Blog plugin (/inc/plugins/ougc_blog.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Adds a blog system to your forum.
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
	$plugins->add_hook('admin_config_menu', 'ougc_blog_config_menu');
	$plugins->add_hook('admin_config_action_handler', 'ougc_blog_config_action_handler');
	$plugins->add_hook('admin_config_permissions', 'ougc_blog_config_permissions');
	$plugins->add_hook('admin_page_output_header', 'ougc_blog_output_header');

	$plugins->add_hook('admin_config_settings_start', create_function('&$args', 'global $ougc_blog;	$ougc_blog->lang_load();'));
	$plugins->add_hook('admin_config_settings_change', create_function('&$args', 'global $ougc_blog;	$ougc_blog->lang_load();'));
}
else
{
	$plugins->add_hook('portal_end', 'ougc_blog_portal_end');
}

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_blog_info()
{
	global $lang, $ougc_blog;
	$ougc_blog->lang_load();

	return array(
		'name'			=> 'OUGC Blog',
		'description'	=> $lang->setting_group_ougc_blog_desc,
		'website'		=> 'http://mods.mybb.com/view/ougc-blog',
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
function ougc_blog_activate()
{
	global $PL, $lang, $cache;
	ougc_blog_deactivate();

	// Add settings group
	$PL->settings('ougc_blog', $lang->setting_group_ougc_blog, $lang->setting_group_ougc_blog_desc, array(
		'seo_scheme'			=> array(
		   'title'			=> $lang->setting_ougc_blog_seo_scheme,
		   'description'	=> $lang->setting_ougc_blog_seo_scheme_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'',
		),
		'seo_scheme_categories'	=> array(
		   'title'			=> $lang->setting_ougc_blog_seo_scheme_categories,
		   'description'	=> $lang->setting_ougc_blog_seo_scheme_categories_desc,
		   'optionscode'	=> 'text',
			'value'			=>	'',
		),
		/*'perpage'				=> array(
		   'title'			=> $lang->setting_ougc_blog_perpage,
		   'description'	=> $lang->setting_ougc_blog_perpage_desc,
		   'optionscode'	=> 'text',
			'value'			=>	20,
		)*/
	));

	// Add template group
	$PL->templates('ougcblog', '<lang:setting_group_ougc_blog>', array(
	''	=> ''
	));

	// Update administrator permissions
	change_admin_permission('config', 'ougc_blog');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_blog_info();

	if(!isset($plugins['blog']))
	{
		$plugins['blog'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['blog'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_blog_deactivate()
{
	ougc_blog_pl_check();

	// Update administrator permissions
	change_admin_permission('config', 'ougc_blog', 0);
}

// _install() routine
function ougc_blog_install()
{
	global $db;

	$collation = $db->build_create_table_collation();

	// Create our table(s)
	$db->write_query("CREATE TABLE `".TABLE_PREFIX."ougc_blog_posts` (
			`pid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`cid` int NOT NULL DEFAULT '0',
			`subject` varchar(100) NOT NULL DEFAULT '',
			`url` varchar(100) NOT NULL DEFAULT '',
			`uid` varchar(120) NOT NULL DEFAULT '',
			`ipaddress` varbinary(16) NOT NULL DEFAULT '',
			`visible` tinyint(1) NOT NULL DEFAULT '1',
			`dateline` int(10) NOT NULL DEFAULT '0',
			`edittime` int(10) NOT NULL DEFAULT '0',
			`message` text NOT NULL,
			PRIMARY KEY (`pid`),
			UNIQUE KEY `url` (`url`)
		) ENGINE=MyISAM{$collation};"
	);

	$db->write_query("CREATE TABLE `".TABLE_PREFIX."ougc_blog_categories` (
			`cid` int UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` varchar(100) NOT NULL DEFAULT '',
			`url` varchar(100) NOT NULL DEFAULT '',
			PRIMARY KEY (`cid`),
			UNIQUE KEY `url` (`url`)
		) ENGINE=MyISAM{$collation};"
	);
}

// _is_installed() routine
function ougc_blog_is_installed()
{
	global $db;

	return $db->table_exists('ougc_blog_posts');
}

// _uninstall() routine
function ougc_blog_uninstall()
{
	global $db, $PL, $cache;
	ougc_blog_pl_check();

	// Drop DB entries
	$db->drop_table('ougc_blog_posts');
	$db->drop_table('ougc_blog_categories');

	$PL->settings_delete('ougc_pages');
	$PL->templates_delete('ougcblog');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['blog']))
	{
		unset($plugins['blog']);
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
	change_admin_permission('config', 'ougc_blog', -1);
}

// PluginLibrary dependency check & load
function ougc_blog_pl_check()
{
	global $lang, $ougc_blog;
	$ougc_blog->lang_load();
	$info = ougc_blog_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_blog_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_blog_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Add menu to ACP
function ougc_blog_config_menu(&$args)
{
	global $ougc_blog, $lang;
	$ougc_blog->lang_load();

	$args[] = array(
		'id'	=> 'ougc_blog',
		'title'	=> $lang->ougc_blog_manage,
		'link'	=> 'index.php?module=config-ougc_blog'
	);
}

// Add action handler to config module
function ougc_blog_config_action_handler(&$args)
{
	$args['ougc_blog'] = array('visible' => 'ougc_blog', 'file' => 'ougc_blog.php');
}

// Insert plugin into the admin permissions page
function ougc_blog_config_permissions(&$args)
{
	global $ougc_blog, $lang;
	$ougc_blog->lang_load();

	$args['ougc_blog'] = $lang->ougc_blog_config_permissions;
}

// Show a flash message if plug-in requires updating
function ougc_blog_output_header()
{
	global $cache;

	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_blog_info();

	if(!isset($plugins['blog']))
	{
		$plugins['blog'] = $info['versioncode'];
	}

	if($info['versioncode'] != $plugins['blog'])
	{
		global $page, $ougc_blog, $lang;
		$ougc_blog->lang_load();

		$page->extra_messages['ougc_blog'] = array('message' => $lang->ougc_blog_error_update, 'type' => 'error');
	}
}

// Cache manager
function update_ougc_blog()
{
	global $ougc_blog;

	$ougc_blog->cache_update();
}

// Hijack the portal announcements
function ougc_blog_portal_end()
{
	global $mybb, $db, $multipage, $announcements, $parser, $templates, $theme, $lang, $ougc_blog;
	$lang->forum = 'Category: ';
	

	// Get latest blog posts
	// Build where clause
	$sqlwhere = array('p.visible=\'1\'');
	if($mybb->get_input('post'))
	{
		$sqlwhere[] = 'p.url=\''.$db->escape_string($mybb->get_input('post')).'\'';
	}

	$postscount = 0;
	
	$query = $db->simple_select('ougc_blog_posts p', "COUNT(p.pid) AS posts", implode(' AND ', $sqlwhere));
	$postscount = $db->fetch_field($query, 'posts');

	$numannouncements = (int)$mybb->settings['portal_numannouncements'];
	if(!$numannouncements)
	{
		$numannouncements = 10; // Default back to 10
	}

	$page = $mybb->get_input('page', 1);
	$pages = $postscount / $numannouncements;
	$pages = ceil($pages);

	if($page > $pages || $page <= 0)
	{
		$page = 1;
	}

	if($page)
	{
		$start = ($page-1) * $numannouncements;
	}
	else
	{
		$start = 0;
		$page = 1;
	}

	$multipage = multipage($postscount, $numannouncements, $page, 'portal.php');

	$query = $db->query("
		SELECT p.*, u.username, u.avatar, u.avatardimensions
		FROM ".TABLE_PREFIX."ougc_blog_posts p
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
		WHERE ".implode(' AND ', $sqlwhere)."
		ORDER BY p.dateline DESC
		LIMIT {$start}, {$numannouncements}"
	);

	$categories = $mybb->cache->read('ougc_blog');
	while($announcement = $db->fetch_array($query))
	{
		$category = $categories[$announcement['cid']];

		$trow2 = alt_trow();
		$trow1 = alt_trow();

		$announcement['visible'] or $trow1 = 'trow_shaded';
		#$announcement['ipaddress']
		#$announcement['edittime']

		$announcement['tid'] = 'post&amp;pid='.$announcement['pid'];
		$announcement['threadlink'] = $ougc_blog->get_post_link($announcement['pid']);
		$announcement['forumlink'] = $ougc_blog->get_category_link($announcement['cid']);
		$announcement['forumname'] = $category['name'];

		if(!$announcement['username'])
		{
			$announcement['username'] = $profilelink = $lang->guest;
		}

		if($announcement['uid'])
		{
			$profilelink = build_profile_link($announcement['username'], $announcement['uid']);
		}

		$announcement['subject'] = htmlspecialchars_uni($parser->parse_badwords($announcement['subject']));
		$icon = '&nbsp;';

		$useravatar = format_avatar(htmlspecialchars_uni($announcement['avatar']), $announcement['avatardimensions']);
		eval('$avatar = "'.$templates->get('portal_announcement_avatar').'";');

		$anndate = my_date('relative', $announcement['dateline']);

		$numcomments = $lastcomment = '';

		$senditem = '';

		$parser_options = array(
			"allow_html" => 0,
			"allow_mycode" => 1,
			"allow_smilies" => 1,
			"allow_imgcode" => 1,
			"allow_videocode" => 1,
			"filter_badwords" => 1
		);
		if($announcement['smilieoff'] == 1)
		{
			$parser_options['allow_smilies'] = 0;
		}

		if($mybb->user['showimages'] != 1 && $mybb->user['uid'] != 0 || $mybb->settings['guestimages'] != 1 && $mybb->user['uid'] == 0)
		{
			$parser_options['allow_imgcode'] = 0;
		}

		if($mybb->user['showvideos'] != 1 && $mybb->user['uid'] != 0 || $mybb->settings['guestvideos'] != 1 && $mybb->user['uid'] == 0)
		{
			$parser_options['allow_videocode'] = 0;
		}

		$message = $parser->parse_message($announcement['message'], $parser_options);

		$post['attachments'] = '';

		eval('$announcements .= "'.$templates->get('portal_announcement').'";');
	}
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

if(!function_exists('ougc_print_selection_javascript'))
{
	function ougc_print_selection_javascript()
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
class OUGC_blog
{
	// Define our ACP url
	public $url = 'index.php?module=config-plugins';

	// Maximum number of rows to return, for SQL queries and mulpage build
	public $query_limit = 10;

	// From what DB row start receiving what.eve.r, for SQL queries and mulpage build
	public $query_start = 0;

	// Build the class
	function __construct()
	{
	}

	// Loads language strings
	function lang_load()
	{
		global $lang;

		isset($lang->setting_group_ougc_blog) or $lang->load((defined('IN_ADMINCP') ? 'config_' : '').'ougc_blog');
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

		$query = $db->simple_select('ougc_blog_categories', 'cid', 'url=\''.$db->escape_string($url).'\'');

		return (int)$db->fetch_field($query, 'cid');
	}

	// Update blog cache
	function update_cache()
	{
		global $db, $cache;

		$update = array();

		$query = $db->simple_select('ougc_blog_categories');

		$update = array();

		while($category = $db->fetch_array($query))
		{
			$update[(int)$category['cid']] = array(
				'name'			=> (string)$category['name'],
				'url'			=> (string)$category['url']
			);
		}

		$db->free_result($query);

		$cache->update('ougc_blog', $update);
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

		//$this->query_limit = isset($limit) ? (int)$limit : (int)$settings['ougc_blog_perpage'];
		$this->query_limit = isset($limit) ? (int)$limit : 20;
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
	function build_post_link($name, $pid)
	{
		return '<a href="'.$this->get_page_link($pid).'">'.htmlspecialchars_uni($name).'</a>';
	}

	// Get the category link.
	function get_category_link($cid)
	{
		global $db, $settings;

		$cid = (int)$cid;

		$query = $db->simple_select('ougc_blog_categories', 'url', 'cid=\''.$cid.'\'');
		$url = $db->fetch_field($query, 'url');

		if(my_strpos($settings['ougc_blog_seo_scheme_categories'], '{url}') !== false)
		{
			$url = str_replace('{url}', $url, $settings['ougc_blog_seo_scheme_categories']);
		}
		else
		{
			$url = 'portal.php?category='.$url;
		}

		return htmlspecialchars_uni($url);
	}

	// Build the page link.
	function build_category_link($name, $pid)
	{
		return '<a href="'.$this->get_category_link($pid).'">'.htmlspecialchars_uni($name).'</a>';
	}

	// Get the page link.
	function get_post_link($pid)
	{
		global $db, $settings;

		$pid = (int)$pid;

		$query = $db->simple_select('ougc_blog_posts', 'url', 'pid=\''.$pid.'\'');
		$url = $db->fetch_field($query, 'url');

		if(my_strpos($settings['ougc_blog_seo_scheme'], '{url}') !== false)
		{
			$link = str_replace('{url}', $url, $settings['ougc_blog_seo_scheme']);
		}
		else
		{
			$link = 'portal.php?post='.$url;
		}

		return htmlspecialchars_uni($link);
	}

	// Get a category from the DB
	function get_category($cid, $url=false)
	{
		global $cache;

		if(!isset($this->cache['categories'][$cid]))
		{
			global $db;
			$this->cache['categories'][$cid] = false;

			$where = ($url === false ? 'cid=\''.(int)$cid.'\'' : 'url=\''.$db->escape_string($url).'\'');

			$query = $db->simple_select('ougc_blog_categories', '*', $where);
			$category = $db->fetch_array($query);

			if(isset($category['cid']))
			{
				$this->cache['categories'][$cid] = $category;
			}
		}

		return $this->cache['categories'][$cid];
	}

	// Get PID by url input
	function get_category_by_url($url)
	{
		return $this->get_category(null, $url);
	}

	// Get a page from the DB
	function get_page($pid, $url=false)
	{
		if(!isset($this->cache['blog'][$pid]))
		{
			global $db;
			$this->cache['blog'][$pid] = false;

			$where = ($url === false ? 'pid=\''.(int)$pid.'\'' : 'url=\''.$db->escape_string($url).'\'');

			$query = $db->simple_select('ougc_blog_posts', '*', $where);
			$blog = $db->fetch_array($query);

			if(isset($blog['pid']))
			{
				$this->cache['blog'][$pid] = $blog;
			}
		}

		return $this->cache['blog'][$pid];
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

	// Delete category from DB
	function delete_page_category($cid)
	{
		global $db;

		$this->cid = (int)$cid;

		$db->delete_query('ougc_blog_categories', 'cid=\''.$this->cid.'\'');

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

		if($insert_data)
		{
			global $plugins;

			if($update)
			{
				$this->cid = (int)$cid;
				$db->update_query('ougc_blog_categories', $insert_data, 'cid=\''.$this->cid.'\'');
			}
			else
			{
				$this->cid = (int)$db->insert_query('ougc_blog_categories', $insert_data);
			}

			$plugins->run_hooks('ouc_blog_'.($update ? 'update' : 'insert').'_category', $this);
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

		if(isset($data['subject']))
		{
			$insert_data['subject'] = $db->escape_string($data['subject']);
		}

		if(isset($data['url']))
		{
			$insert_data['url'] = $db->escape_string($data['url']);
		}

		if(isset($data['uid']))
		{
			$insert_data['uid'] = (int)$data['uid'];
		}

		if(isset($data['ipaddress']))
		{
			$insert_data['ipaddress'] = $db->escape_string($data['ipaddress']);
		}

		if(isset($data['visible']))
		{
			$insert_data['visible'] = (int)$data['visible'];
		}

		if(isset($data['message']))
		{
			$insert_data['message'] = $db->escape_string($data['message']);
		}
		elseif(!$update)
		{
			$insert_data['message'] = '';
		}

		$insert_data['edittime'] = TIME_NOW;

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
				$db->update_query('ougc_blog_posts', $insert_data, 'pid=\''.$this->pid.'\'');
			}
			else
			{
				$this->pid = (int)$db->insert_query('ougc_blog_posts', $insert_data);
			}

			$plugins->run_hooks('ouc_blog_'.($update ? 'update' : 'insert').'_page', $this);
		}
	}

	// Update espesific page.
	function update_page($data=array(), $pid=0)
	{
		$this->insert_page($data, true, $pid);
	}

	// Delete page from DB
	function delete_page($pid)
	{
		global $db;

		$this->pid = (int)$pid;

		$db->delete_query('ougc_blog', 'pid=\''.$this->pid.'\'');

		return $this->pid;
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
		
		$query = $db->simple_select('ougc_blog_categories', 'cid, name', '');

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

	// Cleans the unique URL
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

$GLOBALS['ougc_blog'] = new OUGC_blog;