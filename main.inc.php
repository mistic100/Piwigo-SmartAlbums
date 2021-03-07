<?php
/*
Plugin Name: SmartAlbums
Version: auto
Description: Easily create dynamic albums with tags, date and other criteria
Plugin URI: auto
Author: Mistic
Author URI: http://www.strangeplanet.fr
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

if (basename(dirname(__FILE__)) != 'SmartAlbums')
{
  add_event_handler('init', 'smartalbums_error');
  function smartalbums_error()
  {
    global $page;
    $page['errors'][] = 'SmartAlbums folder name is incorrect, uninstall the plugin and rename it to "SmartAlbums"';
  }
  return;
}

global $prefixeTable;

define('SMART_ID',      'SmartAlbums');
define('SMART_PATH',    PHPWG_PLUGINS_PATH . SMART_ID . '/');
define('CATEGORY_FILTERS_TABLE', $prefixeTable . 'category_filters');
define('SMART_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . SMART_ID);
// define('SMART_DEBUG', true);


add_event_handler('init', 'smart_init');
add_event_handler('init', 'smart_periodic_update');
add_event_handler('delete_categories', 'smart_delete_categories');

if (defined('IN_ADMIN'))
{
  include_once(SMART_PATH.'include/events_admin.inc.php');
  add_event_handler('loc_begin_cat_list', 'smart_cat_list');
  add_event_handler('tabsheet_before_select','smart_tab', EVENT_HANDLER_PRIORITY_NEUTRAL, 2);
  add_event_handler('get_admin_plugin_menu_links', 'smart_admin_menu');
}

include_once(SMART_PATH.'include/events.inc.php');
include_once(SMART_PATH.'include/functions.inc.php');


/**
 * update plugin & unserialize conf & load language
 */
function smart_init()
{
  global $conf;

  if (defined('IN_ADMIN'))
  {
    load_language('plugin.lang', SMART_PATH);
  }
  $conf['SmartAlbums'] = safe_unserialize($conf['SmartAlbums']);

  if (script_basename() == 'index' and $conf['SmartAlbums']['smart_is_forbidden'])
  {
    add_event_handler('loc_end_section_init', 'smart_init_page_items');
  }

  if ($conf['SmartAlbums']['update_on_upload'])
  {
    add_event_handler('invalidate_user_cache', 'smart_make_all_associations');
  }
}
