{combine_css path=$SMART_PATH|cat:'admin/template/style.css'}
{include file='include/datepicker.inc.tpl'}
{combine_script id='common' load='footer' path='admin/themes/default/js/common.js'}

{combine_css path='themes/default/js/plugins/jquery.tokeninput.css'}
{combine_script id='jquery.tokeninput' load='footer' require='jquery' path='themes/default/js/plugins/jquery.tokeninput.js'}

{combine_css path='themes/default/js/plugins/chosen.css'}
{combine_script id='jquery.chosen' load='footer' path='themes/default/js/plugins/chosen.jquery.min.js'}

{combine_css path='themes/default/js/ui/theme/jquery.ui.slider.css'}
{combine_script id='jquery.ui.slider' require='jquery.ui' load='footer' path='themes/default/js/ui/minified/jquery.ui.slider.min.js'}


{footer_script require='jquery'}
var addFilter = (function($){
  var count=0,
      limit_count=0,
      level_count=0;

  // MAIN EVENT HANDLERS
  $('#addFilter').change(function() {
    if ($(this).val() != -1) {
      add_filter($(this).val());
      $(this).val(-1);
    }
  });

  $('#removeFilters').click(function() {
    $('#filtersList li:not(.empty)').remove();
    $('#filtersList li.empty').show();

    $("#addFilter option[value='limit']").removeAttr('disabled');
    $("#addFilter option[value='level']").removeAttr('disabled');

    limit_level=0;
    level_count=0;

    return false;
  });

  $('input[name="is_smart"]').change(function() {
    $('#SmartAlbum_options').toggle();
    $('input[name="countImages"]').toggle();
    $('.count_images_wrapper').toggle();
  });

  $('input[name="countImages"]').click(function() {
    countImages($("#smart"));
    return false;
  });


  // ADD FILTER FUNCTIONS
  function add_filter(type, cond, value) {
    count++;

    $('#filtersList li.empty').hide();

    var content = $("#filtersRepo #filter_"+type).html().replace(/iiii/g, count),
        $block = $($.parseHTML(content)).appendTo("#filtersList");

    if (cond) {
      select_cond($block, type, cond);
    }

    if (value) {
      if (type == "tags") {
        $block.find(".filter-value .tagSelect").html(value);
      }
      else if (type == "album") {
        var values = value.split(','),
            recursive = values.splice(0, 1)[0];
        value = values.join(',');

        select_options($block, value);
        $block.find('.filter-value input[type="checkbox"]').prop('checked', recursive=="true");
      }
      else if (type == "level") {
        select_options($block, value);
      }
      else if (type != "dimensions") {
        $block.find(".filter-value input").val(value);
      }
    }

    init_jquery_handlers($block);

    if (type == "dimensions") {
      select_dimensions($block, cond, value);
    }

    if (type == 'limit') {
      limit_count=1;
      $("#addFilter option[value='limit']").attr('disabled','disabled');
    }
    else if (type == 'level') {
      level_count=1;
      $("#addFilter option[value='level']").attr('disabled','disabled');
    }
  }

  function select_cond($block, type, cond) {
    $block.find(".filter-cond option").removeAttr('selected');
    $block.find(".filter-cond option[value='"+cond+"']").attr('selected', 'selected');
  }

  function select_dimensions($block, cond, value) {
    cond = cond || 'width';
    var values;

    $block.find(">.filter-value>span").hide();
    $block.find(".dimension_"+cond).show();

    if (value) {
      values = value.split(',');
    }
    else {
      values = $block.find(".filter_dimension_"+cond+"_slider").slider("values");
    }
    $block.find(".filter_dimension_"+cond+"_slider").slider("values", values);
  }

  function select_options($block, value) {
    var values = value.split(',');
    for (var j in values) {
      $block.find(".filter-value option[value='"+ values[j] +"']").attr('selected', 'selected');
    }
  }


  // DECLARE JQUERY PLUGINS AND VERSATILE HANDLERS
  function init_jquery_handlers($block) {
    // remove filter
    $block.find(".removeFilter").click(function() {
      var type = $(this).next("input").val();

      if (type == 'limit') {
        limit_count=1;
        $("#addFilter option[value='limit']").removeAttr('disabled');
      }
      else if (type == 'level') {
        level_count=1;
        $("#addFilter option[value='level']").removeAttr('disabled');
      }

      $(this).parents('li').remove();
      return false;
    });

    // date filter
    if ($block.hasClass('filter_date')) {
      $block.find("input[type='text']").each(function() {
        $(this).datepicker({
          dateFormat:'yy-mm-dd',
          firstDay:1
        });
      });
    }

    // tags filter
    if ($block.hasClass('filter_tags')) {
      $block.find(".tagSelect").tokenInput(
        [{foreach from=$all_tags item=tag name=tags}{ name:"{$tag.name|escape:javascript}", id:"{$tag.id}" }{if !$smarty.foreach.tags.last},{/if}{/foreach}],
        {
          hintText: '{'Type in a search term'|translate}',
          noResultsText: '{'No results'|translate}',
          searchingText: '{'Searching...'|translate}',
          animateDropdown: false,
          preventDuplicates: true,
          allowFreeTagging: false
        }
      );
    }

    // album filter
    if ($block.hasClass('filter_album')) {
      $block.find(".albumSelect").chosen();
    }

    // dimension filter
    if ($block.hasClass('filter_dimensions')) {
      $block.find(".filter-cond select").change(function() {
        select_dimensions($block, $(this).val());
      });

      $block.find(".filter_dimension_width_slider").slider({
        range: true,
        min: {$dimensions.bounds.min_width},
        max: {$dimensions.bounds.max_width},
        values: [{$dimensions.bounds.min_width}, {$dimensions.bounds.max_width}],
        slide: function(event, ui) {
          change_dimension_info($block, ui.values, "{'between %d and %d pixels'|translate}");
        },
        change: function(event, ui) {
          change_dimension_info($block, ui.values, "{'between %d and %d pixels'|translate}");
        }
      });

      $block.find(".filter_dimension_height_slider").slider({
        range: true,
        min: {$dimensions.bounds.min_height},
        max: {$dimensions.bounds.max_height},
        values: [{$dimensions.bounds.min_height}, {$dimensions.bounds.max_height}],
        slide: function(event, ui) {
          change_dimension_info($block, ui.values, "{'between %d and %d pixels'|translate}");
        },
        change: function(event, ui) {
          change_dimension_info($block, ui.values, "{'between %d and %d pixels'|translate}");
        }
      });

      $block.find(".filter_dimension_ratio_slider").slider({
        range: true,
        step: 0.01,
        min: {$dimensions.bounds.min_ratio},
        max: {$dimensions.bounds.max_ratio},
        values: [{$dimensions.bounds.min_ratio}, {$dimensions.bounds.max_ratio}],
        slide: function(event, ui) {
          change_dimension_info($block, ui.values, "{'between %.2f and %.2f'|translate}");
        },
        change: function(event, ui) {
          change_dimension_info($block, ui.values, "{'between %.2f and %.2f'|translate}");
        }
      });

      $block.find("a.dimensions-choice").click(function() {
        $block.find(".filter_dimension_"+ $(this).data("type") +"_slider").slider("values",
          [$(this).data("min"), $(this).data("max")]
        );
      });
    }
  }


  // GENERAL FUNCTIONS
  function change_dimension_info($block, values, text) {
    $block.find("input[name$='[value][min]']").val(values[0]);
    $block.find("input[name$='[value][max]']").val(values[1]);
    $block.find(".filter_dimension_info").html(sprintf(text, values[0], values[1]));
  }

  function countImages(form) {
    jQuery.post("{$COUNT_SCRIPT_URL}", 'cat_id={$CAT_ID}&'+form.serialize(),
      function success(data) {
        jQuery('.count_images_wrapper').html(data);
      }
    );
  }

  return add_filter; // expose add_filter method
}(jQuery));

