<h3 class="error" style="width:100%">{$MOD.LBL_REPAIR_DATABASE_DIFFERENCES}</h3>
<p>{$MOD.LBL_REPAIR_DATABASE_TEXT}</p>
<form name="RepairDatabaseForm" method="post">
<input type="hidden" name="module" value="Administration"/>
<input type="hidden" name="action" value="repairDatabase"/>
<input type="hidden" name="raction" value="execute"/>
<textarea class="box-textarea mb-2" name="sql" rows="14" cols="150" id="repairsql">{$qry_str}</textarea>

<div class="d-flex align-items-center gap-2">
     <input type="button" class="btn btn-primary" value="{$MOD.LBL_REPAIR_DATABASE_EXECUTE}" onClick="document.RepairDatabaseForm.submit();"/> 
     <input type="button" class="btn btn-danger" value="{$MOD.LBL_REPAIR_DATABASE_EXPORT}" onClick="document.RepairDatabaseForm.raction.value='export'; document.RepairDatabaseForm.submit();"/>
</div>

<script>document.getElementById('repairsql').scrollIntoView(false);</script>
