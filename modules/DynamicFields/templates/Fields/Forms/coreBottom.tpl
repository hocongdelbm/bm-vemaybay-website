
{if $vardef.type != 'bool'}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_REQUIRED_OPTION"}:</td><td><input type="checkbox" name="required" value="1" {if !empty($vardef.required)}CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>{if $hideLevel > 5}<input type="hidden" name="required" value="{$vardef.required}">{/if}</td></tr>
{/if}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_AUDIT"}:</td><td><input type="checkbox" name="audited" value="1" {if !empty($vardef.audited) }CHECKED{/if} {if $hideLevel > 5}disabled{/if}/>{if $hideLevel > 5}<input type="hidden" name="audited" value="{$vardef.audited}">{/if}</td></tr>

<tr>
    <td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_INLINE_EDIT_TEXT"}:</td>
    <td><input type="hidden" name="inline_edit" value=""><input type="checkbox" name="inline_edit" value="1"
        {if !empty($vardef.inline_edit) }CHECKED{/if} {if ($hideLevel > 5 || !empty($disableInlineEdit))}disabled{/if}/>{if $hideLevel > 5}
            <input type="hidden" name="inline_edit" value="{$vardef.inline_edit}">
        {/if}
    </td>
</tr>

{if !$hideImportable}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_IMPORTABLE"}:</td><td>
    {if $hideLevel < 5}
        {html_options name="importable" id="importable" selected=$vardef.importable options=$importable_options}
        {sugar_help text=$mod_strings.LBL_POPHELP_IMPORTABLE FIXX=250 FIXY=80}
    {else}
        {if isset($vardef.importable)}{$importable_options[$vardef.importable]}
        {else}{$importable_options.true}{/if}
    {/if}
</td></tr>
{/if}
{if !$hideDuplicatable}
<tr><td class='mbLBL'>{sugar_translate module="DynamicFields" label="COLUMN_TITLE_DUPLICATE_MERGE"}:</td><td>
{if $hideLevel < 5}
    {html_options name="duplicate_merge" id="duplicate_merge" selected=$vardef.duplicate_merge_dom_value options=$duplicate_merge_options}
    {sugar_help text=$mod_strings.LBL_POPHELP_DUPLICATE_MERGE FIXX=250 FIXY=80}
{else}
    {if isset($vardef.duplicate_merge_dom_value)}{$vardef.duplicate_merge_dom_value}
    {else}{$duplicate_merge_options[0]}{/if}
{/if}
</td></tr>
{/if}
</table>
