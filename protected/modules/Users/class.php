<?
class Users {
    
    public $settings;
    private $users_search;
    private $users_actions;
    public $user = [];
    
    public $about = [
        'username',
        'state',
        'city_id',
        'city_lat',
        'city_lon',
        'city_name_en',
        'city_name_ru',
        'country_id',
        'country_iso',
        'country_lat',
        'country_lon',
        'country_name_en',
        'country_name_ru',
        'http_accept_language',
        'http_referer',
        'http_user_agent',
        'region_id',
        'region_iso',
        'region_name_en',
        'region_name_ru',
        'remote_addr',
        'self_url',
        'source',
        'city_country_id',
        'company',
        'firstname',
        'lastname',
        'tel',
        'yadevice',
        'yageo',
        'yaregion',
        'yaregionid'
    ];

    function __construct($conf) {
        foreach ($conf['routes'] as $route) APP::Module('Routing')->Add($route[0], $route[1], $route[2]);
    }

    public function Init() {
        $this->settings = APP::Module('Registry')->Get([
            'module_users_register_activation_letter',
            'module_users_subscribe_activation_letter',
            'module_users_reset_password_letter',
            'module_users_change_password_letter',
            'module_users_subscription_restore_letter',
            'module_users_register_letter',
            'module_users_role',
            'module_users_rule',
            'module_users_db_connection',
            'module_users_ssh_connection',
            'module_users_auth_token',
            'module_users_check_rules',
            'module_users_min_pass_length',
            'module_users_gen_pass_length',
            'module_users_login_service',
            'module_users_change_password_service',
            'module_users_register_service',
            'module_users_reset_password_service',
            'module_users_oauth_client_fb_id',
            'module_users_oauth_client_fb_key',
            'module_users_oauth_client_google_id',
            'module_users_oauth_client_google_key',
            'module_users_oauth_client_vk_id',
            'module_users_oauth_client_vk_key',
            'module_users_oauth_client_ya_id',
            'module_users_oauth_client_ya_key',
            'module_users_timeout_activation',
            'module_users_timeout_email',
            'module_users_timeout_token',
            'module_users_tmp_dir',
            'module_users_profile_picture'
        ]);
        
        $this->users_search = new UsersSearch();
        $this->users_actions = new UsersActions();

        $this->user = &APP::Module('Sessions')->session['modules']['users']['user'];

        if (!isset(APP::Module('Sessions')->session['modules']['users']['double_auth'])) {
            APP::Module('Sessions')->session['modules']['users']['double_auth'] = false;
        }

        if (!$this->user) {
            $this->user = [
                'id' => 0,
                'role' => 'default'
            ];
        }

        if (isset($_COOKIE['modules']['users']['token'])) {
            if ($user = $this->Login($_COOKIE['modules']['users']['email'], $_COOKIE['modules']['users']['token'])) {
                $this->user = $this->Auth($user);
            }
        }

        if (((int) $this->settings['module_users_auth_token']) && ((int) $this->settings['module_users_login_service'])) {
            if (isset(APP::Module('Routing')->get['user_token'])) {
                $token = json_decode(APP::Module('Crypt')->Decode(APP::Module('Routing')->get['user_token']), 1);

                if ($user = $this->Login($token[0], $token[1])) {
                    $this->user = $this->Auth($user, true, true);
                }
            }
        }

        if ($this->user['id']) {
            APP::Module('DB')->Update(
                $this->settings['module_users_db_connection'], 'users',
                ['last_visit' => date('Y-m-d H:i:s')],
                [['id', '=', $this->user['id'], PDO::PARAM_INT]]
            );
        }

        if ((int) $this->settings['module_users_check_rules']) {
            if ($target_uri = $this->CheckRules()) {
                $url = parse_url(APP::Module('Routing')->root . $target_uri);
                $target_query = ['return' => APP::Module('Crypt')->SafeB64Encode(APP::Module('Routing')->SelfUrl())];

                if (isset($url['query'])) {
                    foreach (explode('&', $url['query']) as $param) {
                        $param_data = explode('=', $param);
                        $target_query[$param_data[0]] = $param_data[1];
                    }
                }

                header('Location: ' . APP::Module('Routing')->root . $target_uri . '?' . http_build_query($target_query));
                exit;
            }
        }
    }
    
    public function UsersSearch($rules) {
        $out = Array();

        foreach ((array) $rules['rules'] as $rule) {
            $out[] = array_flip((array) $this->users_search->{$rule['method']}($rule['settings']));
        }
        
        if (array_key_exists('children', (array) $rules)) {
            $out[] = array_flip((array) $this->UsersSearch($rules['children']));
        }
        
        if (count($out) > 1) {
            switch ($rules['logic']) {
                case 'intersect': return array_keys((array) call_user_func_array('array_intersect_key', $out)); break;
                case 'merge': return array_keys((array) call_user_func_array('array_replace', $out)); break;
            }
        } else {
            return array_keys($out[0]);
        }
    }

    public function Admin() {
        return APP::Render('users/admin/nav', 'content');
    }

    public function Dashboard() {
        return APP::Render('users/admin/dashboard/index', 'return');
    }
    
