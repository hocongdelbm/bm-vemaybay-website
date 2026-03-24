<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$entry_point_registry = array(
    'emailImage' => array('file' => 'modules/EmailMan/EmailImage.php', 'auth' => false),
    'download' => array('file' => 'download.php', 'auth' => true),
    'export' => array('file' => 'export.php', 'auth' => true),
    'export_dataset' => array('file' => 'export_dataset.php', 'auth' => true),
    'Changenewpassword' => array('file' => 'modules/Users/Changenewpassword.php', 'auth' => false),
    'GeneratePassword' => array('file' => 'modules/Users/GeneratePassword.php', 'auth' => false),
    'vCard' => array('file' => 'vCard.php', 'auth' => true),
    'pdf' => array('file' => 'pdf.php', 'auth' => true),
    'minify' => array('file' => 'jssource/minify.php', 'auth' => true),
    'json_server' => array('file' => 'json_server.php', 'auth' => true),
    'get_url' => array('file' => 'get_url.php', 'auth' => true),
    'HandleAjaxCall' => array('file' => 'HandleAjaxCall.php', 'auth' => true),
    'TreeData' => array('file' => 'TreeData.php', 'auth' => true),
    'ConfirmOptIn' => array('file' => 'include/entryPointConfirmOptInConnector.php', 'auth' => false),
    'acceptDecline' => array('file' => 'modules/Contacts/AcceptDecline.php', 'auth' => false),
    'leadCapture' => array('file' => 'modules/Leads/Capture.php', 'auth' => false),
    'process_queue' => array('file' => 'process_queue.php', 'auth' => true),
    'zipatcher' => array('file' => 'zipatcher.php', 'auth' => true),
    'mm_get_doc' => array('file' => 'modules/MailMerge/get_doc.php', 'auth' => true),
    'getImage' => array('file' => 'include/SugarTheme/getImage.php', 'auth' => false),
    'GenerateQuickComposeFrame' => array('file' => 'modules/Emails/GenerateQuickComposeFrame.php', 'auth' => true),
    'DetailUserRole' => array('file' => 'modules/ACLRoles/DetailUserRole.php', 'auth' => true),
    'getYUIComboFile' => array('file' => 'include/javascript/getYUIComboFile.php', 'auth' => false),
    'UploadFileCheck' => array('file' => 'modules/Configurator/UploadFileCheck.php', 'auth' => true),
    'SAML'=>  array('file' => 'modules/Users/authentication/SAMLAuthenticate/index.php', 'auth' => false),
    'SAML2Metadata'=>  array('file' => 'modules/Users/authentication/SAML2Authenticate/SAML2Metadata.php', 'auth' => false),
    'jslang'=> array('file' => 'include/language/getJSLanguage.php', 'auth' => true),
    'deleteAttachment' => array('file' => 'include/SugarFields/Fields/Image/deleteAttachment.php', 'auth' => true),
    'formLetter' => array('file' => 'modules/AOS_PDF_Templates/formLetterPdf.php' , 'auth' => true),
    'generatePdf' => array('file' => 'modules/AOS_PDF_Templates/generatePdf.php' , 'auth' => true),
    'Reschedule' => array('file' => 'modules/Calls_Reschedule/Reschedule_popup.php' , 'auth' => true),
    'Reschedule2' => array('file' => 'modules/Calls/Reschedule.php' , 'auth' => true),
    'social' => array('file' => 'include/social/get_data.php' , 'auth' => true),
    'social_reader' => array('file' => 'include/social/get_feed_data.php' , 'auth' => true),
    'add_dash_page' => array('file' => 'modules/Home/AddDashboardPages.php' , 'auth' => true),
    'retrieve_dash_page' => array('file' => 'include/MySugar/retrieve_dash_page.php' , 'auth' => true),
    'remove_dash_page' => array('file' => 'modules/Home/RemoveDashboardPages.php' , 'auth' => true),
    'rename_dash_page' => array('file' => 'modules/Home/RenameDashboardPages.php' , 'auth' => true),
    'emailTemplateData' => array('file' => 'modules/EmailTemplates/EmailTemplateData.php', 'auth' => true),
    'emailMarketingData' => array('file' => 'modules/EmailMarketing/Save.php', 'auth' => true),
    'emailMarketingList' => array('file' => 'modules/EmailMarketing/List.php', 'auth' => true),
    'sendConfirmOptInEmail' => array('file' => 'include/entryPointConfirmOptInConnector.php', 'auth' => true),
    'saveGoogleApiKey' => array('file' => 'modules/Users/entryPointSaveGoogleApiKey.php', 'auth' => true),
    'setImapTestSettings' => ['file' => 'include/Imap/ImapTestSettingsEntry.php', 'auth' => true],
    'redirectToExternalOAuth' => ['file' => 'modules/ExternalOAuthConnection/entrypoint/redirectToExternalOAuth.php', 'auth' => true],
    'setExternalOAuthToken' => ['file' => 'modules/ExternalOAuthConnection/entrypoint/setExternalOAuthToken.php', 'auth' => true],

    /*====================  CUSTOM  ====================*/
    'entryPointCheckValueExist' => array('file' => 'custom/entrypoints/epCheckValueExist.php', 'auth' => true),
    'entryPointSaveWorkingProcess' => array('file' => 'custom/entrypoints/epSaveWorkingProcess.php', 'auth' => true),
    'entryPointFlightBookings' => array('file' => 'custom/entrypoints/epFlightBookings.php', 'auth' => true),
    
    // Custom by DucPham
	'entryPointAPIVietjet' => array('file' => 'custom/entrypoints/epAPIVietjet.php', 'auth' => true),
	'entryPointSaveNote' => array('file' => 'custom/entrypoints/epSaveNote.php', 'auth' => true),

    // Custom by Haihugn
	'entryPointAbsence' => array('file' => 'custom/entrypoints/epAbsence.php', 'auth' => true),
	'entryPointEC_Payment_Voucher' => array('file' => 'custom/entrypoints/epEC_Payment_Voucher.php', 'auth' => true),
	'entryPointMySendSMS' => array('file' => 'custom/entrypoints/epMySendSMS.php', 'auth' => true),


);
