<form name="ConfigureMoreSettings" method="POST" action="index.php" >
	<input type='hidden' name='action' value='MoreSettings'/>
	<input type='hidden' name='module' value='Administration'/>
	<input type='hidden' name='saveConfig' value='1'/>

    {if $error.main}
		<span class='error'>{$error.main}</span>
	{/if}

    <div class="actionsContainer actions-button__header flex-start mb-3">
		<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-sm btn-primary btn-save btn-manage-password" id="btn_save" type="submit" onclick="return check_form('ConfigureMoreSettings');"  name="save" value="{$APP.LBL_SAVE_BUTTON_LABEL}" >
		<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}" id="btn_cancel" onclick="document.location.href='index.php?module=Administration&action=index'" class="btn btn-sm btn-secondary btn-cancel btn-manage-password"  type="button" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" >
	</div>

	<!-- PBX -->
    <div class="panel panel-default mb-3 panel-password-pbx" id="pbx_setting_table">
		<div class="panel-heading px-3 py-1">
			<h5 class="text-white m-0 text-uppercase fs-6">{$MOD.LBL_MANAGE_PBX_APP}</h5>
		</div>
		<div class="panel-body">
			<div class="tab-content">
				<div class="row detail-view-row mb-2">
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">{$MOD.LBL_PBX_IP}:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="pbx_ip" id="pbx_ip" value="{$config.webrtc.ip}">
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">{$MOD.LBL_PBX_DOMAIN_NAME}:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="pbx_domain_name" id="pbx_domain_name" value="{$config.webrtc.domain_name}">
							</div>
						</div>
					</div>
				</div>
				<div class="row detail-view-row mb-2">
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">{$MOD.LBL_PBX_PORT}:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="pbx_port" id="pbx_port" value="{$config.webrtc.port}">
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">{$MOD.LBL_PBX_TOKEN_API}:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="pbx_tokenapi" id="pbx_tokenapi" value="{$config.webrtc.tokenapi}">
							</div>
						</div>
					</div>
				</div>
				<div class="row detail-view-row mb-2">
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">{$MOD.LBL_PBX_IP_OLD}:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="pbx_ip_old" id="pbx_ip_old" value="{$config.webrtc.ip_old}">
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- VNBackup -->
    <div class="panel panel-default mb-3 panel-password-vnbackup" id="vnbackup_setting_table">
		<div class="panel-heading px-3 py-1">
			<h5 class="text-white m-0 text-uppercase fs-6">Quản lý cấu hình VNBackup</h5>
		</div>
		<div class="panel-body">
			<div class="tab-content">
				<div class="row detail-view-row mb-2">
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">URL Upload:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="vnbackup_url_upload" id="vnbackup_url_upload" value="{$config.vnbackup.url_upload}">
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">URL Share:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="vnbackup_url_share" id="vnbackup_url_share" value="{$config.vnbackup.url_share}">
							</div>
						</div>
					</div>
				</div>
				<div class="row detail-view-row mb-2">
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">Username:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="vnbackup_username" id="vnbackup_username" value="{$config.vnbackup.username}">
							</div>
						</div>
					</div>
					<div class="col-12 col-sm-6 col-md-6 detail-view-row-item">
						<div class="row align-items-center">
							<div class="col-12 col-sm-4 detail-view-label">
								<label class="form-label fw-semibold text-nowrap">Password:</label>
							</div>
							<div class="col-12 col-sm-8 detail-view-field">
                                <input class="form-control" type="text" name="vnbackup_password" id="vnbackup_password" value="{$config.vnbackup.password}">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <div class="actionsContainer actions-button__footer flex-start mt-3">
		<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" class="btn btn-sm btn-primary btn-save btn-manage-password" id="btn_save" type="submit" onclick="return check_form('ConfigureMoreSettings');" name="save" value="{$APP.LBL_SAVE_BUTTON_LABEL}" />
		<input title="{$MOD.LBL_CANCEL_BUTTON_TITLE}" onclick="document.location.href='index.php?module=Administration&action=index'" class="btn btn-sm btn-secondary btn-cancel btn-manage-password"  type="button" name="cancel" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" />
	</div>
</form>