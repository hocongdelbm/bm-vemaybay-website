<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

#[\AllowDynamicProperties]
class TeamDemoData
{
    public $_team;
    public $_large_scale_test;

    public $guids = array(
        'jim'    => 'seed_jim_id',
        'sarah'    => 'seed_sarah_id',
        'sally'    => 'seed_sally_id',
        'max'    => 'seed_max_id',
        'will'    => 'seed_will_id',
        'chris'    => 'seed_chris_id',
    );

    /**
     * Constructor for creating demo data for teams
     */
    public function __construct($seed_team, $large_scale_test = false)
    {
        $this->_team = $seed_team;
        $this->_large_scale_test = $large_scale_test;
    }

    public function create_demo_data()
    {
        global $sugar_demodata;
        foreach ($sugar_demodata['teams'] as $v) {
            if (!$this->_team->retrieve($v['team_id'])) {
                $this->_team->create_team($v['name'], $v['description'], $v['team_id']);
            }
        }

        if ($this->_large_scale_test) {
            $team_list = $this->_seed_data_get_team_list();
            foreach ($team_list as $team_name) {
                $this->_quick_create($team_name);
            }
        }

        $this->add_users_to_team();
    }

    public function add_users_to_team()
    {
        // Create the west team memberships
        $this->_team->retrieve("West");
        $this->_team->add_user_to_team($this->guids['sarah']);
        $this->_team->add_user_to_team($this->guids['sally']);
        $this->_team->add_user_to_team($this->guids["max"]);

        // Create the east team memberships
        $this->_team->retrieve("East");
        $this->_team->add_user_to_team($this->guids["will"]);
        $this->_team->add_user_to_team($this->guids['chris']);
    }

    /**
     *
     */
    public function get_random_team()
    {
        $team_list = $this->_seed_data_get_team_list();
        $team_list_size = is_countable($team_list) ? count($team_list) : 0;
        $random_index = mt_rand(0, $team_list_size - 1);

        return $team_list[$random_index];
    }

    /**
     *
     */
    public function get_random_teamset()
    {
        $team_list = $this->_seed_data_get_teamset_list();
        $team_list_size = is_countable($team_list) ? count($team_list) : 0;
        $random_index = mt_rand(0, $team_list_size - 1);

        return $team_list[$random_index];
    }


    /**
     *
     */
    public function _seed_data_get_teamset_list()
    {
        $teamsets = array();
        $teamsets[] = array("East", "West");
        $teamsets[] = array("East", "West", "1");
        $teamsets[] = array("West", "East");
        $teamsets[] = array("West", "East", "1");
        $teamsets[] = array("1", "East");
        $teamsets[] = array("1", "West");
        return $teamsets;
    }


    /**
     *
     */
    public function _seed_data_get_team_list()
    {
        $teams = array();
        //bug 28138 todo
        $teams[] = "north";
        $teams[] = "south";
        $teams[] = "left";
        $teams[] = "right";
        $teams[] = "in";
        $teams[] = "out";
        $teams[] = "fly";
        $teams[] = "walk";
        $teams[] = "crawl";
        $teams[] = "pivot";
        $teams[] = "money";
        $teams[] = "dinero";
        $teams[] = "shadow";
        $teams[] = "roof";
        $teams[] = "sales";
        $teams[] = "pillow";
        $teams[] = "feather";

        return $teams;
    }

    /**
     *
     */
    public function _quick_create($name)
    {
        if (!$this->_team->retrieve($name)) {
            $this->_team->create_team($name, $name, $name);
        }
    }
}
