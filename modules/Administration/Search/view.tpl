<h1 class="title">{sugar_translate label="LBL_SEARCH_HEADER"}</h1>

<form id="SearchSettings"
      name="ConfigureSettings"
      class="detail-view"
      enctype='multipart/form-data'
      method="POST"
      action="index.php?module=Administration&action=SearchSettings&do=Save"
      onsubmit="SUGAR.saveGlobalSearchSettings();">

    <input type="hidden" name="module" value="Administration">
    <input type='hidden' name='enabled_modules' value=''>

    <div class="row g-0">
        <div class="panel panel-primary">
            <div class="panel-heading">{$MOD.LBL_SEARCH_INTERFACE}</div>
            <div class="panel-body tab-content text-center d-flex align-items-center">
                <div class="col-md-6">
                    <div class="form-check text-start p-0 mb-0">
                        <div>
                            <label for="search-engine">{sugar_translate label="LBL_SEARCH_ENGINE"}</label>
                            {sugar_help text=$MOD.LBL_SEARCH_ENGINE_TOOLTIP}
                        </div>
                        <div>
                            <small class="form-text text-muted">{sugar_translate label="LBL_SEARCH_ENGINE_HELP"}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    {html_options
                    options=$engines
                    selected=$selectedEngine
                    id="search-engine"
                    name="search-engine"
                    class="form-control"
                    }
                </div>
            </div>
        </div>
    </div>

    {include file='modules/Administration/Search/GlobalSearchSettings.tpl'}

    {$JAVASCRIPT}

    <div class="settings-buttons">
        {$BUTTONS}
    </div>
</form>

