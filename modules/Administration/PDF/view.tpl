<h1 class="title">{sugar_translate label="LBL_PDF_HEADER"}</h1>

<form id="SearchSettings"
      name="ConfigureSettings"
      class="detail-view"
      enctype='multipart/form-data'
      method="POST"
      action="index.php?module=Administration&action=PDFSettings&do=Save">

    <input type="hidden" name="module" value="Administration">

    <div class="row g-0">
        <div class="panel panel-primary">
            <div class="panel-heading">{$MOD.LBL_PDF_INTERFACE}</div>
            <div class="panel-body tab-content text-center d-flex align-items-center">
                <div class="col-md-6">
                    <div class="form-check text-start p-0 mb-0">
                        <div>
                            <label for="pdf-engine">{sugar_translate label="LBL_PDF_OPTIONS"}</label>
                        </div>
                        <div>
                            <small class="form-text text-muted">{sugar_translate label="LBL_PDF_OPTIONS_HELP"}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    {html_options
                    options=$engines
                    selected=$selectedEngine
                    id="pdf-engine"
                    name="pdf-engine"
                    class="form-control"
                    }
                </div>
            </div>
        </div>
    </div>

    {$JAVASCRIPT}

    <div class="settings-buttons">
        {$BUTTONS}
    </div>
</form>

