<div class="view" style="min-height: calc(100vh - 200px)">
    <h2 class="pt-0">{$MOD.LBL_REPAIR_UTF_ENCODING}</h2>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2">
                <strong>{$MOD.LBL_EXECUTION_STATUS}</strong>
            </div>
            <div class="col-sm-1">
                <span class="label label-warning">{if $status eq 'in_progress'} {$MOD.LBL_IN_PROGRESS} {else} {$MOD.LBL_REPAIRED} {/if}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <strong>{$MOD.LBL_EXECUTION_MODE}</strong>
            </div>
            <div class="col-sm-1">
                <span class="label label-warning">{if $mode eq 'sync'} {$MOD.LBL_SYNCHRONOUS} {else} {$MOD.LBL_ASYNCHRONOUS} {/if}</span>
            </div>
        </div>
    </div>

    {if $mode eq 'async'}
        <hr/>
        <div class="alert alert-warning sm" role="alert">
            <h4 class="alert-heading">{$MOD.LBL_WARNING}</h4>
            {if $status eq 'in_progress'}<p>{$MOD.LBL_REPAIR_UTF_ENCODING_ASYNC_WARNING}</p> {/if}
            <p>{$MOD.LBL_REPAIR_UTF_ENCODING_ASYNC_PROGRESS_CHECK}</p>
        </div>

    {/if}

    {if $mode eq 'sync' && $status eq 'in_progress' }
        <hr/>
        <div class="alert alert-warning sm" role="alert">
            <h4 class="alert-heading">{$MOD.LBL_WARNING}</h4>
            <p>{$MOD.LBL_REPAIR_UTF_ENCODING_SYNC_PROGRESS_CHECK}</p>
        </div>

    {/if}
</div>
