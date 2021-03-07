<?php
defined('SMART_PATH') or die('Hacking attempt!');

// +-----------------------------------------------------------------------+
// | Basic checks                                                          |
// +-----------------------------------------------------------------------+

check_status(ACCESS_ADMINISTRATOR);

$page['active_menu'] = get_active_menu('album');

check_input_parameter('cat_id', $_GET, false, PATTERN_ID);
$cat_id = $_GET['cat_id'];

$admin_album_base_url = get_root_url().'admin.php?page=album-'.$cat_id;
$self_url = SMART_ADMIN.'-album&amp;cat_id='.$cat_id;

$query = '
SELECT *
  FROM '.CATEGORIES_TABLE.'
  WHERE id = '.$cat_id.'
;';
$category = pwg_db_fetch_assoc(pwg_query($query));

if (!isset($category['id']))
{
  die("unknown album");
}

// category must be virtual
if ($category['dir'] != NULL)
{
  die("physical album");
}


// +-----------------------------------------------------------------------+
// | Tabs                                                                  |
// +-----------------------------------------------------------------------+

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('album');
$tabsheet->select('smartalbum');
$tabsheet->assign();


// +-----------------------------------------------------------------------+
// | Save Filters                                                          |
// +-----------------------------------------------------------------------+

if (isset($_POST['submitFilters']))
{
  if (defined('SMART_DEBUG'))
  {
    var_dump($_POST['filters']);
  }

  // test if it was a Smart Album
  $query = '
SELECT DISTINCT category_id
  FROM '.CATEGORY_FILTERS_TABLE.'
  WHERE category_id = '.$cat_id.'
;';
  $was_smart = pwg_db_num_rows(pwg_query($query));

  /* this album is no longer a SmartAlbum */
  if ($was_smart and !isset($_POST['is_smart']))
  {
    pwg_query('DELETE FROM '.IMAGE_CATEGORY_TABLE.' WHERE category_id = '.$cat_id.' AND smart = true;');
    pwg_query('DELETE FROM '.CATEGORY_FILTERS_TABLE.' WHERE category_id = '.$cat_id.';');

    set_random_representant(array($cat_id));

    define('SMART_NOT_UPDATE', 1);
    invalidate_user_cache();
  }
  /* no filter selected */
  else if (isset($_POST['is_smart']) and empty($_POST['filters']))
  {
    $page['errors'][] = l10n('No filter selected');
  }
  /* everything is fine */
  else if (isset($_POST['is_smart']))
  {
    pwg_query('DELETE FROM '.CATEGORY_FILTERS_TABLE.' WHERE category_id = '.$cat_id.';');

    $inserts = array();
    foreach ($_POST['filters'] as $filter)
    {
      if (($filter = smart_check_filter($filter)) != false)
      {
        $filter['category_id'] = $cat_id;
        $inserts[] = $filter;
      }
    }

    mass_inserts(
      CATEGORY_FILTERS_TABLE,
      array('category_id', 'type', 'cond', 'value'),
      $inserts,
      array('ignore'=>true)
      );

    $associated_images = smart_make_associations($cat_id);
    $template->assign('IMAGE_COUNT', l10n_dec('%d photo', '%d photos', count($associated_images)));

    $page['infos'][] = l10n('%d photos associated to album %s', count($associated_images), '');

    define('SMART_NOT_UPDATE', 1);
    invalidate_user_cache();
  }
}

// +-----------------------------------------------------------------------+
// | Display page                                                          |
// +-----------------------------------------------------------------------+

