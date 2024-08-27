<?php
global $db, $current_user, $app_list_strings;

if (isset($_POST['type']) && $_POST['type'] == 'AnalyticsOnline') {
    $html = $html_nav = '';

    $arr_site = array(
        'timchuyenbay.com' => 'tcbcom',
        'vietjet.net' => 'bookingvj',
    );

    $name_tab = array(
        'tcbcom' => 'Tìm chuyến bay',
        'bookingvj' => 'Vietjet',
    );

    $html_nav .= '<nav><div class="nav nav-tabs analytics-nav-tabs mt-3" id="nav-tab" role="tablist">';
    $html .= '<div class="tab-content analytics-tab-content overflow-auto" id="nav-tabContent">';

    foreach($arr_site as $domain => $site){
        $is_start = $site === reset($arr_site);

        // lOOP NAV
        $html_nav .= '<button class="nav-link '.($is_start ? 'active' : '').'" id="nav-log-'.$site.'-tab" data-bs-toggle="tab" data-bs-target="#nav-log-'.$site.'" type="button" role="tab" aria-controls="nav-log-'.$site.'" aria-selected="'.($is_start ? 'true' : 'false').'">'.$name_tab[$site].'</button>';

        $html .= '<div class="tab-pane tab-pane'.$site.' fade '.($is_start ? 'show active' : '').'" id="nav-log-'.$site.'" role="tabpanel" aria-labelledby="nav-log-'.$site.'-tab" tabindex="0">';
        $html .= '<div class="analytistics__total">
                        <section class="analytistics__domestic">
                            <canvas id="chartjs__journey" class="mx-auto"></canvas>
                        </section>
                        <section class="analytistics__inter">
                            <canvas id="chartjs__journey--inter" class="mx-auto"></canvas>
                        </section>
                    </div>';
                
        // API
        $url = 'https://'.$domain.'/analytics';
        $post_data = [
            'type' => 'get-analytics',
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        $json   = curl_exec($curl);
        curl_close($curl);
        $arr    = json_decode(html_entity_decode($json), true);

        if(isset($arr['code']) && $arr['code'] == 200){
            $total_online   = $arr['analytics']['total_online'];

            $label_domestic = "[";
            $label_inter    = "[";

            $data_domestic  = "[";
            $data_inter     = "[";

            if(isset($arr['analytics']['journey']) && !empty($arr['analytics']['journey'])){
                foreach($arr['analytics']['journey'] as $key => $journeys){
                    if($key == 'domestic'){
                        foreach($journeys as $journey => $sl){
                            $label_domestic .= "'".$journey."',";
                            $data_domestic .= "'".$sl."',";
                        }
                    } else if($key == 'inter'){
                        foreach($journeys as $journey => $sl){
                            $label_inter .= "'".$journey."',";
                            $data_inter .= "'".$sl."',";
                        }
                    }
                }
            }
        }
        $html .= '<p class="fw-semibold">Đang online: '.$total_online.'</p><i>(Trong 5 phút)</i></div>';

        $label_domestic = substr($label_domestic, 0, -1); 
        $label_domestic .= "]";

        $label_inter = strlen($label_inter > 1) ? substr($label_inter, 0, -1) : $label_inter;
        // $label_inter = substr($label_inter, 0, -1); 
        $label_inter .= "]";

        $data_domestic = substr($data_domestic, 0, -1);
        $data_domestic .= "]";

        $data_inter = strlen($data_inter > 1) ? substr($data_inter, 0, -1) : $data_inter;
        // $data_inter = substr($data_inter, 0, -1); 
        $data_inter .= "]";
    }

    $html_nav .= '</div></nav>';

    echo "<script type='text/javascript'>
                $(document).ready(function() {
                    const label_domestic    = ".$label_domestic.";
                    const data_domestic     = ".$data_domestic.";
                    const label_inter       = ".$label_inter.";
                    const data_inter        = ".$data_inter.";

                    const chart_journey = document.getElementById('chartjs__journey');
                    const chart_journey_inter = document.getElementById('chartjs__journey--inter');

                    const data = {
                        labels: label_domestic,
                        datasets: [
                            {
                                type: 'bar',
                                data: data_domestic,
                                label: 'Nội địa',
                                backgroundColor: 'rgb(236, 178, 16)',
                                borderWidth: 1, 
                                borderRadius: 10,
                                yAxisID: 'bar-y-axis',
                                datalabels: {
                                    align: 'center',
                                    anchor: 'center',
                                }
                            },
                        ]
                    };
                    const data_inter_c = {
                        labels: label_inter,
                        datasets: [
                            {
                                type: 'bar',
                                data: data_inter,
                                label: 'Quốc tế',
                                backgroundColor: 'rgb(35, 218, 224)',
                                borderWidth: 1, 
                                borderRadius: 10,
                                yAxisID: 'bar-y-axis',
                                datalabels: {
                                    align: 'center',
                                    anchor: 'center',
                                }
                            },
                        ]
                    };
                    
                    const options = {
                        responsive: true, 
                        plugins: {
                            title: {
                                display: true,
                                padding: {
                                    top: 10,
                                    bottom: 20
                                },
                                color: '#000',
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                }
                            },
                            datalabels: {
                                color: '#000', 
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            'bar-y-axis': {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true
                            },
                        }
                    };

                    const options__inter = {
                        responsive: true, 
                        plugins: {
                            title: {
                                display: true,
                                padding: {
                                    top: 10,
                                    bottom: 20
                                },
                                color: '#000',
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                }
                            },
                            datalabels: {
                                color: '#000', 
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            'bar-y-axis': {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true
                            },
                        }
                    };

                    const mixedChart = new Chart(chart_journey, {
                        data: data, 
                        plugins: [ChartDataLabels],
                        options: options 
                    });

                    const mixedChart__inter = new Chart(chart_journey_inter, {
                        data: data_inter_c, 
                        plugins: [ChartDataLabels],
                        options: options__inter 
                    });
                });
            </script>";

        echo $html_nav.$html;
}