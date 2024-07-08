<script type="text/javascript" src="{sugar_getjspath file='modules/Studio/JSTransaction.js'}" ></script>
<script>
	var jstransaction = new JSTransaction();
</script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Studio/studiotabgroups.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Studio/ygDDListStudio.js'}"></script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Studio/studiodd.js'}" ></script>
<script type="text/javascript" src="{sugar_getjspath file='modules/Studio/studio.js'}" ></script>

<h2 >{$title}</h2>
<p class="decs-section">{$MOD.LBL_GROUP_TAB_WELCOME}</p>

<table cellspacing=2>
<button class='btn btn-primary' onclick='studiotabs.generateForm("edittabs");document.edittabs.submit()'>
	{$MOD.LBL_BTN_SAVEPUBLISH}
</button>
</table>

<form name='edittabs' id='edittabs' method='POST' action='index.php'>
<input type="hidden" name="slot_count" id="slot_count" value="" />
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
	<td width="100%" class='dataLabel' colspan='2'>
		<div class="d-flex gap-2 align-items-center mt-3">
			<span>{$MOD.LBL_TABGROUP_LANGUAGE}</span>
			{html_options name='grouptab_lang' class='box-select' options=$available_languages selected=$tabGroupSelected_lang onchange=" tabLanguageChange(this)"}
			{sugar_help text=$MOD.LBL_TAB_GROUP_LANGUAGE_HELP}
		</div>
	</td>
</tr>
</table>

<div class="box-section">
<table class="table-edit-module">
	<tr>
		<td valign='top' nowrap class="edit view modules" >
			<table cellpadding="0" cellspacing="0" width="100%" id='s_field_delete'>
				<tr>
					<td>
						<ul id='trash'>
							<li class='nobullet' id='trashcan'>
								<table>
									<tr>
										<!-- <td>{$recycleImage}</td> -->
										<td>
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
										</td>
										<td>{$MOD.LBL_DELETE_MODULE}</td>
									</tr>
								</table>
							</li>
						</ul>
					</td>
				</tr>
			</table>
			<div class='noBullet'><h2>{$MOD.LBL_MODULES}</h2>
			<ul class='listContainer'>
				{counter start=0 name="modCounter" print=false assign="modCounter"}
				{foreach from=$availableModuleList key='key' item='value'}
				<li  id='modSlot{$modCounter}'>
					<span class='slotB'>{$value.label}</span>
				</li>
				<script>
				tabLabelToValue['{$value.label}'] = '{$value.value}';
				subtabModules['modSlot{$modCounter}'] = '{$value.label}'</script>
				{counter name="modCounter"}
				{/foreach}
			</ul>
		</td>

<td valign='top' nowrap class="group-modules">
<table class='tableContainer' id='groupTable'><tr>
{counter start=0 name="tabCounter" print=false assign="tabCounter"}

{foreach from=$tabs item='tab' key='tabName'}
{if $tabCounter > 0 && $tabCounter % 6 == 0}
</tr><tr>
{/if}
<td valign='top' class='tdContainer'>
<div id='slot{$tabCounter}' class='noBullet'>
	<h2 id='handle{$tabCounter}' class="d-flex align-items-center justify-content-between" >
		<span id='tabname_{$tabCounter}' class='slotB'>{$tab.labelValue}</span>
		<span id='tabother_{$tabCounter}' class="d-flex gap-2 align-items-center">
			<span onclick='studiotabs.editTabGroupLabel({$tabCounter}, false)'>
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="cursor-pointer bi bi-pencil" viewBox="0 0 16 16">
				<path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
			   </svg>
			</span>
{if $tab.label != $otherLabel }
	<span onclick='studiotabs.deleteTabGroup({$tabCounter})'>
		<!-- {$deleteImage} -->
		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg cursor-pointer" viewBox="0 0 16 16">
			<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z"/>
		   </svg>
	</span>
{/if}
</span></h2><input type='hidden' name='tablabelid_{$tabCounter}' id='tablabelid_{$tabCounter}'  value='{$tab.label}'><input type='text' name='tablabel_{$tabCounter}' id='tablabel_{$tabCounter}' style='display:none' value='{$tab.labelValue}' onblur='studiotabs.editTabGroupLabel({$tabCounter}, true)'>
<ul id='ul{$tabCounter}' class='listContainer'>
{counter start=0 name="subtabCounter" print=false assign="subtabCounter"}
{foreach from=$tab.modules key='list' item='name'}