    public function APIDashboardAll() {
        $roles = [];

        foreach (APP::Module('Registry')->Get(['module_users_role'], ['value'])['module_users_role'] as $role) {
            $roles[$role['value']] = APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                ['count(id)'], 'users', 
                [['role', '=', $role['value'], PDO::PARAM_STR]]
            );
        }
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'roles' => $roles,
            'states' => [
                'inactive' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['count(distinct user)'], 'users_about', 
                    [
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'inactive', PDO::PARAM_STR]
                    ]
                ),
                'active' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['count(distinct user)'], 'users_about', 
                    [
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'active', PDO::PARAM_STR]
                    ]
                ),
                'pause' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['count(distinct user)'], 'users_about', 
                    [
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'pause', PDO::PARAM_STR]
                    ]
                ),
                'unsubscribe' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['count(distinct user)'], 'users_about', 
                    [
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'unsubscribe', PDO::PARAM_STR]
                    ]
                ),
                'blacklist' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['count(distinct user)'], 'users_about', 
                    [
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'blacklist', PDO::PARAM_STR]
                    ]
                ),
                'dropped' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['count(distinct user)'], 'users_about', 
                    [
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'dropped', PDO::PARAM_STR]
                    ]
                )
            ]
        ]);
        exit;
    }
    
    public function APIDashboardNew() {
        $range = [];

        for ($x = $_POST['date']['to']; $x >= $_POST['date']['from']; $x = $x - 86400) {
            $range[date('d-m-Y', $x)] = [
                'total' => 0,
                'inactive' => 0,
                'active' => 0,
                'pause' => 0,
                'unsubscribe' => 0,
                'blacklist' => 0,
                'dropped' => 0
            ];
        }

        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            [
                'users_about.value as state', 
                'UNIX_TIMESTAMP(reg_date) AS reg_date'
            ], 
            'users', 
            [
                ['users_about.item', '=', 'state', PDO::PARAM_STR],
                ['UNIX_TIMESTAMP(users.reg_date)', 'BETWEEN', $_POST['date']['from'] . ' AND ' . $_POST['date']['to']]
            ], 
            [
                'left join/users_about' => [
                    ['users_about.user', '=', 'users.id']
                ]
            ], 
            false, 
            false, 
            ['users.id', 'desc']
        ) as $user) {
            $date_index = date('d-m-Y', $user['reg_date']);

            if (!isset($range[$date_index])) {
                $range[$date_index] = [
                    'total' => 0,
                    'inactive' => 0,
                    'active' => 0,
                    'pause' => 0,
                    'unsubscribe' => 0,
                    'blacklist' => 0,
                    'dropped' => 0
                ];
            }

            ++ $range[$date_index]['total'];
            ++ $range[$date_index][$user['state']];
        }

        $range_values = [];
        $out_range          = [];

        foreach ($range as $date_index => $counters) {
            $date_values = explode('-', $date_index);

            $range_values[] = [
                'dt' => $date_index,
                'total' => (int) $counters['total'],
                'inactive' => Array((int) $counters['inactive'], APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . strtotime($date_index) . ',"date_to":' . strtotime($date_index . ' +1 day') . '}},{"method":"state","settings":{"logic":"=","value":"inactive"}}]}')),
                'active' => Array((int) $counters['active'], APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . strtotime($date_index) . ',"date_to":' . strtotime($date_index . ' +1 day') . '}},{"method":"state","settings":{"logic":"=","value":"active"}}]}')),
                'pause' => Array((int) $counters['pause'], APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . strtotime($date_index) . ',"date_to":' . strtotime($date_index . ' +1 day') . '}},{"method":"state","settings":{"logic":"=","value":"pause"}}]}')),
                'unsubscribe' => Array((int) $counters['unsubscribe'], APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . strtotime($date_index) . ',"date_to":' . strtotime($date_index . ' +1 day') . '}},{"method":"state","settings":{"logic":"=","value":"unsubscribe"}}]}')),
                'blacklist' => Array((int) $counters['blacklist'], APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . strtotime($date_index) . ',"date_to":' . strtotime($date_index . ' +1 day') . '}},{"method":"state","settings":{"logic":"=","value":"blacklist"}}]}')),
                'dropped' => Array((int) $counters['dropped'], APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . strtotime($date_index) . ',"date_to":' . strtotime($date_index . ' +1 day') . '}},{"method":"state","settings":{"logic":"=","value":"dropped"}}]}')),
            ];

            $out_range['inactive'][$date_index] = [strtotime($date_index) * 1000, $counters['inactive'] ? round((int) $counters['inactive'] / ((int) $counters['total'] / 100)) : 0];
            $out_range['active'][$date_index] = [strtotime($date_index) * 1000, $counters['active'] ? round((int) $counters['active'] / ((int) $counters['total'] / 100)) : 0];
            $out_range['pause'][$date_index] = [strtotime($date_index) * 1000, $counters['pause'] ? round((int) $counters['pause'] / ((int) $counters['total'] / 100)) : 0];
            $out_range['unsubscribe'][$date_index] = [strtotime($date_index) * 1000, $counters['unsubscribe'] ? round((int) $counters['unsubscribe'] / ((int) $counters['total'] / 100)) : 0];
            $out_range['blacklist'][$date_index] = [strtotime($date_index) * 1000, $counters['blacklist'] ? round((int) $counters['blacklist'] / ((int) $counters['total'] / 100)) : 0];
            $out_range['dropped'][$date_index] = [strtotime($date_index) * 1000, $counters['dropped'] ? round((int) $counters['dropped'] / ((int) $counters['total'] / 100)) : 0];
        }

        $date_from_values = explode('-', date('Y-m-d', $_POST['date']['from']));
        $date_to_values   = explode('-', date('Y-m-d', $_POST['date']['to']));

        $out = [
            'total'  => [
                'value' => (int) APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], ['count(id)'], 'users', [
                        ['reg_date', 'BETWEEN', '"' . date('Y-m-d', $_POST['date']['from']) . ' 00:00:00" AND "' . date('Y-m-d', $_POST['date']['to']) . ' 23:59:59"', PDO::PARAM_STR]
                    ]
                ),
                'hash'  => [
                    'inactive'      => APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . $_POST['date']['from'] . ',"date_to":' . $_POST['date']['to'] . '}},{"method":"state","settings":{"logic":"=","value":"inactive"}}]}'),
                    'active'      => APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . $_POST['date']['from'] . ',"date_to":' . $_POST['date']['to'] . '}},{"method":"state","settings":{"logic":"=","value":"active"}}]}'),
                    'pause'      => APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . $_POST['date']['from'] . ',"date_to":' . $_POST['date']['to'] . '}},{"method":"state","settings":{"logic":"=","value":"pause"}}]}'),
                    'unsubscribe'      => APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . $_POST['date']['from'] . ',"date_to":' . $_POST['date']['to'] . '}},{"method":"state","settings":{"logic":"=","value":"unsubscribe"}}]}'),
                    'blacklist'      => APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . $_POST['date']['from'] . ',"date_to":' . $_POST['date']['to'] . '}},{"method":"state","settings":{"logic":"=","value":"blacklist"}}]}'),
                    'dropped'      => APP::Module('Crypt')->Encode('{"logic":"intersect","rules":[{"method":"reg_date","settings":{"date_from":' . $_POST['date']['from'] . ',"date_to":' . $_POST['date']['to'] . '}},{"method":"state","settings":{"logic":"=","value":"dropped"}}]}'),
                ]
            ],
            'values' => $range_values,
            'range'  => [
                'inactive' => array_values($out_range['inactive']),
                'active' => array_values($out_range['active']),
                'pause' => array_values($out_range['pause']),
                'unsubscribe' => array_values($out_range['unsubscribe']),
                'blacklist' => array_values($out_range['blacklist']),
                'dropped' => array_values($out_range['dropped'])
            ]
        ];

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }
    
    public function Login($email, $password) {
        return (int) APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchColumn', 0], ['id'], 'users',
            [
                ['email', '=', $email, PDO::PARAM_STR],
                ['password', '=', $password, PDO::PARAM_STR]
            ]
        );
    }

    public function Register($email, $password, $role = 'new', $state = 'inactive') {
        $user = APP::Module('DB')->Insert(
            $this->settings['module_users_db_connection'], 'users',
            Array(
                'id'            => 'NULL',
                'email'         => [$email, PDO::PARAM_STR],
                'password'      => [APP::Module('Crypt')->Encode($password), PDO::PARAM_STR],
                'role'          => [$role, PDO::PARAM_STR],
                'reg_date'      => 'NOW()',
                'last_visit'    => 'NOW()',
            )
        );

        APP::Module('DB')->Insert(
            $this->settings['module_users_db_connection'], 'users_about',
            Array(
                'id' => 'NULL',
                'user' => [$user, PDO::PARAM_INT],
                'item' => ['state', PDO::PARAM_STR],
                'value' => [$state, PDO::PARAM_STR],
                'up_date' => 'NOW()'
            )
        );

        return $user;
    }

    public function Auth($id, $set_cookie = true, $save_password = false) {
        $user = APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_ASSOC], ['*'], 'users', [['id', '=', $id, PDO::PARAM_INT]]);

        if ($set_cookie) {
            setcookie(
                'modules[users][email]',
                $user['email'],
                strtotime('+' . $this->settings['module_users_timeout_email']),
                APP::$conf['location'][2],
                APP::$conf['location'][1]
            );

            setcookie(
                'modules[users][token]',
                APP::Module('Crypt')->Encode($user['password']),
                $save_password ? strtotime('+' . $this->settings['module_users_timeout_token']) : 0,
                APP::$conf['location'][2],
                APP::$conf['location'][1]
            );
        }

        return $user;
    }

    public function CheckRules() {
        $rules = [];

        foreach (APP::Module('Registry')->Get(['module_users_role'], ['id', 'value'])['module_users_role'] as $role) {
            $rule = (array) APP::Module('Registry')->Get(['module_users_rule'], 'value', $role['id']);
            $rules[$role['value']] = array_key_exists('module_users_rule', $rule) ? (array) $rule['module_users_rule'] : [];
        }

        if (array_key_exists($this->user['role'], $rules)) {
            foreach ($rules[$this->user['role']] as $rule) {
                $rule_data = json_decode($rule, 1);

                if (preg_match('/^' . preg_quote(APP::$conf['location'][2], '/') . $rule_data[0] . '$/', APP::Module('Routing')->RequestURI())) {
                    return $rule_data[1];
                }
            }
        }

        return false;
    }

    public function GeneratePassword($length, $letters = true, $numbers = true, $other = false) {
        $chars = [];

        if ($letters) {
            $chars = array_merge($chars, [
                'a','b','c','d','e','f',
                'g','h','i','j','k','l',
                'm','n','o','p','r','s',
                't','u','v','x','y','z',
                'A','B','C','D','E','F',
                'G','H','I','J','K','L',
                'M','N','O','P','R','S',
                'T','U','V','X','Y','Z'
            ]);
        }

        if ($numbers) {
            $chars = array_merge($chars, [
                '1','2','3','4','5','6',
                '7','8','9','0'
            ]);
        }

        if ($other) {
            $chars = array_merge($chars, [
                '.',',',
                '(',')','[',']','!','?',
                '&','^','%','@','*','$',
                '<','>','/','|','+','-',
                '{','}','`','~'
            ]);
        }

        $pass = '';

        for($i = 0; $i < $length; $i++) {
            $index = rand(0, count($chars) - 1);
            $pass .= $chars[$index];
        }

        return $pass;
    }

    public function Logout() {
        APP::Module('Triggers')->Exec('user_logout', $this->user);

        if (isset(APP::Module('Routing')->get['account']) ? (bool) APP::Module('Routing')->get['account'] : false) {
            setcookie(
                'modules[users][email]', '',
                strtotime('-' . $this->settings['module_users_timeout_email']),
                APP::$conf['location'][2], APP::$conf['location'][1]
            );
        }

        setcookie(
            'modules[users][token]', '',
            strtotime('-' . $this->settings['module_users_timeout_token']),
            APP::$conf['location'][2], APP::$conf['location'][1]
        );

        $this->user = false;

        header('Location: ' . APP::Module('Routing')->root);
        exit;
    }

    public function SaveAbout($user) {
        $data = [
            'remote_addr' => APP::Module('Utils')->ClientIP(),
            'self_url' => APP::Module('Routing')->SelfUrl()
        ];

        if (isset($_SERVER['HTTP_USER_AGENT'])) $data['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $data['http_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if (isset($_SERVER['HTTP_REFERER'])) $data['http_referer'] = $_SERVER['HTTP_REFERER'];

        foreach ($data as $item => $value) {
            if (!APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                ['COUNT(id)'], 'users_about',
                [
                    ['user', '=', $user, PDO::PARAM_INT],
                    ['item', '=', $item, PDO::PARAM_STR]
                ]
            )) {
                APP::Module('DB')->Insert(
                    $this->settings['module_users_db_connection'], 'users_about',
                    Array(
                        'id' => 'NULL',
                        'user' => [$user, PDO::PARAM_INT],
                        'item' => [$item, PDO::PARAM_STR],
                        'value' => [$value, PDO::PARAM_STR],
                        'up_date' => 'NOW()'
                    )
                );
            }
        }

        APP::Module('Triggers')->Exec('user_save_about', [
            'user' => $user,
            'data' => $data
        ]);
    }

    public function SaveUTMLabels($user) {
        $num = (int) APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
            ['num'], 'users_utm', [['user_id', '=', $user, PDO::PARAM_INT]],
            false, false, false,
            ['num', 'DESC']
        );

        if ($num) {
            $num ++;
        } else {
            $num = 1;
        }

        $labels = array();
        $values = array();

        foreach ($_GET as $par_key => $par_value) {
            if (preg_match('/^utm_(.*)$/', $par_key, $utm_data)) {
                $values[] = $this->AddUTMLabel($user, $utm_data[1], $par_value, $num);
                $labels[] = $utm_data[1];
            }
        }

        foreach ($_POST as $par_key => $par_value) {
            if (preg_match('/^utm_(.*)$/', $par_key, $utm_data))  {
                $values[] = $this->AddUTMLabel($user, $utm_data[1], $par_value, $num);
                $labels[] = $utm_data[1];
            }
        }

        if ($num == 1) {
            foreach (['content', 'term', 'campaign', 'medium', 'source'] as $label) {
                if (!in_array($label, $labels)){
                    $values[] = $this->AddUTMLabel($user, $label, '', $num);
                }
            }
        }

        APP::Module('Triggers')->Exec('user_save_utm', [
            'user' => $user,
            'data' => $values
        ]);
    }

    public function AddUTMLabel($user, $item, $value, $num = 1) {
        if (!empty($value) || $num == 1) {
            return APP::Module('DB')->Insert(
                $this->settings['module_users_db_connection'], 'users_utm',
                [
                    'id' => 'NULL',
                    'user' => [$user, PDO::PARAM_INT],
                    'num' => [$num, PDO::PARAM_INT],
                    'item' => [$item, PDO::PARAM_STR],
                    'value' => $value ? [$value, PDO::PARAM_STR] : 'NULL',
                    'cr_date' => 'NOW()'
                ]
            );
        }

        return false;
    }


    public function NewUsersGC() {
        if ($this->settings['module_users_timeout_activation'] != '') {
            $lock = fopen($this->settings['module_users_tmp_dir'] . '/module_users_new_users_gc.lock', 'w');

            if (flock($lock, LOCK_EX|LOCK_NB)) {
                APP::Module('DB')->Delete(
                    $this->settings['module_users_db_connection'], 'users',
                    [
                        ['role', '=', 'new', PDO::PARAM_STR],
                        ['UNIX_TIMESTAMP(reg_date)', '<=', strtotime('-' . $this->settings['module_users_timeout_activation']), PDO::PARAM_INT]
                    ]
                );
            } else {
                exit;
            }

            fclose($lock);
        }
    }


    public function ManageUsers() {
        APP::Render('users/admin/index');
    }

    public function AddUser() {
        APP::Render('users/admin/add', 'include', ['roles' => APP::Module('Registry')->Get(['module_users_role'])['module_users_role']]);
    }

    public function EditUser() {
        $user_id = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['user_id_hash']);

        APP::Render(
            'users/admin/edit', 'include',
            [
                'user' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_ASSOC],
                    ['id', 'email', 'password', 'role', 'reg_date', 'last_visit'], 'users',
                    [['id', '=', $user_id, PDO::PARAM_INT]]
                ),
                'roles' => APP::Module('Registry')->Get(['module_users_role'])['module_users_role']
            ]
        );
    }

    public function Actions() {
        $return = isset(APP::Module('Routing')->get['return']) ? APP::Module('Crypt')->SafeB64Decode(APP::Module('Routing')->get['return']) : false;
        APP::Render('users/actions', 'include', ['return' => $return ? filter_var($return, FILTER_VALIDATE_URL) ? $return : false : false]);
    }

    public function DoubleLoginForm() {
        $return = isset(APP::Module('Routing')->get['return_hash']) ? APP::Module('Crypt')->SafeB64Decode(APP::Module('Routing')->get['return_hash']) : false;

        APP::Render(
            'users/double_login', 'include',
            [
                'return' => $return ? filter_var($return, FILTER_VALIDATE_URL) ? $return : false : false,
                'email' => $this->user['email']
            ]
        );
    }

    public function Activate() {
        $result = 'success';

        $user_id = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['user_id_hash']);
        $params = isset(APP::Module('Routing')->get['params']) ? json_decode(APP::Module('Crypt')->Decode(APP::Module('Routing')->get['params']), 1) : [];

        if (APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['id'], 'users', [['id', '=', $user_id, PDO::PARAM_INT]])) {
            APP::Module('DB')->Update(
                $this->settings['module_users_db_connection'], 'users',
                ['role' => 'user'],
                [
                    ['id', '=', $user_id, PDO::PARAM_INT],
                    ['role', '!=', 'user', PDO::PARAM_STR]
                ]
            );

            APP::Module('DB')->Update(
                $this->settings['module_users_db_connection'], 'users_about',
                ['value' => 'active'],
                [
                    ['user', '=', $user_id, PDO::PARAM_INT],
                    ['item', '=', 'state', PDO::PARAM_STR],
                    ['value', '!=', 'active', PDO::PARAM_STR]
                ]
            );

            APP::Module('DB')->Delete(
                APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], 'task_manager',
                [
                    ['token', '=', 'user_' . $user_id . '_activation', PDO::PARAM_STR],
                    ['state', '=', 'wait', PDO::PARAM_STR]
                ]
            );

            APP::Module('Triggers')->Exec('user_activate', [
                'user_id' => $user_id,
                'params' => $params
            ]);
        } else {
            $result = 'error';
        }

        if (isset($params['return'])) {
            if ($params['return']) {
                header('Location: ' . $params['return']);
                exit;
            }
        }

        APP::Render('users/activate', 'include', $result);
    }

    public function ManageRoles() {
        APP::Render('users/admin/roles/index');
    }

    public function AddRole() {
        APP::Render('users/admin/roles/add');
    }

    public function ManageRules() {
        APP::Render('users/admin/roles/rules/index', 'include', [
            'role' => APP::Module('DB')->Select(
                APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
                ['value'], 'registry',
                [['id', '=', APP::Module('Crypt')->Decode(APP::Module('Routing')->get['role_id_hash']), PDO::PARAM_INT]]
            )
        ]);
    }

    public function AddRule() {
        $role_id = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['role_id_hash']);

        APP::Render('users/admin/roles/rules/add', 'include', APP::Module('DB')->Select(
            APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
            ['value'], 'registry',
            [['id', '=', $role_id, PDO::PARAM_INT]]
        ));
    }

    public function EditRule() {
        $role_id = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['role_id_hash']);
        $rule_id = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['rule_id_hash']);

        APP::Render('users/admin/roles/rules/edit', 'include', [
            'role' => APP::Module('DB')->Select(
                APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
                ['value'], 'registry',
                [['id', '=', $role_id, PDO::PARAM_INT]]
            ),
            'rule' => json_decode(APP::Module('DB')->Select(
                APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
                ['value'], 'registry',
                [['id', '=', $rule_id, PDO::PARAM_INT]]
            ), 1)
        ]);
    }

    public function SetupOAuthClients() {
        APP::Render('users/admin/oauth_clients');
    }

    public function SetupNotifications() {
        APP::Render('users/admin/notifications');
    }

    public function SetupServices() {
        APP::Render('users/admin/services');
    }

    public function SetupAuth() {
        APP::Render('users/admin/auth');
    }

    public function SetupPasswords() {
        APP::Render('users/admin/passwords');
    }

    public function SetupTimeouts() {
        APP::Render('users/admin/timeouts');
    }

    public function SetupOther() {
        APP::Render('users/admin/settings');
    }
    
    public function ImportUsers() {
        if (isset($_FILES['users'])) {
            foreach (file($_FILES['users']['tmp_name']) as $string) {
                $user = explode(';', trim($string));
                
                for ($i = 0; $i <= 16; $i ++) {
                    if (!isset($user[$i])) {
                        $user[$i] = false;
                    } 
                }

                $user_id = APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                    ['id'], 'users',
                    [
                        ['email', '=', $user[0], PDO::PARAM_STR]
                    ]
                );
                
                if ($user_id) {
                    if ($user[4]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'source', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['source', PDO::PARAM_STR],
                                    'value' => [$user[4], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[5]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'remote_addr', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['remote_addr', PDO::PARAM_STR],
                                    'value' => [$user[5], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[6]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'country_name_ru', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['country_name_ru', PDO::PARAM_STR],
                                    'value' => [$user[6], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[7]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'region_name_ru', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['region_name_ru', PDO::PARAM_STR],
                                    'value' => [$user[7], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[8]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'city_name_ru', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['city_name_ru', PDO::PARAM_STR],
                                    'value' => [$user[8], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[9]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'firstname', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['firstname', PDO::PARAM_STR],
                                    'value' => [$user[9], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[10]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'lastname', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['lastname', PDO::PARAM_STR],
                                    'value' => [$user[10], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ($user[11]) {
                        if (!APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['id'], 'users_about',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR],
                                ['item', '=', 'tel', PDO::PARAM_STR]
                            ]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], 'users_about',
                                Array(
                                    'id' => 'NULL',
                                    'user' => [$user_id, PDO::PARAM_INT],
                                    'item' => ['tel', PDO::PARAM_STR],
                                    'value' => [$user[11], PDO::PARAM_STR],
                                    'up_date' => 'NOW()'
                                )
                            );
                        }
                    }
                    
                    if ((((($user[12]) || ($user[13]) || ($user[14]) || ($user[15]) || ($user[16]))))) {
                        $user_utm_num = (int) APP::Module('DB')->Select(
                            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                            ['MAX(num)'], 'users_utm',
                            [
                                ['user', '=', $user_id, PDO::PARAM_STR]
                            ]
                        ) + 1;
                        
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_utm',
                            [
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'num' => [$user_utm_num, PDO::PARAM_INT],
                                'item' => ['source', PDO::PARAM_STR],
                                'value' => $user[12] ? [$user[12], PDO::PARAM_STR] : 'NULL',
                                'cr_date' => 'NOW()'
                            ]
                        );

                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_utm',
                            [
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'num' => [$user_utm_num, PDO::PARAM_INT],
                                'item' => ['medium', PDO::PARAM_STR],
                                'value' => $user[13] ? [$user[13], PDO::PARAM_STR] : 'NULL',
                                'cr_date' => 'NOW()'
                            ]
                        );

                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_utm',
                            [
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'num' => [$user_utm_num, PDO::PARAM_INT],
                                'item' => ['campaign', PDO::PARAM_STR],
                                'value' => $user[14] ? [$user[14], PDO::PARAM_STR] : 'NULL',
                                'cr_date' => 'NOW()'
                            ]
                        );

                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_utm',
                            [
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'num' => [$user_utm_num, PDO::PARAM_INT],
                                'item' => ['term', PDO::PARAM_STR],
                                'value' => $user[15] ? [$user[15], PDO::PARAM_STR] : 'NULL',
                                'cr_date' => 'NOW()'
                            ]
                        );

                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_utm',
                            [
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'num' => [$user_utm_num, PDO::PARAM_INT],
                                'item' => ['content', PDO::PARAM_STR],
                                'value' => $user[16] ? [$user[16], PDO::PARAM_STR] : 'NULL',
                                'cr_date' => 'NOW()'
                            ]
                        );
                    }
                } else {
                    $user_id = APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users',
                        Array(
                            'id'            => 'NULL',
                            'email'         => [$user[0], PDO::PARAM_STR],
                            'password'      => [APP::Module('Crypt')->Encode($user[1] ? $user[1] : $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length'])), PDO::PARAM_STR],
                            'role'          => [$user[2], PDO::PARAM_STR],
                            'reg_date'      => 'NOW()',
                            'last_visit'    => 'NOW()',
                        )
                    );
                    
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users_about',
                        Array(
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'item' => ['state', PDO::PARAM_STR],
                            'value' => [$user[3], PDO::PARAM_STR],
                            'up_date' => 'NOW()'
                        )
                    );
                    
                    if ($user[4]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['source', PDO::PARAM_STR],
                                'value' => [$user[4], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[5]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['remote_addr', PDO::PARAM_STR],
                                'value' => [$user[5], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[6]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['country_name_ru', PDO::PARAM_STR],
                                'value' => [$user[6], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[7]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['region_name_ru', PDO::PARAM_STR],
                                'value' => [$user[7], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[8]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['city_name_ru', PDO::PARAM_STR],
                                'value' => [$user[8], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[9]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['firstname', PDO::PARAM_STR],
                                'value' => [$user[9], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[10]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['lastname', PDO::PARAM_STR],
                                'value' => [$user[10], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    if ($user[11]) {
                        APP::Module('DB')->Insert(
                            $this->settings['module_users_db_connection'], 'users_about',
                            Array(
                                'id' => 'NULL',
                                'user' => [$user_id, PDO::PARAM_INT],
                                'item' => ['tel', PDO::PARAM_STR],
                                'value' => [$user[11], PDO::PARAM_STR],
                                'up_date' => 'NOW()'
                            )
                        );
                    }
                    
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users_utm',
                        [
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'num' => [1, PDO::PARAM_INT],
                            'item' => ['source', PDO::PARAM_STR],
                            'value' => $user[12] ? [$user[12], PDO::PARAM_STR] : 'NULL',
                            'cr_date' => 'NOW()'
                        ]
                    );
                    
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users_utm',
                        [
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'num' => [1, PDO::PARAM_INT],
                            'item' => ['medium', PDO::PARAM_STR],
                            'value' => $user[13] ? [$user[13], PDO::PARAM_STR] : 'NULL',
                            'cr_date' => 'NOW()'
                        ]
                    );
                    
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users_utm',
                        [
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'num' => [1, PDO::PARAM_INT],
                            'item' => ['campaign', PDO::PARAM_STR],
                            'value' => $user[14] ? [$user[14], PDO::PARAM_STR] : 'NULL',
                            'cr_date' => 'NOW()'
                        ]
                    );
                    
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users_utm',
                        [
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'num' => [1, PDO::PARAM_INT],
                            'item' => ['term', PDO::PARAM_STR],
                            'value' => $user[15] ? [$user[15], PDO::PARAM_STR] : 'NULL',
                            'cr_date' => 'NOW()'
                        ]
                    );
                    
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], 'users_utm',
                        [
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'num' => [1, PDO::PARAM_INT],
                            'item' => ['content', PDO::PARAM_STR],
                            'value' => $user[16] ? [$user[16], PDO::PARAM_STR] : 'NULL',
                            'cr_date' => 'NOW()'
                        ]
                    );
                }
            }

            header('Location: ' . APP::Module('Routing')->root . 'admin/users/import?success');
        } else {
            APP::Render('users/admin/import', 'include', 'form');
        }
    }


    public function PublicProfile() {
        if (!isset(APP::Module('Routing')->get['user_id_hash'])) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $user_id = (int) APP::Module('Crypt')->Decode(APP::Module('Routing')->get['user_id_hash']);
        $about = [];
        $comments = false;

        if ((!APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'users',
            [['id', '=', $user_id, PDO::PARAM_INT]]
        )) || (!$user_id)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['item', 'value'], 'users_about',
            [['user', '=', $user_id, PDO::PARAM_INT]]
        ) as $value) {
            $about[$value['item']] = $value['value'];
        }

        APP::Render(
            'users/profiles/public', 'include',
            [
                'user' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_ASSOC],
                    ['id', 'email', 'password', 'role', 'reg_date', 'last_visit'], 'users',
                    [['id', '=', $user_id, PDO::PARAM_INT]]
                ),
                'social-profiles' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    ['service', 'extra'], 'users_accounts',
                    [['user_id', '=', $user_id, PDO::PARAM_INT]]
                ),
                'about' => $about,
            ]
        );
    }

    public function PrivateProfile() {
        $about = [];
        $comments = false;
        $likes = false;
        $premium = false;

        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['item', 'value'], 'users_about',
            [['user', '=', $this->user['id'], PDO::PARAM_INT]]
        ) as $value) {
            $about[$value['item']] = $value['value'];
        }

        if (isset(APP::$modules['Comments'])) {
            $comments = APP::Module('DB')->Select(
                APP::Module('Comments')->settings['module_comments_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                ['id', 'message', 'url', 'UNIX_TIMESTAMP(up_date) as up_date'], 'comments_messages',
                [['user', '=', $this->user['id'], PDO::PARAM_INT]],
                false, false, false,
                ['id', 'desc']
            );
        }

        if (isset(APP::$modules['Likes'])) {
            $likes = APP::Module('DB')->Select(
                APP::Module('Likes')->settings['module_likes_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                ['id', 'url', 'UNIX_TIMESTAMP(up_date) as up_date'], 'likes_list',
                [['user', '=', $this->user['id'], PDO::PARAM_INT]],
                false, false, false,
                ['id', 'desc']
            );
        }
        
        if (isset(APP::$modules['Members'])) {
            foreach (APP::Module('DB')->Select(
                APP::Module('Members')->settings['module_members_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                ['item', 'item_id'], 'members_access',
                [['user_id', '=', $this->user['id'], PDO::PARAM_INT]]
            ) as $value) {
                $table = false;
                $title = false;
                
                switch ($value['item']) {
                    case 'g': 
                        $table = 'members_pages_groups';
                        $title = 'name'; 
                        break;
                    case 'p': 
                        $table = 'members_pages'; 
                        $title = 'title'; 
                        break;
                }
                
                $premium[] = [
                    'type' => $value['item'],
                    'id' => $value['item_id'],
                    'title' => APP::Module('DB')->Select(
                        APP::Module('Members')->settings['module_members_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                        [$title . ' AS name'], $table,
                        [['id', '=', $value['item_id'], PDO::PARAM_INT]]
                    )
                ];
            }
        }

        APP::Render(
            'users/profiles/private', 'include',
            [
                'user' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_ASSOC],
                    ['id', 'email', 'password', 'role', 'reg_date', 'last_visit'], 'users',
                    [['id', '=', $this->user['id'], PDO::PARAM_INT]]
                ),
                'social-profiles' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    ['service', 'extra'], 'users_accounts',
                    [['user_id', '=', $this->user['id'], PDO::PARAM_INT]]
                ),
                'about' => $about,
                'comments' => $comments,
                'likes' => $likes,
                'premium' => $premium
            ]
        );
    }

    public function AdminProfile() {
        $user_id = APP::Module('Routing')->get['user_id'];
        
        $about = [];
        $mail = [];
        $tunnels_subscriptions = [];
        $tunnels_queue = [];
        $tunnels_allow = [];
        $tags = [];
        $utm = [];
        $comments = [];
        $likes = [];
        $premium = [];
        $invoices = [];
        $polls = [];
        $taskmanager = [];

        if ((!APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'users',
            [['id', '=', $user_id, PDO::PARAM_INT]]
        )) || (!is_numeric($user_id))) {
            if (!$user_id = APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                ['id'], 'users',
                [['email', '=', APP::Module('Routing')->get['user_id'], PDO::PARAM_STR]]
            )) {
                header('HTTP/1.0 404 Not Found');
                exit;
            }
        }

        // ABOUT
        
        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['item', 'value'], 'users_about',
            [['user', '=', $user_id, PDO::PARAM_INT]]
        ) as $value) {
            $about[$value['item']] = $value['value'];
        }
        
        // MAIL
        
        $mail_id = [];
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'mail_log.id', 
                'mail_log.letter', 
                'mail_log.sender', 
                'mail_log.transport', 
                'mail_log.state', 
                'mail_log.result', 
                'mail_log.retries', 
                'mail_log.ping', 
                'mail_log.cr_date',
                
                'mail_letters.subject AS letter_subject',
                'mail_letters.priority AS letter_priority',
                
                'mail_senders.name AS sender_name',
                'mail_senders.email AS sender_email',
                
                'mail_transport.module AS transport_module',
                'mail_transport.method AS transport_method'
            ], 
            'mail_log',
            [['user', '=', $user_id, PDO::PARAM_INT]],
            [
                'join/mail_letters' => [['mail_letters.id', '=', 'mail_log.letter']],
                'join/mail_senders' => [['mail_senders.id', '=', 'mail_log.sender']],
                'join/mail_transport' => [['mail_transport.id', '=', 'mail_log.transport']]
            ],
            ['mail_log.id'],
            false,
            ['mail_log.id', 'DESC']
        ) as $value) {
            $mail_id[] = $value['id'];
            $mail[$value['id']] = [
                'log' => $value,
                'events' => [],
                'tags' => []
            ];
        }
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['id', 'log', 'event', 'details', 'cr_date'], 'mail_events',
            [['log', 'IN', $mail_id]],
            false, false, false,
            ['id', 'DESC']
        ) as $value) {
            $value['details'] = APP::Module('Utils')->IsSerialized($value['details']) ? json_encode(unserialize($value['details'])) : $value['details'];
            
            $mail[$value['log']]['events'][] = $value;
            $mail[$value['log']]['tags'][] = $value['event'];
        }

        // TUNNELS SUBSCRIPTIONS
        
        $tunnels_id = [];
        $user_tunnel_id = [];
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'tunnels_users.id', 
                'tunnels_users.tunnel_id', 
                'tunnels_users.state', 
                'tunnels_users.object', 
                
                'tunnels.type AS tunnel_type',
                'tunnels.name AS tunnel_name'
            ], 
            'tunnels_users',
            [['user_id', '=', $user_id, PDO::PARAM_INT]],
            [
                'join/tunnels' => [['tunnels.id', '=', 'tunnels_users.tunnel_id']]
            ],
            ['tunnels_users.id'],
            false,
            ['tunnels_users.id', 'DESC']
        ) as $value) {
            $tunnels_id[] = $value['tunnel_id'];
            $user_tunnel_id[] = $value['id'];
            $tunnels_subscriptions[$value['id']] = [
                'info' => $value,
                'tags' => []
            ];
        }
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['id', 'user_tunnel_id', 'label_id', 'info', 'cr_date'], 'tunnels_tags',
            [['user_tunnel_id', 'IN', $user_tunnel_id]],
            false, false, false,
            ['id', 'DESC']
        ) as $value) {
            $tunnels_subscriptions[$value['user_tunnel_id']]['tags'][] = $value;
        }
        
        // TUNNELS QUEUE

        foreach (APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'tunnels_queue.id', 
                'tunnels_queue.tunnel_id', 
                'tunnels_queue.object_id', 
                'tunnels_queue.timeout', 
                'tunnels_queue.settings', 
                'tunnels_queue.cr_date', 

                'tunnels.type AS tunnel_type',
                'tunnels.name AS tunnel_name'
            ], 
            'tunnels_queue',
            [['tunnels_queue.user_id', '=', $user_id, PDO::PARAM_INT]],
            [
                'join/tunnels' => [['tunnels.id', '=', 'tunnels_queue.tunnel_id']]
            ],
            ['tunnels_queue.id'],
            false,
            ['tunnels_queue.cr_date', 'DESC']
        ) as $value) {
            $tunnels_queue[$value['id']] = $value;
        }

        // TUNNELS ALLOW

        $tunnels_allow = APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['id', 'type', 'name'], 'tunnels',
            [['id', 'NOT IN', $tunnels_id]],
            false, false, false,
            ['type', 'DESC']
        );
        
        // TAGS
        
        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'users_tags.id', 
                'users_tags.item', 
                'users_tags.value', 
                'users_tags.cr_date'
            ], 
            'users_tags',
            [['user', '=', $user_id, PDO::PARAM_INT]],
            false, false, false,
            ['users_tags.id', 'DESC']
        ) as $value) {
            $tags[$value['id']] = $value;
        }
        
        // UTM
        
        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'id', 
                'num', 
                'item', 
                'value',
                'cr_date'
            ], 
            'users_utm',
            [['user', '=', $user_id, PDO::PARAM_INT]]
        ) as $value) {
            $utm[$value['num']][$value['item']] = [$value['value'], $value['cr_date']];
        }
        
        // INVOICES
        foreach (APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'id', 
                'amount', 
                'state', 
                'author',
                'up_date',
                'cr_date'
            ], 
            'billing_invoices',
            [['user_id', '=', $user_id, PDO::PARAM_INT]]
        ) as $invoice) {
            $invoices[] = [
                'invoice' => $invoice,
                'details' => APP::Module('DB')->Select(
                    APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    [
                        'id', 
                        'item', 
                        'value'
                    ], 
                    'billing_invoices_details',
                    [['invoice', '=', $invoice['id'], PDO::PARAM_INT]]
                ),
                'products' => APP::Module('DB')->Select(
                    APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    [
                        'billing_invoices_products.id', 
                        'billing_invoices_products.type', 
                        'billing_invoices_products.product',
                        'billing_invoices_products.amount',
                        'billing_invoices_products.cr_date',
                        
                        'billing_products.name'
                    ], 
                    'billing_invoices_products',
                    [['billing_invoices_products.invoice', '=', $invoice['id'], PDO::PARAM_INT]],
                    [
                        'join/billing_products' => [['billing_products.id', '=', 'billing_invoices_products.product']]
                    ],
                    ['billing_invoices_products.id'],
                    false,
                    ['billing_invoices_products.cr_date', 'ASC']
                ),
                'tags' => APP::Module('DB')->Select(
                    APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    [
                        'id', 
                        'action', 
                        'action_data',
                        'cr_date'
                    ], 
                    'billing_invoices_tag',
                    [['invoice', '=', $invoice['id'], PDO::PARAM_INT]]
                ),
                'payments' => APP::Module('DB')->Select(
                    APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    [
                        'id', 
                        'method',
                        'cr_date'
                    ], 
                    'billing_payments',
                    [['invoice', '=', $invoice['id'], PDO::PARAM_INT]]
                )
            ];
        }
        
        // POLLS
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Polls')->settings['module_polls_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'polls_users.id', 
                'polls_users.poll', 
                'polls_users.cr_date',

                'polls.name'
            ], 
            'polls_users',
            [['polls_users.user', '=', $user_id, PDO::PARAM_INT]],
            [
                'join/polls' => [['polls.id', '=', 'polls_users.poll']]
            ],
            ['polls_users.id'],
            false,
            ['polls_users.cr_date', 'ASC']
        ) as $poll) {
            $poll_questions = [];
            $poll_answers = [];
            
            foreach (APP::Module('DB')->Select(
                APP::Module('Polls')->settings['module_polls_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                [
                    'id', 
                    'question'
                ], 
                'polls_questions',
                [['poll_id', '=', $poll['poll'], PDO::PARAM_INT]]
            ) as $value) {
                $poll_questions[$value['id']] = $value['question'];
            }

            foreach (APP::Module('DB')->Select(
                APP::Module('Polls')->settings['module_polls_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                [
                    'question',
                    'answer',
                    'up_date'
                ], 
                'polls_answers_users',
                [
                    ['user', '=', $user_id, PDO::PARAM_INT],
                    ['question', 'IN', array_keys($poll_questions), PDO::PARAM_STR]
                ]
            ) as $value) {
                $poll_answers[] = [
                    'question' => $poll_questions[$value['question']],
                    'answer' => $value['answer'],
                    'date' => $value['up_date']
                ];
            }
            
            $polls[] = [
                'poll' => $poll,
                'answers' => $poll_answers
            ];
        }
        
        // COMMENTS

        if (isset(APP::$modules['Comments'])) {
            $comments = APP::Module('DB')->Select(
                APP::Module('Comments')->settings['module_comments_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                ['id', 'message', 'url', 'UNIX_TIMESTAMP(up_date) as up_date'], 'comments_messages',
                [['user', '=', $user_id, PDO::PARAM_INT]],
                false, false, false,
                ['id', 'desc']
            );
        }

        // LIKES
        
        if (isset(APP::$modules['Likes'])) {
            $likes = APP::Module('DB')->Select(
                APP::Module('Likes')->settings['module_likes_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                ['id', 'url', 'UNIX_TIMESTAMP(up_date) as up_date'], 'likes_list',
                [['user', '=', $user_id, PDO::PARAM_INT]],
                false, false, false,
                ['id', 'desc']
            );
        }
        
        // MEMBERS
        
        if (isset(APP::$modules['Members'])) {
            foreach (APP::Module('DB')->Select(
                APP::Module('Members')->settings['module_members_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                ['item', 'item_id'], 'members_access',
                [['user_id', '=', $user_id, PDO::PARAM_INT]]
            ) as $value) {
                $table = false;
                $title = false;
                
                switch ($value['item']) {
                    case 'g': 
                        $table = 'members_pages_groups';
                        $title = 'name'; 
                        break;
                    case 'p': 
                        $table = 'members_pages'; 
                        $title = 'title'; 
                        break;
                }
                
                $premium[] = [
                    'type' => $value['item'],
                    'id' => $value['item_id'],
                    'title' => APP::Module('DB')->Select(
                        APP::Module('Members')->settings['module_members_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                        [$title . ' AS name'], $table,
                        [['id', '=', $value['item_id'], PDO::PARAM_INT]]
                    )
                ];
            }
        }
        
        // TASKMANAGER
        
        foreach (APP::Module('DB')->Select(
            APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            [
                'id', 
                'token', 
                'module', 
                'method',
                'args',
                'state',
                'cr_date',
                'exec_date',
                'complete_date'
            ], 
            'task_manager',
            [['token', 'LIKE', 'user\_' . $user_id . '\_%', PDO::PARAM_STR]]
        ) as $value) {
            $taskmanager[$value['id']] = $value;
        }

        APP::Render(
            'users/admin/profile', 'include',
            [
                'user' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_ASSOC],
                    ['id', 'email', 'password', 'role', 'reg_date', 'last_visit'], 'users',
                    [['id', '=', $user_id, PDO::PARAM_INT]]
                ),
                'social-profiles' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
                    ['service', 'extra'], 'users_accounts',
                    [['user_id', '=', $user_id, PDO::PARAM_INT]]
                ),
                'about' => $about,
                'mail' => $mail,
                'tunnels' => [
                    'subscriptions' => $tunnels_subscriptions,
                    'queue' => $tunnels_queue,
                    'allow' => $tunnels_allow
                ],
                'tags' => $tags,
                'utm' => $utm,
                'comments' => $comments,
                'likes' => $likes,
                'premium' => $premium,
                'invoices' => $invoices,
                'polls' => $polls,
                'taskmanager' => $taskmanager
            ]
        );
    }

    public function APISearchUsers() {
        $request = json_decode(file_get_contents('php://input'), true);
        $out = $this->UsersSearch(json_decode($request['search'], 1));
        $rows = [];
        
        // Удаление системного пользователя из результатов поиска
        if (array_search(0, $out) !== false) {
            unset($out[array_search(0, $out)]);
        }

        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            [
                'users.id', 
                'users.email', 
                'users.password', 
                'users.role', 
                'users.reg_date', 
                'users.last_visit',
                
                'state.value as state',
                'phone.value as tel',
                'yaregion.value as yaregion',
                'SUM(billing_invoices.amount) as amount'
            ], 
            'users',
            [
                ['users.id', 'IN', $out, PDO::PARAM_INT]
            ],
            [
                'left join/billing_invoices' => [
                    ['billing_invoices.user_id', '=', 'users.id'],
                    ['billing_invoices.state', '=', '"success"']
                ],
                'left join/users_about/state' => [
                    ['state.user', '=', 'users.id'],
                    ['state.item', '=', '"state"']
                ],
                'left join/users_about/phone' => [
                    ['phone.user', '=', 'users.id'],
                    ['phone.item', '=', '"mobile_phone"']
                ],
                'left join/users_about/yaregion' => [
                    ['yaregion.user', '=', 'users.id'],
                    ['yaregion.item', '=', '"yaregion"']
                ]
            ], 
            ['users.id'], false, 
            [$request['sort_by'], $request['sort_direction']],
            $request['rows'] === -1 ? false : [($request['current'] - 1) * $request['rows'], $request['rows']]
        ) as $row) {
            $row['auth_token'] = APP::Module('Crypt')->Encode(json_encode([$row['email'], $row['password']]));
            $row['user_id_token'] = APP::Module('Crypt')->Encode($row['id']);
            $row['social'] = APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
                ['extra', 'service'], 'users_accounts',
                [['user_id', '=', $row['id'], PDO::PARAM_INT]]
            );
            
            foreach(APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
                ['item', 'value'], 'users_about',
                [['user', '=', $row['id'], PDO::PARAM_INT]]
            ) as $item){
                $row[$item['item']] = $item['value'];
            }
            
            array_push($rows, $row);
        }
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode([
            'current' => $request['current'],
            'rowCount' => $request['rows'],
            'rows' => $rows,
            'total' => count($out)
        ]);
        exit;
    }
    
    public function APISearchUsersAction() {
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($this->users_actions->{$_POST['action']}($this->UsersSearch(json_decode($_POST['rules'], 1)), isset($_POST['settings']) ? $_POST['settings'] : false));
        exit;
    }

    public function APIListUsers() {
        $rows = [];
        $where = [['id', '!=', 0, PDO::PARAM_INT]];

        foreach (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_ASSOC],
            ['id', 'email', 'password', 'role', 'reg_date', 'last_visit'], 'users',
            $_POST['searchPhrase'] ? array_merge([['email', 'LIKE', $_POST['searchPhrase'] . '%' ]], $where) : $where,
            false, false, false,
            [array_keys($_POST['sort'])[0], array_values($_POST['sort'])[0]],
            $_POST['rowCount'] == -1 ? false : [($_POST['current'] - 1) * $_POST['rowCount'], $_POST['rowCount']]
        ) as $row) {
            $row['auth_token'] = APP::Module('Crypt')->Encode(json_encode([$row['email'], $row['password']]));
            $row['user_id_token'] = APP::Module('Crypt')->Encode($row['id']);

            array_push($rows, $row);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'current' => $_POST['current'],
            'rowCount' => $_POST['rowCount'],
            'rows' => $rows,
            'total' => APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['COUNT(id)'], 'users', $_POST['searchPhrase'] ? array_merge([['email', 'LIKE', $_POST['searchPhrase'] . '%' ]], $where) : $where)
        ]);
        exit;
    }

    public function APIRemoveUser() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (!APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'users',
            [['id', '=', $_POST['id'], PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if ($out['status'] == 'success') {
            $out['count'] = APP::Module('DB')->Delete(
                $this->settings['module_users_db_connection'], 'users',
                [['id', '=', $_POST['id'], PDO::PARAM_INT]]
            );

            APP::Module('Triggers')->Exec('remove_user', ['id' => $_POST['id'], 'result' => $out['count']]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIAddUser() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        } else if (APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['id'], 'users', [['email', '=', $_POST['email'], PDO::PARAM_STR]])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }

        if (empty($_POST['password'])) {
            $_POST['password'] = $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length']);
        } else if (strlen($_POST['password']) < (int) $this->settings['module_users_min_pass_length']) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        } else if ($_POST['password'] != $_POST['re-password']) {
            $out['status'] = 'error';
            $out['errors'][] = 4;
        }

        if ($out['status'] == 'success') {
            $user_id = $this->Register($_POST['email'], $_POST['password'], $_POST['role']);

            if ((int) $_POST['notification']) {
                APP::Module('Mail')->Send($_POST['email'], $_POST['notification'], [
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'expire' => strtotime('+' . $this->settings['module_users_timeout_activation']),
                    'link' => APP::Module('Routing')->root . 'users/activate/' . APP::Module('Crypt')->Encode($user_id) . '/'
                ], true, 'add_user');
            }

            $out['user_id'] = $user_id;

            APP::Module('Triggers')->Exec('add_user', [
                'id' => $out['user_id'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role'],
                'notification' => $_POST['notification']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APILogin() {
        $status = 'error';

        if (($user = $this->Login($_POST['email'], APP::Module('Crypt')->Encode($_POST['password']))) && ((int) $this->settings['module_users_login_service'])) {
            $this->user = $this->Auth($user, true, isset($_POST['remember-me']));

            $status = 'success';

            APP::Module('Triggers')->Exec('user_login', [
                'id' => $user,
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'remember-me' => isset($_POST['remember-me'])
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        echo json_encode(['status' => $status]);
        exit;
    }

    public function APIDoubleLogin() {
        $status = 'error';

        if ($this->user['password'] === APP::Module('Crypt')->Encode($_POST['password'])) {
            APP::Module('Triggers')->Exec('user_double_login', $this->user);
            APP::Module('Sessions')->session['modules']['users']['double_auth'] = true;
            $status = 'success';
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        echo json_encode(['status' => $status]);
        exit;
    }

    public function APIRegister() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        } else if (APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['id'], 'users', [['email', '=', $_POST['email'], PDO::PARAM_STR]])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }

        if (empty($_POST['password'])) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        } else if (strlen($_POST['password']) < (int) $this->settings['module_users_min_pass_length']) {
            $out['status'] = 'error';
            $out['errors'][] = 4;
        } else if ($_POST['password'] != $_POST['re-password']) {
            $out['status'] = 'error';
            $out['errors'][] = 5;
        }

        if (!(int) $this->settings['module_users_register_service']) {
            $out['status'] = 'error';
            $out['errors'][] = 6;
        }

        if ($out['status'] == 'success') {
            $user_id = $this->Register($_POST['email'], $_POST['password']);
            $this->user = $this->Auth($user_id, true, false);

            APP::Module('Mail')->Send($_POST['email'], $this->settings['module_users_register_activation_letter'], [
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'expire' => strtotime('+' . $this->settings['module_users_timeout_activation']),
                'link' => APP::Module('Routing')->root . 'users/activate/' . APP::Module('Crypt')->Encode($user_id) . '/'
            ], true, 'register_user');

            $out['user_id'] = $user_id;

            APP::Module('Triggers')->Exec('register_user', [
                'id' => $out['user_id'],
                'email' => $_POST['email'],
                'password' => $_POST['password']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APISubscribe() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        } else if (APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['id'], 'users', [['email', '=', $_POST['email'], PDO::PARAM_STR]])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }

        if (!(int) $this->settings['module_users_register_service']) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        }

        if ($out['status'] == 'success') {
            $password = $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length']);
            $user_id = $this->Register($_POST['email'], $password);
            $this->user = $this->Auth($user_id, true, false);

            APP::Module('Mail')->Send($_POST['email'], $this->settings['module_users_subscribe_activation_letter'], [
                'email' => $_POST['email'],
                'password' => $password,
                'expire' => strtotime('+' . $this->settings['module_users_timeout_activation']),
                'link' => APP::Module('Routing')->root . 'users/activate/' . APP::Module('Crypt')->Encode($user_id) . '/'
            ], true, 'subscribe_user');

            $out['user_id'] = $user_id;

            APP::Module('Triggers')->Exec('subscribe_user', [
                'id' => $out['user_id'],
                'email' => $_POST['email'],
                'password' => $password
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIResetPassword() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        } else if (!APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['id'], 'users', [['email', '=', $_POST['email'], PDO::PARAM_STR]])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }

        if (!(int) $this->settings['module_users_reset_password_service']) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        }

        if ($out['status'] == 'success') {
            $out = [
                'status' => 'success',
                'info' => APP::Module('Mail')->Send($_POST['email'], $this->settings['module_users_reset_password_letter'], [
                    'link' => APP::Module('Routing')->root . 'users/actions/change-password?user_token=' . APP::Module('Crypt')->Encode(json_encode([
                        $_POST['email'],
                        APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['password'], 'users', [['email', '=', $_POST['email'], PDO::PARAM_STR]])
                    ]))
                ], true, 'reset_password_user')
            ];

            APP::Module('Triggers')->Exec('reset_user_password', ['email' => $_POST['email']]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIChangePassword() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (!isset($this->user['id'])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        } else if (empty($_POST['password'])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        } else if (strlen($_POST['password']) < (int) $this->settings['module_users_min_pass_length']) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        } else if ($_POST['password'] != $_POST['re-password']) {
            $out['status'] = 'error';
            $out['errors'][] = 4;
        }

        if (!(int) $this->settings['module_users_change_password_service']) {
            $out['status'] = 'error';
            $out['errors'][] = 5;
        }

        if ($out['status'] == 'success') {
            APP::Module('Triggers')->Exec('change_user_password', [
                'user' => $this->user,
                'password' => $_POST['password']
            ]);

            APP::Module('DB')->Update(
                $this->settings['module_users_db_connection'], 'users',
                ['password' => APP::Module('Crypt')->Encode($_POST['password'])],
                [['id', '=', $this->user['id'], PDO::PARAM_INT]]
            );

            $this->user = $this->Auth($this->user['id'], true, true);

            $out = [
                'status' => 'success',
                'info' => APP::Module('Mail')->Send($this->user['email'], $this->settings['module_users_change_password_letter'], [
                    'email' => $this->user['email'],
                    'password' => $_POST['password']
                ], true, 'change_password_user')
            ];
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APILogout() {
        APP::Module('Triggers')->Exec('user_logout', $this->user);

        if (isset(APP::Module('Routing')->get['account']) ? (bool) APP::Module('Routing')->get['account'] : false) {
            setcookie(
                'modules[users][email]', '',
                strtotime('-' . $this->settings['module_users_timeout_email']),
                APP::$conf['location'][2], APP::$conf['location'][1]
            );
        }

        setcookie(
            'modules[users][token]', '',
            strtotime('-' . $this->settings['module_users_timeout_token']),
            APP::$conf['location'][2], APP::$conf['location'][1]
        );

        $this->user = false;

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode(['result' => true]);
        exit;
    }

    public function APIUpdateUser() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        $user_id = APP::Module('Crypt')->Decode($_POST['id']);

        if (!APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['COUNT(id)'], 'users', [['id', '=', $user_id, PDO::PARAM_INT]])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if (APP::Module('Sessions')->session['modules']['users']['double_auth']) {
            if (empty($_POST['password'])) {
                $out['status'] = 'error';
                $out['errors'][] = 2;
            } else if (strlen($_POST['password']) < (int) $this->settings['module_users_min_pass_length']) {
                $out['status'] = 'error';
                $out['errors'][] = 3;
            } else if ($_POST['password'] != $_POST['re-password']) {
                $out['status'] = 'error';
                $out['errors'][] = 4;
            }
        }

        if (array_search($_POST['role'], APP::Module('Registry')->Get(['module_users_role'])['module_users_role']) === false) {
            $out['status'] = 'error';
            $out['errors'][] = 5;
        }

        if ($out['status'] == 'success') {
            APP::Module('DB')->Update($this->settings['module_users_db_connection'], 'users', ['role' => $_POST['role']], [['id', '=', $user_id, PDO::PARAM_INT]]);

            if (APP::Module('Sessions')->session['modules']['users']['double_auth']) {
                APP::Module('DB')->Update($this->settings['module_users_db_connection'], 'users', ['password' => APP::Module('Crypt')->Encode($_POST['password'])], [['id', '=', $user_id, PDO::PARAM_INT]]);
            }

            APP::Module('Triggers')->Exec('update_user', [
                'id' => $user_id,
                'password' => APP::Module('Sessions')->session['modules']['users']['double_auth'] ? $_POST['password'] : false,
                'role' => $_POST['role']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIListRoles() {
        $roles = [];
        $rows = [];

        foreach (APP::Module('Registry')->Get(['module_users_role'], ['id', 'value'])['module_users_role'] as $role) {
            if (($_POST['searchPhrase']) && (preg_match('/^' . $_POST['searchPhrase'] . '/', $role['value']) === 0)) continue;
            array_push($roles, $role);
        }

        for ($x = ($_POST['current'] - 1) * $_POST['rowCount']; $x < $_POST['rowCount'] * $_POST['current']; $x ++) {
            if (!isset($roles[$x])) continue;
            $roles[$x]['token'] = APP::Module('Crypt')->Encode($roles[$x]['id']);
            array_push($rows, $roles[$x]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'current' => $_POST['current'],
            'rowCount' => $_POST['rowCount'],
            'rows' => $rows,
            'total' => count($roles)
        ]);
        exit;
    }

    public function APIAddRole() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (empty($_POST['role'])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if ($out['status'] == 'success') {
            $out['role_id'] = APP::Module('Registry')->Add('module_users_role', $_POST['role']);
            APP::Module('Triggers')->Exec('add_user_role', ['id' => $out['role_id'], 'role' => $_POST['role']]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIRemoveRole() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (!APP::Module('DB')->Select(
            APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'registry',
            [['id', '=', $_POST['id'], PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if ($out['status'] == 'success') {
            $out['count'] = APP::Module('Registry')->Delete([['id', '=', $_POST['id'], PDO::PARAM_INT]]);
            $out['count'] = APP::Module('Registry')->Delete([['sub_id', '=', $_POST['id'], PDO::PARAM_INT]]);

            APP::Module('Triggers')->Exec('remove_user_role', ['id' => $_POST['id'], 'result' => $out['count']]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIListRules() {
        $tmp_rules = APP::Module('Registry')->Get(['module_users_rule'], ['id', 'value'], isset($_POST['role']) ? $_POST['role'] : 0);

        $rules = [];
        $rows = [];

        foreach (array_key_exists('module_users_rule', $tmp_rules) ? (array) $tmp_rules['module_users_rule'] : [] as $rule) {
            $rule_value = json_decode($rule['value'], 1);
            if (($_POST['searchPhrase']) && (preg_match('/^' . $_POST['searchPhrase'] . '/', $rule_value[0]) === 0)) continue;

            array_push($rules, [
                'id' => $rule['id'],
                'pattern' => $rule_value[0],
                'target' => $rule_value[1],
                'token' => APP::Module('Crypt')->Encode($rule['id'])
            ]);
        }

        for ($x = ($_POST['current'] - 1) * $_POST['rowCount']; $x < $_POST['rowCount'] * $_POST['current']; $x ++) {
            if (!isset($rules[$x])) continue;
            array_push($rows, $rules[$x]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'current' => $_POST['current'],
            'rowCount' => $_POST['rowCount'],
            'rows' => $rows,
            'total' => count($rules)
        ]);
        exit;
    }

    public function APIAddRule() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        $role_id = APP::Module('Crypt')->Decode($_POST['role']);

        if (!APP::Module('DB')->Select(
            APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'registry',
            [['id', '=', $role_id, PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if (empty($_POST['target'])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }

        if ($out['status'] == 'success') {
            $out['rule_id'] = APP::Module('Registry')->Add('module_users_rule', json_encode([$_POST['uri_pattern'], $_POST['target']]), $role_id);

            APP::Module('Triggers')->Exec('add_user_rule', [
                'id' => $out['rule_id'],
                'role_id' => $role_id,
                'uri_pattern' => $_POST['uri_pattern'],
                'target' => $_POST['target']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIRemoveRule() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (!APP::Module('DB')->Select(
            APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'registry',
            [['id', '=', $_POST['id'], PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if ($out['status'] == 'success') {
            $out['count'] = APP::Module('Registry')->Delete([['id', '=', $_POST['id'], PDO::PARAM_INT]]);
            APP::Module('Triggers')->Exec('remove_user_rule', ['id' => $_POST['id'], 'result' => $out['count']]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIUpdateRule() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        $rule_id = APP::Module('Crypt')->Decode($_POST['rule']);

        if (!APP::Module('DB')->Select(
            APP::Module('Registry')->conf['connection'], ['fetchColumn', 0],
            ['COUNT(id)'], 'registry',
            [['id', '=', $rule_id, PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if (empty($_POST['target'])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }

        if ($out['status'] == 'success') {
            APP::Module('Registry')->Update(['value' => json_encode([$_POST['uri_pattern'], $_POST['target']])], [['id', '=', $rule_id, PDO::PARAM_INT]]);

            APP::Module('Triggers')->Exec('update_user_rule', [
                'id' => $rule_id,
                'rule' => $_POST['rule'],
                'uri_pattern' => $_POST['uri_pattern'],
                'target' => $_POST['target']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIUpdateOAuthClientSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_fb_id']], [['item', '=', 'module_users_oauth_client_fb_id', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_fb_key']], [['item', '=', 'module_users_oauth_client_fb_key', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_vk_id']], [['item', '=', 'module_users_oauth_client_vk_id', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_vk_key']], [['item', '=', 'module_users_oauth_client_vk_key', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_google_id']], [['item', '=', 'module_users_oauth_client_google_id', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_google_key']], [['item', '=', 'module_users_oauth_client_google_key', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_ya_id']], [['item', '=', 'module_users_oauth_client_ya_id', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_oauth_client_ya_key']], [['item', '=', 'module_users_oauth_client_ya_key', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_users_oauth_settings', [
            'oauth_client_fb_id' => $_POST['module_users_oauth_client_fb_id'],
            'oauth_client_fb_key' => $_POST['module_users_oauth_client_fb_key'],
            'oauth_client_vk_id' => $_POST['module_users_oauth_client_vk_id'],
            'oauth_client_vk_key' => $_POST['module_users_oauth_client_vk_key'],
            'oauth_client_google_id' => $_POST['module_users_oauth_client_google_id'],
            'oauth_client_google_key' => $_POST['module_users_oauth_client_google_key'],
            'oauth_client_ya_id' => $_POST['module_users_oauth_client_ya_id'],
            'oauth_client_ya_key' => $_POST['module_users_oauth_client_ya_key']
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIUpdateNotificationsSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_users_register_activation_letter']], [['item', '=', 'module_users_register_activation_letter', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_reset_password_letter']], [['item', '=', 'module_users_reset_password_letter', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_register_letter']], [['item', '=', 'module_users_register_letter', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_change_password_letter']], [['item', '=', 'module_users_change_password_letter', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_subscription_restore_letter']], [['item', '=', 'module_users_subscription_restore_letter', PDO::PARAM_STR]]);
        
        APP::Module('Triggers')->Exec('update_users_notifications_settings', [
            'register_activation_letter' => $_POST['module_users_register_activation_letter'],
            'reset_password_letter' => $_POST['module_users_reset_password_letter'],
            'register_letter' => $_POST['module_users_register_letter'],
            'change_password_letter' => $_POST['module_users_change_password_letter'],
            'subscription_restore_letter' => $_POST['module_users_subscription_restore_letter']
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIUpdateServicesSettings() {
        APP::Module('Registry')->Update(['value' => isset($_POST['module_users_login_service'])], [['item', '=', 'module_users_login_service', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => isset($_POST['module_users_register_service'])], [['item', '=', 'module_users_register_service', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => isset($_POST['module_users_reset_password_service'])], [['item', '=', 'module_users_reset_password_service', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => isset($_POST['module_users_change_password_service'])], [['item', '=', 'module_users_change_password_service', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_users_services_settings', [
            'login_service' => isset($_POST['module_users_login_service']),
            'register_service' => isset($_POST['module_users_register_service']),
            'reset_password_service' => isset($_POST['module_users_reset_password_service']),
            'change_password_service' => isset($_POST['module_users_change_password_service'])
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIUpdateAuthSettings() {
        APP::Module('Registry')->Update(['value' => isset($_POST['module_users_check_rules'])], [['item', '=', 'module_users_check_rules', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => isset($_POST['module_users_auth_token'])], [['item', '=', 'module_users_auth_token', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_users_auth_settings', [
            'check_rules' => isset($_POST['module_users_check_rules']),
            'auth_token' => isset($_POST['module_users_auth_token'])
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIUpdatePasswordsSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_users_min_pass_length']], [['item', '=', 'module_users_min_pass_length', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_gen_pass_length']], [['item', '=', 'module_users_gen_pass_length', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_users_passwords_settings', [
            'min_pass_length' => $_POST['module_users_min_pass_length'],
            'gen_pass_length' => $_POST['module_users_gen_pass_length']
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIUpdateTimeoutsSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_users_timeout_token']], [['item', '=', 'module_users_timeout_token', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_timeout_email']], [['item', '=', 'module_users_timeout_email', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_timeout_activation']], [['item', '=', 'module_users_timeout_activation', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_users_timeouts_settings', [
            'timeout_token' => $_POST['module_users_timeout_token'],
            'timeout_email' => $_POST['module_users_timeout_email'],
            'timeout_activation' => $_POST['module_users_timeout_activation']
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIUpdateOtherSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_users_db_connection']], [['item', '=', 'module_users_db_connection', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_tmp_dir']], [['item', '=', 'module_users_tmp_dir', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_users_profile_picture']], [['item', '=', 'module_users_profile_picture', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_users_other_settings', [
            'db_connection' => $_POST['module_users_db_connection'],
            'tmp_dir' => $_POST['module_users_tmp_dir'],
            'profile_picture' => $_POST['module_users_profile_picture']
        ]);

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }

    public function APIAddAbout() {
        $id = APP::Module('DB')->Insert(
            $this->settings['module_users_db_connection'], 'users_about',
            [
                'id' => 'NULL',
                'user' => [$_POST['user'], PDO::PARAM_INT],
                'item' => [$_POST['item'], PDO::PARAM_STR],
                'value' => [$_POST['value'], PDO::PARAM_STR],
                'up_date' => 'NOW()'
            ]
        );
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode($id ? [
            'result' => 'success',
            'action' => [
                'id' => $id,
                'id_hash' => APP::Module('Crypt')->Encode($id)
            ]
        ] : [
            'result' => 'error',
        ]);
    }
    
    public function APIUpdateAbout() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (!isset($_POST['about'])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if ($out['status'] == 'success') {
            APP::Module('DB')->Delete(
                $this->settings['module_users_db_connection'], 'users_about',
                [
                    ['user', '=', $this->user['id'], PDO::PARAM_INT],
                    ['item', 'IN', array_keys((array) $_POST['about'])]
                ]
            );

            foreach ((array) $_POST['about'] as $item => $value) {
                if ((!empty($value)) && (array_search($item, $this->about) !== false)) {
                    APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], ' users_about',
                        [
                            'id' => 'NULL',
                            'user' => [$this->user['id'], PDO::PARAM_INT],
                            'item' => [$item, PDO::PARAM_STR],
                            'value' => [$value, PDO::PARAM_STR],
                            'up_date' => 'CURRENT_TIMESTAMP'
                        ]
                    );
                }
            }

            APP::Module('Triggers')->Exec('update_about_user', [
                'user' => $this->user['id'],
                'about' => (array) $_POST['about']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }

    public function APIAdminUpdateAbout() {
        $out = [
            'status' => 'success',
            'errors' => [],
            'items' => []
        ];

        $user_id = APP::Module('Crypt')->Decode($_POST['user']);

        if (!APP::Module('DB')->Select($this->settings['module_users_db_connection'], ['fetchColumn', 0], ['COUNT(id)'], 'users', [['id', '=', $user_id, PDO::PARAM_INT]])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }

        if ($out['status'] == 'success') {
            APP::Module('DB')->Delete(
                $this->settings['module_users_db_connection'], 'users_about',
                [
                    ['user', '=', $user_id, PDO::PARAM_INT],
                    ['item', 'IN', array_keys($_POST['about'])]
                ]
            );

            foreach ($_POST['about'] as $item => $value) {
                if ((!empty($value)) && (array_search($item, $this->about) !== false)) {
                    $out['items'][$item] = APP::Module('DB')->Insert(
                        $this->settings['module_users_db_connection'], ' users_about',
                        [
                            'id' => 'NULL',
                            'user' => [$user_id, PDO::PARAM_INT],
                            'item' => [$item, PDO::PARAM_STR],
                            'value' => [$value, PDO::PARAM_STR],
                            'up_date' => 'CURRENT_TIMESTAMP'
                        ]
                    );
                }
            }

            APP::Module('Triggers')->Exec('update_about_user', [
                'user' => $user_id,
                'about' => $_POST['about']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');

        echo json_encode($out);
        exit;
    }


    public function LoginVK() {
        if (!(int) $this->settings['module_users_login_service']) APP::Render('users/errors', 'include', 'auth_service');

        if (isset(APP::Module('Routing')->get['code'])) {
            $vk_result = json_decode(file_get_contents('https://oauth.vk.com/access_token?' . urldecode(http_build_query(['client_id' => $this->settings['module_users_oauth_client_vk_id'], 'client_secret' => $this->settings['module_users_oauth_client_vk_key'], 'code' => APP::Module('Routing')->get['code'], 'redirect_uri' => APP::Module('Routing')->root . 'users/login/vk']))), true);

            if (isset($vk_result['user_id'])) {
                if ($user_id = APP::Module('DB')->Select(
                        $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                        ['user_id'], 'users_accounts',
                        [
                            ['service', '=', 'vk', PDO::PARAM_STR],
                            ['extra', '=', $vk_result['user_id'], PDO::PARAM_STR]
                        ]
                )) {
                    $this->user = $this->Auth($user_id, true, true);
                } else {
                    if (isset($vk_result['email'])) {
                        if ($user_id = APP::Module('DB')->Select(
                                $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                                ['id'], 'users', [['email', '=', $vk_result['email'], PDO::PARAM_STR]]
                        )) {
                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], ' users_accounts',
                                [
                                    'id' => 'NULL',
                                    'user_id' => [$user_id, PDO::PARAM_INT],
                                    'service' => '"vk"',
                                    'extra' => [$vk_result['user_id'], PDO::PARAM_STR],
                                    'up_date' => 'NOW()',
                                ]
                            );

                            $this->user = $this->Auth($user_id, true, true);
                        } else {
                            $password = $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length']);
                            $user_id = $this->Register($vk_result['email'], $password, 'user');
                            $this->user = $this->Auth($user_id, true, true);

                            APP::Module('DB')->Insert(
                                $this->settings['module_users_db_connection'], ' users_accounts',
                                [
                                    'id' => 'NULL',
                                    'user_id' => [$user_id, PDO::PARAM_INT],
                                    'service' => '"vk"',
                                    'extra' => [$vk_result['user_id'], PDO::PARAM_STR],
                                    'up_date' => 'NOW()',
                                ]
                            );

                            APP::Module('Mail')->Send($vk_result['email'], $this->settings['module_users_register_letter'], [
                                'email' => $vk_result['email'],
                                'password' => $password
                            ], true, 'register_vk_user');
                        }
                    } else {
                        APP::Render('users/errors', 'include', 'auth_vk_email');
                    }
                }
            } else {
                APP::Render('users/errors', 'include', 'auth_vk_user_id');
            }

            header('Location: ' . APP::Module('Crypt')->Decode(json_decode(APP::Module('Crypt')->SafeB64Decode(APP::Module('Routing')->get['state']), 1)['return']));
            exit;

            /*
            if (isset($token['access_token'])) {
                $params = array(
                    'uids'         => $token['user_id'],
                    'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
                    'access_token' => $token['access_token']
                );

                $userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . urldecode(http_build_query($params))), true);

                ?><pre><? print_r($userInfo); ?></pre><?
            } else {
                // ошибка
            }
             *
             */
        } else {
            APP::Render('users/errors', 'include', 'auth_vk_code');
        }
    }

    public function LoginFB() {
        if (!(int) $this->settings['module_users_login_service']) APP::Render('users/errors', 'include', 'auth_service');

        if (isset(APP::Module('Routing')->get['code'])) {
            $fb_result = null;

            parse_str(file_get_contents('https://graph.facebook.com/oauth/access_token?' . urldecode(http_build_query(['client_id' => $this->settings['module_users_oauth_client_fb_id'], 'client_secret' => $this->settings['module_users_oauth_client_fb_key'], 'code' => APP::Module('Routing')->get['code'], 'redirect_uri' => APP::Module('Routing')->root . 'users/login/fb']))), $fb_result);

            if (count($fb_result) > 0 && isset($fb_result['access_token'])) {
                $fb_user = json_decode(file_get_contents('https://graph.facebook.com/me?fields=email&' . urldecode(http_build_query(array('access_token' => $fb_result['access_token'])))), true);

                if (isset($fb_user['id'])) {
                    if ($user_id = APP::Module('DB')->Select(
                        $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                        ['user_id'], 'users_accounts',
                        [
                            ['service', '=', 'fb', PDO::PARAM_STR],
                            ['extra', '=', $fb_user['id'], PDO::PARAM_STR]
                        ]
                    )) {
                        $this->user = $this->Auth($user_id, true, true);
                    } else {
                        if (isset($fb_user['email'])) {
                            if ($user_id = APP::Module('DB')->Select(
                                $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                                ['id'], 'users', [['email', '=', $fb_user['email'], PDO::PARAM_STR]]
                            )) {
                                APP::Module('DB')->Insert(
                                    $this->settings['module_users_db_connection'], ' users_accounts',
                                    [
                                        'id' => 'NULL',
                                        'user_id' => [$user_id, PDO::PARAM_INT],
                                        'service' => '"fb"',
                                        'extra' => [$fb_user['id'], PDO::PARAM_STR],
                                        'up_date' => 'NOW()',
                                    ]
                                );

                                $this->user = $this->Auth($user_id, true, true);
                            } else {
                                $password = $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length']);
                                $user_id = $this->Register($fb_user['email'], $password, 'user');
                                $this->user = $this->Auth($user_id, true, true);

                                APP::Module('DB')->Insert(
                                    $this->settings['module_users_db_connection'], ' users_accounts',
                                    [
                                        'id' => 'NULL',
                                        'user_id' => [$user_id, PDO::PARAM_INT],
                                        'service' => '"fb"',
                                        'extra' => [$fb_user['id'], PDO::PARAM_STR],
                                        'up_date' => 'NOW()',
                                    ]
                                );

                                APP::Module('Mail')->Send($fb_user['email'], $this->settings['module_users_register_letter'], [
                                    'email' => $fb_user['email'],
                                    'password' => $password
                                ], true, 'register_fb_user');
                            }
                        } else {
                            APP::Render('users/errors', 'include', 'auth_fb_email');
                        }
                    }
                } else {
                    APP::Render('users/errors', 'include', 'auth_fb_id');
                }

                header('Location: ' . APP::Module('Crypt')->Decode(json_decode(APP::Module('Crypt')->SafeB64Decode(APP::Module('Routing')->get['state']), 1)['return']));
                exit;
            } else {
                APP::Render('users/errors', 'include', 'auth_fb_access_token');
            }
        } else {
            APP::Render('users/errors', 'include', 'auth_fb_code');
        }
    }

    public function LoginGoogle() {
        if (!(int) $this->settings['module_users_login_service']) APP::Render('users/errors', 'include', 'auth_service');

        if (isset(APP::Module('Routing')->get['code'])) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, 'https://accounts.google.com/o/oauth2/token');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query(['client_id' => $this->settings['module_users_oauth_client_google_id'], 'client_secret' => $this->settings['module_users_oauth_client_google_key'], 'code' => APP::Module('Routing')->get['code'], 'redirect_uri' => APP::Module('Routing')->root . 'users/login/google', 'grant_type' => 'authorization_code'])));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $google_result = json_decode(curl_exec($curl), true);

            curl_close($curl);

            if (isset($google_result['access_token'])) {
                $google_user = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?' . urldecode(http_build_query(['access_token' => $google_result['access_token']]))), true);

                if (isset($google_user['id'])) {
                    if ($user_id = APP::Module('DB')->Select(
                        $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                        ['user_id'], 'users_accounts',
                        [
                            ['service', '=', 'google', PDO::PARAM_STR],
                            ['extra', '=', $google_user['id'], PDO::PARAM_STR]
                        ]
                    )) {
                        $this->user = $this->Auth($user_id, true, true);
                    } else {
                        if (isset($google_user['email'])) {
                            if ($user_id = APP::Module('DB')->Select(
                                $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                                ['id'], 'users', [['email', '=', $google_user['email'], PDO::PARAM_STR]]
                            )) {
                                APP::Module('DB')->Insert(
                                    $this->settings['module_users_db_connection'], ' users_accounts',
                                    [
                                        'id' => 'NULL',
                                        'user_id' => [$user_id, PDO::PARAM_INT],
                                        'service' => '"google"',
                                        'extra' => [$google_user['id'], PDO::PARAM_STR],
                                        'up_date' => 'NOW()',
                                    ]
                                );

                                $this->user = $this->Auth($user_id, true, true);
                            } else {
                                $password = $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length']);
                                $user_id = $this->Register($google_user['email'], $password, 'user');
                                $this->user = $this->Auth($user_id, true, true);

                                APP::Module('DB')->Insert(
                                    $this->settings['module_users_db_connection'], ' users_accounts',
                                    [
                                        'id' => 'NULL',
                                        'user_id' => [$user_id, PDO::PARAM_INT],
                                        'service' => '"google"',
                                        'extra' => [$google_user['id'], PDO::PARAM_STR],
                                        'up_date' => 'NOW()',
                                    ]
                                );

                                APP::Module('Mail')->Send($google_user['email'], $this->settings['module_users_register_letter'], [
                                    'email' => $google_user['email'],
                                    'password' => $password
                                ], true, 'register_google_user');
                            }
                        } else {
                            APP::Render('users/errors', 'include', 'auth_google_email');
                        }
                    }
                } else {
                    APP::Render('users/errors', 'include', 'auth_google_id');
                }

                header('Location: ' . APP::Module('Crypt')->Decode(json_decode(APP::Module('Crypt')->SafeB64Decode(APP::Module('Routing')->get['state']), 1)['return']));
                exit;
            } else {
                APP::Render('users/errors', 'include', 'auth_google_access_token');
            }
        } else {
            APP::Render('users/errors', 'include', 'auth_google_code');
        }
    }

    public function LoginYA() {
        if (!(int) $this->settings['module_users_login_service']) APP::Render('users/errors', 'include', 'auth_service');

        if (isset(APP::Module('Routing')->get['code'])) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, 'https://oauth.yandex.ru/token');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query(['client_id' => $this->settings['module_users_oauth_client_ya_id'], 'client_secret' => $this->settings['module_users_oauth_client_ya_key'], 'code' => APP::Module('Routing')->get['code'], 'redirect_uri' => APP::Module('Routing')->root . 'users/login/ya', 'grant_type' => 'authorization_code'])));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $ya_result = json_decode(curl_exec($curl), true);

            curl_close($curl);

            if (isset($ya_result['access_token'])) {
                $ya_user = json_decode(file_get_contents('https://login.yandex.ru/info?' . urldecode(http_build_query(['oauth_token' => $ya_result['access_token'], 'format' => 'json']))), true);

                if (isset($ya_user['id'])) {
                    if ($user_id = APP::Module('DB')->Select(
                        $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                        ['user_id'], 'users_accounts',
                        [
                            ['service', '=', 'ya', PDO::PARAM_STR],
                            ['extra', '=', $ya_user['id'], PDO::PARAM_STR]
                        ]
                    )) {
                        $this->user = $this->Auth($user_id, true, true);
                    } else {
                        if (isset($ya_user['default_email'])) {
                            if ($user_id = APP::Module('DB')->Select(
                                $this->settings['module_users_db_connection'], ['fetchColumn', 0],
                                ['id'], 'users', [['email', '=', $ya_user['default_email'], PDO::PARAM_STR]]
                            )) {
                                APP::Module('DB')->Insert(
                                    $this->settings['module_users_db_connection'], ' users_accounts',
                                    [
                                        'id' => 'NULL',
                                        'user_id' => [$user_id, PDO::PARAM_INT],
                                        'service' => '"ya"',
                                        'extra' => [$ya_user['id'], PDO::PARAM_STR],
                                        'up_date' => 'NOW()',
                                    ]
                                );

                                $this->user = $this->Auth($user_id, true, true);
                            } else {
                                $password = $this->GeneratePassword((int) $this->settings['module_users_gen_pass_length']);
                                $user_id = $this->Register($ya_user['default_email'], $password, 'user');
                                $this->user = $this->Auth($user_id, true, true);

                                APP::Module('DB')->Insert(
                                    $this->settings['module_users_db_connection'], ' users_accounts',
                                    [
                                        'id' => 'NULL',
                                        'user_id' => [$user_id, PDO::PARAM_INT],
                                        'service' => '"ya"',
                                        'extra' => [$ya_user['id'], PDO::PARAM_STR],
                                        'up_date' => 'NOW()',
                                    ]
                                );

                                APP::Module('Mail')->Send($ya_user['default_email'], $this->settings['module_users_register_letter'], [
                                    'email' => $ya_user['default_email'],
                                    'password' => $password
                                ], true, 'register_yandex_user');
                            }
                        } else {
                            APP::Render('users/errors', 'include', 'auth_ya_email');
                        }
                    }
                } else {
                    APP::Render('users/errors', 'include', 'auth_ya_id');
                }

                header('Location: ' . APP::Module('Crypt')->Decode(json_decode(APP::Module('Crypt')->SafeB64Decode(APP::Module('Routing')->get['state']), 1)['return']));
                exit;
            } else {
                APP::Render('users/errors', 'include', 'auth_ya_access_token');
            }
        } else {
            APP::Render('users/errors', 'include', 'auth_ya_code');
        }
    }
    
    
    public function RenderUnsubscribeShortcode($id, $data) {
        $unsubscribe_link = APP::Module('Routing')->root . 'users/unsubscribe/[letter_hash]';

        $data['letter']['html'] = str_replace('[unsubscribe-link]', $unsubscribe_link, $data['letter']['html']);
        $data['letter']['plaintext'] = str_replace('[unsubscribe-link]', $unsubscribe_link, $data['letter']['plaintext']);
        
        return $data;
    }
    
    public function Unsubscribe() {
        
        $mail_log = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['mail_log_hash']);
        
        if (!APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['COUNT(id)'], 'mail_log',
            [['id', '=', $mail_log, PDO::PARAM_INT]]
        )) {
            APP::Render(
                'users/unsubscribe', 'include', 
                [
                    'error' => true,
                ]
            );
            exit;
        }

        $user_id = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['user'], 'mail_log',
            [['id', '=', $mail_log, PDO::PARAM_INT]]
        );
        
        APP::Render(
            'users/unsubscribe', 
            'include', 
            [
                'error' => false,
                'active' => APP::Module('DB')->Select(
                    $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['COUNT(id)'], 'users_about',
                    [
                        ['user', '=', $user_id, PDO::PARAM_INT],
                        ['item', '=', 'state', PDO::PARAM_STR],
                        ['value', '=', 'active', PDO::PARAM_STR]
                    ]
                )
            ]
        );
    }
    
    public function APIUnsubscribe() {
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        $mail_log = APP::Module('Crypt')->Decode($_POST['mail_log']);
        
        if (!APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['COUNT(id)'], 'mail_log',
            [['id', '=', $mail_log, PDO::PARAM_INT]]
        )) {
            echo json_encode([
                'status' => 'error',
                'errors' => [1]
            ]);
            exit;
        }

        $mail = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetch', PDO::FETCH_ASSOC], 
            ['user', 'letter'], 'mail_log',
            [['id', '=', $mail_log, PDO::PARAM_INT]]
        );

        if (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['value'], 'users_about',
            [
                ['user', '=', $mail['user'], PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        ) == 'unsubscribe') {
            echo json_encode([
                'status' => 'success',
                'errors' => []
            ]);
            exit;
        }
        
        APP::Module('DB')->Insert(
            APP::Module('Mail')->settings['module_mail_db_connection'], 'mail_events',
            [
                'id' => 'NULL',
                'log' => [$mail_log, PDO::PARAM_INT],
                'user' => [$mail['user'], PDO::PARAM_INT],
                'letter' => [$mail['letter'], PDO::PARAM_INT],
                'event' => ['unsubscribe', PDO::PARAM_STR],
                'details' => 'NULL',
                'token' => [$mail_log, PDO::PARAM_STR],
                'cr_date' => 'NOW()'
            ]
        );
        
        APP::Module('DB')->Insert(
            $this->settings['module_users_db_connection'], 'users_tags',
            [
                'id' => 'NULL',
                'user' => [$mail['user'], PDO::PARAM_INT],
                'item' => ['unsubscribe', PDO::PARAM_STR],
                'value' => [json_encode([
                    'item' => 'mail',
                    'id' => $mail_log
                ]), PDO::PARAM_STR],
                'cr_date' => 'NOW()'
            ]
        );
        
        APP::Module('DB')->Update(
            $this->settings['module_users_db_connection'], 'users_about', 
            [
                'value' => 'unsubscribe'
            ], 
            [
                ['user', '=', $mail['user'], PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        );

        APP::Module('Mail')->Send(
            APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                ['email'], 'users',
                [['id', '=', $mail['user'], PDO::PARAM_INT]]
            ),
            $this->settings['module_users_subscription_restore_letter'],
            [], 
            true, 
            'unsubscribe_user'
        );
        
        APP::Module('Triggers')->Exec('user_unsubscribe', [
            'user' => $mail['user'],
            'label' => 'unsubscribe'
        ]);

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }
    
    public function APIPause() {
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        $mail_log = APP::Module('Crypt')->Decode($_POST['mail_log']);
        
        if (!APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['COUNT(id)'], 'mail_log',
            [['id', '=', $mail_log, PDO::PARAM_INT]]
        )) {
            echo json_encode([
                'status' => 'error',
                'errors' => [1]
            ]);
            exit;
        }

        $mail = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], ['fetch', PDO::FETCH_ASSOC], 
            ['user', 'letter'], 'mail_log',
            [['id', '=', $mail_log, PDO::PARAM_INT]]
        );

        if (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['value'], 'users_about',
            [
                ['user', '=', $mail['user'], PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        ) == 'pause') {
            echo json_encode([
                'status' => 'success',
                'errors' => []
            ]);
            exit;
        }
        
        APP::Module('TaskManager')->Add(
            'Users', 'ActivateUserTask', 
            date('Y-m-d H:i:s', strtotime($_POST['timeout'])), 
            json_encode([$mail['user']]), 
            'user_'. $mail['user'] . '_activate_task', 
            'wait'
        );
        
        APP::Module('DB')->Insert(
            APP::Module('Mail')->settings['module_mail_db_connection'], 'mail_events',
            [
                'id' => 'NULL',
                'log' => [$mail_log, PDO::PARAM_INT],
                'user' => [$mail['user'], PDO::PARAM_INT],
                'letter' => [$mail['letter'], PDO::PARAM_INT],
                'event' => ['pause', PDO::PARAM_STR],
                'details' => 'NULL',
                'token' => [$mail_log, PDO::PARAM_STR],
                'cr_date' => 'NOW()'
            ]
        );

        APP::Module('DB')->Insert(
            $this->settings['module_users_db_connection'], 'users_tags',
            [
                'id' => 'NULL',
                'user' => [$mail['user'], PDO::PARAM_INT],
                'item' => ['pause', PDO::PARAM_STR],
                'value' => [json_encode([
                    'item' => 'mail',
                    'id' => $mail_log,
                    'timeout' => $_POST['timeout']
                ]), PDO::PARAM_STR],
                'cr_date' => 'NOW()'
            ]
        );
        
        APP::Module('DB')->Update(
            $this->settings['module_users_db_connection'], 'users_about', 
            [
                'value' => 'pause'
            ], 
            [
                ['user', '=', $mail['user'], PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        );

        APP::Module('Mail')->Send(
            APP::Module('DB')->Select(
                $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                ['email'], 'users',
                [['id', '=', $mail['user'], PDO::PARAM_INT]]
            ),
            $this->settings['module_users_subscription_restore_letter'],
            [], 
            true, 
            'pause_user'
        );

        APP::Module('Triggers')->Exec('user_pause', [
            'user' => $mail['user'],
            'label' => 'pause'
        ]);

        echo json_encode([
            'status' => 'success',
            'errors' => []
        ]);
        exit;
    }
    
    
    public function RenderRestoreUserShortcode($id, $data) {
        $user_email_hash = isset($data['params']['recepient']) ? APP::Module('Crypt')->Encode($data['params']['recepient']) : '[user_email]';
        $unsubscribe_link = APP::Module('Routing')->root . 'users/restore/' . $user_email_hash;

        $data['letter']['html'] = str_replace('[restore-user-link]', $unsubscribe_link, $data['letter']['html']);
        $data['letter']['plaintext'] = str_replace('[restore-user-link]', $unsubscribe_link, $data['letter']['plaintext']);
        
        return $data;
    }
    
    public function Restore() {
        $user_email = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['user_email_hash']);
        
        if (!APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['COUNT(id)'], 'users',
            [['email', '=', $user_email, PDO::PARAM_STR]]
        )) {
            APP::Render('users/restore', 'include', false);
            exit;
        }

        $user_id = APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['id'], 'users',
            [['email', '=', $user_email, PDO::PARAM_STR]]
        );

        if (APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['value'], 'users_about',
            [
                ['user', '=', $user_id, PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        ) == 'active') {
            APP::Render('users/restore', 'include', true);
            exit;
        }
        
        APP::Module('DB')->Insert(
            $this->settings['module_users_db_connection'], 'users_tags',
            [
                'id' => 'NULL',
                'user' => [$user_id, PDO::PARAM_INT],
                'item' => ['restore', PDO::PARAM_STR],
                'value' => 'NULL',
                'cr_date' => 'NOW()'
            ]
        );
        
        APP::Module('DB')->Update(
            $this->settings['module_users_db_connection'], 'users_about', 
            [
                'value' => 'active'
            ], 
            [
                ['user', '=', $user_id, PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        );

        APP::Module('Triggers')->Exec('user_restore', [
            'user' => $user_id
        ]);
        
        APP::Render('users/restore', 'include', true);
    }
    
    
    public function ActivateUserTask($user_id) {
        if (!APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['COUNT(id)'], 'users_about',
            [
                ['user', '=', $user_id, PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR],
                ['value', '=', 'active', PDO::PARAM_STR]
            ]
        )) {
            APP::Module('DB')->Update(
                $this->settings['module_users_db_connection'], 'users_about', 
                [
                    'value' => 'active'
                ], 
                [
                    ['user', '=', $user_id, PDO::PARAM_INT],
                    ['item', '=', 'state', PDO::PARAM_STR]
                ]
            );
            
            APP::Module('DB')->Insert(
                $this->settings['module_users_db_connection'], 'users_tags',
                [
                    'id' => 'NULL',
                    'user' => [$user_id, PDO::PARAM_INT],
                    'item' => ['change_state', PDO::PARAM_STR],
                    'value' => ['active', PDO::PARAM_STR],
                    'cr_date' => 'NOW()'
                ]
            );
        }
    }

    public function APIAdminAboutItemList(){
        $data = APP::Module('DB')->Select(
            $this->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['DISTINCT(item)'], 'users_about'
        );
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

class UsersSearch {

    public function id($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'users',
            [['id', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function email($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'users',
            [['email', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function role($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'users',
            [['role', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function state($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['item', '=', 'state', PDO::PARAM_STR],
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR]
            ]
        );
    }
    
    public function source($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['item', '=', 'source', PDO::PARAM_STR],
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR]
            ]
        );
    }
    
    public function about($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['item', '=', $settings['item'], PDO::PARAM_STR],
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR]
            ]
        );
    }
    
    public function firstname($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR],
                ['item', '=', 'firstname', PDO::PARAM_STR]
            ]
        );
    }
    
    public function lastname($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR],
                ['item', '=', 'lastname', PDO::PARAM_STR]
            ]
        );
    }
    
    public function tel($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR],
                ['item', 'IN', ['tel', 'mobile_phone'], PDO::PARAM_STR]
            ]
        );
    }
    
    public function city($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR],
                ['item', '=', 'city_name_ru', PDO::PARAM_STR]
            ]
        );
    }
    
    public function country($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['value', $settings['logic'], $settings['value'], PDO::PARAM_STR],
                ['item', '=', 'country_name_ru', PDO::PARAM_STR]
            ]
        );
    }
    
    public function reg_date($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'users',
            [
                ['UNIX_TIMESTAMP(reg_date)', 'BETWEEN', $settings['date_from'] . ' AND ' . $settings['date_to'], PDO::PARAM_STR]
            ]
        );
    }
    
    public function utm($settings) {
        $where = [
            $settings['value'] ? ['value', '=', $settings['value'], PDO::PARAM_STR] : ['value', 'IS', 'NULL'],
            ['item', '=', $settings['item'], PDO::PARAM_STR]
        ];
        
        if ((int)$settings['num']) {
            $where[] = ['num', '=', $settings['num'], PDO::PARAM_INT];
        }
        
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_utm', $where
        );
    }
    
    public function social_id($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user_id'], 'users_accounts',
            [
                ['service', '=', $settings['service'], PDO::PARAM_STR],
                ['extra', $settings['logic'], $settings['value'], PDO::PARAM_STR]
            ]
        );
    }

    public function tags($settings) {
        switch ($settings['logic']) {
            case 'exist':
                return APP::Module('DB')->Select(
                    APP::Module('Users')->settings['module_users_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['user'], 'users_tags',
                    [
                        ['item', '=', $settings['value'], PDO::PARAM_STR],
                        ['cr_date', 'BETWEEN', '"' . $settings['date_from'] . ' 00:00:00" AND "' . $settings['date_to'] . ' 23:59:59"', PDO::PARAM_STR]
                    ]
                );
                break;
            case 'not_exist':

                $u_id = APP::Module('DB')->Select(
                    APP::Module('Users')->settings['module_users_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['user'], 'users_tags',
                    [
                        ['item', '=', $settings['value'], PDO::PARAM_STR],
                        ['cr_date', 'BETWEEN', '"' . $settings['date_from'] . ' 00:00:00" AND "' . $settings['date_to'] . ' 23:59:59"', PDO::PARAM_STR]
                    ]
                );
                
                return APP::Module('DB')->Select(
                    APP::Module('Users')->settings['module_users_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['user'], 'users_tags',
                    [
                        ['user', 'NOT IN', $u_id, PDO::PARAM_INT],
                        ['cr_date', 'BETWEEN', '"' . $settings['date_from'] . ' 00:00:00" AND "' . $settings['date_to'] . ' 23:59:59"', PDO::PARAM_STR]
                    ]
                );
                break;
        }
    }
    
    public function group($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Groups')->settings['module_groups_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user_id'], 'groups_users',
            [
                ['group_id', '=', $settings['value'], PDO::PARAM_STR]
            ]
        );
    }
    
    public function tunnels($settings) {
        switch ($settings['logic']) {
            case 'exist':
                return APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['tunnels_users.user_id'], 'tunnels_users',
                    [['tunnel_id', '=', $settings['value'], PDO::PARAM_INT]]
                );
                break;

            case 'not_exist':
                $u_id = APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['user_id'], 'tunnels_users',
                    [['tunnels_users.tunnel_id', '=', $settings['value'], PDO::PARAM_INT]]
                );
                
                return APP::Module('DB')->Select(
                    APP::Module('Users')->settings['module_users_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['users.id'], 'users',
                    [['users.id', 'NOT IN', $u_id, PDO::PARAM_INT]]
                );
                break;

            case 'active' : 
                return APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['tunnels_users.user_id'], 'tunnels_users',
                    [
                        ['tunnel_id', '=', $settings['value'], PDO::PARAM_INT],
                        ['state', '=', 'active', PDO::PARAM_STR]
                    ]
                );
                break;

            case 'complete':
                return APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['tunnels_users.user_id'], 'tunnels_users',
                    [
                        ['tunnel_id', '=', $settings['value'], PDO::PARAM_INT],
                        ['state', '=', 'complete', PDO::PARAM_STR]
                    ]
                );
                break;

            case 'pause':
                return APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN], 
                    ['tunnels_users.user_id'], 'tunnels_users',
                    [
                        ['tunnel_id', '=', $settings['value'], PDO::PARAM_INT],
                        ['state', '=', 'pause', PDO::PARAM_STR]
                    ]
                );
                break;
        }
    }
    
    public function tunnels_type($settings) {       
        $users = APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['tunnels_users.user_id'], 'tunnels_users',
            [
                ['tunnels_users.state', '=', 'active', PDO::PARAM_STR],
                ['tunnels.type', '=', $settings['value'], PDO::PARAM_STR]
            ],
            [
                'join/tunnels' => [
                    ['tunnels.id', '=', 'tunnels_users.tunnel_id']
                ]
            ]
        );
        
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['DISTINCT user'], 'users_about',
            [
                ['item', '=', 'state', PDO::PARAM_STR],
                ['value', '=', 'active', PDO::PARAM_STR],
                ['user', $settings['logic'], $users, PDO::PARAM_INT]
            ]
        );
    }
    
    public function tunnels_tags($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['DISTINCT tunnels_users.user_id'], 'tunnels_tags',
            [
                ['tunnels_tags.token', '=', $settings['token'], PDO::PARAM_STR],
                ['tunnels_tags.label_id', '=', $settings['label'], PDO::PARAM_STR]
            ],
            [
                'join/tunnels_users' => [
                    ['tunnels_users.id', '=', 'tunnels_tags.user_tunnel_id']
                ]
            ],
            false,
            false,
            ['tunnels_tags.cr_date', 'DESC']
        );
    }
    
    public function tunnels_queue($settings) {
        $users_in_queue = APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['user_id'], 'tunnels_queue'
        );
        
        return APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user'], 'users_about',
            [
                ['item', '=', 'state', PDO::PARAM_STR],
                ['value', '=', 'active', PDO::PARAM_STR],
                ['user', $settings['logic'], $users_in_queue, PDO::PARAM_INT]
            ]
        );
    }
    
    public function tunnels_object($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['user_id'], 'tunnels_users',
            [
                ['tunnel_id', '=', $settings['value'], PDO::PARAM_INT],
                ['object', '=', $settings['object'], PDO::PARAM_INT]
            ]
        );
    }
    
    public function tunnels_label($settings) {
        $settings['label_data'] = isset($settings['label_data']) ? $settings['label_data'] : '';

        if(isset($settings['from']) || isset($settings['to'])){
            if(!isset($settings['from'])){
                $date_from = APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN],
                    ['UNIX_TIMESTAMP(cr_date)'], 
                    'tunnels_tags',
                    false,
                    false,
                    false,
                    false,
                    ['cr_date', 'ASC']
                );
            }

            $date_range = [
                'from' => (isset($settings['from']) ? $settings['from'] : date('Y-m-d', $date_from)). ' 00:00:00',
                'to' => (isset($settings['to']) ? $settings['to'] : date('Y-m-d', time())) . ' 23:59:59'
            ];
        }

        switch ($settings['mode']) {
            case 'exist':
                $timeout = 0;
                $cr_date = 0;
                $where = false;
                $join = false;

                if (isset($settings['timeout'])) {
                    switch ($settings['timeout']['mode']) {
                        case 'min': $timeout = (int) $settings['timeout']['value'] * 60; break;
                        case 'hours': $timeout = (int) $settings['timeout']['value'] * 3600; break;
                        case 'days': $timeout = (int) $settings['timeout']['value'] * 86400; break;
                        default: $timeout = (int) $settings['timeout']['value'];
                    }
                }

                if (isset($settings['cr_date_mode'])) {
                    switch ($settings['cr_date_mode']) {
                        case 'min': $cr_date = (int) $settings['cr_date_value'] * 60; break;
                        case 'hours': $cr_date = (int) $settings['cr_date_value'] * 3600; break;
                        case 'days': $cr_date = (int) $settings['cr_date_value'] * 86400; break;
                        default: $cr_date = (int) $settings['cr_date_value'];
                    }
                }

                if(isset($settings['label_id'])){
                    $where[] = ['label_id', '=', $settings['label_id'], PDO::PARAM_STR];
                }

                if($timeout){
                    $where[] = ['UNIX_TIMESTAMP(cr_date)', '<=', (time() - $timeout), PDO::PARAM_STR];
                }

                if ($cr_date) {
                    $where[] = ['cr_date', 'BETWEEN', '"' . date('Y-m-d H:i:s', (time() - $cr_date)) . '" AND "' . date('Y-m-d H:i:s',time()) . '"', PDO::PARAM_STR];
                }

                if (isset($date_range)) {
                    $where[] = ['cr_date', 'BETWEEN', '"' . $date_range['from'] . '" AND "' . $date_range['to'] . '"', PDO::PARAM_STR];
                }
                
                if (isset($settings['token'])) {
                    $where[] = ['token', '=', $settings['token'], PDO::PARAM_STR];
                }


                $join['join/tunnels_users'][] = ['tunnels_users.id', '=', 'tunnels_tags.user_tunnel_id'];

                if (isset($settings['tunnel_id'])) {
                    $join['join/tunnels_users'][] = ['tunnels_users.tunnel_id', '=', $settings['tunnel_id']];
                }

                return  APP::Module('DB')->Select(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 
                    ['fetchAll', PDO::FETCH_COLUMN],
                    ['DISTINCT tunnels_users.user_id'], 
                    'tunnels_tags',
                    $where,
                    $join,
                    false,
                    false,
                    ['cr_date', 'DESC']
                );
                break;
            case 'not_exist': 
                return [];
                break;
        }
    }
    
    public function mail_count($settings) {
        return APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user'], 'mail_log',
            false,
            false,
            ['user'],
            [['COUNT(id)', $settings['logic'], $settings['value']]]
        );
    }
    
    public function mail_events($settings) {  
        $where = [
            ['mail_events.event', $settings['logic'], $settings['value'], PDO::PARAM_STR],
            ['mail_log.state', '=', 'success', PDO::PARAM_STR]
        ];
        
        if (isset($settings['letter']) && count($settings['letter'])) {
            $where[] = ['mail_log.letter', 'IN', $settings['letter'], PDO::PARAM_INT];
        }

        if ((isset($settings['date_from']) && $settings['date_from']) && (isset($settings['date_to']) && $settings['date_to'])) {
            $where[] = ['UNIX_TIMESTAMP(mail_log.cr_date)', 'BETWEEN', $settings['date_from'] . ' AND ' . $settings['date_to'], PDO::PARAM_STR];
        }

        return APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['mail_log.user'], 'mail_log',
            $where,
            [
                'join/mail_events' => [
                    ['mail_events.log', '=', 'mail_log.id']
                ]
            ],
            ['mail_log.user']
        );
    }
    
    public function mail_user_inactive($settings) {
        $active_uid = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['mail_events.user'], 'mail_events',
            [
                ['mail_events.event','IN',['open','click'],PDO::PARAM_STR],
                ['mail_events.cr_date','BETWEEN','"'.$settings['date_from'].' 00:00:00" AND "'.$settings['date_to'].' 23:59:59"',PDO::PARAM_STR]
            ],false,['mail_events.user']
        );
        
        $target_uid = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['mail_events.user'], 'mail_events',
            [
                ['mail_events.event','=','delivered',PDO::PARAM_STR],
                ['mail_events.cr_date','BETWEEN','"'.$settings['date_from'].' 00:00:00" AND "'.$settings['date_to'].' 23:59:59"',PDO::PARAM_STR]
            ],false,['mail_events.user'],[['COUNT(mail_events.id)', '>=', $settings['count']]]
        );

        return array_diff($target_uid, $active_uid);
    }
    
    public function mail_log($settings){
        $where = [];
        
        if(isset($settings['result']) && $settings['result']){
            $where[] = ['result', 'LIKE', '%' . $settings['result'] . '%',PDO::PARAM_STR];
        }

        if(isset($settings['state']) && $settings['state']){
            $where[] = ['state', '=', $settings['state'],PDO::PARAM_STR];
        }

        if(isset($settings['letter']) && $settings['letter']){
            $where[] = ['letter', '=', $settings['letter'],PDO::PARAM_INT];
        }

        if(isset($settings['date_from']) && $settings['date_from']){
            $where[] = ['cr_date', 'BETWEEN', '"'.$settings['date_from'].' 00:00:00" AND "'.$settings['date_to'].' 23:59:59"',PDO::PARAM_STR];
        }
        
        return APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user'], 'mail_log',
            $where
        );
    }
    
    public function mail_open_pct($settings) {

        if (($settings['from'] == 0) && ($settings['to'] == 100))  return []; 
        
        return APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user'], 'mail_open_pct',
            [['pct', 'BETWEEN', '"'.$settings['from'] . '" AND "' . $settings['to'].'"', PDO::PARAM_INT]]
        );
    }
    
    public function mail_open_pct30($settings) {

        if (($settings['from'] == 0) && ($settings['to'] == 100))  return []; 
        
        return APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user'], 'mail_open_pct30',
            [['pct', 'BETWEEN', '"'.$settings['from'] . '" AND "' . $settings['to'].'"', PDO::PARAM_INT]]
        );
    }
    
    public function mail_open_time($settings) {

        return APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'],
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user'],'mail_events',
            [['HOUR(cr_date)', '=', $settings['value'], PDO::PARAM_INT],['event', '=', 'open', PDO::PARAM_STR]]
        );
    }
    
    public function product_buy($settings) {        
        $invoices = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['invoice'], 'billing_invoices_products',
            [['product', 'IN', $settings['product'], PDO::PARAM_INT]]
        );
        
        return APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user_id'], 'billing_invoices',
            [['id', 'IN', $invoices, PDO::PARAM_INT],['state', '=', 'success', PDO::PARAM_STR]]
        );
    }
    
    public function user_product_availability($settings) {
        
        $select_products = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['invoice'], 'billing_products'
        );

        return false;
    }
    
    public function product_order($settings) {
        $invoices = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['invoice'], 'billing_invoices_products',
            [['product', 'IN', $settings['product'], PDO::PARAM_INT]]
        );
        
        return APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user_id'], 'billing_invoices',
            [['id', 'IN', $invoices, PDO::PARAM_INT]]
        );
    }
    
    
    public function product_order_sum($settings) {
        
        $invoices = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN],
            ['user_id'], 'billing_invoices',
            [['state', '=', 'success', PDO::PARAM_STR]],false,['user_id'],[['sum(amount)',$settings['mode'],$settings['sum']]]
        );
        
        if($settings['mode'] == '=' && !$settings['sum']){
            $succes_order = APP::Module('DB')->Select(
                APP::Module('Billing')->settings['module_billing_db_connection'], 
                ['fetchAll', PDO::FETCH_COLUMN],
                ['user_id'], 'billing_invoices',
                [['state', '=', 'success', PDO::PARAM_STR]],false,['user_id']
            );
            
            $users = APP::Module('DB')->Select(
                APP::Module('Users')->settings['module_users_db_connection'], 
                ['fetchAll', PDO::FETCH_COLUMN],
                ['id'], 'users',
                [['id', 'NOT IN', $succes_order, PDO::PARAM_INT]]
            );
            
            return array_merge($users, $invoices);
        }
       
        return $invoices;
    }
    
    public function rfm_billing($settings){
        $users = Array();

        $dates_from = strtotime($settings['dates_from']);
        $dates_to = strtotime($settings['dates_to']);


        $conf = Array(
            'dates' => Array(
                'date' => Array(
                    $dates_from,
                    $dates_to
                )
            ),
            'units' => Array(
                'period' => Array(
                    $settings['units_from'],
                    $settings['units_to']
                )
            )
        );

        $orders = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            ['users.email','users.id','UNIX_TIMESTAMP(billing_invoices.cr_date) as cr_date'], 
            'billing_invoices',
            [
                ['billing_invoices.state', '=', 'success', PDO::PARAM_STR],
                ['billing_invoices.amount', '!=', '0', PDO::PARAM_INT],
            ],
            [
                'join/users' => [
                    ['users.id', '=', 'billing_invoices.user_id']
                ]
            ]
        );

        $clients = [];
        
        foreach ($orders as $order) {
            $clients[$order['id']][] = $order['cr_date'];
        }

        $raw_date = [];
        
        foreach ($clients as $client => $orders) {
            foreach ($conf['dates'] as $group_id => $group_range) {
                $max_orders = max($orders);
                if (($group_range[0] <= $max_orders) && ($group_range[1] >= $max_orders)){
                    $raw_date[$group_id][$client] = $orders;
                }
            }
        }

        $out = [];   
        foreach ($raw_date as $date_group_id => $clients) {
            foreach ($clients as $client_id => $orders) {
                foreach ($conf['units'] as $unit_group_id => $unit_group_range) {
                    $count_orders = count($orders);
                    if (($unit_group_range[0] <= $count_orders) && ($unit_group_range[1] >= $count_orders)){
                        $users[] = $client_id;
                    }
                }
            }
        }
        return $users;    
    }
    
    public function rfm_mail($settings){
        $users = [];

        $dates_from = strtotime($settings['dates_from']);
        $dates_to = strtotime($settings['dates_to']);

        $conf = Array(
            'dates' => Array(
                'date' => Array(
                    $dates_from,
                    $dates_to
                )
            ),
            'units' => Array(
                'period' => Array(
                    $settings['units_from'],
                    $settings['units_to']
                )
            )
        );

        $mail_users = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'],
            ['fetchAll',PDO::FETCH_ASSOC], ['mail_events.user','UNIX_TIMESTAMP(mail_events.cr_date) as cr_date'],
            'mail_events',
            [
                ['mail_log.state', '=', 'success', PDO::PARAM_STR],
                ['mail_events.event', '=', $settings['event'], PDO::PARAM_STR]
            ],
            [
                'join/mail_log'=>[
                    ['mail_log.id', '=', 'mail_events.log']
                ]
            ]
        );
        

        $clients = [];
        
        foreach ($mail_users as $user) {
            $clients[$user['user']][] = $user['cr_date'];
        }
        
        $raw_date = [];
        
        foreach ($clients as $client => $cr_date) {
            foreach ($conf['dates'] as $group_id => $group_range) {
                $max_date = max($cr_date);
                if (($group_range[0] <= $max_date) && ($group_range[1] >= $max_date)){
                    $raw_date[$group_id][$client] = $cr_date;
                }
            }
        }

        $out = [];   
        foreach ($raw_date as $date_group_id => $clients) {
            foreach ($clients as $client_id => $orders) {
                foreach ($conf['units'] as $unit_group_id => $unit_group_range) {
                    $count_orders = count($orders);
                    if (($unit_group_range[0] <= $count_orders) && ($unit_group_range[1] >= $count_orders)){
                        $users[] = $client_id;
                    }
                }
            }
        }
        return $users;    
    }
}

