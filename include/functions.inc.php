<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

/*
 * Associates images to the category according to the filters
 * @param int category_id
 * @return array
 */
function smart_make_associations($cat_id)
{
  $query = '
DELETE FROM '.IMAGE_CATEGORY_TABLE.' 
  WHERE 
    category_id = '.$cat_id.' 
    AND smart = true
;';
  pwg_query($query);
  
  $images = smart_get_pictures($cat_id);
  
  if (count($images) != 0)
  {
    foreach ($images as $img)
    {
      $datas[] = array(
        'image_id' => $img,
        'category_id' => $cat_id,
        'smart' => true,
        );
    }
    mass_inserts(
      IMAGE_CATEGORY_TABLE, 
      array_keys($datas[0]), 
      $datas,
      array('ignore'=>true)
      );
  }
  
  // representant, try to not overwrite if still in images list
  $query = '
SELECT representative_picture_id
  FROM '.CATEGORIES_TABLE.'
  WHERE id = '.$cat_id.'
;';
  list($rep_id) = pwg_db_fetch_row(pwg_query($query));
  
  if ( !in_array($rep_id, $images) )
  {
    include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
    set_random_representant(array($cat_id));
  }
  
  $query = '
UPDATE '.CATEGORIES_TABLE.'
  SET smart_update = NOW()
  WHERE id = '.$cat_id.'
;';
  pwg_query($query);
  
  return $images;
}


/*
 * Make associations for all SmartAlbums
 * Called with invalidate_user_cache
 */
function smart_make_all_associations()
{
  global $conf;
  
  if ( defined('SMART_NOT_UPDATE') OR !$conf['SmartAlbums']['update_on_upload'] ) return;
  
  // get categories with smart filters
  $query = '
SELECT DISTINCT id
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.CATEGORY_FILTERS_TABLE.' AS cf
    ON c.id = cf.category_id
;';
  
  // regenerate photo list
  $smart_cats = array_from_query($query, 'id');
  array_map('smart_make_associations', $smart_cats);
}


/*
 * Generates the list of images, according to the filters of the category
 * @param int category_id
 * @param array filters, if null => catch from db
 * @return array
 */