/* select options, for html_options */
$options = array(
  'tags' => array(
    'name' => l10n('Tags'),
    'options' => array(
      'all'   => l10n('All these tags'),
      'one'   => l10n('One of these tags'),
      'none'  => l10n('None of these tags'),
      'only'  => l10n('Only these tags'),
      ),
    ),
  'date' => array(
    'name' => l10n('Date'),
    'options' => array(
      'the_post'     => l10n('Added on'),
      'before_post'  => l10n('Added before'),
      'after_post'   => l10n('Added after'),
      'the_taken'    => l10n('Created on'),
      'before_taken' => l10n('Created before'),
      'after_taken'  => l10n('Created after'),
      ),
    ),
  'name' => array(
    'name' => l10n('Photo name'),
    'options' => array(
      'contain'     => l10n('Contains'),
      'begin'       => l10n('Begins with'),
      'end'         => l10n('Ends with'),
      'not_contain' => l10n('Doesn\'t contain'),
      'not_begin'   => l10n('Doesn\'t begin with'),
      'not_end'     => l10n('Doesn\'t end with'),
      'regex'       => l10n('Regular expression'),
      ),
    ),
  'album' => array(
    'name' => l10n('Album'),
    'options' => array(
      'all'   => l10n('All these albums'),
      'one'   => l10n('One of these albums'),
      'none'  => l10n('None of these albums'),
      'only'  => l10n('Only these albums'),
      ),
    ),
  'dimensions' => array(
    'name' => l10n('Dimensions'),
    'options' => array(
      'width'  => l10n('Width'),
      'height' => l10n('Height'),
      'ratio'  => l10n('Ratio').' ('.l10n('Width').'/'.l10n('Height').')',
      ),
    ),
  'author' => array(
    'name' => l10n('Author'),
    'options' => array(
      'is'      => l10n('Is'),
      'in'      => l10n('Is in'),
      'not_is'  => l10n('Is not'),
      'not_in'  => l10n('Is not in'),
      'regex'   => l10n('Regular expression'),
      ),
    ),
  'hit' => array(
    'name' => l10n('Hits'),
    'options' => array(
      'less' => l10n('Bellow'),
      'more' => l10n('Above'),
      ),
    ),
  'rating_score' => array(
    'name' => l10n('Rating score'),
    'options' => array(
      'less' => l10n('Bellow'),
      'more' => l10n('Above'),
      ),
    ),
  'level' => array(
    'name' => l10n('Privacy level'),
    'options' => array(),
    ),
  'limit' => array(
    'name' => l10n('Max. number of photos'),
    'options' => array(
      ''                    => '-- ' . l10n('Default') . ' --',
      'file ASC'            => l10n('File name, A &rarr; Z'),
      'file DESC'           => l10n('File name, Z &rarr; A'),
      'name ASC'            => l10n('Photo title, A &rarr; Z'),
      'name DESC'           => l10n('Photo title, Z &rarr; A'),
      'date_creation DESC'  => l10n('Date created, new &rarr; old'),
      'date_creation ASC'   => l10n('Date created, old &rarr; new'),
      'date_available DESC' => l10n('Date posted, new &rarr; old'),
      'date_available ASC'  => l10n('Date posted, old &rarr; new'),
      'rating_score DESC'   => l10n('Rating score, high &rarr; low'),
      'rating_score ASC'    => l10n('Rating score, low &rarr; high'),
      'hit DESC'            => l10n('Visits, high &rarr; low'),
      'hit ASC'             => l10n('Visits, low &rarr; high'),
      'id ASC'              => l10n('Numeric identifier, 1 &rarr; 9'),
      'id DESC'             => l10n('Numeric identifier, 9 &rarr; 1'),
      ),
    ),
  'public_physical' => array(
      'name' => l10n('From public physical albums'),
      'sif_lnk' => ($conf['SmartAlbums']['smart_is_forbidden'])?'Smart is forbidden':'Smart is allowed',
      'sif' => ($conf['SmartAlbums']['smart_is_forbidden'])?'visitor permissions prepend. Pictures from private albums will be rejected for ungranted visitors. Hence, <strong>using this filter is not required</strong>.':'static permissions. Pictures from private albums may be associated to this virtual smart album. <strong>Using this filter will prevent this to happen</strong>.',
    ),
  'permission' => array(
      'name' => l10n('Permission from'),
      'options' => array(
        'public_physical'   => l10n('From public physical albums'),
        'public_virtual'    => l10n('From public virtual albums'),
        'public_any'        => l10n('From public any albums'),
      ),
      'sif_lnk' => ($conf['SmartAlbums']['smart_is_forbidden'])?'Smart is forbidden':'Smart is allowed',
      'sif' => ($conf['SmartAlbums']['smart_is_forbidden'])?'visitor permissions prepend. Pictures from private albums will be rejected for ungranted visitors. Hence, <strong>using this filter is not required</strong>.':'static permissions. Pictures from private albums may be associated to this virtual smart album. <strong>Using this filter will prevent this to happen</strong>.',
    ),
  );
$template->assign('options', $options);


/* get filters for this album */
$query = '
SELECT *
  FROM '.CATEGORY_FILTERS_TABLE.'
  WHERE category_id = '.$cat_id.'
  ORDER BY
    type ASC,
    cond ASC
