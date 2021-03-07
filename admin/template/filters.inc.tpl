<!-- tags -->
<div id="filter_tags">
<li id="filter_iiii" class="filter_tags">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="tags"/>
    {$options.tags.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.tags.options}
    </select>
  </span>

  <span class="filter-value">
    <select name="filters[iiii][value]" class="tagSelect">
    </select>
  </span>
</li>
</div>

<!-- date -->
<div id="filter_date">
<li id="filter_iiii" class="filter_date">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="date"/>
    {$options.date.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.date.options}
    </select>
  </span>

  <span class="filter-value">
    <input type="text" name="filters[iiii][value]" size="30"/>
  </span>
</li>
</div>

<!-- name -->
<div id="filter_name">
<li id="filter_iiii" class="filter_name">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="name"/>
    {$options.name.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.name.options}
    </select>
  </span>

  <span class="filter-value">
    <input type="text" name="filters[iiii][value]" size="30"/>
  </span>
</li>
</div>

<!-- album -->
<div id="filter_album">
<li id="filter_iiii" class="filter_album">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="album"/>
    {$options.album.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.album.options}
    </select>
  </span>

  <span class="filter-value">
    <select name="filters[iiii][value][]" class="albumSelect" multiple="multiple" data-placeholder="{'Select albums...'|translate}">
      {html_options options=$all_albums}
    </select>
    
    <label><input type="checkbox" name="filters[iiii][recursive]"> {'include child albums'|translate}</label>
  </span>
</li>
</div>

<!-- dimensions -->
<div id="filter_dimensions">
<li id="filter_iiii" class="filter_dimensions">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="dimensions"/>
    {$options.dimensions.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.dimensions.options}
    </select>
  </span>

  <span class="filter-value">
    <span class="dimension_width">
      <span class="filter_dimension_info"></span>
        | <a class="dimensions-choice" data-type="width" data-min="{$dimensions.bounds.min_width}" data-max="{$dimensions.bounds.max_width}">{'Reset'|translate}</a>
        <div class="filter_dimension_width_slider"></div>
    </span>

    <span class="dimension_height">
      <span class="filter_dimension_info"></span>
        | <a class="dimensions-choice" data-type="height" data-min="{$dimensions.bounds.min_height}" data-max="{$dimensions.bounds.max_height}">{'Reset'|translate}</a>
        <div class="filter_dimension_height_slider"></div>
    </span>

    <span class="dimension_ratio">
      <span class="filter_dimension_info"></span>
      {if isset($dimensions.ratio_portrait)}
        | <a class="dimensions-choice" data-type="ratio" data-min="{$dimensions.ratio_portrait.min}" data-max="{$dimensions.ratio_portrait.max}">{'Portrait'|translate}</a>
      {/if}
      {if isset($dimensions.ratio_square)}
        | <a class="dimensions-choice" data-type="ratio" data-min="{$dimensions.ratio_square.min}" data-max="{$dimensions.ratio_square.max}">{'square'|translate}</a>
      {/if}
      {if isset($dimensions.ratio_landscape)}
        | <a class="dimensions-choice" data-type="ratio" data-min="{$dimensions.ratio_landscape.min}" data-max="{$dimensions.ratio_landscape.max}">{'Landscape'|translate}</a>
      {/if}
      {if isset($dimensions.ratio_panorama)}
        | <a class="dimensions-choice" data-type="ratio" data-min="{$dimensions.ratio_panorama.min}" data-max="{$dimensions.ratio_panorama.max}">{'Panorama'|translate}</a>
      {/if}
        | <a class="dimensions-choice" data-type="ratio" data-min="{$dimensions.bounds.min_ratio}" data-max="{$dimensions.bounds.max_ratio}">{'Reset'|translate}</a>
        <div class="filter_dimension_ratio_slider"></div>
    </span>
  </span>

  <input type="hidden" name="filters[iiii][value][min]" value="">
  <input type="hidden" name="filters[iiii][value][max]" value="">
</li>
</div>

<!-- author -->
<div id="filter_author">
<li id="filter_iiii" class="filter_author">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="author"/>
    {$options.author.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.author.options}
    </select>
  </span>

  <span class="filter-value">
    <input type="text" name="filters[iiii][value]" size="30"/>
    <br><i>{'For "Is (not) in", separate each author by a comma'|translate}</i>
  </span>
</li>
</div>

<!-- hit -->
<div id="filter_hit">
<li id="filter_iiii" class="filter_hit">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="hit"/>
    {$options.hit.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.hit.options}
    </select>
  </span>

  <span class="filter-value">
    <input type="number" name="filters[iiii][value]" size="5"/>
  </span>
</li>
</div>

<!-- rating_score -->
<div id="filter_rating_score">
<li id="filter_iiii" class="filter_rating_score">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="rating_score"/>
    {$options.rating_score.name}
  </span>

  <span class="filter-cond">
    <select name="filters[iiii][cond]">
      {html_options options=$options.rating_score.options}
    </select>
  </span>

  <span class="filter-value">
    <input type="number" name="filters[iiii][value]" size="5"/>
  </span>
</li>
</div>

<!-- level -->
<div id="filter_level">
<li id="filter_iiii" class="filter_level">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="level"/>
    {$options.level.name}
  </span>

  <input type="hidden" name="filters[iiii][cond]" value="level"/>

  <span class="filter-value">
    <select name="filters[iiii][value]">
      {html_options options=$level_options}
    </select>
  </span>
</li>
</div>

<!-- limit -->
<div id="filter_limit">
<li id="filter_iiii" class="filter_limit">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="limit"/>
    {$options.limit.name}
  </span>

  <input type="hidden" name="filters[iiii][cond]" value="limit"/>
  
  <span class="filter-value" style="width:200px;">
    <input type="number" name="filters[iiii][value]" size="5"/>
  </span>
  
  <span class="filter-cond" style="width:auto;">
    <b>{'Sort order'|translate}</b>
    <select name="filters[iiii][cond]">
      {html_options options=$options.limit.options}
    </select>
    <br><i>{'The sort order is only used in addition to the limit filter, it does not impact the final display order'|translate}</i>
  </span>
</li>
</div>

<!-- public_physical -->
<div id="filter_public_physical">
<li id="filter_iiii" class="filter_public_physical">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="public_physical"/>
    {$options.public_physical.name}
  </span>
  
  <span><b>{'Info'|translate}</b> <a href="admin.php?page=plugin-SmartAlbums-config" title="{'Edit'|translate} {'Exclude SmartAlbums from permissions management'|translate}">{$options.permission.sif_lnk}</a> : {$options.permission.sif}</span>
</li>
</div>

<!-- permission -->
<div id="filter_permission">
<li id="filter_iiii" class="filter_permission">
  <span class="filter-title">
    <a href="#" class="removeFilter" title="{'remove this filter'|translate}"><span>[x]</span></a>
    <input type="hidden" name="filters[iiii][type]" value="permission"/>
    {$options.permission.name}
  </span>
  
  <span class="filter-cond" style="width:auto;">
    <b>{'From'|translate}</b>
    <select name="filters[iiii][cond]">
      {html_options options=$options.permission.options}
    </select>
    <br><i>{'The permission'|translate}</i>
  </span>
  
  <span><b>{'Info'|translate}</b> <a href="admin.php?page=plugin-SmartAlbums-config" title="{'Edit'|translate} {'Exclude SmartAlbums from permissions management'|translate}">{$options.permission.sif_lnk}</a> : {$options.permission.sif}</span>
</li>
</div>