function smart_get_pictures($cat_id, $filters = null)
{
  global $conf;

  /* get filters */
  if ($filters === null)
  {
    $query = '
SELECT * 
  FROM '.CATEGORY_FILTERS_TABLE.' 
  WHERE category_id = '.$cat_id.' 
  ORDER BY type ASC, cond ASC
;';
    $result = pwg_query($query);
    
    if (!pwg_db_num_rows($result)) return array();
    
    $filters = array();
    while ($row = pwg_db_fetch_assoc($result))
    {
      $filters[] = array(
        'type' => $row['type'],
        'cond' => $row['cond'],
        'value' => $row['value'],
        );
    }
  }
  else if (!count($filters))
  {
    return array();
  }
  
  $mode = 'and';
  
  /* build constrains */
  ## generate 'join', 'where' arrays and 'limit' string to create the SQL query
  ## inspired by PicsEngine 3 by Michael Villar
  $i_tags = 1;
  foreach ($filters as $filter)
  {
    switch ($filter['type'])
    {
      // tags
      case 'tags':
      {
        switch ($filter['cond'])
        {
          // search images which have all tags
          case 'all':
          {
            $tags_arr = explode(',', $filter['value']);
            foreach($tags_arr as $value)
            {
              $join[] = IMAGE_TAG_TABLE.' AS it'.$i_tags.' ON i.id = it'.$i_tags.'.image_id';
              $where[] = 'it'.$i_tags.'.tag_id = '.$value;
              $i_tags++;
            }
            
            break;
          }
          // search images which tags are in the list
          case 'one':
          {
            $join[] = IMAGE_TAG_TABLE.' AS it'.$i_tags.' ON i.id = it'.$i_tags.'.image_id';
            $where[] = 'it'.$i_tags.'.tag_id IN ('.$filter['value'].')';
            $i_tags++;
            
            break;
          }
          // exclude images which tags are in the list
          case 'none':
          {
            $sub_query = '
      SELECT it'.$i_tags.'.image_id
        FROM '.IMAGE_TAG_TABLE.' AS it'.$i_tags.'
        WHERE 
          it'.$i_tags.'.image_id = i.id AND
          it'.$i_tags.'.tag_id IN ('.$filter['value'].')
        GROUP BY it'.$i_tags.'.image_id
      ';
            $join[] = IMAGE_TAG_TABLE.' AS it'.$i_tags.' ON i.id = it'.$i_tags.'.image_id';
            $where[] = 'NOT EXISTS ('.$sub_query.')';
            $i_tags++;
            
            break;
          }
          // exclude images which tags are not in the list and search images which have all tags
          case 'only':
          {
            $sub_query = '
      SELECT it'.$i_tags.'.image_id
        FROM '.IMAGE_TAG_TABLE.' AS it'.$i_tags.'
        WHERE 
          it'.$i_tags.'.image_id = i.id AND
          it'.$i_tags.'.tag_id NOT IN ('.$filter['value'].')
        GROUP BY it'.$i_tags.'.image_id
      ';
            $join[] = IMAGE_TAG_TABLE.' AS it'.$i_tags.' ON i.id = it'.$i_tags.'.image_id';
            $where[] = 'NOT EXISTS ('.$sub_query.')';
            $i_tags++;
            
            $tags_arr = explode(',', $filter['value']);
            foreach($tags_arr as $value)
            {
              $join[] = IMAGE_TAG_TABLE.' AS it'.$i_tags.' ON i.id = it'.$i_tags.'.image_id';
              $where[] = 'it'.$i_tags.'.tag_id = '.$value;
              $i_tags++;
            }
            
            break;
          }
        }
        
        break;
      }
    
      // date
      case 'date':
      {
        switch ($filter['cond'])
        {
          case 'the_post':
            $where[] = 'date_available BETWEEN "'.$filter['value'].' 00:00:00" AND "'.$filter['value'].' 23:59:59"';
            break;
          case 'before_post':
            $where[] = 'date_available < "'.$filter['value'].' 00:00:00"';
            break;
          case 'after_post':
            $where[] = 'date_available > "'.$filter['value'].' 23:59:59"';
            break;
          case 'the_taken':
            $where[] = 'date_creation BETWEEN "'.$filter['value'].' 00:00:00" AND "'.$filter['value'].' 23:59:59"';
            break;
          case 'before_taken':
            $where[] = 'date_creation < "'.$filter['value'].' 00:00:00"';
            break;
          case 'after_taken':
            $where[] = 'date_creation > "'.$filter['value'].' 23:59:59"';
            break;
        }
        
        break;
      }
      
      // name
      case 'name':
      {
        switch ($filter['cond'])
        {
          case 'contain':
            $where[] = 'name LIKE "%'.$filter['value'].'%"';
            break;
          case 'begin':
            $where[] = 'name LIKE "'.$filter['value'].'%"';
            break;
          case 'end':
            $where[] = 'name LIKE "%'.$filter['value'].'"';
            break;
          case 'not_contain':
            $where[] = 'name NOT LIKE "%'.$filter['value'].'%"';
            break;
          case 'not_begin':
            $where[] = 'name NOT LIKE "'.$filter['value'].'%"';
            break;
          case 'not_end':
            $where[] = 'name NOT LIKE "%'.$filter['value'].'"';
            break;
          case 'regex':
            $where[] = 'name REGEXP "'.$filter['value'].'"';
            break;
        }
        
        break;
      }
      
      // album
      case 'album':
      {
        switch ($filter['cond'])
        {
          // search images existing in all albums
          case 'all':
          {
            $albums_arr = explode(',', $filter['value']);
            foreach($albums_arr as $value)
            {
              $sub_query = '
      SELECT image_id 
        FROM '.IMAGE_CATEGORY_TABLE.'
        WHERE category_id = '.$value.'
      ';
              $where[] = 'i.id IN ('.$sub_query.')';
            }
            
            break;
          }
          // search images existing in one of these albums
          case 'one':
          {
            $sub_query = '
      SELECT image_id
        FROM '.IMAGE_CATEGORY_TABLE.'
        WHERE category_id IN('.$filter['value'].')
      ';
            $where[] = 'i.id IN ('.$sub_query.')';
            
            break;
          }
          // exclude images existing in one of these albums
          case 'none':
          {
            $sub_query = '
      SELECT image_id
        FROM '.IMAGE_CATEGORY_TABLE.'
        WHERE category_id IN('.$filter['value'].')
      ';
            $where[] = 'i.id NOT IN ('.$sub_query.')';
            
            break;
          }
          // exclude images existing on other albums, and search images existing in all albums
          case 'only':
          {
            $sub_query = '
      SELECT image_id
        FROM '.IMAGE_CATEGORY_TABLE.'
        WHERE category_id NOT IN('.$filter['value'].')
      ';
            $where[] = 'i.id NOT IN ('.$sub_query.')';
            
            $albums_arr = explode(',', $filter['value']);
            foreach($albums_arr as $value)
            {
              $sub_query = '
      SELECT image_id 
        FROM '.IMAGE_CATEGORY_TABLE.'
        WHERE category_id = '.$value.'
      ';
              $where[] = 'i.id IN ('.$sub_query.')';
            }
            
            break;
          }
        }
        
        break;
      }
      
      // dimensions
      case 'dimensions':
      {
        $filter['value'] = explode(',', $filter['value']);
        
        switch ($filter['cond'])
        {
          case 'width':
            $where[] = 'width >= '.$filter['value'][0].' AND width <= '.$filter['value'][1];
            break;
          case 'height':
            $where[] = 'height >= '.$filter['value'][0].' AND height <= '.$filter['value'][1];
            break;
          case 'ratio':
            $where[] = 'width/height >= '.$filter['value'][0].' AND width/height < '.($filter['value'][1]+0.01);
            break;
        }
      }
      
      // author
      case 'author':
      {
        switch ($filter['cond'])
        {
          case 'is':
            if ($filter['value'] != 'NULL') $filter['value'] = '"'.$filter['value'].'"';
            $where[] = 'author = '.$filter['value'].'';
            break;
          case 'not_is':
            if ($filter['value'] != 'NULL') $filter['value'] = '"'.$filter['value'].'"';
            $where[] = 'author != '.$filter['value'].'';
            break;
          case 'in':
            $filter['value'] = '"'.str_replace(',', '","', $filter['value']).'"';
            $where[] = 'author IN('.$filter['value'].')';
            break;
          case 'not_in':
            $filter['value'] = '"'.str_replace(',', '","', $filter['value']).'"';
            $where[] = 'author NOT IN('.$filter['value'].')';
            break;
          case 'regex':
            $where[] = 'author REGEXP "'.$filter['value'].'"';
            break;
        }
        
        break;
      }
      
      // hit
      case 'hit':
      {
        switch ($filter['cond'])
        {
          case 'less':
            $where[] = 'hit < '.$filter['value'].'';
            break;
          case 'more':
            $where[] = 'hit >= '.$filter['value'].'';
            break;
        }
        
        break;
      }
      
      // rating_score
      case 'rating_score':
      {
        switch ($filter['cond'])
        {
          case 'less':
            $where[] = 'rating_score < '.$filter['value'].'';
            break;
          case 'more':
            $where[] = 'rating_score >= '.$filter['value'].'';
            break;
        }
        
        break;
      }
      
      // level
      case 'level':
      {
        $where[] = 'level = '.$filter['value'].'';
        break;
      }
      
      // limit
      case 'limit':
      {
        $limit = '0, '.$filter['value'];
        break;
      }
      
      // mode
      case 'mode':
      {
        $mode = $filter['value'];
        break;
      }
    }
  }
  
  /* bluid query */
  $MainQuery = '
SELECT i.id
  FROM '.IMAGES_TABLE.' AS i';
    
    if (isset($join))
    {
      $MainQuery.= '
    LEFT JOIN '.implode("\n    LEFT JOIN ", $join);
    }
    if (isset($where))
    {
      $MainQuery.= '
  WHERE
    '.implode("\n    ".$mode." ", $where);
    }

  $MainQuery.= '
  GROUP BY i.id
  '.$conf['order_by'].'
  '.(isset($limit) ? "LIMIT ".$limit : null).'
;';

  if (defined('SMART_DEBUG'))
  {
    file_put_contents(SMART_PATH.'dump_filters.txt', print_r($filters, true));
    file_put_contents(SMART_PATH.'dump_query.sql', $MainQuery);
  }
  
  return array_from_query($MainQuery, 'id');
}