<li id='subslot{$tabCounter}_{$subtabCounter}' class='listStyle' name='{$list}'><span class='slotB' >{$availableModuleList[$list].label}</span></li>
<script>subtabModules['subslot{$tabCounter}_{$subtabCounter}'] = '{$availableModuleList[$list].label}'</script>
{counter name="subtabCounter"}
{/foreach}
<li class='noBullet' id='noselectbottom{$tabCounter}'>&nbsp;</li>
<script>subtabCount[{$tabCounter}] = {$subtabCounter};</script>
</ul>
</div>
<div id='slot{$tabCounter}b'>
<input type='hidden' name='slot_{$tabCounter}' id='slot_{$tabCounter}' value ='{$tabCounter}'>
<input type='hidden' name='delete_{$tabCounter}' id='delete_{$tabCounter}' value ='0'>
</div>
{counter name="tabCounter"}
</td>
{/foreach}

</tr>
<tr><td><input type='button' class='btn btn-primary ms-2' onclick='addTabGroup()' value='{$MOD.LBL_ADD_GROUP}'></td></tr>
</table>

</td>
</table>
</div>
<span class='error'>{$error}</span>



{literal}
		<script>
		function tabLanguageChange(sel){
			var partURL = window.location.href;
			if(partURL.search(/&lang=\w*&/i) != -1){
				partURL = partURL.replace(/&lang=\w*&/i, '&lang='+ sel.value+'&');
			}else if(partURL.search(/&lang=\w*/i) != -1){
				partURL = partURL.replace(/&lang=\w*/i, '&lang='+ sel.value);
			}else{
				partURL = window.location.href + '&lang='+ sel.value;
			}
			window.location.href = partURL;
		}

		function addTabGroup(){
			var table = document.getElementById('groupTable');
		  	var rowIndex = table.rows.length - 1;
		  	var rowExists = false;
		  	for(var i = 0; i < rowIndex;i++){
		  		if(table.rows[i].cells.length < 6){
		  			rowIndex = i;
		  			rowExists = true;
		  		}
		  	}

		  	if(!rowExists)table.insertRow(rowIndex);
		  	cell = table.rows[rowIndex].insertCell(table.rows[rowIndex].cells.length);
		  	cell.className='tdContainer';
		  	cell.vAlign='top';
		  	var slotDiv = document.createElement('div');
		  	slotDiv.id = 'slot'+ slotCount;
		  	var header = document.createElement('h2');
		  	header.id = 'handle' + slotCount;
		  	headerSpan = document.createElement('span');
		  	headerSpan.innerHTML = '{/literal}{$TGMOD.LBL_NEW_GROUP}{literal}';
		  	headerSpan.id = 'tabname_' + slotCount;
		  	header.appendChild(headerSpan);
		  	headerSpan2 = document.createElement('span');
		  	headerSpan2.id = 'tabother_' + slotCount;
		  	subspan1 = document.createElement('span');
		  	subspan1.slotCount=slotCount;
		  	subspan1.innerHTML = '';
		  	subspan1.onclick= function() {
		  		studiotabs.editTabGroupLabel(this.slotCount, false);
		  	};
		  	subspan2 = document.createElement('span');
		  	subspan2.slotCount=slotCount;
		  	subspan2.innerHTML = '{/literal}{$deleteImage}{literal}&nbsp;';
		  	subspan2.onclick= function(){
		  		studiotabs.deleteTabGroup(this.slotCount);
		  	};
		  	headerSpan2.appendChild(subspan1);
		  	headerSpan2.appendChild(subspan2);

		  	var editLabel = document.createElement('input');
		  	editLabel.style.display = 'none';
		  	editLabel.type = 'text';
		  	editLabel.value = '{/literal}{$TGMOD.LBL_NEW_GROUP}{literal}';
		  	editLabel.id = 'tablabel_' + slotCount;
		  	editLabel.name = 'tablabel_' + slotCount;
		  	editLabel.slotCount = slotCount;
		  	editLabel.onblur = function(){
		  		studiotabs.editTabGroupLabel(this.slotCount, true);
		  	}

		  	var list = document.createElement('ul');
		  	list.id = 'ul' + slotCount;
		  	list.className = 'listContainer';
		  	header.appendChild(headerSpan2);
		  	var li = document.createElement('li');
		  	li.id = 'noselectbottom' + slotCount;
		  	li.className = 'noBullet';
		  	li.innerHTML = '{/literal}{$TGMOD.LBL_DROP_HERE}{literal}';
		  	list.appendChild(li);

		  	slotDiv.appendChild(header);
		  	slotDiv.appendChild(editLabel);
		  	slotDiv.appendChild(list);
			var slotB = document.createElement('div');
		  	slotB.id = 'slot' + slotCount + 'b';
		  	var slot = document.createElement('input');
		  	slot.type = 'hidden';
		  	slot.id =  'slot_' + slotCount;
		  	slot.name =  'slot_' + slotCount;
		  	slot.value = slotCount;
		  	var deleteSlot = document.createElement('input');
		  	deleteSlot.type = 'hidden';
		  	deleteSlot.id =  'delete_' + slotCount;
		  	deleteSlot.name =  'delete_' + slotCount;
		  	deleteSlot.value = 0;
		  	slotB.appendChild(slot);
		  	slotB.appendChild(deleteSlot);
		  	cell.appendChild(slotDiv);
		  	cell.appendChild(slotB);

		  	yahooSlots["slot" + slotCount] = new ygDDSlot("slot" + slotCount, "mainTabs");
			yahooSlots["slot" + slotCount].setHandleElId("handle" + slotCount);
		  	yahooSlots["noselectbottom"+ slotCount] = new ygDDListStudio("noselectbottom"+ slotCount , "subTabs", -1);
		  	subtabCount[slotCount] = 0;
		  	slotCount++;
		  	ygDDListStudio.prototype.updateTabs();
		}

		var slotCount = {/literal}{$tabCounter}{literal};
		var modCount = {/literal}{$modCounter}{literal};
		var subSlots = [];
		var yahooSlots = [];

		function dragDropInit(){

			YAHOO.util.DDM.mode = YAHOO.util.DDM.POINT;

			for(mj = 0; mj <= slotCount; mj++){
				yahooSlots["slot" + mj] = new ygDDSlot("slot" + mj, "mainTabs");
				yahooSlots["slot" + mj].setHandleElId("handle" + mj);

				yahooSlots["noselectbottom"+ mj] = new ygDDListStudio("noselectbottom"+ mj , "subTabs", -1);
				for(msi = 0; msi <= subtabCount[mj]; msi++){
					yahooSlots["subslot"+ mj + '_' + msi] = new ygDDListStudio("subslot"+ mj + '_' + msi, "subTabs", 0);

				}

			}
			for(msi = 0; msi <= modCount ; msi++){
					yahooSlots["modSlot"+ msi] = new ygDDListStudio("modSlot" + msi, "subTabs", 1);

			}
			var trash1  = new ygDDListStudio("trashcan" , "subTabs", 'trash');
			ygDDListStudio.prototype.updateTabs();

		}

		YAHOO.util.DDM.mode = YAHOO.util.DDM.INTERSECT;
		YAHOO.util.Event.addListener(window, "load", dragDropInit);

</script>
{/literal}
	<input type='hidden' name='action' value='SaveTabs'>
	<input type='hidden' name='module' value='Studio'>
</form>


