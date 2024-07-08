{* <div class="selectedRecords label mx-2 hidden">{$APP.LBL_LISTVIEW_SELECTED_OBJECTS}</div>*}
<div class="selectedRecords value hidden">{$TOTAL_ITEMS_SELECTED}</div>
<input type="hidden" id="selectCountTop" name="selectCount[]" value="{$TOTAL_ITEMS_SELECTED}" />
<script>
    {
        literal
    }
    $(document).ready(function () {
        setInterval(function () {
            sListView.toggleSelected();
        }, 100);
    }); {
        /literal}
</script>