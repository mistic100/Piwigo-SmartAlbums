{footer_script require="jquery"}
jQuery(".showInfo").tipTip({
  delay: 0,
  fadeIn: 200,
  fadeOut: 200,
  maxWidth: '300px',
  defaultPosition:"center"
});
{/footer_script}


<div class="titrePage">
  <h2>SmartAlbums</h2>
</div>

<form method="post" action="" class="properties">
  <fieldset id="commentsConf">
    <ul>
      <li>
        <label>
          <input type="checkbox" name="update_on_date" value="true" {if $update_on_date}checked="checked"{/if}/>
          {assign var=input value='</label><input type="text" name="update_timeout" size="2" value="'|cat:$update_timeout|cat:'"/>'}
          <b>{'Update albums every %s days'|translate:$input}</b>
        <!-- label -->
      </li>
      <li>
        <label>
          <input type="checkbox" name="update_on_upload" value="true" {if $update_on_upload}checked="checked"{/if}/>
          <b>{'Update albums on file upload'|translate}</b>
          <a class="icon-info-circled-1 showInfo" title="{'can cause slowdowns'|translate}"></a>
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="smart_is_forbidden" value="true" {if $smart_is_forbidden}checked="checked"{/if}/>
          <b>{'Exclude SmartAlbums from permissions management'|translate}</b>
          <a class="icon-info-circled-1 showInfo" title="{'SmartAlbums are considered private for everyone, and a user can see it\'s content only if available in another album he has access to.'|translate}"></a>
        </label>
      </li>
    </ul>
  </fieldset>

  <p class="formButtons"><input class="submit" type="submit" value="{'Submit'|translate}" name="submit" /></p>
</form>