/**
 * Check if the filter is proper
 * @param array filter
 * @return array or false
 */
function smart_check_filter($filter)
{
  global $page, $limit_is_set, $level_is_set;
  $error = false;
  
  if (!isset($limit_is_set)) $limit_is_set = false;
  if (!isset($level_is_set)) $level_is_set = false;
  
  switch ($filter['type'])
  {
    # tags
    case 'tags':
    {
      if ($filter['value'] == null)
      {
        $error = true;
        array_push($page['errors'], l10n('No tag selected'));
      }
      else
      {
        include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
        $filter['value'] = implode(',', get_tag_ids($filter['value']));
      }
      break;
    }
    # date
    case 'date':
    {
      if (!preg_match('#([0-9]{4})-([0-9]{2})-([0-9]{2})#', $filter['value']))
      {
        $error = true;
        array_push($page['errors'], l10n('Date string is malformed'));
      }
      break;
    }
    # name
    case 'name':
    {
      if (empty($filter['value']))
      {
        $error = true;
        array_push($page['errors'], l10n('Name is empty'));
      }
      else if ( $filter['cond']=='regex' and @preg_match('/'.$filter['value'].'/', null)===false )
      {
        $error = true;
        array_push($page['errors'], l10n('Regex is malformed'));
      }
      break;
    }
    # album
    case 'album':
    {
      if (@$filter['value'] == null)
      {
        $error = true;
        array_push($page['errors'], l10n('No album selected'));
      }
      else
      {
        $filter['value'] = implode(',', $filter['value']);
      }
      break;
    }
    # dimensions
    case 'dimensions':
    {
      if ( empty($filter['value']['min']) or empty($filter['value']['max']) )
      {
        $error = true;
      }
      else
      {
        $filter['value'] = $filter['value']['min'].','.$filter['value']['max'];
      }
      break;
    }
    # author
    case 'author':
    {
      if (empty($filter['value']))
      {
        $error = true;
        array_push($page['errors'], l10n('Author is empty'));
      }
      else if ( $filter['cond']=='regex' and @preg_match('/'.$filter['value'].'/', null)===false )
      {
        $error = true;
        array_push($page['errors'], l10n('Regex is malformed'));
      }
      else
      {
        $filter['value'] = preg_replace('#([ ]?),([ ]?)#', ',', $filter['value']);
      }
      break;
    }
    # hit
    case 'hit':
    {
      if (!preg_match('#([0-9]{1,})#', $filter['value']))
      {
        $error = true;
        array_push($page['errors'], l10n('Hits must be an integer'));
      }
      break;
    }
    # rating_score
    case 'rating_score':
    {
      if (!preg_match('#([0-9]{1,})#', $filter['value']))
      {
        $error = true;
        array_push($page['errors'], l10n('Rating score must be an integer'));
      }
      break;
    }
    # level
    case 'level':
    {
      if ($level_is_set == true) // only one level is allowed, first is saved
      {
        $error = true;
        array_push($page['errors'], l10n('You can\'t use more than one level filter'));
      }
      else
      {
        $filter['cond'] = 'level';
        $level_is_set = true;
      }
      break;
    }
    # limit
    case 'limit':
    {
      if ($limit_is_set == true) // only one limit is allowed, first is saved
      {
        $error = true;
        array_push($page['errors'], l10n('You can\'t use more than one limit filter'));
      }
      else if (!preg_match('#([0-9]{1,})#', $filter['value']))
      {
        $error = true;
        array_push($page['errors'], l10n('Limit must be an integer'));
      }
      else 
      {
        $filter['cond'] = 'level';
        $limit_is_set = true;
      }
      break;
    }
    # mode
    case 'mode':
    {
      $filter['cond'] = 'mode';
      break;
    }
    
    default:
    {
      $error = true;
      break;
    }
  }
  
  
  if ($error == false)
  {
    return $filter;
  }
  else
  {
    return false;
  }
}


/**
 * clean table when categories are deleted
 */
function smart_delete_categories($ids)
{
  $query = '
DELETE FROM '.CATEGORY_FILTERS_TABLE.'
  WHERE category_id IN('.implode(',', $ids).')
;';
  pwg_query($query);
}

/**
 * update images list periodically
 */
function smart_periodic_update()
{
  global $conf;
  
  // we only search for old albums every hour, nevermind which user is connected
  if ($conf['SmartAlbums']['last_update'] > time() - 3600) return;
  
  $conf['SmartAlbums']['last_update'] = time();
  conf_update_param('SmartAlbums', serialize($conf['SmartAlbums']));
  
  // get categories with smart filters
  $query = '
SELECT DISTINCT id
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.CATEGORY_FILTERS_TABLE.' AS cf
    ON c.id = cf.category_id
  WHERE smart_update < DATE_SUB(NOW(), INTERVAL '.$conf['SmartAlbums']['update_timeout'].' DAY)
;';
  
  // regenerate photo list
  $smart_cats = array_from_query($query, 'id');
  array_map('smart_make_associations', $smart_cats);
}

?>