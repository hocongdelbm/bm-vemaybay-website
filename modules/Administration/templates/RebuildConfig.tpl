<p>
    <form name="RebuildConfig" method="post" action="index.php">
        <input type="hidden" name="module" value="Administration">
        <input type="hidden" name="action" value="RebuildConfig">
        <input type="hidden" name="return_module" value="Administration">
        <input type="hidden" name="return_action" value="RebuildConfig">
        <input type="hidden" name="perform_rebuild" value="true">

        <table cellspacing="{$CELLSPACING}" class="other view">
        <tr>
            <td width="20%" scope="row">{$LBL_CONFIG_CHECK}</td>
            <td>{$CONFIG_CHECK}</td>
        </tr>
        <tr>
            <td scope="row">{$LBL_PERFORM_REBUILD}</td>
            <td><input type="submit" name="button" {$DISABLE_CONFIG_REBUILD} value="{$BTN_PERFORM_REBUILD}"></td>
        </tr>
        </table>
    </form>
</p>