class UsersActions {

    public function remove($id, $settings) {
        return APP::Module('DB')->Delete(APP::Module('Users')->settings['module_users_db_connection'], 'users', [['id', 'IN', $id]]);
    }
    
    public function tunnel_subscribe($id, $settings){
        foreach (APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['email'], 'users',
            [['id', 'IN', $id, PDO::PARAM_INT]]
        ) as $email) {
            $result = APP::Module('Tunnels')->Subscribe([
                'email'             => $email,
                'tunnel'            => $settings['tunnel'],
                'activation'        => $settings['activation'],
                'source'            => isset($settings['source']) && $settings['source'] ? substr($settings['source'], 0, 100) : 'APISubscribe',
                'roles_tunnel'      => isset($settings['roles_tunnel']) ? $settings['roles_tunnel'] : false,
                'welcome'           => isset($settings['welcome']) && $settings['welcome'][0] ? $settings['welcome'] : false,
                'queue_timeout'     => isset($settings['queue_timeout']) ? $settings['queue_timeout'] : APP::Module('Tunnels')->settings['module_tunnels_default_queue_timeout'],
                'complete_tunnels'  => isset($settings['complete_tunnels']) && count($settings['complete_tunnels']) ? $settings['complete_tunnels'] : false,
                'pause_tunnels'     => isset($settings['pause_tunnels']) && count($settings['pause_tunnels']) ? $settings['pause_tunnels'] : false,
                'input_data'        => isset($settings['input_data']) ? $settings['input_data'] : [],
                'about_user'        => isset($settings['about_user']) ? $settings['about_user'] : [],
                'auto_save_about'   => isset($settings['auto_save_about']) ? $settings['auto_save_about'] : false,
                'save_utm'          => isset($settings['save_utm']) ? $settings['save_utm'] : false
            ]);
            
            if (isset($result['status']) && $result['status'] == 'error') {
                $out['status'] = 'error';
                $out['code'][] = $result['code'];
            } else {
                $out = $result;
            }
        }
        
