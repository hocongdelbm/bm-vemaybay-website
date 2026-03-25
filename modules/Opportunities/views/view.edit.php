<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class OpportunitiesViewEdit extends ViewEdit
{
    public function __construct()
    {
        parent::__construct();
        $this->useForSubpanel = true;
    }




    public function display()
    {
        global $app_list_strings;
        $json = getJSONobj();
        $prob_array = $json->encode($app_list_strings['sales_probability_dom']);
        $prePopProb = '';
        if (empty($this->bean->id) && empty($_REQUEST['probability'])) {
            $prePopProb = 'document.getElementsByName(\'sales_stage\')[0].onchange();';
        }

        $probability_script=<<<EOQ
	<script>
	prob_array = $prob_array;
	document.getElementsByName('sales_stage')[0].onchange = function() {
			if(typeof(document.getElementsByName('sales_stage')[0].value) != "undefined" && prob_array[document.getElementsByName('sales_stage')[0].value]
			&& typeof(document.getElementsByName('probability')[0]) != "undefined"
			) {
				document.getElementsByName('probability')[0].value = prob_array[document.getElementsByName('sales_stage')[0].value];
				SUGAR.util.callOnChangeListers(document.getElementsByName('probability')[0]);

			}
		};
	$prePopProb
	</script>
EOQ;

        $this->ss->assign('PROBABILITY_SCRIPT', $probability_script);
        parent::display();
    }
}
