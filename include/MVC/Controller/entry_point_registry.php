<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$entry_point_registry = array(
    'emailImage'                            => array('file' => 'modules/EmailMan/EmailImage.php', 'auth' => false),
    'download'                              => array('file' => 'custom/download.php', 'auth' => true), //custom
    'export'                                => array('file' => 'export.php', 'auth' => true),
    'export_dataset'                        => array('file' => 'export_dataset.php', 'auth' => true),
    'Changenewpassword'                     => array('file' => 'modules/Users/Changenewpassword.php', 'auth' => false),
    'GeneratePassword'                      => array('file' => 'modules/Users/GeneratePassword.php', 'auth' => false),
    'pdf'                                   => array('file' => 'pdf.php', 'auth' => true),
    'minify'                                => array('file' => 'jssource/minify.php', 'auth' => true),
    'json_server'                           => array('file' => 'json_server.php', 'auth' => true),
    'get_url'                               => array('file' => 'get_url.php', 'auth' => true),
    'HandleAjaxCall'                        => array('file' => 'HandleAjaxCall.php', 'auth' => true),
    'TreeData'                              => array('file' => 'TreeData.php', 'auth' => true),
    'ConfirmOptIn'                          => array('file' => 'include/entryPointConfirmOptInConnector.php', 'auth' => false),
    'acceptDecline'                         => array('file' => 'modules/Contacts/AcceptDecline.php', 'auth' => false),
    'process_queue'                         => array('file' => 'process_queue.php', 'auth' => true),
    'zipatcher'                             => array('file' => 'zipatcher.php', 'auth' => true),
    'mm_get_doc'                            => array('file' => 'modules/MailMerge/get_doc.php', 'auth' => true),
    'getImage'                              => array('file' => 'include/SugarTheme/getImage.php', 'auth' => false),
    'GenerateQuickComposeFrame'             => array('file' => 'modules/Emails/GenerateQuickComposeFrame.php', 'auth' => true),
    'DetailUserRole'                        => array('file' => 'modules/ACLRoles/DetailUserRole.php', 'auth' => true),
    'getYUIComboFile'                       => array('file' => 'include/javascript/getYUIComboFile.php', 'auth' => false),
    'UploadFileCheck'                       => array('file' => 'modules/Configurator/UploadFileCheck.php', 'auth' => true),
    'SAML'                                  => array('file' => 'modules/Users/authentication/SAMLAuthenticate/index.php', 'auth' => false),
    'SAML2Metadata'                         => array('file' => 'modules/Users/authentication/SAML2Authenticate/SAML2Metadata.php', 'auth' => false),
    'jslang'                                => array('file' => 'include/language/getJSLanguage.php', 'auth' => true),
    'deleteAttachment'                      => array('file' => 'include/SugarFields/Fields/Image/deleteAttachment.php', 'auth' => true),
    'formLetter'                            => array('file' => 'modules/AOS_PDF_Templates/formLetterPdf.php', 'auth' => true),
    'generatePdf'                           => array('file' => 'modules/AOS_PDF_Templates/generatePdf.php', 'auth' => true),
    'Reschedule'                            => array('file' => 'modules/Calls_Reschedule/Reschedule_popup.php', 'auth' => true),
    'Reschedule2'                           => array('file' => 'modules/Calls/Reschedule.php', 'auth' => true),
    'social'                                => array('file' => 'include/social/get_data.php', 'auth' => true),
    'social_reader'                         => array('file' => 'include/social/get_feed_data.php', 'auth' => true),
    'add_dash_page'                         => array('file' => 'modules/Home/AddDashboardPages.php', 'auth' => true),

    'retrieve_dash_page'                    => array('file' => 'include/MySugar/retrieve_dash_page.php', 'auth' => true),
    'remove_dash_page'                      => array('file' => 'modules/Home/RemoveDashboardPages.php', 'auth' => true),
    'rename_dash_page'                      => array('file' => 'modules/Home/RenameDashboardPages.php', 'auth' => true),

    'emailTemplateData'                     => array('file' => 'modules/EmailTemplates/EmailTemplateData.php', 'auth' => true),
    'emailMarketingData'                    => array('file' => 'modules/EmailMarketing/Save.php', 'auth' => true),
    'emailMarketingList'                    => array('file' => 'modules/EmailMarketing/List.php', 'auth' => true),
    'sendConfirmOptInEmail'                 => array('file' => 'include/entryPointConfirmOptInConnector.php', 'auth' => true),
    'saveGoogleApiKey'                      => array('file' => 'modules/Users/entryPointSaveGoogleApiKey.php', 'auth' => true),
    'setImapTestSettings'                   => array('file' => 'include/Imap/ImapTestSettingsEntry.php', 'auth' => true),
    'redirectToExternalOAuth'               => array('file' => 'modules/ExternalOAuthConnection/entrypoint/redirectToExternalOAuth.php', 'auth' => true),
    'setExternalOAuthToken'                 => array('file' => 'modules/ExternalOAuthConnection/entrypoint/setExternalOAuthToken.php', 'auth' => true),

    /*====================  CUSTOM  ====================*/
    'entryPointCheckValueExist'     => ['file' => 'custom/entrypoints/epCheckValueExist.php', 'auth' => true],
    'entryPointSaveWorkingProcess'  => ['file' => 'custom/entrypoints/epSaveWorkingProcess.php', 'auth' => true],
    'entryPointFlightBookings'      => ['file' => 'custom/entrypoints/epFlightBookings.php', 'auth' => true],

    // Custom by DucPham
    // 'entryPointAPIVietjet'      => ['file' => 'custom/entrypoints/epAPIVietjet.php', 'auth' => true],
    // 'entryPointAutoBook'        => ['file' => 'custom/entrypoints/epAutoBook.php', 'auth' => true],
    'entryPointSaveNote'        => ['file' => 'custom/entrypoints/epSaveNote.php', 'auth' => true],
    'entryPointCallContact'     => ['file' => 'custom/entrypoints/epCallContact.php', 'auth' => true],
    'entryPointSMS'             => ['file' => 'custom/entrypoints/epSMS.php', 'auth' => true],
    'entryPointSaveResultSMS'   => ['file' => 'custom/entrypoints/epSaveResultSMS.php', 'auth' => false],
    'entrypointZaloOA'              => array('file' => 'custom/entrypoints/epZaloOA.php', 'auth' => true),
    'entryPointZaloAuthCallback'    => array('file' => 'custom/entrypoints/epZaloAuthCallback.php', 'auth' => false),
    'entryPointZaloWebhook'         => array('file' => 'custom/entrypoints/epZaloWebhook.php', 'auth' => false),
    // Voucher
    'entryPointVoucher' => array('file' => 'custom/entrypoints/epVoucher.php', 'auth' => true),

    // Custom by Haihugn
    'entryPointAbsence'                     => array('file' => 'custom/entrypoints/epAbsence.php', 'auth' => true),
    'entryPointEC_Payment_Voucher'          => array('file' => 'custom/entrypoints/epEC_Payment_Voucher.php', 'auth' => true),
    'entryPointEC_HoaDonBan'                => array('file' => 'custom/entrypoints/epEC_HoaDonBan.php', 'auth' => true),

    'entryPointCheckCallsHistory'           => array('file' => 'custom/entrypoints/epCheckCallsHistory.php', 'auth' => true),
    'entryPointStatisticsCall'              => array('file' => 'custom/entrypoints/epStatisticsCall.php', 'auth' => true),

    'entryPointLoadWorkingProcessDetail'    => array('file' => 'custom/entrypoints/epLoadWorkingProcessDetail.php', 'auth' => true),
    'entryPointGetReportData'               => array('file' => 'custom/entrypoints/epGetReportData.php', 'auth' => true),
    'entryPointCheckBookingPaid'            => array('file' => 'custom/entrypoints/epCheckBookingPaid.php', 'auth' => true),
    'entryPointGetAirportAndAirline'        => array('file' => 'custom/entrypoints/epGetAirportAndAirline.php', 'auth' => true),
    'entryPointUploadAirlineLogo'           => array('file' => 'custom/entrypoints/epUploadAirlineLogo.php', 'auth' => true),

    // ONLINE - OFFLINE
    'entryPointUpdateTimeUserClick'         => array('file' => 'custom/entrypoints/epUpdateTimeUserClick.php', 'auth' => true),
    'entryPointBehaviorUser'                => array('file' => 'custom/entrypoints/epBehaviorUser.php', 'auth' => true),
    'entryPointRecordActivity'              => array('file' => 'custom/entrypoints/epRecordActivity.php', 'auth' => true),

    // Tracker
    'entryPointTracker'                     => array('file' => 'custom/entrypoints/epTracker.php', 'auth' => true),

    // ANALYTICS ON SITE
    'entryPointAnalytics'                   => array('file' => 'custom/entrypoints/epAnalytics.php', 'auth' => true),
    'entryPointBookerIps'                   => array('file' => 'custom/entrypoints/epBookerIps.php', 'auth' => true),
    'entryPointBookingStats'                => array('file' => 'custom/entrypoints/epCityStats.php', 'auth' => true),
    // Config phone outbound
    'entryPointPhoneOutbound'         => array('file' => 'custom/entrypoints/epConfigPhoneOutbound.php', 'auth' => false),

    // Bank Account
    'entryPointBankAccount'         => array('file' => 'custom/entrypoints/epBankAccount.php', 'auth' => true),

    // DASHBOARD - HOME
    'entryPointOverviewDashBoard'           => array('file' => 'custom/entrypoints/epOverviewDashboard.php', 'auth' => true),

    // CUSTOM BY DATLNT
    'entryPointSummarySite' => ['file' => 'custom/entrypoints/ep_Summary_sites.php', 'auth' => true],

    // New version
    'entryPointGeneral'         => ['file' => 'custom/entrypoints/entryGeneral.php', 'auth' => true],
    'entryPointGeneralNA'       => ['file' => 'custom/entrypoints/entryGeneralNonAuth.php', 'auth' => false],
    'entryFareSystemWebhook'    => ['file' => 'custom/entrypoints/entryFareSystemWebhook.php', 'auth' => false],
    'entryImageUploadWebhook'   => ['file' => 'custom/entrypoints/entryImageUploadWebhook.php', 'auth' => false],
    'entryTelegramWebhook'      => ['file' => 'custom/entrypoints/entryTelegramWebhook.php', 'auth' => false],
    'entryOnepayIPN'            => ['file' => 'custom/entrypoints/entryOnepayIPN.php', 'auth' => false],
    'entryPointIpManage'        => ['file' => 'custom/entrypoints/epIpManage.php', 'auth' => true],
    'epZaloPost'                => ['file' => 'custom/entrypoints/epZaloPost.php', 'auth' => false],
);