{if isset($new_smart)}
function doBlink(obj, start, finish) {
  jQuery(obj).fadeOut(400).fadeIn(400);

  if (start != finish) {
    doBlink(obj,start+1,finish);
  }
  else {
    jQuery(obj).fadeOut(400);
  }
}

doBlink('.new_smart', 0, 3);
{/if}
{/footer_script}


<div class="titrePage">
  <h2><span style="letter-spacing:0">{$CATEGORIES_NAV}</span> &#8250; {'Edit album'|translate} [SmartAlbum]</h2>
</div>

<noscript>
  <div class="errors"><ul><li>JavaScript required!</li></ul></div>
</noscript>

<div id="batchManagerGlobal">
<form action="{$F_ACTION}" method="POST" id="smart">
  <p style="text-align:left;"><label><input type="checkbox" name="is_smart" {if isset($filters) OR isset($new_smart)}checked="checked"{/if}/> {'This album is a SmartAlbum'|translate}</label></p>

  <fieldset id="SmartAlbum_options" style="margin-top:1em;{if !isset($filters) AND !isset($new_smart)}display:none;{/if}">
    <legend>{'Filters'|translate}</legend>
    
    <div>
      <label><input type="radio" name="filters[0][value]" value="and" {if $filter_mode=='and'}checked="checked"{/if}> {'Photos must match all filters'|translate}</label>
      <label><input type="radio" name="filters[0][value]" value="or" {if $filter_mode=='or'}checked="checked"{/if}> {'Photos must match at least one filter'|translate}</label>
      <input type="hidden" name="filters[0][type]" value="mode">
      <input type="hidden" name="filters[0][cond]" value="mode">
    </div>

    <fieldset>
    <ul id="filtersList">
      <li class="empty">{'No filter'|translate}</li>
    {foreach from=$filters item=filter}{strip}
      {if $filter.type == 'tags'}
        {capture assign='value'}{foreach from=$filter.value item=tag}<option value="{$tag.id}" class="selected">{$tag.name}</option>{/foreach}{/capture}
      {else}
        {assign var='value' value=$filter.value}
      {/if}

      {if $filter.type == 'limit'}
        {footer_script}
        limit_count=1;
        $("#addFilter option[value='limit']").attr('disabled','disabled');
        {/footer_script}
      {elseif $filter.type == 'level'}
        {footer_script}
        level_count=1;
        $("#addFilter option[value='level']").attr('disabled','disabled');
        {/footer_script}
      {/if}

      {footer_script}addFilter('{$filter.type}', '{$filter.cond}', '{$value|escape:javascript}');{/footer_script}
    {/strip}{/foreach}
    </ul>
    </fieldset>

    <p class="actionButtons">
      <select id="addFilter">
        <option value="-1">{'Add a filter'|translate}</option>
        <option disabled="disabled">------------------</option>
        <option value="tags">{'Tags'|translate}</option>
        <option value="date">{'Date'|translate}</option>
        <option value="name">{'Photo name'|translate}</option>
        <option value="album">{'Album'|translate}</option>
        <option value="dimensions">{'Dimensions'|translate}</option>
        <option value="author">{'Author'|translate}</option>
        <option value="hit">{'Hits'|translate}</option>
        <option value="rating_score">{'Rating score'|translate}</option>
        <option value="level">{'Privacy level'|translate}</option>
        <option value="limit">{'Max. number of photos'|translate}</option>
        <option value="public_physical">{'From public physical albums'|translate}</option>
        <option value="permission">{'Permission from'|translate}</option>
      </select>
      <a id="removeFilters">{'Remove all filters'|translate}</a>
      {if isset($new_smart)}<span class="new_smart">{'Add filters here'|translate}</span>{/if}
    </p>
  </fieldset>

  <p class="actionButtons" id="applyFilterBlock">
    <input class="submit" type="submit" value="{'Save'|translate}" name="submitFilters"/>
    <input class="submit" type="submit" value="{'Count'|translate}" name="countImages" {if !isset($filters) AND !isset($new_smart)}style="display:none;"{/if}/>
    <span class="count_images_wrapper" {if !isset($filters) AND !isset($new_smart)}style="display:none;"{/if}><span class="count_image">{$IMAGE_COUNT}</span></span>
  </p>

</form>
</div>

<div id="filtersRepo" style="display:none;">
  {include file=$SMART_ABS_PATH|cat:'admin/template/filters.inc.tpl'}
</div>
