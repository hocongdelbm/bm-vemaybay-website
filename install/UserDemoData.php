<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

#[\AllowDynamicProperties]
class UserDemoData
{
    public $_user;
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
     * Constructor for creating user demo data
     */
    public function __construct($seed_user, $large_scale_test = false)
    {
        // use a seed user so it does not have to be known which file to
        // include the User class from
        $this->_user = $seed_user;
        $this->_large_scale_test = $large_scale_test;
    }

    /**
     *
     */
    public function create_demo_data()
    {
        global $current_language;
        global $sugar_demodata;
        foreach ($sugar_demodata['users'] as $v) {
            $this->_create_seed_user($v['id'], $v['last_name'], $v['first_name'], $v['user_name'], $v['title'], $v['is_admin'], $v['reports_to'], $v['reports_to_name'], $v['email']);
        }
        if ($this->_large_scale_test) {
            $user_list = $this->_seed_data_get_user_list();
            foreach ($user_list as $user_name) {
                $this->_quick_create_user($user_name);
            }
        }
    }


    /**
     *  Create a user in the seed data.
     */
    public function _create_seed_user(
        $id,
        $last_name,
        $first_name,
        $user_name,
        $title,
        $is_admin,
        $reports_to,
        $reports_to_name,
        $email
    ) {
        $u = BeanFactory::newBean('Users');

        $u->id = $id;
        $u->new_with_id = true;
        $u->last_name = $last_name;
        $u->first_name = $first_name;
        $u->user_name = $user_name;
        $u->title = $title;
        $u->status = 'Active';
        $u->employee_status = 'Active';
        $u->is_admin = $is_admin;
        $u->user_hash = User::getPasswordHash($user_name);
        $u->reports_to_id = $reports_to;
        $u->reports_to_name = $reports_to_name;
        $u->emailAddress->addAddress($email, true);
        $u->emailAddress->addAddress("reply." . $email, false, true);
        $u->emailAddress->addAddress("alias." . $email);

        // bug 15371 tyoung set a user preference so that Users/DetailView.php can find something without repeatedly querying the db in vain
        $u->setPreference('max_tabs', '7');
        $u->savePreferencesToDB();

        $u->save();
    }

    /**
     *
     */
    public function _seed_data_get_user_list()
    {
        $users = array();
        //bug 28138 todo
        $users[] = "north";
        $users[] = "south";
        $users[] = "east";
        $users[] = "west";
        $users[] = "left";
        $users[] = "right";
        $users[] = "in";
        $users[] = "out";
        $users[] = "fly";
        $users[] = "walk";
        $users[] = "crawl";
        $users[] = "pivot";
        $users[] = "money";
        $users[] = "dinero";
        $users[] = "shadow";
        $users[] = "roof";
        $users[] = "sales";
        $users[] = "pillow";
        $users[] = "feather";

        return $users;
    }

    /**
     *
     */
    public function _quick_create_user($name)
    {
        global $sugar_demodata;
        if (!$this->_user->retrieve($name . '_id')) {
            $this->_create_seed_user(
                "{$name}_id",
                $name,
                $name,
                $name,
                $sugar_demodata['users'][0]['title'],
                $sugar_demodata['users'][0]['is_admin'],
                "seed_jim_id",
                $sugar_demodata['users'][0]['last_name'] . ", " . $sugar_demodata['users'][0]['first_name'],
                $sugar_demodata['users'][0]['email']
            );
        }
    }
}