        return $out;
    }
    
    public function tunnel_pause($id, $settings){
        $out['status'] = 'success';
        
        foreach(APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'tunnels_users',
            [
                ['tunnel_id', '=', $settings['tunnel_id'], PDO::PARAM_INT],
                ['user_id', 'IN', $id, PDO::PARAM_INT],
                ['state', '=', 'active', PDO::PARAM_STR]
            ]
        ) as $id){
            if (APP::Module('DB')->Insert(
                APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 'tunnels_tags',
                [
                    'id' => 'NULL',
                    'user_tunnel_id' => [$id, PDO::PARAM_INT],
                    'label_id' => ['pause', PDO::PARAM_STR],
                    'token' => '""',
                    'info' => '""',
                    'cr_date' => 'NOW()'
                ]
            )){
                APP::Module('DB')->Update(APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 'tunnels_users', [
                    'state' => 'pause'
                ], [
                    ['id', '=', $id, PDO::PARAM_INT]
                ]);
            }
        }
        
        return $out;
    }
    
    public function tunnel_complete($id, $settings) {
        $out['status'] = 'success';
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'tunnels_users', 
            [
                ['tunnel_id', '=', $settings['tunnel_id'], PDO::PARAM_INT],
                ['user_id', 'IN', $id, PDO::PARAM_INT],
                ['state', '=', 'active', PDO::PARAM_STR]
            ]
        ) as $id) {
            APP::Module('DB')->Insert(
                APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 'tunnels_tags',
                [
                    'id' => 'NULL',
                    'user_tunnel_id' => [$id, PDO::PARAM_INT],
                    'label_id' => ['complete', PDO::PARAM_STR],
                    'token' => '""',
                    'info' => '""',
                    'cr_date' => 'NOW()'
                ]
            );
            
            APP::Module('DB')->Update(APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 'tunnels_users', [
                'state' => 'complete',
                'resume_date' => '0000-00-00 00:00:00',
                'object' => '',
                'input_data' => ''
            ], [
                ['id', '=', $id, PDO::PARAM_INT]
            ]);
        }
        
        return $out;
    }
    
    public function tunnel_manually_complete($id, $settings){
        $out['status'] = 'success';
        
        foreach ($id as $user_id) {
            if (!APP::Module('DB')->Select(
                APP::Module('Tunnels')->settings['module_tunnels_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
                ['id'], 'tunnels_users', 
                [
                    ['tunnel_id', '=', $settings['tunnel_id'], PDO::PARAM_INT],
                    ['user_id', '=', $user_id, PDO::PARAM_INT]
                ]
            )){
                if ($user_tunnel_id = APP::Module('DB')->Insert(
                    APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 'tunnels_users',
                    [
                        'id' => 'NULL',
                        'tunnel_id' => [$settings['tunnel_id'], PDO::PARAM_INT],
                        'user_id' => [$user_id, PDO::PARAM_INT],
                        'state' => ['complete', PDO::PARAM_STR],
                        'resume_date' => ['0000-00-00 00:00:00', PDO::PARAM_STR],
                        'object' => ['', PDO::PARAM_STR],
                        'input_data' => ['{}', PDO::PARAM_STR]
                    ]
                )){
                    APP::Module('DB')->Insert(
                        APP::Module('Tunnels')->settings['module_tunnels_db_connection'], 'tunnels_tags',
                        [
                            'id' => 'NULL',
                            'user_tunnel_id' => [$user_tunnel_id, PDO::PARAM_INT],
                            'label_id' => ['manually_complete', PDO::PARAM_STR],
                            'token' => '""',
                            'info' => '""',
                            'cr_date' => 'NOW()'
                        ]
                    );
                }
            }
        }
        
        return $out;
    }
    
    public function add_tag($id, $settings){
        $out['status'] = 'success';
        
        foreach ($id as $user_id) {
            APP::Module('DB')->Insert(
                APP::Module('Users')->settings['module_users_db_connection'], 'users_tags',
                [
                    'id' => 'NULL',
                    'user' => [$user_id, PDO::PARAM_INT],
                    'item' => [$settings['item'], PDO::PARAM_STR],
                    'value' => [$settings['value'], PDO::PARAM_STR],
                    'cr_date' => 'NOW()'
                ]
            );
        }
        
        return $out;
    }
    
    public function change_state($id, $settings){
        $out['status'] = 'error';
        
        if (APP::Module('DB')->Delete(
            APP::Module('Users')->settings['module_users_db_connection'], 'users_about',
            [
                ['user', 'IN', $id, PDO::PARAM_INT],
                ['item', '=', 'state', PDO::PARAM_STR]
            ]
        )) {
            $out['status'] = 'success';
            
            foreach ($id as $user_id) {
                $result = APP::Module('DB')->Insert(
                    APP::Module('Users')->settings['module_users_db_connection'], ' users_about',
                    [
                        'id' => 'NULL',
                        'user' => [$user_id, PDO::PARAM_INT],
                        'item' => ['state', PDO::PARAM_STR],
                        'value' => [$settings['value'], PDO::PARAM_STR],
                        'up_date' => 'CURRENT_TIMESTAMP'
                    ]
                );
            }
        }
        
        return $out;
    }
    
    public function add_group($id, $settings){
        $out['status'] = 'success';
        
        foreach ($id as $user_id) {
            if (!APP::Module('DB')->Select(
                APP::Module('Groups')->settings['module_groups_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                ['COUNT(id)'], 'groups_users',
                [
                    ['group_id', '=', $settings['group_id'], PDO::PARAM_INT],
                    ['user_id', '=', $user_id, PDO::PARAM_INT]
                ]
            )){
                $out['user_id'][] = APP::Module('DB')->Insert(
                    APP::Module('Groups')->settings['module_groups_db_connection'], 'groups_users',
                    [
                        'id' => 'NULL',
                        'group_id' => [$settings['group_id'], PDO::PARAM_INT],
                        'user_id' => [$user_id, PDO::PARAM_STR],
                        'cr_date' => 'NOW()'
                    ]
                );
            }
        }
        return $out;
    }
    
    public function delete_group($id, $settings){
        $out['status'] = 'success';
        
        foreach ($id as $user_id) {
            if (APP::Module('DB')->Select(
                APP::Module('Groups')->settings['module_groups_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                ['COUNT(id)'], 'groups_users',
                [
                    ['group_id', '=', $settings['group_id'], PDO::PARAM_INT],
                    ['user_id', '=', $user_id, PDO::PARAM_INT]
                ]
            )){
                APP::Module('DB')->Delete(
                    APP::Module('Groups')->settings['module_groups_db_connection'], 'groups_users',
                    [
                        ['group_id', '=', $settings['group_id'], PDO::PARAM_INT],
                        ['user_id', '=', $user_id, PDO::PARAM_INT]
                    ]
                );
            }
        }
        return $out;
    }
    
    public function send_mail($id, $settings){
        $out['status'] = 'success';
        
        foreach (APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
            ['email'], 'users',
            [['id', 'IN', $id, PDO::PARAM_INT]]
        ) as $email) {
            APP::Module('Mail')->Send($email, $settings['letter'], false, isset($settings['save_copy']), 'manage_users');
        }
        
        return $out;
    }
}