;';
$result = pwg_query($query);

$template->assign('filter_mode', 'and');

while ($filter = pwg_db_fetch_assoc($result))
{
  if ($filter['type'] == 'mode')
  {
    $template->assign('filter_mode', $filter['value']);
    continue;
  }
  else if ($filter['type'] == 'tags')
  {
    $query = '
SELECT id, name
  FROM '.TAGS_TABLE.'
  WHERE id IN('.$filter['value'].')
;';
    $filter['value'] = get_taglist($query);
  }

  $template->append('filters', $filter);
}


/* format types */
$template->assign('format_options', array(
  'portrait' => l10n('Portrait'),
  'square'   => l10n('square'),
  'lanscape' => l10n('Landscape'),
  'panorama' => l10n('Panorama'),
  ));

/* all tags */
$query = '
SELECT id, name
  FROM '.TAGS_TABLE.'
;';
$template->assign('all_tags', get_taglist($query));

/* all albums */
$query = '
SELECT
    id,
    name,
    uppercats,
    global_rank
  FROM '.CATEGORIES_TABLE.'
;';
display_select_cat_wrapper($query, array(), 'all_albums');

// +-----------------------------------------------------------------------+
// |                              dimensions                               |
// +-----------------------------------------------------------------------+

$widths = array();
$heights = array();
$ratios = array();

// get all width, height and ratios
$query = '
SELECT
  DISTINCT width, height
  FROM '.IMAGES_TABLE.'
  WHERE width IS NOT NULL
    AND height IS NOT NULL
;';
$result = pwg_query($query);

while ($row = pwg_db_fetch_assoc($result))
{
  if ($row['width']>0 && $row['height']>0)
  {
    $widths[] = $row['width'];
    $heights[] = $row['height'];
    $ratios[] = floor($row['width'] / $row['height'] * 100) / 100;
  }
}

$widths = array_unique($widths);
sort($widths);

$heights = array_unique($heights);
sort($heights);

$ratios = array_unique($ratios);
sort($ratios);

$dimensions['bounds'] = array(
  'min_width' => $widths[0],
  'max_width' => $widths[count($widths)-1],
  'min_height' => $heights[0],
  'max_height' => $heights[count($heights)-1],
  'min_ratio' => $ratios[0],
  'max_ratio' => $ratios[count($ratios)-1],
  );

// find ratio categories
$ratio_categories = array(
  'portrait' => array(),
  'square' => array(),
  'landscape' => array(),
  'panorama' => array(),
  );

foreach ($ratios as $ratio)
{
  if ($ratio < 0.95)
  {
    $ratio_categories['portrait'][] = $ratio;
  }
  else if ($ratio >= 0.95 and $ratio <= 1.05)
  {
    $ratio_categories['square'][] = $ratio;
  }
  else if ($ratio > 1.05 and $ratio < 2)
  {
    $ratio_categories['landscape'][] = $ratio;
  }
  else if ($ratio >= 2)
  {
    $ratio_categories['panorama'][] = $ratio;
  }
}

foreach ($ratio_categories as $ratio_name => $ratio_values)
{
  if (count($ratio_values) > 0)
  {
    $dimensions['ratio_'.$ratio_name] = array(
      'min' => $ratio_values[0],
      'max' => array_pop($ratio_values),
      );
  }
}

$template->assign('dimensions', $dimensions);

/* get image number */
if ($template->get_template_vars('IMAGE_COUNT') == null)
{
  $query = '
SELECT count(1)
  FROM '.IMAGE_CATEGORY_TABLE.'
  WHERE
    category_id = '.$cat_id.'
    AND smart = true
';

  list($image_num) = pwg_db_fetch_row(pwg_query($query));
  $template->assign('IMAGE_COUNT', l10n_dec('%d photo', '%d photos', $image_num));
}


/* template vars */
if (isset($_GET['new_smart']))
{
  $template->assign('new_smart', true);
}

$template->assign(array(
  'COUNT_SCRIPT_URL' => SMART_PATH.'include/count_images.php',
  'level_options' => get_privacy_level_options(),
  'F_ACTION' => $self_url,
  'CATEGORIES_NAV' => get_cat_display_name_cache($category['uppercats'], SMART_ADMIN.'-album&amp;cat_id='),
));

$template->set_filename('SmartAlbums_content', realpath(SMART_PATH . 'admin/template/album.tpl'));
