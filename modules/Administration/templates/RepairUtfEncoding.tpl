<div class="view" style="min-height: calc(100vh - 200px)">
    <h2 class="pt-0">{$MOD.LBL_REPAIR_UTF_ENCODING}</h2>
    <div class="alert alert-warning sm" role="alert">
        <h4 class="alert-heading">{$MOD.LBL_WARNING}</h4>
        <p>{$MOD.LBL_REPAIR_UTF_ENCODING_DATA_WARNING}</p>
        <p>{$MOD.LBL_REPAIR_UTF_ENCODING_BACKUP_WARNING}</p>
        <p>{$MOD.LBL_REPAIR_UTF_ENCODING_SYNC_WARNING}</p>
    </div>

    {if $invalid_repair_from eq true}
        <div class="alert alert-danger sm" role="alert">
            <h4 class="alert-heading">{$MOD.LBL_ERROR}</h4>
            <p>{$MOD.LBL_REPAIR_UTF_ENCODING_REPAIR_FROM_INVALID}</p>
        </div>
    {/if}

    <form name="RepairUtfEncoding" method="post" action="index.php">
        <input type="hidden" name="module" value="Administration">
        <input type="hidden" name="action" value="RepairUtfEncoding">
        <input type="hidden" name="return_module" value="Administration">
        <input type="hidden" name="return_action" value="RepairUtfEncoding">
        <input type="hidden" name="perform_rebuild_utf_encoding" value="true">
        <div class="other view container-fluid">
            <div class="row">
                <div class="col-sm-3" >{$MOD.LBL_SYNC_RUN}</div>
                <div class="col-sm-9"><input type="checkbox" name="syncRun" value="true"></div>
            </div>
            <div class="row">
                <div class="col-sm-3" >{$MOD.LBL_KEEP_TRACKING_TABLES}</div>
                <div  class="col-sm-9"><input type="checkbox" name="keepTrackingTables" value="true"></div>
            </div>
            <div class="row">
                <div class="col-sm-3" >{$MOD.LBL_REPAIR_FROM_DATE}</div>
                <div  class="col-sm-3"><input type="date" name="repairFrom" class="form-control" value="2021-04-27"></div>
            </div>
            <div class="row">
                <div  class="col-sm-3"><input type="submit" name="button" value="{$MOD.LBL_SUBMIT}"></div>
            </div>
        </div>
    </form>
</div>
