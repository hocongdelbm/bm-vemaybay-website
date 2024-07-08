<h1 class="title">{$MOD.LBL_ELASTIC_SEARCH_SETTINGS}</h1>
<p class="elastic_search_help">{$MOD.LBL_ELASTIC_SEARCH_SETTINGS_HELP}</p>

<form id="ConfigureSettings"
      name="ConfigureSettings"
      class="detail-view"
      enctype='multipart/form-data'
      method="POST"
      action="?module=Administration&action=ElasticSearchSettings&do=SaveConfig">

      <div class="elastic-search-row my-3 d-flex gap-4 flex-column">
          <div class="row g-0">
              <div class="panel panel-primary">
                  <div class="panel-heading">{$MOD.LBL_ELASTIC_SEARCH_GENERAL}</div>
                  <div class="panel-body tab-content text-center d-flex align-items-center">
                      <div class="col-md-6">
                          <div class="d-flex align-items-center gap-2 form-check text-start mb-0">
                              <input type="checkbox" class="form-check-input" id="es-enabled" name="enabled" {if $config.enabled}checked='checked'{/if}>
                              <label class="form-check-label" for="es-enabled">{$MOD.LBL_ELASTIC_SEARCH_ENABLE}</label>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <button class="btn btn-primary" id="es-test-connection" type="button">{$MOD.LBL_ELASTIC_SEARCH_TEST_CONNECTION}</button>
                      </div>
                  </div>
              </div>
          </div>
      
          <div class="row">
              <div class="col-md-6">
                  <div class="panel panel-primary">
                      <div class="panel-heading">{$MOD.LBL_ELASTIC_SEARCH_SERVER}</div>
                      <div class="panel-body tab-content">
                          <div class="form-group">
                              <label for="es-host">{$MOD.LBL_ELASTIC_SEARCH_HOST}</label>
                              <input type="text" class="form-control"
                                     id="es-host" name="host" value="{$config.host}">
                              <small class="form-text text-muted">e.g. localhost, 192.168.1.1:9200,
                                  mydomain.server.com:9201, https://192.168.1.3:9200
                              </small>
                          </div>
                          <div class="form-group">
                              <label for="es-user">{$MOD.LBL_ELASTIC_SEARCH_USER}</label>
                              <input type="text" class="form-control"
                                     id="es-user" name="user" value="{$config.user}">
                              <label for="es-password">{$MOD.LBL_ELASTIC_SEARCH_PASS}</label>
                              <input type="password" class="form-control"
                                     id="es-password" name="pass" value="{$config.pass}">
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-md-6">
                  <div class="panel panel-primary">
                      <div class="panel-heading">{$MOD.LBL_ELASTIC_SEARCH_SCHEDULERS}</div>
                      <div class="panel-body tab-content">
                          <label>{$MOD.LBL_ELASTIC_SEARCH_SCHEDULERS_HELP}</label>
                          <ul class="list-group">
                              {foreach from=$schedulers item=scheduler}
                                  <li class="list-group-item {if $scheduler->status eq 'Inactive'}list-group-item-warning{/if}">
                                      {sugar_link
                                      module=$scheduler->module_name
                                      record=$scheduler->id
                                      label=$scheduler->name
                                      action='DetailView'}
                                      &mdash;
                                      <b>{$scheduler->status}</b>
                                      &mdash;
                                      {if !empty($scheduler->last_run)}
                                          {$MOD.LBL_ELASTIC_SEARCH_SCHEDULERS_LAST_RUN}
                                          {$scheduler->last_run}
                                          (<b>{diff_for_humans datetime=$scheduler->last_run}</b>)
                                      {else}
                                          {$MOD.LBL_ELASTIC_SEARCH_SCHEDULERS_NEVER_RUN}
                                      {/if}
                                  </li>
                                  {foreachelse}
                                  <p class="error">{$MOD.LBL_ELASTIC_SEARCH_SCHEDULERS_NOT_FOUND}</p>
                              {/foreach}
                          </ul>
                          <small class="form-text text-muted">{$MOD.LBL_ELASTIC_SEARCH_SCHEDULERS_DESC}</small>
                      </div>
                  </div>
              </div>
          </div>
      
          <div class="row">
              <div class="col-md-12">
                  <div class="panel panel-primary ">
                      <div class="panel-heading">{$MOD.LBL_ELASTIC_SEARCH_INDEX}</div>
                      <div class="panel-body tab-content">
                          <label class="label">{$MOD.LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_HELP}</label>
                          <div class="d-flex align-items-center gap-2 mt-2">
                              <button class="btn btn-primary" type="button" id="es-full-index">{$MOD.LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_FULL}</button>
                              <button class="btn btn-primary" type="button" id="es-partial-index">{$MOD.LBL_ELASTIC_SEARCH_INDEX_SCHEDULE_PART}</button>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

    <div class="d-flex align-items-center gap-2">
        {$BUTTONS}
    </div>

    {$JAVASCRIPT}
</form>
<script src="modules/Administration/Search/ElasticSearch/scripts.js"></script>
<script src="modules/Administration/Search/ajaxSubmit.js"></script>