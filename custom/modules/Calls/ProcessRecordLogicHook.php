<?php
class ProcessRecordLogicHook
{
    public function custom_column(SugarBean $bean, $event, $arguments)
    {
        global $app_list_strings, $current_user;

        // Custom call_type
        switch ($bean->call_type) {
            case 'zalo':
                $bean->call_type = '<svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 48 48"> <path fill="#2962ff" d="M15,36V6.827l-1.211-0.811C8.64,8.083,5,13.112,5,19v10c0,7.732,6.268,14,14,14h10	c4.722,0,8.883-2.348,11.417-5.931V36H15z"></path><path fill="#eee" d="M29,5H19c-1.845,0-3.601,0.366-5.214,1.014C10.453,9.25,8,14.528,8,19	c0,6.771,0.936,10.735,3.712,14.607c0.216,0.301,0.357,0.653,0.376,1.022c0.043,0.835-0.129,2.365-1.634,3.742	c-0.162,0.148-0.059,0.419,0.16,0.428c0.942,0.041,2.843-0.014,4.797-0.877c0.557-0.246,1.191-0.203,1.729,0.083	C20.453,39.764,24.333,40,28,40c4.676,0,9.339-1.04,12.417-2.916C42.038,34.799,43,32.014,43,29V19C43,11.268,36.732,5,29,5z"></path><path fill="#2962ff" d="M36.75,27C34.683,27,33,25.317,33,23.25s1.683-3.75,3.75-3.75s3.75,1.683,3.75,3.75	S38.817,27,36.75,27z M36.75,21c-1.24,0-2.25,1.01-2.25,2.25s1.01,2.25,2.25,2.25S39,24.49,39,23.25S37.99,21,36.75,21z"></path><path fill="#2962ff" d="M31.5,27h-1c-0.276,0-0.5-0.224-0.5-0.5V18h1.5V27z"></path><path fill="#2962ff" d="M27,19.75v0.519c-0.629-0.476-1.403-0.769-2.25-0.769c-2.067,0-3.75,1.683-3.75,3.75	S22.683,27,24.75,27c0.847,0,1.621-0.293,2.25-0.769V26.5c0,0.276,0.224,0.5,0.5,0.5h1v-7.25H27z M24.75,25.5	c-1.24,0-2.25-1.01-2.25-2.25S23.51,21,24.75,21S27,22.01,27,23.25S25.99,25.5,24.75,25.5z"></path><path fill="#2962ff" d="M21.25,18h-8v1.5h5.321L13,26h0.026c-0.163,0.211-0.276,0.463-0.276,0.75V27h7.5	c0.276,0,0.5-0.224,0.5-0.5v-1h-5.321L21,19h-0.026c0.163-0.211,0.276-0.463,0.276-0.75V18z"></path> </svg>';
                break;
            case 'phone':
                $bean->call_type = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="28px" width="28px" version="1.1" viewBox="0 0 60 60"><title/><desc/><defs/><g fill="none" fill-rule="evenodd" id="Page-1" stroke="none" stroke-width="1"><g id="Social_icons" transform="translate(-322.000000, -1955.000000)"><g id="Phone" transform="translate(322.000000, 1955.000000)"><path d="M0,30 C0,13.4314567 13.4314567,0 30,0 C46.5685433,0 60,13.4314567 60,30 C60,46.5685433 46.5685433,60 30,60 C13.4314567,60 0,46.5685433 0,30 Z" fill="#6FD454" id="back"/><path d="M21.2142895,15.0612255 C21.5785814,15.0306128 21.8908315,15.1193898 22.0928758,15.4530689 C22.5459447,16.1877752 23.0020749,16.9224816 23.4337148,17.6755556 C23.9327029,18.5449581 24.3918944,19.4388508 24.8816986,20.3174371 C25.0072109,20.5531554 25.1817037,20.7674447 25.3286449,20.9939792 C25.4633411,21.2082685 25.5919147,21.4286804 25.717427,21.6429698 C25.9745743,22.1297127 25.9623291,22.6195169 25.5796696,23.008299 C25.0347624,23.5654513 24.4347522,24.0828071 23.856171,24.6124079 C23.3724893,25.0562929 22.87044,25.4818104 22.3990034,25.9348793 C22.0316503,26.2899873 21.9367507,26.7338724 22.1847141,27.2022477 C22.5673736,27.9185864 22.9469719,28.634925 23.3663668,29.3359573 C24.9214952,31.9288584 26.9082636,34.1329774 29.4185102,35.8319858 C30.3858735,36.4901603 31.3930335,37.0932317 32.4093772,37.6687517 C33.3675567,38.2105976 33.7410325,38.1187593 34.4849226,37.2891534 C35.1767711,36.520773 35.8441293,35.7401475 36.5329165,34.9656446 C36.6982255,34.7789068 36.8972084,34.6135978 37.0900688,34.4544115 C37.4819122,34.1237936 37.9104909,34.0870583 38.3604985,34.3135928 C38.4860109,34.3748183 38.620707,34.4299213 38.730913,34.5095144 C39.9248108,35.3636106 41.2595273,35.966682 42.5207731,36.6922045 C43.1973152,37.0809866 43.8554897,37.4973202 44.5197866,37.8952861 C44.877956,38.1065142 45.021836,38.4157031 44.9973457,38.8412205 C44.9299977,40.0381796 44.7310147,41.2137097 44.036105,42.2239309 C43.7452837,42.6433258 43.3320114,43.010679 42.9156778,43.3076228 C41.7615766,44.1157998 40.4727792,44.6147878 39.1013274,44.9209154 C37.8553879,45.1994916 36.6125097,45.0984695 35.4186119,44.7249938 C33.1134708,44.0147776 30.8420037,43.2004781 28.7511519,41.9667837 C26.8531606,40.8432953 25.1266007,39.4871499 23.5286144,37.971818 C21.5051107,36.0462752 19.6989577,33.9462396 18.2387288,31.5553827 C17.2866719,30.0033156 16.6070685,28.3318587 15.9917519,26.6267277 C15.6182762,25.5981389 15.2509231,24.5787338 15.0856141,23.4889194 C14.895815,22.255225 15.0274499,21.0674498 15.4223545,19.8980422 C15.7774626,18.8419019 16.2213477,17.8316807 16.8917672,16.9286041 C17.4183067,16.2275718 18.0764812,15.7132774 18.927516,15.4836817 C19.6101806,15.3030664 20.2989678,15.1591864 20.9846937,15 C21.0734708,15.0704094 21.1438801,15.0704094 21.2142895,15.0612255 Z" fill="#FFFFFF" id="Shape"/></g></g></g></svg>';
                break;
            default:
                $bean->call_type;
        }


        $sql = "SELECT log FROM calls WHERE id = '{$bean->id}' AND deleted = 0";
        $log = $GLOBALS['db']->getOne($sql);
        $log_array = json_decode(html_entity_decode($log), true);
        $other_caller = $log_array['other_caller'];

        
        // FROM - TO
        $call_from_zalo = strlen($bean->call_from) > 18 ? '-zalo' : '';
        $call_from_phone = ($bean->direction == 'outbound' && strlen($bean->call_from) < 12) ? (!empty($other_caller) ? '<strong>'.$other_caller.'</strong>' : $bean->call_from) : $bean->call_from;

        $call_from = '<div class="phone-dropdown dropdown flex-between">
                        <span>' . $call_from_phone . '</span>
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-item flex-start cursor-pointer copy-phone" onclick="copyContent(\'' . strip_tags($call_from_phone) . '\');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
                                    <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
                                    <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
                                </svg>
                                <span>Sao chép</span>
                            </li>
                            <li class="dropdown-item flex-start cursor-pointer btn-voiceip-calling' . $call_from_zalo . '" id="listview-call_from" phone="' . strip_tags($call_from_phone) . '">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                    <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"></path>
                                </svg>
                                <span>Gọi</span>
                            </li>
                        </ul>
                    </div>';
        $bean->call_from = $call_from;

        $call_to_zalo = strlen($bean->call_to) > 18 ? '-zalo' : '';
        $call_to = '<div class="phone-dropdown dropdown flex-between">
                        <span>' . $bean->call_to . '</span>
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                            </svg>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-item flex-start cursor-pointer copy-phone" onclick="copyContent(\'' . $bean->call_to . '\');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard" viewBox="0 0 16 16">
                                    <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/>
                                    <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/>
                                </svg>
                                <span>Sao chép</span>
                            </li>
                            <li class="dropdown-item flex-start cursor-pointer btn-voiceip-calling' . $call_to_zalo . '" id="listview-call_to" phone="' . $bean->call_to . '">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                    <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"></path>
                                </svg>
                                <span>Gọi</span>
                            </li>
                        </ul>
                    </div>';
        $bean->call_to = $call_to;

        // Description
        $bean->description = '<p class="call_description w-100">' . $bean->description . '</p>';

        // Custom direction
        $direction = $GLOBALS['app_list_strings']['calls_direction_list'][$bean->direction];
        switch ($bean->direction) {
            case 'inbound':
                $bean->direction = '<b class="text-success">' . $direction . '</b>';
                break;
            case 'outbound':
                $bean->direction = '<b class="text-primary">' . $direction . '</b>';
                break;
            case 'missed':
                $bean->direction = '<b class="text-danger">' . $direction . '</b>';
                break;
            case 'spam':
                $bean->direction = '<b class="text-spam">' . $direction . '</b>';
                break;
            case 'suddenly':
                $bean->direction = '<b class="text-warning">' . $direction . '</b>';
                break;
            case 'internal':
                $bean->direction;
                break;
            default:
                $bean->direction;
        }

        $bean->call_talk = global_secondsToTimeFormat($bean->call_talk);

        // Custom status
        $status = $app_list_strings['call_status_dom'][$bean->status];
        switch ($bean->status) {
            case 'new':
                $bean->status = '<b class="text-dark">' . $status . '</b>';
                break;
            case 'processing':
                $bean->status = '<b class="text-warning">' . $status . '</b>';
                break;
            case 'done':
                $bean->status = '<b class="text-success">' . $status . '</b>';
                break;
            default:
                $bean->status;
        }
    }

    

    // function getDurationCallsValue(&$bean, $event, $arguments)
    // {
    //     // Get access to custom fields from $bean
    //     $bean->custom_fields->retrieve();

    //     // Get access to name property using DBManager because $bean->name return null
    //     $sql = "SELECT log FROM calls WHERE id = '{$bean->id}' AND deleted = 0";
    //     $log = $GLOBALS['db']->getOne($sql);

    //     // if($GLOBALS['current_user']->user_name == 'hungnh'){
    //     //     pr($log);
    //     // }

    //     $log_array = json_decode(html_entity_decode($log), true);

    //     $bean->call_duration_c = secondsToTimeFormat($log_array['call_talk']);
    // }
}
