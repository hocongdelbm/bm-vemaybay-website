<div class="view" >
    <h2 class="pt-0">{$MOD.LBL_REPAIR_UTF_ENCODING}</h2>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-2">
                <strong>{$MOD.LBL_EXECUTION_STATUS}</strong>
            </div>
            <div class="col-sm-1">
                <span class="label label-warning">{$MOD.LBL_IN_PROGRESS}</span>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <strong>{$MOD.LBL_EXECUTION_MODE}</strong>
            </div>
            <div class="col-sm-1">
                <span class="label label-warning">{$MOD.LBL_SYNCHRONOUS}</span>
            </div>
        </div>
    </div>

    {if $mode eq 'sync'}
        <hr/>
        <div class="alert alert-warning sm" role="alert">
            <h4 class="alert-heading">{$MOD.LBL_WARNING}</h4>
            <p>{$MOD.LBL_SYNC_LONG_EXECUTION_WARNING}</p>
            <p>{$MOD.LBL_SYNC_RUNNING_INFORMATION_OUTPUT}</p>
            <p>{$MOD.LBL_SYNC_RUNNING_INFORMATION_LOGS}</p>
        </div>

    {/if}
</div>
<h3 class="pt-0">{$MOD.LBL_OUTPUT}</h3>

