<?
class Analytics {

    public $settings;
    public $config;

    function __construct($conf) {
        foreach ($conf['routes'] as $route)
            APP::Module('Routing')->Add($route[0], $route[1], $route[2]);
        
        $this->config['rfm'] = $conf['rfm'];
        $this->config['rfm_mail'] = $conf['rfm_mail'];
    }

    public function Init() {
        $this->settings = APP::Module('Registry')->Get([
            'module_analytics_db_connection',
            'module_analytics_tmp_dir',
            'module_analytics_max_execution_time',
            'module_analytics_cache',
            'module_analytics_yandex_token',
            'module_analytics_yandex_client_id',
            'module_analytics_yandex_client_secret',
            'module_analytics_yandex_counter'
        ]);
    }
 
    public function Admin() {
        return APP::Render('analytics/admin/nav', 'content');
    }
    
    public function Dashboard() {
        return APP::Render('analytics/admin/dashboard/index', 'return');
    }
    
    public function GetYandex() {
        if (empty($this->settings['module_analytics_yandex_token'])) exit;
        set_time_limit($this->settings['module_analytics_max_execution_time']);
        $date = isset(APP::Module('Routing')->get['date']) ? APP::Module('Routing')->get['date'] : date('Y-m-d', strtotime('-1 day'));

        $out = json_decode(APP::Module('Utils')->Curl([
            'url' => 'https://api-metrika.yandex.ru/stat/v1/data/bytime?' . http_build_query([
                'id' => $this->settings['module_analytics_yandex_counter'],
                'metrics' => 'ym:s:visits,ym:s:pageviews,ym:s:users',
                'date1' => $date,
                'date2' => $date,
                'group' => 'day',
                'oauth_token' => $this->settings['module_analytics_yandex_token']
            ]),
            'custom_request' => 'GET',
            'return_transfer' => 1,
            'http_header' => [
                'Content-Type' => 'application/json'
            ]
        ]), true);

        if (isset($out['data'][0]['metrics'])) {
            if (!APP::Module('DB')->Select(
                $this->settings['module_analytics_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                ['COUNT(id)'], 'analytics_yandex_metrika',
                [['date', '=', $date, PDO::PARAM_STR]]
            )) {
                APP::Module('DB')->Insert(
                    $this->settings['module_analytics_db_connection'], 'analytics_yandex_metrika',
                    Array(
                        'id' => 'NULL',
                        'visits' => [$out['data'][0]['metrics'][0][0], PDO::PARAM_INT],
                        'pageviews' => [$out['data'][0]['metrics'][1][0], PDO::PARAM_INT],
                        'users' => [$out['data'][0]['metrics'][2][0], PDO::PARAM_INT],
                        'date' => [$date, PDO::PARAM_STR],
                    )
                );
            }
        }

        APP::Module('Triggers')->Exec(
            'download_yandex_analytics', 
            [
                'out' => $out, 
                'date' => $date
            ]
        );
        
        if (isset(APP::Module('Routing')->get['debug'])) {
            print_r($out);
        }
    }
    
    public function GetYandexToken() {
        if (isset(APP::Module('Routing')->get['code'])) {
            $data = json_decode(APP::Module('Utils')->Curl([
                'url' => 'https://oauth.yandex.ru/token',
                'return_transfer' => 1,
                'post' => [
                    'grant_type' => 'authorization_code',
                    'code' => APP::Module('Routing')->get['code'],
                    'client_id' => $this->settings['module_analytics_yandex_client_id'],
                    'client_secret' => $this->settings['module_analytics_yandex_client_secret']
                ]
            ]));
            
            if ($data->access_token) {
                APP::Module('Registry')->Update(['value' => $data->access_token], [['item', '=', 'module_analytics_yandex_token', PDO::PARAM_STR]]);
                header('Location: ' . APP::Module('Routing')->root . 'admin/analytics/settings?yandex_token=success');
            } else {
                header('Location: ' . APP::Module('Routing')->root . 'admin/analytics/settings?yandex_token=error');
            }
        } else {
            header('Location: https://oauth.yandex.ru/authorize?response_type=code&client_id=' . $this->settings['module_analytics_yandex_client_id']);
        }
        
        exit;
    }
    
    public function Cohorts() {
        // Установка часового пояса
        ini_set('date.timezone', 'Europe/Moscow');
        // Выходные данные

        $out = [];

        // Фильтр для выборки пользователей      
        if (isset($_POST['rules']) and $_POST['rules']){
            $rules = json_decode($_POST['rules'], true);
            $users = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $users = APP::Module('Users')->UsersSearch($rules);
        }

        // Способ компоновки данных: day|week|month
        $group_by = isset($_POST['group']) ? $_POST['group'] : 'month';

        // UTM-метки
        $utm_labels = [];

        foreach ($rules['rules'] as $rule) {
            if ($rule['method'] === 'utm') {
                $utm_labels[$rule['settings']['name']] = $rule['settings']['value'];
            }
        }

        // Показатели
        $indicators = [
            'subscribers_unsubscribe',          // Отписанные подписчики
            'total_subscribers_unsubscribe',    // Общее кол-во отписанных подписчиков
            'subscribers_dropped',              // Дропнутые подписчики
            'total_subscribers_dropped',        // Общее кол-во дропнутых подписчиков
            'subscribers_active',               // Активные подписчики
            'total_subscribers_active',         // Общее кол-во активных подписчиков
            'clients',                          // Покупатели (сколько уникальных клиентов)
            'total_clients',                    // Общее кол-во покупателей
            'orders',                           // Заказы
            'total_orders',                     // Общее кол-во заказов
            'revenue',                          // Выручка
            'total_revenue',                    // Общий доход
            'ltv_client',                       // LTV клиента (общий доход / кол-во клиентов)
            'cost',                             // Расходы (сколько затрат на привлечение - по API из директа, vk, либо вручную)
            'subscriber_cost',                  // Расходы на подписчика (расходы / кол-во подписчиков активированных)
            'client_cost',                      // Расходы на покупателя (расходы / кол-во покупателей)
            'roi',                              // ROI ((доходы - расходы) / расходы)
        ];

        // Сохранение целевых пользователей во временную таблицу
        APP::Module('DB')->Open(APP::Module('Analytics')->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_cohorts_tmp (user) VALUES (' . implode('),(', $users) . ')');

        // Получение минимальной даты
        $min_date = APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
            ['UNIX_TIMESTAMP(MIN(reg_date))'], 'users', [['id', 'IN', 'SELECT user FROM analytics_cohorts_tmp', PDO::PARAM_INT]]
        );
    
        // Инициализация выходных данных
        switch ($group_by) {
            case 'day':
                for ($x = strtotime(date('Y-m-d', $min_date)); $x <= strtotime('Today'); $x = $x + 86400) {
                    $out[] = ['label' => date('Y-m-d', $x), 'date' => [$x, strtotime('+ 1 day', $x) - 1]];
                }
                break;
            case 'week':
                for ($x = strtotime('last Monday', strtotime(date('Y-m-d', $min_date))); $x <= strtotime('Today'); $x = $x + (86400 * 7)) {
                    $out[] = ['label' => date('Y-m-d', $x), 'date' => [$x, strtotime('+ 1 week', $x) - 1]];
                }
                break;
            case 'month':
                for ($x = strtotime(date('Y-m-01', $min_date)); $x <= strtotime('Today'); $x = $x + (86400 * cal_days_in_month(CAL_GREGORIAN, date('m', $x), date('Y', $x)))) {
                    $out[] = ['label' => date('Y-m-01', $x), 'date' => [$x, strtotime('+ 1 month', $x) - 1]];
                }
                break;
        }

        // Сохранение сгруппированных списков пользователей
        foreach ($out as $index => $values) {
            $out[$index]['users'] = APP::Module('DB')->Select(
                APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN], 
                ['id'], 'users',[['id', 'IN', 'SELECT user FROM analytics_cohorts_tmp', PDO::PARAM_INT], ['UNIX_TIMESTAMP(reg_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR]]
            );
        }


        $orig_orders = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],
            ['id'],'billing_invoices',
            [
                ['state', '=', 'success', PDO::PARAM_STR],
                ['user_id', 'IN', 'SELECT analytics_cohorts_tmp.user FROM analytics_cohorts_tmp', PDO::PARAM_INT],
                ['amount', '!=', '0', PDO::PARAM_INT]
            ]
        );


        // Удаление целевых пользователей из временной таблицы
        APP::Module('DB')->Open(APP::Module('Analytics')->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_cohorts_tmp');

        $target_orders = [];

        // Вычисление индикаторов
        foreach ($out as $index => $values) {
            $clients_buffer = [];

            foreach ($out as $l_index => $l_values) {
                if ($index < $l_index) {
                    break;
                }

                foreach ($indicators as $indicator) {
                    switch ($indicator) {
                        case 'subscribers_unsubscribe':
                            $out[$index]['indicators'][$l_index][$indicator] = (int) APP::Module('DB')->Select(
                                APP::Module('Users')->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                                ['COUNT(DISTINCT user)'], 'users_about',
                                [
                                    ['item', '=', 'state', PDO::PARAM_STR],
                                    ['value', '=', 'unsubscribe', PDO::PARAM_STR],
                                    ['user', 'IN', $l_values['users'], PDO::PARAM_INT],
                                    ['UNIX_TIMESTAMP(up_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR],
                                ]
                            );
                            
                            break;
                        case 'total_subscribers_unsubscribe':
                            $cohorts_total_subscribers_unsubscribe = [];

                            foreach ($out as $value) {
                                $cohorts_total_subscribers_unsubscribe[] = isset($value['indicators'][$l_index]['subscribers_unsubscribe']) ?(int) $value['indicators'][$l_index]['subscribers_unsubscribe'] : 0;
                            }

                            $out[$index]['indicators'][$l_index]['total_subscribers_unsubscribe'] = array_sum($cohorts_total_subscribers_unsubscribe);
                            break;
                        case 'subscribers_dropped':
                            $out[$index]['indicators'][$l_index][$indicator] = (int) APP::Module('DB')->Select(
                                APP::Module('Users')->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                                ['COUNT(DISTINCT user)'], 'users_about',
                                [
                                    ['item', '=', 'state', PDO::PARAM_STR],
                                    ['value', '=', 'dropped', PDO::PARAM_STR],
                                    ['user', 'IN', $l_values['users'], PDO::PARAM_INT],
                                    ['UNIX_TIMESTAMP(up_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR],
                                ]
                            );
                            break;
                        case 'total_subscribers_dropped':
                            $cohorts_total_subscribers_dropped = [];

                            foreach ($out as $value) {
                                $cohorts_total_subscribers_dropped[] = isset($value['indicators'][$l_index]['subscribers_dropped']) ? (int) $value['indicators'][$l_index]['subscribers_dropped'] : 0;
                            }

                            $out[$index]['indicators'][$l_index]['total_subscribers_dropped'] = array_sum($cohorts_total_subscribers_dropped);
                            break;
                        case 'subscribers_active':
                            $out[$index]['indicators'][$l_index][$indicator] = (int) APP::Module('DB')->Select(
                                APP::Module('Users')->settings['module_users_db_connection'], ['fetch', PDO::FETCH_COLUMN],
                                ['COUNT(DISTINCT user)'], 'users_about',
                                [
                                    ['item', '=', 'state', PDO::PARAM_STR],
                                    ['value', '=', 'active', PDO::PARAM_STR],
                                    ['user', 'IN', $l_values['users'], PDO::PARAM_INT],
                                    ['UNIX_TIMESTAMP(up_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR],
                                ]
                            );
                            break;
                        case 'total_subscribers_active':
                            $cohorts_total_subscribers_active = [];

                            foreach ($out as $value) {
                                $cohorts_total_subscribers_active[] = isset($value['indicators'][$l_index]['subscribers_active']) ? (int) $value['indicators'][$l_index]['subscribers_active'] : 0;
                            }

                            $cohorts_total_subscribers_unsubscribe = [];

                            foreach ($out as $value) {
                                $cohorts_total_subscribers_unsubscribe[] = isset($value['indicators'][$l_index]['subscribers_unsubscribe']) ? (int) $value['indicators'][$l_index]['subscribers_unsubscribe'] : 0;
                            }

                            $out[$index]['indicators'][$l_index]['total_subscribers_active'] = array_sum($cohorts_total_subscribers_active) - array_sum($cohorts_total_subscribers_unsubscribe);
                            break;
                        case 'clients':
                            $clients = APP::Module('DB')->Select(
                                APP::Module('Billing')->settings['module_billing_db_connection'],
                                ['fetchAll', PDO::FETCH_COLUMN], ['DISTINCT user_id'], 'billing_invoices',
                                [
                                    ['state', '=', 'success', PDO::PARAM_STR],
                                    ['amount', '!=', '0', PDO::PARAM_INT],
                                    ['user_id', 'IN', $l_values['users'], PDO::PARAM_INT],
                                    ['UNIX_TIMESTAMP(cr_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR],
                                ]
                            );

                            $out[$index]['indicators'][$l_index][$indicator] = count(array_diff($clients, isset($clients_buffer[$l_index]) ? (array) $clients_buffer[$l_index] : []));
                            $clients_buffer[$l_index] = array_unique(array_merge(isset($clients_buffer[$l_index]) ? (array) $clients_buffer[$l_index] : [], $clients));
                            break;
                        case 'total_clients':
                            $cohorts_total_clients = [];

                            foreach ($out as $value) {
                                $cohorts_total_clients[] = isset($value['indicators'][$l_index]['clients']) ? (int) $value['indicators'][$l_index]['clients'] : 0;
                            }

                            $out[$index]['indicators'][$l_index]['total_clients'] = array_sum($cohorts_total_clients);
                            break;
                        case 'orders':
                            $t_ord = APP::Module('DB')->Select(
                                APP::Module('Billing')->settings['module_billing_db_connection'],
                                ['fetchAll', PDO::FETCH_COLUMN], ['id'], 'billing_invoices',
                                [
                                    ['state', '=', 'success', PDO::PARAM_STR],
                                    ['amount', '!=', '0', PDO::PARAM_INT],
                                    ['user_id', 'IN', $l_values['users'], PDO::PARAM_INT],
                                    ['UNIX_TIMESTAMP(cr_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR],
                                ]
                            );

                            $out[$index]['indicators'][$l_index][$indicator] = $t_ord;

                            $target_orders = array_merge($target_orders, $t_ord);
                            break;
                        case 'total_orders':
                            $cohorts_total_orders = [];

                            foreach ($out as $value) {
                                $cohorts_total_orders[] = isset($value['indicators'][$l_index]['orders']) ? (int) count((array) $value['indicators'][$l_index]['orders']) : 0;
                            }

                            $out[$index]['indicators'][$l_index]['total_orders'] = array_sum($cohorts_total_orders);
                            break;
                        case 'revenue':
                            // Сохранение целевых пользователей во временную таблицу
                            APP::Module('DB')->Open(APP::Module('Analytics')->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_cohorts_tmp (user) VALUES (' . implode('),(', $l_values['users']) . ')');

                            $out[(int) $index]['indicators'][(int) $l_index][$indicator] = (int) APP::Module('DB')->Select(
                                APP::Module('Billing')->settings['module_billing_db_connection'],
                                ['fetch', PDO::FETCH_COLUMN], ['SUM(amount)'], 'billing_invoices',
                                [
                                    ['state', '=', 'success', PDO::PARAM_STR],
                                    ['amount', '!=', '0', PDO::PARAM_INT],
                                    ['user_id', 'IN', 'SELECT analytics_cohorts_tmp.user FROM analytics_cohorts_tmp', PDO::PARAM_INT],
                                    ['UNIX_TIMESTAMP(cr_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR],
                                ]
                            );
                            
                            // Удаление целевых пользователей из временной таблицы
                            APP::Module('DB')->Open(APP::Module('Analytics')->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_cohorts_tmp');
                            break;
                        case 'total_revenue':
                            $cohorts_revenue = Array();

                            foreach ($out as $value) {
                                $cohorts_revenue[] = isset($value['indicators'][$l_index]['revenue']) ? (int) $value['indicators'][$l_index]['revenue'] : 0;
                            }

                            $out[$index]['indicators'][$l_index]['total_revenue'] = array_sum($cohorts_revenue);
                            break;
                        case 'ltv_client':
                            $cohorts_clients = 0;

                            foreach ($out as $value) {
                                $cohorts_clients += isset($value['indicators'][$l_index]['clients']) ? $value['indicators'][$l_index]['clients']:0;
                            }

                            $cohorts_revenue = 0;

                            foreach ($out as $value) {
                                $cohorts_revenue += isset($value['indicators'][$l_index]['revenue']) ? $value['indicators'][$l_index]['revenue'] : 0;
                            }

                            $out[$index]['indicators'][$l_index]['ltv_client'] = $cohorts_clients ? (int) $cohorts_revenue / $cohorts_clients : (int) $cohorts_revenue;
                            break;
                        default:
                            $out[$index]['indicators'][$l_index][$indicator] = 0;
                    }
                }
            }
        }
     
        // Вычисление кол-ва пользователей
        foreach ($out as $index => $values) {
            $out[$index]['users'] = count($out[$index]['users']);
        }

        // Вычисление расхода
       foreach ([
            'source',
            'medium',
            'campaign',
            'term',
            'content'
        ] as $utm_key) {
            foreach (APP::Module('DB')->Select(
                APP::Module('Costs')->settings['module_costs_db_connection'],
                ['fetchAll', PDO::FETCH_ASSOC], 
                [
                    'utm_source',
                    'utm_medium',
                    'utm_campaign',
                    'utm_term',
                    'utm_content',
                    'utm_label',
                    'utm_alias'
                ],
                'cost_extra',
                [
                    ['utm_label', '=', $utm_key, PDO::PARAM_STR]
                ]
            ) as $value) {
                $utm_alias_value = $value['utm_' . $value['utm_label']];
                $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                $utm_alias_data = [];

                if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                $use_utm_alias = true;

                foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                    if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                }

                if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
            }
        }
        
        $cost_utm = [];
        foreach ($utm_labels as $label => $value) {
            $cost_utm[] = ['utm_' . $label, '=', $value, PDO::PARAM_STR];
        }

        foreach ($out as $index => $values) {
            $cost = (float) round(APP::Module('DB')->Select(
                APP::Module('Costs')->settings['module_costs_db_connection'],
                ['fetch', PDO::FETCH_COLUMN], ['SUM(cost)'],'cost',
                array_merge(
                    [
                        ['cost_date', 'BETWEEN', '"' . date('Y-m-d', $values['date'][0]) . '" AND "' . date('Y-m-d', $values['date'][1]) . '"', PDO::PARAM_STR]
                    ],
                    $cost_utm
                )
            ), 2);

            foreach (array_reverse($out) as $key => $date_value) {
                if (isset($out[$key]['indicators'][$index])) {
                    $out[$key]['indicators'][$index]['cost'] = $cost;
                    $out[$key]['indicators'][$index]['subscriber_cost'] = ($out[$key]['indicators'][$index]['total_subscribers_active'] ? round($cost / $out[$key]['indicators'][$index]['total_subscribers_active'], 2) : round($cost,2));
                    $out[$key]['indicators'][$index]['client_cost'] = ($out[$key]['indicators'][$index]['total_clients'] ? round($cost / $out[$key]['indicators'][$index]['total_clients'], 2) : round($cost,2));
                    $out[$key]['indicators'][$index]['roi'] = ($cost ? round((($out[$key]['indicators'][$index]['total_revenue'] - $cost) / $cost) * 100, 2) : 0);
                }
            }
        }

        APP::Render('analytics/admin/cohorts', 'include', $out);
    }
    
    public function UtmRoi() {
        $out = [];
        $sort = ['default', 'asc'];
        $uid = false;
        $settings = ['rules'=>[]];

        if (isset($_POST['settings']['sort'])) {
            $sort = $_POST['settings']['sort'];
        }

        if (isset($_POST['rules']) and $_POST['rules']){
            $rules = json_decode($_POST['rules'], true);
            $uid = APP::Module('Users')->UsersSearch($rules);
            $settings['rules'] = $rules;
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $uid = APP::Module('Users')->UsersSearch($rules);
            $settings = [
                'rules' => $rules
            ];
        }

        if ($uid)  APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_utm_roi_tmp (user) VALUES (' . implode('),(', $uid) . ')');

        if(isset($_POST['api'])){
            switch ($_POST['api']) {
                case 'labels':
                    switch ($_POST['settings']['label']) {
                        case 'root':
                            if ($uid) {
                                $users_utm = APP::Module('DB')->Select(
                                    APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],
                                    ['DISTINCT(users_utm.value)'],
                                    'users_utm',
                                    [
                                        ['users_utm.item', '=', 'source', PDO::PARAM_STR],
                                        ['users_utm.num', '=', '1', PDO::PARAM_INT],
                                        ['users_utm.user', 'IN', 'SELECT analytics_utm_roi_tmp.user FROM analytics_utm_roi_tmp', PDO::PARAM_INT]
                                    ]
                                );
                                APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');
                            } else {
                                $users_utm = APP::Module('DB')->Select(
                                    APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],['DISTINCT(utm_source)'],'users_utm_index');
                            }

                            foreach ($users_utm as $item) {
                                $label_value = trim($item);
                                $search_rules = [
                                    'logic' => 'intersect',
                                    'rules' => [
                                        [
                                            'method' => 'utm',
                                            'settings' => [
                                                'num' => '1',
                                                'item' => 'source',
                                                'value' => $label_value
                                            ]
                                        ]
                                    ]
                                ];

                                $utm_uid = APP::Module('Users')->UsersSearch($search_rules);

                                $target_uid = $uid ? array_intersect($uid, $utm_uid) : $utm_uid;
                                APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_utm_roi_tmp (user) VALUES (' . implode('),(', $target_uid) . ')');

                                $revenue_value = (int) APP::Module('DB')->Select(
                                    APP::Module('Billing')->settings['module_billing_db_connection'],
                                    ['fetch', PDO::FETCH_COLUMN], ['SUM(billing_invoices.amount)'],
                                    'billing_invoices',
                                    [
                                        ['billing_invoices.user_id', 'IN', 'SELECT analytics_utm_roi_tmp.user FROM analytics_utm_roi_tmp', PDO::PARAM_INT],
                                        ['billing_invoices.state', '=', 'success', PDO::PARAM_STR],
                                        ['billing_invoices.amount', '!=', '0', PDO::PARAM_INT]
                                    ]
                                );

                                APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                // Вычисление расхода
                                $utm_labels = ['source' => $label_value];

                                foreach ([
                                    'source',
                                    'medium',
                                    'campaign',
                                    'term',
                                    'content'
                                ] as $utm_key) {
                                    foreach (APP::Module('DB')->Select(
                                        APP::Module('Users')->settings['module_users_db_connection'],
                                        ['fetchAll',PDO::FETCH_ASSOC],
                                        [
                                            'utm_source',
                                            'utm_medium',
                                            'utm_campaign',
                                            'utm_term',
                                            'utm_content',
                                            'utm_label',
                                            'utm_alias'
                                        ],
                                        'costs_extra',
                                        [
                                            ['utm_label', '=', $utm_key, PDO::PARAM_STR]
                                        ]
                                    ) as $value) {
                                        $utm_alias_value = $value['utm_' . $value['utm_label']];
                                        $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                        $utm_alias_data = [];

                                        if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                        if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                        if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                        if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                        if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                        $use_utm_alias = true;

                                        foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                            if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                        }

                                        if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                    }
                                }
                                
                                foreach ($utm_labels as $label => $value) {
                                    $cost_utm[] = ['utm_' . $label, '=', $value, PDO::PARAM_STR];
                                }

                                $cost_value = (int) APP::Module('DB')->Select(
                                    APP::Module('Costs')->settings['module_costs_db_connection'],
                                    ['fetch', PDO::FETCH_COLUMN],['SUM(amount)'],'costs', $cost_utm
                                );
                                
                                
                                //////////////////////////////////////

                                $out[md5($label_value . time())] = [
                                    'name' => $label_value,
                                    'stat' => [
                                        'cost' => $cost_value,
                                        'revenue' => $revenue_value,
                                        'profit' => $revenue_value - $cost_value,
                                        'roi' => ($cost_value ? round((($revenue_value - $cost_value) / $cost_value) * 100, 2) : 0)
                                    ],
                                    'rules' => htmlentities(json_encode($search_rules)),
                                    'ref' => APP::Module('Crypt')->Encode(json_encode($search_rules))
                                ];
                            }
                           
                            break;
                        case 'source':
                            if (false) {
                                /*
                                $users_utm_where = Array(
                                    Array('admin_pult_ref.users_utm.num', '=', '1')
                                );

                                if ($uid) $users_utm_where[] = Array('admin_pult_ref.users_utm.user_id', 'IN', 'SELECT user_id FROM utm_roi_tmp');

                                $users_utm = Shell::$app->Get('extensions','EORM')->SelectV2(
                                    'pult_ref', Array('fetchAll', PDO::FETCH_ASSOC),
                                    Array(
                                        'admin_pult_ref.users_utm.user_id',
                                        'admin_pult_ref.users_utm.item',
                                        'admin_pult_ref.users_utm.value'
                                    ),
                                    'admin_pult_ref.users_utm',
                                    $users_utm_where
                                );

                                if ($uid) APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                $users = Array();

                                foreach ($users_utm as $item) {
                                    $users[$item['user_id']][$item['item']] = $item['value'];
                                }

                                unset($users_utm);
                                $label_list = Array();

                                foreach ($users as $item) {
                                    if ($item['source'] == $_POST['settings']['value']) {
                                        if (array_search($item['medium'], $label_list) === false) {
                                            $search_rules = Array(
                                                'logic' => 'intersect',
                                                'rules' => Array(
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'source',
                                                            'value' => $item['source']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'medium',
                                                            'value' => $item['medium']
                                                        )
                                                    )
                                                )
                                            );

                                            $utm_uid = Shell::$app->Get('extensions','ERef')->Search($search_rules);
                                            $target_uid = $uid ? array_intersect($uid, $utm_uid) : $utm_uid;
                                            Shell::$app->Get('extensions','EModDB')->Open('pult_ref')->query('INSERT INTO utm_roi_tmp (user_id) VALUES (' . implode('),(', $target_uid) . ')');

                                            $revenue_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_billing',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(admin_pult_billing.invoices.amount)'
                                                ),
                                                'admin_pult_billing.invoices',
                                                Array(
                                                    Array('admin_pult_billing.invoices.usr_id', 'IN', 'SELECT admin_pult_ref.utm_roi_tmp.user_id FROM admin_pult_ref.utm_roi_tmp'),
                                                    Array('admin_pult_billing.invoices.state', '=', 'success'),
                                                    Array('admin_pult_billing.invoices.amount', '!=', '0')
                                                )
                                            );

                                            APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                            // Вычисление расхода
                                            $utm_labels = Array(
                                                'source' => $item['source'],
                                                'medium' => $item['medium']
                                            );

                                            foreach (Array(
                                                'source',
                                                'medium',
                                                'campaign',
                                                'term',
                                                'content'
                                            ) as $utm_key) {
                                                foreach (Shell::$app->Get('extensions','EORM')->SelectV2(
                                                    'pult_ref',
                                                    Array(
                                                        'fetchAll',
                                                        PDO::FETCH_ASSOC
                                                    ),
                                                    Array(
                                                        'utm_source',
                                                        'utm_medium',
                                                        'utm_campaign',
                                                        'utm_term',
                                                        'utm_content',
                                                        'utm_label',
                                                        'utm_alias'
                                                    ),
                                                    'cost_extra',
                                                    Array(
                                                        Array('utm_label', '=', $utm_key)
                                                    )
                                                ) as $value) {
                                                    $utm_alias_value = $value['utm_' . $value['utm_label']];
                                                    $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                                    $utm_alias_data = Array();

                                                    if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                                    if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                                    if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                                    if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                                    if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                                    $use_utm_alias = true;

                                                    foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                        if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                                    }

                                                    if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                                }
                                            }

                                            foreach ($utm_labels as $label => $value) {
                                                $cost_utm[] = Array('utm_' . $label, '=', $value);
                                            }

                                            $cost_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_ref',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(cost)'
                                                ),
                                                'cost',
                                                $cost_utm
                                            );
                                            //////////////////////////////////////

                                            $label_list[] = $item['medium'];

                                            $out[md5($item['source'] . $item['medium'] . time())] = Array(
                                                'name' => $item['medium'],
                                                'stat' => Array(
                                                    'cost' => $cost_value,
                                                    'revenue' => $revenue_value,
                                                    'profit' => $revenue_value - $cost_value,
                                                    'roi' => round((($revenue_value - $cost_value) / $cost_value) * 100, 2)
                                                ),
                                                'rules' => htmlentities(json_encode($search_rules)),
                                                'ref' => Shell::$app->Get('extensions','ECrypt')->Encrypt(json_encode($search_rules))
                                            );
                                        }
                                    }
                                }
                                 *
                                 */
                            } else {
                                if ($uid)  APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');
                                
                                $users_utm = APP::Module('DB')->Select(
                                    APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],
                                    ['DISTINCT(utm_medium)'],'users_utm_index',[['utm_source', '=', $_POST['settings']['value'], PDO::PARAM_STR]]
                                );

                                foreach ($users_utm as $utm_value) {
                                    $search_rules = [
                                        'logic' => 'intersect',
                                        'rules' => [
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'source',
                                                    'value' => $_POST['settings']['value']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'medium',
                                                    'value' => $utm_value
                                                ]
                                            ]
                                        ]
                                    ];

                                    
                                    if(!$this->settings['module_analytics_cache']){
                                        $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                    }else{
                                        $cache_id = md5(json_encode($search_rules));
                                        if (!$utm_uid = APP::Module('Cache')->memcache->get($cache_id)) {
                                            $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                            APP::Module('Cache')->memcache->set($cache_id, $utm_uid, false, 180);
                                        }
                                    }

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_utm_roi_tmp (user)  VALUES (' . implode('),(', $utm_uid) . ')');

                                    $revenue_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Billing')->settings['module_billing_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN], ['SUM(billing_invoices.amount)'],
                                        'billing_invoices',
                                        [
                                            ['billing_invoices.user_id', 'IN', 'SELECT analytics_utm_roi_tmp.user FROM analytics_utm_roi_tmp', PDO::PARAM_INT],
                                            ['billing_invoices.state', '=', 'success', PDO::PARAM_STR],
                                            ['billing_invoices.amount', '!=', '0', PDO::PARAM_INT]
                                        ]
                                    );

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                    // Вычисление расхода
                                    $utm_labels = [
                                        'source' => $_POST['settings']['value'],
                                        'medium' => $utm_value
                                    ];

                                    foreach ([
                                        'source',
                                        'medium',
                                        'campaign',
                                        'term',
                                        'content'
                                    ] as $utm_key) {
                                        foreach (APP::Module('DB')->Select(
                                            APP::Module('Users')->settings['module_users_db_connection'],
                                            ['fetchAll', PDO::FETCH_COLUMN],
                                            [
                                                'utm_source',
                                                'utm_medium',
                                                'utm_campaign',
                                                'utm_term',
                                                'utm_content',
                                                'utm_label',
                                                'utm_alias'
                                            ],
                                            'costs_extra', [['utm_label', '=', $utm_key, PDO::PARAM_STR]]
                                        ) as $value) {
                                            $utm_alias_value = $value['utm_' . $value['utm_label']];
                                            $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                            $utm_alias_data = [];

                                            if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                            if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                            if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                            if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                            if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                            $use_utm_alias = true;

                                            foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                            }

                                            if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                        }
                                    }
                                    
                                    foreach ($utm_labels as $label => $value) {
                                        $cost_utm[] = ['utm_' . $label, '=', $value, PDO::PARAM_STR];
                                    }

                                    $cost_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Costs')->settings['module_costs_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN],['SUM(amount)'],'costs',$cost_utm
                                    );
                                    //////////////////////////////////////

                                    $out[md5($_POST['settings']['value'] . $utm_value . time())] = [
                                        'name' => $utm_value,
                                        'stat' => [
                                            'cost' => $cost_value,
                                            'revenue' => $revenue_value,
                                            'profit' => $revenue_value - $cost_value,
                                            'roi' => ($cost_value ?  round((($revenue_value - $cost_value) / $cost_value) * 100, 2) : 0)
                                        ],
                                        'rules' => htmlentities(json_encode($search_rules)),
                                        'ref' => APP::Module('Crypt')->Encode(json_encode($search_rules))
                                    ];
                                }
                            }
                            break;
                        case 'medium':
                            if (false) {
                                /*
                                $users_utm_where = Array(
                                    Array('admin_pult_ref.users_utm.num', '=', '1')
                                );

                                if ($uid) $users_utm_where[] = Array('admin_pult_ref.users_utm.user_id', 'IN', 'SELECT user_id FROM utm_roi_tmp');

                                $users_utm = Shell::$app->Get('extensions','EORM')->SelectV2(
                                    'pult_ref', Array('fetchAll', PDO::FETCH_ASSOC),
                                    Array(
                                        'admin_pult_ref.users_utm.user_id',
                                        'admin_pult_ref.users_utm.item',
                                        'admin_pult_ref.users_utm.value'
                                    ),
                                    'admin_pult_ref.users_utm',
                                    $users_utm_where
                                );

                                if ($uid) APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                $users = Array();

                                foreach ($users_utm as $item) {
                                    $users[$item['user_id']][$item['item']] = $item['value'];
                                }

                                unset($users_utm);
                                $label_list = Array();

                                foreach ($users as $item) {
                                    if (($item['source'] == $_POST['settings']['value']['source']) && ($item['medium'] == $_POST['settings']['value']['medium'])) {
                                        if (array_search($item['campaign'], $label_list) === false) {
                                            $search_rules = Array(
                                                'logic' => 'intersect',
                                                'rules' => Array(
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'source',
                                                            'value' => $item['source']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'medium',
                                                            'value' => $item['medium']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'campaign',
                                                            'value' => $item['campaign']
                                                        )
                                                    )
                                                )
                                            );

                                            $utm_uid = Shell::$app->Get('extensions','ERef')->Search($search_rules);
                                            $target_uid = $uid ? array_intersect($uid, $utm_uid) : $utm_uid;
                                            Shell::$app->Get('extensions','EModDB')->Open('pult_ref')->query('INSERT INTO utm_roi_tmp (user_id) VALUES (' . implode('),(', $target_uid) . ')');

                                            $revenue_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_billing',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(admin_pult_billing.invoices.amount)'
                                                ),
                                                'admin_pult_billing.invoices',
                                                Array(
                                                    Array('admin_pult_billing.invoices.usr_id', 'IN', 'SELECT admin_pult_ref.utm_roi_tmp.user_id FROM admin_pult_ref.utm_roi_tmp'),
                                                    Array('admin_pult_billing.invoices.state', '=', 'success'),
                                                    Array('admin_pult_billing.invoices.amount', '!=', '0')
                                                )
                                            );

                                            APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                            // Вычисление расхода
                                            $utm_labels = Array(
                                                'source' => $item['source'],
                                                'medium' => $item['medium'],
                                                'campaign' => $item['campaign']
                                            );

                                            foreach (Array(
                                                'source',
                                                'medium',
                                                'campaign',
                                                'term',
                                                'content'
                                            ) as $utm_key) {
                                                foreach (Shell::$app->Get('extensions','EORM')->SelectV2(
                                                    'pult_ref',
                                                    Array(
                                                        'fetchAll',
                                                        PDO::FETCH_ASSOC
                                                    ),
                                                    Array(
                                                        'utm_source',
                                                        'utm_medium',
                                                        'utm_campaign',
                                                        'utm_term',
                                                        'utm_content',
                                                        'utm_label',
                                                        'utm_alias'
                                                    ),
                                                    'cost_extra',
                                                    Array(
                                                        Array('utm_label', '=', $utm_key)
                                                    )
                                                ) as $value) {
                                                    $utm_alias_value = $value['utm_' . $value['utm_label']];
                                                    $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                                    $utm_alias_data = Array();

                                                    if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                                    if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                                    if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                                    if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                                    if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                                    $use_utm_alias = true;

                                                    foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                        if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                                    }

                                                    if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                                }
                                            }

                                            foreach ($utm_labels as $label => $value) {
                                                $cost_utm[] = Array('utm_' . $label, '=', $value);
                                            }

                                            $cost_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_ref',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(cost)'
                                                ),
                                                'cost',
                                                $cost_utm
                                            );
                                            //////////////////////////////////////

                                            $label_list[] = $item['campaign'];

                                            $out[md5($item['source'] . $item['medium'] . $item['campaign'] . time())] = Array(
                                                'name' => $item['campaign'],
                                                'stat' => Array(
                                                    'cost' => $cost_value,
                                                    'revenue' => $revenue_value,
                                                    'profit' => $revenue_value - $cost_value,
                                                    'roi' => round((($revenue_value - $cost_value) / $cost_value) * 100, 2)
                                                ),
                                                'rules' => htmlentities(json_encode($search_rules)),
                                                'ref' => Shell::$app->Get('extensions','ECrypt')->Encrypt(json_encode($search_rules))
                                            );
                                        }
                                    }
                                }
                                 */
                            } else {
                                
                                if ($uid)  APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');
                                
                                $users_utm = APP::Module('DB')->Select(
                                    APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],
                                    ['DISTINCT(utm_campaign)'],'users_utm_index',
                                    [
                                        ['utm_source', '=', $_POST['settings']['value']['source'], PDO::PARAM_STR],
                                        ['utm_medium', '=', $_POST['settings']['value']['medium'], PDO::PARAM_STR]
                                    ]
                                );

                                foreach ($users_utm as $utm_value) {
                                    $search_rules = [
                                        'logic' => 'intersect',
                                        'rules' => [
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'source',
                                                    'value' => $_POST['settings']['value']['source']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'medium',
                                                    'value' => $_POST['settings']['value']['medium']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'campaign',
                                                    'value' => $utm_value
                                                ]
                                            ]
                                        ]
                                    ];

                                    if(!$this->settings['module_analytics_cache']){
                                        $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                    }else{
                                        $cache_id = md5(json_encode($search_rules));
                                        if (!$utm_uid = APP::Module('Cache')->memcache->get($cache_id)) {
                                            $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                            APP::Module('Cache')->memcache->set($cache_id, $utm_uid, false, 180);
                                        }
                                    }

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_utm_roi_tmp (user) VALUES (' . implode('),(', $utm_uid) . ')');

                                    $revenue_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Billing')->settings['module_billing_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN], ['SUM(billing_invoices.amount)'],
                                        'billing_invoices',
                                        [
                                            ['billing_invoices.user_id', 'IN', 'SELECT analytics_utm_roi_tmp.user FROM analytics_utm_roi_tmp', PDO::PARAM_INT],
                                            ['billing_invoices.state', '=', 'success', PDO::PARAM_STR],
                                            ['billing_invoices.amount', '!=', '0', PDO::PARAM_INT]
                                        ]
                                    );

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                    // Вычисление расхода
                                    $utm_labels = [
                                        'source' => $_POST['settings']['value']['source'],
                                        'medium' => $_POST['settings']['value']['medium'],
                                        'campaign' => $utm_value
                                    ];

                                    foreach ([
                                        'source',
                                        'medium',
                                        'campaign',
                                        'term',
                                        'content'
                                    ] as $utm_key) {
                                        foreach (APP::Module('DB')->Select(
                                            APP::Module('Users')->settings['module_users_db_connection'],
                                            ['fetchAll', PDO::FETCH_COLUMN],
                                            [
                                                'utm_source',
                                                'utm_medium',
                                                'utm_campaign',
                                                'utm_term',
                                                'utm_content',
                                                'utm_label',
                                                'utm_alias'
                                            ],
                                            'costs_extra', [['utm_label', '=', $utm_key, PDO::PARAM_STR]]
                                        ) as $value) {
                                            $utm_alias_value = $value['utm_' . $value['utm_label']];
                                            $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                            $utm_alias_data = [];

                                            if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                            if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                            if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                            if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                            if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                            $use_utm_alias = true;

                                            foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                            }

                                            if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                        }
                                    }

                                    foreach ($utm_labels as $label => $value) {
                                        $cost_utm[] = ['utm_' . $label, '=', $value, PDO::PARAM_STR];
                                    }

                                    $cost_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Costs')->settings['module_costs_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN],['SUM(amount)'],'costs',$cost_utm
                                    );
                                    //////////////////////////////////////

                                    $out[md5($_POST['settings']['value']['source'] . $_POST['settings']['value']['medium'] . $utm_value . time())] = [
                                        'name' => $utm_value,
                                        'stat' => [
                                            'cost' => $cost_value,
                                            'revenue' => $revenue_value,
                                            'profit' => $revenue_value - $cost_value,
                                            'roi' => ($cost_value ? round((($revenue_value - $cost_value) / $cost_value) * 100, 2) : 0)
                                        ],
                                        'rules' => htmlentities(json_encode($search_rules)),
                                        'ref' => APP::Module('Crypt')->Encode(json_encode($search_rules))
                                    ];
                                }
                            }
                            break;
                        case 'campaign':
                            if (false) {
                                /*
                                $users_utm_where = Array(
                                    Array('admin_pult_ref.users_utm.num', '=', '1')
                                );

                                if ($uid) $users_utm_where[] = Array('admin_pult_ref.users_utm.user_id', 'IN', 'SELECT user_id FROM utm_roi_tmp');

                                $users_utm = Shell::$app->Get('extensions','EORM')->SelectV2(
                                    'pult_ref', Array('fetchAll', PDO::FETCH_ASSOC),
                                    Array(
                                        'admin_pult_ref.users_utm.user_id',
                                        'admin_pult_ref.users_utm.item',
                                        'admin_pult_ref.users_utm.value'
                                    ),
                                    'admin_pult_ref.users_utm',
                                    $users_utm_where
                                );

                                if ($uid) APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                $users = Array();

                                foreach ($users_utm as $item) {
                                    $users[$item['user_id']][$item['item']] = $item['value'];
                                }

                                unset($users_utm);
                                $label_list = Array();

                                foreach ($users as $item) {
                                    if ((($item['source'] == $_POST['settings']['value']['source']) && ($item['medium'] == $_POST['settings']['value']['medium']) && ($item['campaign'] == $_POST['settings']['value']['campaign']))) {
                                        if (array_search($item['term'], $label_list) === false) {
                                            $search_rules = Array(
                                                'logic' => 'intersect',
                                                'rules' => Array(
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'source',
                                                            'value' => $item['source']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'medium',
                                                            'value' => $item['medium']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'campaign',
                                                            'value' => $item['campaign']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'term',
                                                            'value' => $item['term']
                                                        )
                                                    )
                                                )
                                            );

                                            $utm_uid = Shell::$app->Get('extensions','ERef')->Search($search_rules);
                                            $target_uid = $uid ? array_intersect($uid, $utm_uid) : $utm_uid;
                                            Shell::$app->Get('extensions','EModDB')->Open('pult_ref')->query('INSERT INTO utm_roi_tmp (user_id) VALUES (' . implode('),(', $target_uid) . ')');

                                            $revenue_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_billing',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(admin_pult_billing.invoices.amount)'
                                                ),
                                                'admin_pult_billing.invoices',
                                                Array(
                                                    Array('admin_pult_billing.invoices.usr_id', 'IN', 'SELECT admin_pult_ref.utm_roi_tmp.user_id FROM admin_pult_ref.utm_roi_tmp'),
                                                    Array('admin_pult_billing.invoices.state', '=', 'success'),
                                                    Array('admin_pult_billing.invoices.amount', '!=', '0')
                                                )
                                            );

                                            APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                            // Вычисление расхода
                                            $utm_labels = Array(
                                                'source' => $item['source'],
                                                'medium' => $item['medium'],
                                                'campaign' => $item['campaign'],
                                                'term' => $item['term']
                                            );

                                            foreach (Array(
                                                'source',
                                                'medium',
                                                'campaign',
                                                'term',
                                                'content'
                                            ) as $utm_key) {
                                                foreach (Shell::$app->Get('extensions','EORM')->SelectV2(
                                                    'pult_ref',
                                                    Array(
                                                        'fetchAll',
                                                        PDO::FETCH_ASSOC
                                                    ),
                                                    Array(
                                                        'utm_source',
                                                        'utm_medium',
                                                        'utm_campaign',
                                                        'utm_term',
                                                        'utm_content',
                                                        'utm_label',
                                                        'utm_alias'
                                                    ),
                                                    'cost_extra',
                                                    Array(
                                                        Array('utm_label', '=', $utm_key)
                                                    )
                                                ) as $value) {
                                                    $utm_alias_value = $value['utm_' . $value['utm_label']];
                                                    $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                                    $utm_alias_data = Array();

                                                    if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                                    if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                                    if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                                    if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                                    if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                                    $use_utm_alias = true;

                                                    foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                        if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                                    }

                                                    if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                                }
                                            }

                                            foreach ($utm_labels as $label => $value) {
                                                $cost_utm[] = Array('utm_' . $label, '=', $value);
                                            }

                                            $cost_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_ref',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(cost)'
                                                ),
                                                'cost',
                                                $cost_utm
                                            );
                                            //////////////////////////////////////

                                            $label_list[] = $item['term'];

                                            $out[md5($item['source'] . $item['medium'] . $item['campaign'] . $item['term'] . time())] = Array(
                                                'name' => $item['term'],
                                                'stat' => Array(
                                                    'cost' => $cost_value,
                                                    'revenue' => $revenue_value,
                                                    'profit' => $revenue_value - $cost_value,
                                                    'roi' => round((($revenue_value - $cost_value) / $cost_value) * 100, 2)
                                                ),
                                                'rules' => htmlentities(json_encode($search_rules)),
                                                'ref' => Shell::$app->Get('extensions','ECrypt')->Encrypt(json_encode($search_rules))
                                            );
                                        }
                                    }
                                }
                                 */
                            } else {
                                
                                if ($uid)  APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');
                                
                                $users_utm = APP::Module('DB')->Select(
                                    APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],
                                    ['DISTINCT(utm_term)'],'users_utm_index',
                                    [
                                        ['utm_source', '=', $_POST['settings']['value']['source'], PDO::PARAM_STR],
                                        ['utm_medium', '=', $_POST['settings']['value']['medium'], PDO::PARAM_STR],
                                        ['utm_campaign', '=', $_POST['settings']['value']['campaign'], PDO::PARAM_STR]
                                    ]
                                );

                                foreach ($users_utm as $utm_value) {
                                    $search_rules = [
                                        'logic' => 'intersect',
                                        'rules' => [
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'source',
                                                    'value' => $_POST['settings']['value']['source']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'medium',
                                                    'value' => $_POST['settings']['value']['medium']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'campaign',
                                                    'value' => $_POST['settings']['value']['campaign']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'term',
                                                    'value' => $utm_value
                                                ]
                                            ]
                                        ]
                                    ];

                                    if(!$this->settings['module_analytics_cache']){
                                        $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                    }else{
                                        $cache_id = md5(json_encode($search_rules));
                                        if (!$utm_uid = APP::Module('Cache')->memcache->get($cache_id)) {
                                            $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                            APP::Module('Cache')->memcache->set($cache_id, $utm_uid, false, 180);
                                        }
                                    }

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_utm_roi_tmp (user) VALUES (' . implode('),(', $utm_uid) . ')');

                                    $revenue_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Billing')->settings['module_billing_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN], ['SUM(billing_invoices.amount)'],
                                        'billing_invoices',
                                        [
                                            ['billing_invoices.user_id', 'IN', 'SELECT analytics_utm_roi_tmp.user FROM analytics_utm_roi_tmp', PDO::PARAM_INT],
                                            ['billing_invoices.state', '=', 'success', PDO::PARAM_STR],
                                            ['billing_invoices.amount', '!=', '0', PDO::PARAM_INT]
                                        ]
                                    );

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                    // Вычисление расхода
                                    $utm_labels = [
                                        'source' => $_POST['settings']['value']['source'],
                                        'medium' => $_POST['settings']['value']['medium'],
                                        'campaign' => $_POST['settings']['value']['campaign'],
                                        'term' => $utm_value
                                    ];

                                    foreach ([
                                        'source',
                                        'medium',
                                        'campaign',
                                        'term',
                                        'content'
                                    ] as $utm_key) {
                                        foreach (APP::Module('DB')->Select(
                                            APP::Module('Users')->settings['module_users_db_connection'],
                                            ['fetchAll', PDO::FETCH_COLUMN],
                                            [
                                                'utm_source',
                                                'utm_medium',
                                                'utm_campaign',
                                                'utm_term',
                                                'utm_content',
                                                'utm_label',
                                                'utm_alias'
                                            ],
                                            'costs_extra', [['utm_label', '=', $utm_key, PDO::PARAM_STR]]
                                        ) as $value) {
                                            $utm_alias_value = $value['utm_' . $value['utm_label']];
                                            $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                            $utm_alias_data = [];

                                            if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                            if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                            if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                            if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                            if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                            $use_utm_alias = true;

                                            foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                            }

                                            if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                        }
                                    }

                                    foreach ($utm_labels as $label => $value) {
                                        $cost_utm[] = ['utm_' . $label, '=', $value, PDO::PARAM_STR];
                                    }

                                    $cost_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Costs')->settings['module_costs_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN],['SUM(amount)'],'costs',$cost_utm
                                    );
                                    //////////////////////////////////////

                                    $out[md5($_POST['settings']['value']['source'] . $_POST['settings']['value']['medium'] . $_POST['settings']['value']['campaign'] . $utm_value . time())] = [
                                        'name' => $utm_value,
                                        'stat' => [
                                            'cost' => $cost_value,
                                            'revenue' => $revenue_value,
                                            'profit' => $revenue_value - $cost_value,
                                            'roi' => ($cost_value ? round((($revenue_value - $cost_value) / $cost_value) * 100, 2) : 0)
                                        ],
                                        'rules' => htmlentities(json_encode($search_rules)),
                                        'ref' => APP::Module('Crypt')->Encode(json_encode($search_rules))
                                    ];
                                }
                            }
                            break;
                        case 'term':
                            if (false) {
                                /*
                                $users_utm_where = Array(
                                    Array('admin_pult_ref.users_utm.num', '=', '1')
                                );

                                if ($uid) $users_utm_where[] = Array('admin_pult_ref.users_utm.user_id', 'IN', 'SELECT user_id FROM utm_roi_tmp');

                                $users_utm = Shell::$app->Get('extensions','EORM')->SelectV2(
                                    'pult_ref', Array('fetchAll', PDO::FETCH_ASSOC),
                                    Array(
                                        'admin_pult_ref.users_utm.user_id',
                                        'admin_pult_ref.users_utm.item',
                                        'admin_pult_ref.users_utm.value'
                                    ),
                                    'admin_pult_ref.users_utm',
                                    $users_utm_where
                                );

                                if ($uid) APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                $users = Array();

                                foreach ($users_utm as $item) {
                                    $users[$item['user_id']][$item['item']] = $item['value'];
                                }

                                unset($users_utm);
                                $label_list = Array();

                                foreach ($users as $item) {
                                    if (((($item['source'] == $_POST['settings']['value']['source']) && ($item['medium'] == $_POST['settings']['value']['medium']) && ($item['campaign'] == $_POST['settings']['value']['campaign']) && ($item['term'] == $_POST['settings']['value']['term'])))) {
                                        if (array_search($item['content'], $label_list) === false) {
                                            $search_rules = Array(
                                                'logic' => 'intersect',
                                                'rules' => Array(
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'source',
                                                            'value' => $item['source']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'medium',
                                                            'value' => $item['medium']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'campaign',
                                                            'value' => $item['campaign']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'term',
                                                            'value' => $item['term']
                                                        )
                                                    ),
                                                    Array(
                                                        'method' => 'utm',
                                                        'settings' => Array(
                                                            'num' => '1',
                                                            'name' => 'content',
                                                            'value' => $item['content']
                                                        )
                                                    )
                                                )
                                            );

                                            $utm_uid = Shell::$app->Get('extensions','ERef')->Search($search_rules);
                                            $target_uid = $uid ? array_intersect($uid, $utm_uid) : $utm_uid;
                                            Shell::$app->Get('extensions','EModDB')->Open('pult_ref')->query('INSERT INTO utm_roi_tmp (user_id) VALUES (' . implode('),(', $target_uid) . ')');

                                            $revenue_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_billing',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(admin_pult_billing.invoices.amount)'
                                                ),
                                                'admin_pult_billing.invoices',
                                                Array(
                                                    Array('admin_pult_billing.invoices.usr_id', 'IN', 'SELECT admin_pult_ref.utm_roi_tmp.user_id FROM admin_pult_ref.utm_roi_tmp'),
                                                    Array('admin_pult_billing.invoices.state', '=', 'success'),
                                                    Array('admin_pult_billing.invoices.amount', '!=', '0')
                                                )
                                            );

                                            APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                            // Вычисление расхода
                                            $utm_labels = Array(
                                                'source' => $item['source'],
                                                'medium' => $item['medium'],
                                                'campaign' => $item['campaign'],
                                                'term' => $item['term'],
                                                'content' => $item['content']
                                            );

                                            foreach (Array(
                                                'source',
                                                'medium',
                                                'campaign',
                                                'term',
                                                'content'
                                            ) as $utm_key) {
                                                foreach (Shell::$app->Get('extensions','EORM')->SelectV2(
                                                    'pult_ref',
                                                    Array(
                                                        'fetchAll',
                                                        PDO::FETCH_ASSOC
                                                    ),
                                                    Array(
                                                        'utm_source',
                                                        'utm_medium',
                                                        'utm_campaign',
                                                        'utm_term',
                                                        'utm_content',
                                                        'utm_label',
                                                        'utm_alias'
                                                    ),
                                                    'cost_extra',
                                                    Array(
                                                        Array('utm_label', '=', $utm_key)
                                                    )
                                                ) as $value) {
                                                    $utm_alias_value = $value['utm_' . $value['utm_label']];
                                                    $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                                    $utm_alias_data = Array();

                                                    if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                                    if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                                    if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                                    if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                                    if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                                    $use_utm_alias = true;

                                                    foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                        if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                                    }

                                                    if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                                }
                                            }

                                            foreach ($utm_labels as $label => $value) {
                                                $cost_utm[] = Array('utm_' . $label, '=', $value);
                                            }

                                            $cost_value = (int) Shell::$app->Get('extensions','EORM')->SelectV2(
                                                'pult_ref',
                                                Array(
                                                    'fetchColumn', 0
                                                ),
                                                Array(
                                                    'SUM(cost)'
                                                ),
                                                'cost',
                                                $cost_utm
                                            );
                                            //////////////////////////////////////

                                            $label_list[] = $item['content'];

                                            $out[md5($item['source'] . $item['medium'] . $item['campaign'] . $item['term'] . $item['content'] . time())] = Array(
                                                'name' => $item['content'],
                                                'stat' => Array(
                                                    'cost' => $cost_value,
                                                    'revenue' => $revenue_value,
                                                    'profit' => $revenue_value - $cost_value,
                                                    'roi' => round((($revenue_value - $cost_value) / $cost_value) * 100, 2)
                                                ),
                                                'rules' => htmlentities(json_encode($search_rules)),
                                                'ref' => Shell::$app->Get('extensions','ECrypt')->Encrypt(json_encode($search_rules))
                                            );
                                        }
                                    }
                                }
                                 */
                            } else {
                                
                                if ($uid)  APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');
                                
                                $users_utm = APP::Module('DB')->Select(
                                    APP::Module('Users')->settings['module_users_db_connection'], ['fetchAll', PDO::FETCH_COLUMN],
                                    ['DISTINCT(utm_content)'],'users_utm_index',
                                    [
                                        ['utm_source', '=', $_POST['settings']['value']['source'], PDO::PARAM_STR],
                                        ['utm_medium', '=', $_POST['settings']['value']['medium'], PDO::PARAM_STR],
                                        ['utm_campaign', '=', $_POST['settings']['value']['campaign'], PDO::PARAM_STR],
                                        ['utm_term', '=', $_POST['settings']['value']['term'], PDO::PARAM_STR]
                                    ]
                                );

                                foreach ($users_utm as $utm_value) {
                                    $search_rules = [
                                        'logic' => 'intersect',
                                        'rules' => [
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'source',
                                                    'value' => $_POST['settings']['value']['source']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'medium',
                                                    'value' => $_POST['settings']['value']['medium']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'campaign',
                                                    'value' => $_POST['settings']['value']['campaign']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'term',
                                                    'value' => $_POST['settings']['value']['term']
                                                ]
                                            ],
                                            [
                                                'method' => 'utm',
                                                'settings' => [
                                                    'num' => '1',
                                                    'item' => 'content',
                                                    'value' => $utm_value
                                                ]
                                            ]
                                        ]
                                    ];

                                    if(!$this->settings['module_analytics_cache']){
                                        $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                    }else{
                                        $cache_id = md5(json_encode($search_rules));
                                        if (!$utm_uid = APP::Module('Cache')->memcache->get($cache_id)) {
                                            $utm_uid = APP::Module('Users')->UsersSearch($search_rules);
                                            APP::Module('Cache')->memcache->set($cache_id, $utm_uid, false, 180);
                                        }
                                    }

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_utm_roi_tmp (user) VALUES (' . implode('),(', $utm_uid) . ')');

                                    $revenue_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Billing')->settings['module_billing_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN], ['SUM(billing_invoices.amount)'],
                                        'billing_invoices',
                                        [
                                            ['billing_invoices.user_id', 'IN', 'SELECT analytics_utm_roi_tmp.user FROM analytics_utm_roi_tmp', PDO::PARAM_INT],
                                            ['billing_invoices.state', '=', 'success', PDO::PARAM_STR],
                                            ['billing_invoices.amount', '!=', '0', PDO::PARAM_INT]
                                        ]
                                    );

                                    APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');

                                    // Вычисление расхода
                                    $utm_labels = [
                                        'source' => $_POST['settings']['value']['source'],
                                        'medium' => $_POST['settings']['value']['medium'],
                                        'campaign' => $_POST['settings']['value']['campaign'],
                                        'term' => $_POST['settings']['value']['term'],
                                        'content' => $utm_value
                                    ];

                                    foreach ([
                                        'source',
                                        'medium',
                                        'campaign',
                                        'term',
                                        'content'
                                    ] as $utm_key) {
                                        foreach (APP::Module('DB')->Select(
                                            APP::Module('Users')->settings['module_users_db_connection'],
                                            ['fetchAll', PDO::FETCH_COLUMN],
                                            [
                                                'utm_source',
                                                'utm_medium',
                                                'utm_campaign',
                                                'utm_term',
                                                'utm_content',
                                                'utm_label',
                                                'utm_alias'
                                            ],
                                            'costs_extra', [['utm_label', '=', $utm_key, PDO::PARAM_STR]]
                                        ) as $value) {
                                            $utm_alias_value = $value['utm_' . $value['utm_label']];
                                            $value['utm_' . $value['utm_label']] = $value['utm_alias'];

                                            $utm_alias_data = [];

                                            if ($value['utm_source']) $utm_alias_data['source'] = $value['utm_source'];
                                            if ($value['utm_medium']) $utm_alias_data['medium'] = $value['utm_medium'];
                                            if ($value['utm_campaign']) $utm_alias_data['campaign'] = $value['utm_campaign'];
                                            if ($value['utm_term']) $utm_alias_data['term'] = $value['utm_term'];
                                            if ($value['utm_content']) $utm_alias_data['content'] = $value['utm_content'];

                                            $use_utm_alias = true;

                                            foreach ($utm_labels as $utm_label_key => $utm_label_value) {
                                                if (($utm_alias_data[$utm_label_key] != $utm_label_value) && (isset($utm_alias_data[$utm_label_key]))) $use_utm_alias = false;
                                            }

                                            if (($use_utm_alias) && (isset($utm_labels[$value['utm_label']]))) $utm_labels[$value['utm_label']] = $utm_alias_value;
                                        }
                                    }

                                    foreach ($utm_labels as $label => $value) {
                                        $cost_utm[] = ['utm_' . $label, '=', $value, PDO::PARAM_STR];
                                    }

                                    $cost_value = (int) APP::Module('DB')->Select(
                                        APP::Module('Costs')->settings['module_costs_db_connection'],
                                        ['fetch', PDO::FETCH_COLUMN],['SUM(amount)'],'costs',$cost_utm
                                    );
                                    //////////////////////////////////////

                                    $out[md5($_POST['settings']['value']['source'] . $_POST['settings']['value']['medium'] . $_POST['settings']['value']['campaign'] . $_POST['settings']['value']['term'] . $utm_value . time())] = [
                                        'name' => $utm_value,
                                        'stat' => [
                                            'cost' => $cost_value,
                                            'revenue' => $revenue_value,
                                            'profit' => $revenue_value - $cost_value,
                                            'roi' => ($cost_value ? round((($revenue_value - $cost_value) / $cost_value) * 100, 2) : 0)
                                        ],
                                        'rules' => htmlentities(json_encode($search_rules)),
                                        'ref' => APP::Module('Crypt')->Encode(json_encode($search_rules))
                                    ];
                                }
                            }
                            break;
                    }

                    $sort_index = [];

                    switch ($sort[0]) {
                        case 'default': foreach ($out as $key => $value) $sort_index[$key] = $value['name']; break;
                        case 'cost': foreach ($out as $key => $value) $sort_index[$key] = $value['stat']['cost']; break;
                        case 'revenue': foreach ($out as $key => $value) $sort_index[$key] = $value['stat']['revenue']; break;
                        case 'profit': foreach ($out as $key => $value) $sort_index[$key] = $value['stat']['profit']; break;
                        case 'roi': foreach ($out as $key => $value) $sort_index[$key] = $value['stat']['roi']; break;
                    }

                    switch ($sort[1]) {
                        case 'asc': asort($sort_index); break;
                        case 'desc': arsort($sort_index); break;
                    }

                    header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
                    header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
                    header('Content-Type: application/json');

                    echo json_encode(['labels' => $out,'sort' => array_keys($sort_index)]);

                    break;
                default:
                    APP::Render('analytics/admin/utm-roi', 'include', ['uid' => $settings['uid'],'rules' => $settings['rules']]);
                    break;
            }
        }else{
            APP::Render('analytics/admin/utm-roi', 'include', ['rules' => $settings['rules']]);
        }

        APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_utm_roi_tmp');
    }
    
    public function OpenLettersPct(){
        $pct = [];

        if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $uid = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $uid = APP::Module('Users')->UsersSearch($rules);
        }

        for ($x = 0; $x <= 100; $x ++) $pct[$x] = 0;
        
        $users =  APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'],
            ['fetchAll', PDO::FETCH_ASSOC], ['pct'] , 'mail_open_pct',
            [['user', 'IN', $uid, PDO::PARAM_INT]]
        );
        
        $avg =  APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'],
            ['fetch', PDO::FETCH_COLUMN], ['ROUND(AVG(pct),2) AS avg'] , 'mail_open_pct',
            [['user', 'IN', $uid, PDO::PARAM_INT]]
        );

        $urls = [];
        
        foreach ($users as $user){
            $url_rule = $rules;
            $pct[$user['pct']] = $pct[$user['pct']] + 1;
            $url_rule['rules'][] = [
                "method" => "mail_open_pct",
                "settings" => [
                    "from" => $user['pct'],
                    "to" => $user['pct']
                ]
            ];

            $urls[$user['pct']] = APP::Module('Crypt')->Encode(json_encode($url_rule));
        }

        $out = compact('pct','avg');
        $out['url'] = $urls;

        APP::Render('analytics/admin/open_letter_pct', 'include', $out);
    }
    
    public function LetterOpenTime(){
        $users = [];
        
        if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $uid = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $uid = APP::Module('Users')->UsersSearch($rules);
        }

        $sort_time = 'asc';
        if(isset($_POST['sort_time'])){
            $sort_time = $_POST['sort_time'];
        }

        $data = [];
        foreach(APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'],
            ['fetchAll', PDO::FETCH_ASSOC],
            ['HOUR(cr_date) as cr_date','COUNT(id) as count'], 'mail_events',
            [['user', 'IN', $uid, PDO::PARAM_INT], ['event', '=', 'open', PDO::PARAM_STR]],
            false,['HOUR(cr_date)'],false,['cr_date', $sort_time]
        ) as $item){
            isset($data[$item['cr_date']]) ? $data[$item['cr_date']] += $item['count'] : $data[$item['cr_date']] = $item['count'] ;
            
        }

        $time_list = [];
        $chart = [];
        foreach ($data as $key => $value) {
            $filter = $rules;

            $filter['rules'][] = [
                'method'    =>  'mail_open_time',
                'settings'  => [
                    'value' => $key
                ]
            ];

            $time_list[] = [
                'time'      => $key,
                'count'     => $value,
                'filter'    => APP::Module('Crypt')->Encode(json_encode($filter))
            ];

            $chart[] = [
                'label' => $key . ' час.',
                'data'  => (int)$value,
                'color' => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT)
            ];
        }

        APP::Render(
            'analytics/admin/open_time',
            'include',
            [
                'data'      => $time_list,
                'rules'     => json_encode($rules),
                'sort_time' => $sort_time,
                'chart'     => json_encode($chart)
            ]
        );
    }
    
    public function RfmBilling() {
        
        if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $uid = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $uid = APP::Module('Users')->UsersSearch($rules);
        }
        
        if(isset($_POST['dates_from']) && $_POST['dates_from']){
            $this->config['rfm']['dates'] = [
                '≤30' => [
                    strtotime($_POST['dates_from'] . ' 23:59:59') - 2592000,
                    strtotime($_POST['dates_from'] . ' 23:59:59')
                ],
                '31-60' => [
                    strtotime($_POST['dates_from']. ' 23:59:59') - 5184000,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 2592000,
                ],
                '61-120' => [
                    strtotime($_POST['dates_from']. ' 23:59:59') - 10368000,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 5184000,
                ],
                '121-365' => [
                    strtotime($_POST['dates_from']. ' 23:59:59') - 31536000,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 10368000,
                ],
                '365+' => [
                    0,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 31536000,
                ]
            ];
        }


        // Сохранение целевых пользователей во временную таблицу
        APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_rfm_tmp (user) VALUES (' . implode('),(', $uid) . ')');

        $orders = APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'],
            ['fetchAll',PDO::FETCH_ASSOC], ['user_id','UNIX_TIMESTAMP(cr_date) as cr_date'],
            'billing_invoices',
            [
                ['state', '=', 'success', PDO::PARAM_STR],
                ['amount', '!=', '0', PDO::PARAM_INT],
                ['user_id', 'IN', 'SELECT user FROM analytics_rfm_tmp', PDO::PARAM_INT]
            ]
        );

        $orders2 = $orders;

        // Удаление целевых пользователей из временной таблицы
        APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_rfm_tmp');
        
        $clients = [];

        foreach ($orders as $order) {
            $clients[$order['user_id']][] = $order['cr_date'];
        }

        $raw_date = [];

        foreach ($clients as $client => $orders) {
            foreach ($this->config['rfm']['dates'] as $group_id => $group_range) {
                $max_orders = max($orders);
                if (($group_range[0] <= $max_orders) && ($group_range[1] >= $max_orders)){
                    $raw_date[$group_id][$client] = $orders;
                }else{
                    $raw_date[$group_id][$client] = [];
                }
            }
        }

        $out = [];

        foreach ($raw_date as $date_group_id => $clients) {
            foreach ($clients as $client_id => $orders) {
                foreach ($this->config['rfm']['units'] as $unit_group_id => $unit_group_range) {
                    $count_orders = count($orders);
                    if (($unit_group_range[0] <= $count_orders) && ($unit_group_range[1] >= $count_orders)){
                        $out[$unit_group_id][$date_group_id] = isset($out[$unit_group_id][$date_group_id]) ? $out[$unit_group_id][$date_group_id]+1 : 1;
                    }else{
                        $out[$unit_group_id][$date_group_id] = isset($out[$unit_group_id][$date_group_id]) ? $out[$unit_group_id][$date_group_id] : 0;
                    }
                }
            }
        }

        $totals['units'] = [];

        foreach ($out as $unit_id => $unit_data) {
            $totals['units'][$unit_id] =  !isset($totals['units'][$unit_id]) ? array_sum($unit_data) : $totals['units'][$unit_id] + array_sum($unit_data);
            foreach ($unit_data as $date_id => $date_data) {
                !isset($totals['dates'][$date_id]) ? $totals['dates'][$date_id] = $date_data : $totals['dates'][$date_id] + $date_data;
            }
        }

        $totals['summary'] = array_sum($totals['units']);

        $result = [
            'table1' => $this->config['rfm']['dates'],
            'report' => $out,
            'report2' => 0,
            'method' => 'rfm_billing',
            'totals' => $totals,
            'totals2'=>0,
            'filter' => $rules,
            'dates_from' => isset($_POST['dates_from']) && $_POST['dates_from'] ? $_POST['dates_from'].' 23:59:59' : date('Y-m-d', time()),
            'dates_two_from'=>0,
            'table2' => []
        ];


        //ДОПОЛНИТЕЛЬНАЯ ТАБЛИЦА СРАВНЕНИЯ
        if(isset($_POST['dates_two_from']) && $_POST['dates_two_from']){

            $this->config['rfm']['dates'] = [
                '≤30' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 2592000,
                    strtotime($_POST['dates_two_from']. ' 23:59:59')
                ],
                '31-60' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 5184000,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 2592000,
                ],
                '61-120' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 10368000,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 5184000,
                ],
                '121-365' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 31536000,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 10368000,
                ],
                '365+' => [
                    0,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 31536000,
                ]
            ];

            $result['table2'] = $this->config['rfm']['dates'];


            $clients2 = [];
            foreach ($orders2 as $order2) {
                $clients2[$order2['user_id']][] = $order2['cr_date'];
            }

            $raw_date2 = [];

            foreach ($clients2 as $client2 => $orders2) {
                foreach ($this->config['rfm']['dates'] as $group_id2 => $group_range2) {
                    $max_orders2 = max($orders2);
                    if (($group_range2[0] <= $max_orders2) && ($group_range2[1] >= $max_orders2)){
                        $raw_date2[$group_id2][$client2] = $orders2;
                    }else{
                        $raw_date2[$group_id2][$client2] = [];
                    }
                }
            }

            $out2 = [];

            foreach ($raw_date2 as $date_group_id2 => $clients2) {
                foreach ($clients2 as $client_id2 => $orders2) {
                    foreach ($this->config['rfm']['units'] as $unit_group_id2 => $unit_group_range2) {
                        $count_orders2 = count($orders2);
                        if (($unit_group_range2[0] <= $count_orders2) && ($unit_group_range2[1] >= $count_orders2)){
                            $out2[$unit_group_id2][$date_group_id2] = isset($out2[$unit_group_id2][$date_group_id2]) ? $out2[$unit_group_id2][$date_group_id2] + 1 : 1;
                        }else{
                            $out2[$unit_group_id2][$date_group_id2] = isset($out2[$unit_group_id2][$date_group_id2]) ? $out2[$unit_group_id2][$date_group_id2]:0;
                        }
                    }
                }
            }

            $totals2['units'] = [];

            foreach ($out2 as $unit_id2 => $unit_data2) {
                $totals2['units'][$unit_id2] = !isset($totals2['units'][$unit_id2]) ? array_sum($unit_data2) : $totals2['units'][$unit_id2] + array_sum($unit_data2);
                foreach ($unit_data2 as $date_id2 => $date_data2) {
                    $totals2['dates'][$date_id2] = !isset($totals2['dates'][$date_id2]) ?  $date_data2 : $totals2['dates'][$date_id2] + $date_data2;
                }
            }

            $totals2['summary'] = (array_sum($totals2['units']) ? array_sum($totals2['units']) : 1);
            $result['report2'] = $out2;
            $result['totals2'] = $totals2;
            $result['dates_two_from'] = $_POST['dates_two_from'].' 23:59:59';
        }

        APP::Render('analytics/admin/rfm/index', 'include', $result);
    }

    public function RfmMail(){
    	if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $uid = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $uid = APP::Module('Users')->UsersSearch($rules);
        }
        
        $event = APP::Module('Routing')->get['event'];
        $title = [
           'open'  => 'открытие писем',
           'click' => 'клики в письмах'
        ];

        if(isset($_POST['dates_from']) && $_POST['dates_from']){
            $this->config['rfm_mail']['dates'] = [
                '≤7' => [
                    strtotime($_POST['dates_from'] . ' 23:59:59') - 604800,
                    strtotime($_POST['dates_from'] . ' 23:59:59')
                ],
                '8-14' => [
                    strtotime($_POST['dates_from']. ' 23:59:59') - 1209600,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 604800,
                ],
                '15-30' => [
                    strtotime($_POST['dates_from']. ' 23:59:59') - 2592000,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 1209600,
                ],
                '31-60' => [
                    strtotime($_POST['dates_from']. ' 23:59:59') - 5184000,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 2592000,
                ],
                '61+' => [
                    0,
                    strtotime($_POST['dates_from']. ' 23:59:59') - 5184000,
                ]
            ];
        }

         // Сохранение целевых пользователей во временную таблицу
        APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('INSERT INTO analytics_rfm_tmp (user) VALUES (' . implode('),(', $uid) . ')');
        
        $users = APP::Module('DB')->Select(
            APP::Module('Mail')->settings['module_mail_db_connection'],
            ['fetchAll',PDO::FETCH_ASSOC], ['mail_events.user','UNIX_TIMESTAMP(mail_events.cr_date) as cr_date'],
            'mail_events',
            [
                ['mail_log.state', '=', 'success', PDO::PARAM_STR],
                ['mail_events.event', '=', $event, PDO::PARAM_STR],
                ['mail_events.user', 'IN', 'SELECT user FROM analytics_rfm_tmp', PDO::PARAM_INT]
            ],
            [
                'join/mail_log'=>[
                    ['mail_log.id', '=', 'mail_events.log']
                ]
            ]
        );

        // Удаление целевых пользователей из временной таблицы
        APP::Module('DB')->Open($this->settings['module_analytics_db_connection'])->query('TRUNCATE TABLE analytics_rfm_tmp');

        $clients = [];
        foreach ($users as $user) {
            $clients[$user['user']][] = $user['cr_date'];
        }

        $raw_date = [];

        foreach ($clients as $client => $cr_date) {
            foreach ($this->config['rfm_mail']['dates'] as $group_id => $group_range) {
                $max = max($cr_date);
                if (($group_range[0] <= $max) && ($group_range[1] >= $max)){
                    $raw_date[$group_id][$client] = $cr_date;
                }else{
                    $raw_date[$group_id][$client] = [];
                }
            }
        }

        $out = [];

        foreach ($raw_date as $date_group_id => $raw_clients) {
            foreach ($raw_clients as $client_id => $cr_date) {
                foreach ($this->config['rfm_mail']['units'] as $unit_group_id => $unit_group_range) {
                    $count = count($cr_date);
                    if (($unit_group_range[0] <= $count) && ($unit_group_range[1] >= $count)){
                        $out[$unit_group_id][$date_group_id] =  isset($out[$unit_group_id][$date_group_id]) ? $out[$unit_group_id][$date_group_id] + 1 : 1;
                    }else{
                        $out[$unit_group_id][$date_group_id] =  isset($out[$unit_group_id][$date_group_id]) ? $out[$unit_group_id][$date_group_id] : 0;
                    }
                }
            }
        }

        $totals = [
            'units' => [],
            'dates' => []
        ];

        foreach ($out as $unit_id => $unit_data) {
            $totals['units'][$unit_id] = isset($totals['units'][$unit_id]) ? $totals['units'][$unit_id] + array_sum($unit_data) : array_sum($unit_data);
            foreach ($unit_data as $date_id => $date_data) {
                $totals['dates'][$date_id] = isset($totals['dates'][$date_id]) ? $totals['dates'][$date_id] + $date_data : $date_data;
            }
        }

        $totals['summary'] = array_sum($totals['units']);

        $result = [
            'table1' => $this->config['rfm_mail']['dates'],
            'report' => $out,
            'report2' => 0,
            'totals2' => 0,
            'title'  => $title[$event],
            'method' => 'rfm_mail',
            'event'  => $event,
            'totals' => $totals,
            'filter' => $rules,
            'table2' => [],
            'dates_from' => isset($_POST['dates_from']) && $_POST['dates_from'] ? $_POST['dates_from'].' 23:59:59' : date('Y-m-d H:i:s', time()),
            'dates_two_from'=>0
        ];

        //ДОПОЛНИТЕЛЬНАЯ ТАБЛИЦА СРАВНЕНИЯ
        if(isset($_POST['dates_two_from']) && $_POST['dates_two_from']){

            $this->config['rfm_mail']['dates'] = [
                '≤7' => [
                    strtotime($_POST['dates_two_from'] . ' 23:59:59') - 604800,
                    strtotime($_POST['dates_two_from'] . ' 23:59:59')
                ],
                '8-14' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 1209600,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 604800,
                ],
                '15-30' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 2592000,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 1209600,
                ],
                '31-60' => [
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 5184000,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 2592000,
                ],
                '61+' => [
                    0,
                    strtotime($_POST['dates_two_from']. ' 23:59:59') - 5184000,
                ]
            ];

            $result['table2'] = $this->conf['rfm_mail']['dates'];

            $raw_date2 = [];

            foreach ($clients as $client2 => $cr_date) {
                foreach ($this->config['rfm_mail']['dates'] as $group_id2 => $group_range2) {
                    $max = max($cr_date);
                    if (($group_range2[0] <= $max) && ($group_range2[1] >= $max)){
                        $raw_date2[$group_id2][$client2] = $cr_date;
                    }else{
                        $raw_date2[$group_id2][$client2] = [];
                    }
                }
            }

            $out2 = [];

            foreach ($raw_date2 as $date_group_id2 => $clients2) {
                foreach ($clients2 as $client_id2 => $cr_date) {
                    foreach ($this->config['rfm_mail']['units'] as $unit_group_id2 => $unit_group_range2) {
                        $count = count($cr_date);
                        if (($unit_group_range2[0] <= $count) && ($unit_group_range2[1] >= $count)){
                            $out2[$unit_group_id2][$date_group_id2] = isset($out2[$unit_group_id2][$date_group_id2]) ? $out2[$unit_group_id2][$date_group_id2] + 1 : 1;
                        }else{
                            $out2[$unit_group_id2][$date_group_id2] = isset($out2[$unit_group_id2][$date_group_id2]) ? $out2[$unit_group_id2][$date_group_id2] : 0;
                        }
                    }
                }
            }

            $totals2 = Array();

            foreach ($out2 as $unit_id2 => $unit_data2) {
                $totals2['units'][$unit_id2] = isset($totals2['units'][$unit_id2]) ? $totals2['units'][$unit_id2] + array_sum($unit_data2) : array_sum($unit_data2);
                foreach ($unit_data2 as $date_id2 => $date_data2) {
                    $totals2['dates'][$date_id2] =  isset($totals2['dates'][$date_id2]) ? $totals2['dates'][$date_id2] + $date_data2 : $date_data2;
                }
            }

            $totals2['summary'] = array_sum($totals2['units']);
            $result['report2'] = $out2;
            $result['totals2'] = $totals2;
            $result['dates_two_from'] = $_POST['dates_two_from'].' 23:59:59';
        }

        APP::Render('analytics/admin/rfm/mail', 'include', $result);
    }
    
    public function Geo(){

        if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $users = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = [
                "logic" => "intersect",
                "rules" => [
                    [
                        "method" => "email",
                        "settings" => [
                            "logic" => "LIKE",
                            "value" => "%"
                        ]
                    ]
                ]
            ];
            $users = APP::Module('Users')->UsersSearch($rules);
        }

        $data = [];
        foreach(APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'],
            ['fetchAll',PDO::FETCH_ASSOC], ['value', 'user_id', 'item'],
            'user_about',
            [
                ['user', 'IN', $users, PDO::PARAM_INT],
                ['item', 'IN', ['city_lon', 'city_lat'], PDO::PARAM_STR]
            ]
        ) as $item){
            switch ($item['item']) {
                case 'city_lat':
                    $data[$item['user_id']][0] = $item['value'];
                    break;
                case 'city_lon':
                    $data[$item['user_id']][1] = $item['value'];
                    break;
            }
        }

        APP::Render('analytics/admin/geo/index', 'include', ['maps'=>json_encode($data), 'rules'=>json_encode($rules)]);
    }
    
    public function APIGetGeoCity(){
        $users = [];

        if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $users = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = ['logic' => 'intersect'];
        }

        $location = [];
        $url = [];
        $amount = [];

        $uids = APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'],
            ['fetchAll',PDO::FETCH_ASSOC], ['user'],
            'user_about',
            [
                ['item', '=', 'country_name_ru', PDO::PARAM_STR],
                ['value', '=', $_POST['country_name_ru'], PDO::PARAM_STR],
                ['user', 'IN', $users, PDO::PARAM_INT]   
            ]
        );

        $location['Не определенно'] = count($uids);
        $user_def = [];

        foreach(APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'],
            ['fetchAll', PDO::FETCH_ASSOC], ['user', 'value'],
            'user_about',
            [
                ['item', '=', 'city_name_ru', PDO::PARAM_STR],
                ['user', 'IN', $uids, PDO::PARAM_INT]   
            ]
        ) as $item){
            if ($item['value']){
                $location['Не определенно'] = $location['Не определенно'] - 1;

                $filter = $rules;
                $filter['rules'][] = [
                    "method" => "city",
                    "settings" => [
                        "logic" => "=",
                        "value" => $item['value']
                    ]
                ];

                $location[$item['value']] = $location[$item['value']] + 1;
                $url[$item['value']] = APP::Module('Crypt')->Encode(json_encode($filter));
                $amount[$item['value']][] = $item['user_id'];
                $user_def[] = $item['user_id'];
            }
        }

        foreach ($amount as $city => $users) {
            $amount[$city] = (int) APP::Module('DB')->Select(
                APP::Module('Billing')->settings['module_billing_db_connection'],
                ['fetch',PDO::FETCH_COLUMN], ['SUM(amount)'],
                'billing_invoices',
                [
                    ['state', '=', 'success', PDO::PARAM_STR],
                    ['user_id', 'IN', $users, PDO::PARAM_INT]
                    
                ]
            );
        }

        $amount['Не определенно'][] = (int) APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'],
            ['fetch',PDO::FETCH_COLUMN], ['SUM(amount)'],
            'billing_invoices',
            [
                ['state', '=', 'success', PDO::PARAM_STR],
                ['user_id', 'IN', array_diff($uids, $user_def), PDO::PARAM_INT]
                
            ]
        );

        $url['Не определенно'] = false;
        arsort($location);
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode(['url'=>$url, 'location'=>$location, 'amount'=>$amount]);
        exit;
    }

    public function APIGetGeoCountry(){
        $users = [];

        if(isset($_POST['rules'])){
            $rules = json_decode($_POST['rules'], true);
            $users = APP::Module('Users')->UsersSearch($rules);
        }else{
            $rules = ['logic' => 'intersect'];
        }

        $location = [];
        $url = [];
        $amount = [];

        $location['Не определенно'] = count($users);
        $user_def = [];

        foreach(APP::Module('DB')->Select(
            APP::Module('Users')->settings['module_users_db_connection'],
            ['fetchAll',PDO::FETCH_ASSOC], ['value', 'user', 'item'],
            'user_about',[['item', '=', 'country_name_ru', PDO::PARAM_STR],['user', 'IN', $users, PDO::PARAM_INT]],
            false, ['user']
        ) as $item){
            if($item['value']){
                $location['Не определенно'] = $location['Не определенно'] - 1;

                $filter = $rules;
                $filter['rules'][] = [
                    "method"=>"country",
                    "settings"=>[
                        "logic" => "=",
                        "value" => $item['value']
                    ]
                ];

                $location[$item['value']] = $location[$item['value']] + 1;
                $url[$item['value']] = APP::Module('Crypt')->Encode(json_encode($filter));
                $amount[$item['value']][] = $item['user_id'];
                $user_def[] = $item['user_id'];
            }
        }

        $amount['Не определенно'] = (int) APP::Module('DB')->Select(
            APP::Module('Billing')->settings['module_billing_db_connection'],
            ['fetch',PDO::FETCH_COLUMN], ['SUM(amount)'],
            'billing_invoices',
            [
                ['state', '=', 'success', PDO::PARAM_STR],
                ['user_id', 'IN', array_diff($users, $user_def), PDO::PARAM_INT]
                
            ]
        );

        foreach ($amount as $city => $users_list) {
            $amount[$city] = (int) APP::Module('DB')->Select(
                APP::Module('Billing')->settings['module_billing_db_connection'],
                ['fetch',PDO::FETCH_COLUMN], ['SUM(amount)'],
                'billing_invoices',
                [
                    ['state', '=', 'success', PDO::PARAM_STR],
                    ['user_id', 'IN', $users_list, PDO::PARAM_INT]
                    
                ]
            );
        }

        $url['Не определенно'] = false;

        arsort($location);
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode(['url'=>$url, 'location'=>$location, 'amount'=>$amount, 'tmp'=>array_diff($users, $user_def)]);
        exit;
    }
    
    public function AdminSales(){
        if (isset($_POST['do'])) {
            switch ($_POST['do']) {
                case 'comments':
                    Shell::$app->Get('components','CUtils')->Headers(
                        Array(
                            'Access-Control-Allow-Origin' => '*',
                            'content-type' => 'application/json'
                        )
                    );

                    echo json_encode(Shell::$app->Get('extensions','EORM')->SelectV2(
                        'pult_ref',
                        Array(
                            'fetchAll',
                            PDO::FETCH_ASSOC
                        ),
                        Array(
                            'cr_date', 'comment'
                        ),
                        'user_comments',
                        Array(
                            Array('user_id', '=', $_POST['user'])
                        )
                    ));
                    exit;
                    break;
                case 'post-comment':
                    Shell::$app->Get('extensions','ERef')->modules['users']->CommentInsert($_POST['user'], $_POST['comment']);

                    Shell::$app->Get('components','CUtils')->Headers(
                        Array(
                            'Access-Control-Allow-Origin' => '*',
                            'content-type' => 'application/json'
                        )
                    );

                    echo json_encode(Array(
                        'full' => $_POST['comment'],
                        'short' => Shell::$app->Get('components','CUtils')->mbCutString($_POST['comment'], 50)
                    ));
                    exit;
                    break;
                case 'invoices':
                    $out = Array();

                    $out = Array();

                    foreach (Shell::$app->Get('extensions','EORM')->SelectV2(
																								'pult_billing',
																								Array(
																												'fetchAll',
																												PDO::FETCH_ASSOC
																								),
																								Array(
                            'admin_pult_billing.invoices.*',
																												'admin_pult_billing.invoices_details.value as comment'
                        ),
																								'admin_pult_billing.invoices',
																								Array(
                            Array('admin_pult_billing.invoices.usr_id', '=', $_POST['user']),
                            Array('admin_pult_billing.invoices.state', 'IN', Array('success', 'processed', 'new'))
                        ),
																								Array(
																												'LEFT JOIN/admin_pult_billing.invoices_details' => Array(
																																Array('admin_pult_billing.invoices.id', '=', 'admin_pult_billing.invoices_details.inv_id'),
																																Array('admin_pult_billing.invoices_details.item', '=', '"comment"')
																												)
																								),
																								false,
																								false,
																								Array('admin_pult_billing.invoices.id', 'desc')
																				) as $user_invoice) {
                        $invoice_packages = Array();

                        foreach (Shell::$app->Get('extensions','EBilling')->modules['invoices']->GetInvoicesPackages(
                            Array(
                                'admin_pult_billing.invoices_packages.id',
                                'admin_pult_billing.invoices_packages.price',
                                'admin_pult_billing.products_packages_groups.name AS group_name',
                                'admin_pult_billing.products_packages.name AS name',
                            ),
                            Array(
                                Array('admin_pult_billing.invoices_packages.inv_id', '=', $user_invoice['id'])
                            ),
                            0,
                            Shell::$app->Get('extensions','EBilling')->modules['invoices']->CountInvoicesPackages(
                                Array(
                                    Array('inv_id', '=', $user_invoice['id'])
                                )
                            )
                        ) as $package) {
                            $package_products = Shell::$app->Get('extensions','EBilling')->modules['invoices']->GetInvoicesPackagesProducts(
                                Array(
                                    'admin_pult_billing.invoices_packages_products.prod_id',
                                    'admin_pult_billing.products.name',
                                ),
                                Array(
                                    Array('admin_pult_billing.invoices_packages_products.pack_id', '=', $package['id'])
                                ),
                                0,
                                Shell::$app->Get('extensions','EBilling')->modules['invoices']->CountInvoicesPackagesProducts(
                                    Array(
                                        Array('pack_id', '=', $package['id'])
                                    )
                                )
                            );

                            foreach ($package_products as $package_product) {
                                $out['user_invoices_products'][] = Array(
                                    'id' => $package_product['prod_id'],
                                    'name' => $package_product['name'],
                                    'price' => false,
                                    'state' => $user_invoice['state'] == 'success',
                                    'invoice' => $user_invoice['id']
                                );
                            }

                            $invoice_packages[] = Array(
                                'group_name' => $package['group_name'],
                                'name' => $package['name'],
                                'price' => $package['price'],
                                'products' => $package_products
                            );
                        }

                        $invoice_products = Shell::$app->Get('extensions','EBilling')->modules['invoices']->GetInvoicesProducts(
                            Array(
                                'admin_pult_billing.invoices_products.prod_id',
                                'admin_pult_billing.invoices_products.price',
                                'admin_pult_billing.products.name',
                            ),
                            Array(
                                Array('admin_pult_billing.invoices_products.inv_id', '=', $user_invoice['id'])
                            ),
                            0,
                            Shell::$app->Get('extensions','EBilling')->modules['invoices']->CountInvoicesProducts(
                                Array(
                                    Array('inv_id', '=', $user_invoice['id'])
                                )
                            )
                        );

																								$adm_comment = Shell::$app->Get('extensions','EORM')->SelectV2(
																												'pult_billing',
																												Array(
																																'fetchAll',
																																PDO::FETCH_ASSOC
																												),
																												Array(
																																'admin_pult_billing.invoices_comments.message', 'admin_pult_billing.invoices_comments.cr_date','admin_pult.users.email'
																												),
																												'admin_pult_billing.invoices_comments',
																												Array(
																																Array('admin_pult_billing.invoices_comments.inv_id', '=', $user_invoice['id']),
																																Array('admin_pult_billing.invoices_comments.usr_id', '!=', 19401)
																												),
																												Array(
																																'left join/admin_pult.users' => Array(
																																				Array('admin_pult.users.id', '=', 'admin_pult_billing.invoices_comments.usr_id')
																																)
																												)
																								);

                        foreach ($invoice_products as $invoice_product) {
                            $out['user_invoices_products'][] = Array(
                                'id' => $invoice_product['prod_id'],
                                'name' => $invoice_product['name'],
                                'price' => $invoice_product['price'],
                                'state' => $user_invoice['state'] == 'success',
                                'invoice' => $user_invoice['id'],
																																'comment' => $user_invoice['comment'],
																																'adm_comment' => $adm_comment
                            );
                        }

                        $out['user_invoices'][] = Array(
                            'main' => $user_invoice,
                            'packages' => $invoice_packages,
                            'products' => $invoice_products,
																												'comment' => $user_invoice['comment'],
																												'adm_comment' => $adm_comment
                        );
                    }

                    Shell::$app->Get('components','CUtils')->Headers(
                        Array(
                            'Access-Control-Allow-Origin' => '*',
                            'content-type' => 'application/json'
                        )
                    );

                    echo json_encode($out['user_invoices']);
                    exit;
                    break;
            }
        }

        ////////////////////////////////////////////////////////////////////////

        $sale = Array(
            3 => Array(23,24,25),
            5 => Array(179,180,181,182,183,184,185,186,187,188),
            7 => Array(158,159,160,161,162,163,164,165,166,167),
            9 => Array(254,255,256),
            11 => Array(203,204,205,206),
            2 => Array(87,88,89,90,92,93),
            13 => Array(114,115,116,117),
            14 => Array(127,128,129),
            15 => Array(64,65,66,67,70,71),
            16 => Array(30,31,32,33,35,36),
            17 => Array(98,99,100,101,102,103,104),
            19 => Array(266,267,268,270,271),
            20 => Array(291,292,293,294,295,297,298,299),
            35 => Array(395,396,397,398,400,401,402,403,404,406),
            48 => Array(488,489,490,491,492,495),
            49 => Array(87,88,89,90,92,93)
        );

        $processes = Array(
            3 => 'Гардероб на 100% Line (основной)',
            5 => 'Как выглядеть на 2 размера стройнее с помощью имиджмейкера (основной)',
            7 => 'Школа Имиджмейкеров (основной)',
            9 => '1000 интернет клиентов для имиджмейкера (основной)',
            11 => 'Портфолио для имиджмейкера за 1 месяц (основной)',
            2 => 'Шоппинг осень-зима под контролем стилиста',
            13 => '101 рецепт стильного гардероба в офис',
            14 => '5 секретов преображения Вашего гардероба',
            15 => 'Революция Цвета',
            16 => 'Верхняя одежда под контролем стилиста',
            17 => 'Головные уборы под контролем стилиста',
            19 => 'Шоппинг весна-лето под контролем стилиста',
            20 => 'MakeUp Must Have',
            35 => 'Новый год для вашего гардероба',
            48 => 'Революция Цвета v2 (викторина)',
            49 => 'Шоппинг осень-зима под контролем стилиста v2',
        );

        ////////////////////////////////////////////////////////////////////////

        $uid = Shell::$app->Get('extensions','ERef')->Search(json_decode($_POST['rules'], true));

        ////////////////////////////////////////////////////////////////////////

        $users = Array();

        foreach(Shell::$app->Get('extensions','EORM')->SelectV2(
            'pult_ref',
            Array(
                'fetchAll',
                PDO::FETCH_ASSOC
            ),
            Array(
                'value', 'user_id', 'item'
            ),
            'users_data',
            Array(
                Array('item', 'IN', Array('firstname', 'tel', 'lastname')),
                Array('user_id', 'IN', $uid),
            )
        ) as $data){
            $users[$data['user_id']][$data['item']] = $data['value'];
        }

        ////////////////////////////////////////////////////////////////////////

        $user_processes = Array();

        foreach(Shell::$app->Get('extensions','EORM')->SelectV2(
            'pult_processes',
            Array(
                'fetchAll',
                PDO::FETCH_ASSOC
            ),
            Array(
                'id', 'process_id', 'user_id'
            ),
            'user_processes',
            Array(
                Array('state', '=', 'active'),
                Array('process_id', 'IN', array_keys($sale)),
                Array('user_id', 'IN', $uid),
            )
        ) as $proc){
            $user_processes[$proc['user_id']] = Array($proc['process_id'], $proc['id']);
        }
        ?>
        <!DOCTYPE html>
        <html lang="ru">
          <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <title>salestool</title>

            <!-- Bootstrap core CSS -->
            <link href="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/bootstrap-3.3.6-dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/ie10-viewport-bug-workaround.css" rel="stylesheet">
            <link href="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/bootstrap-3.3.6-dist/css/non-responsive.css" rel="stylesheet">
            <script src="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/ie-emulation-modes-warning.js"></script>

            <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
            <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
              <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->

            <style>
                body {
                    margin: 25px;
                }
																.product-item p{
																				margin:0;
																				display: inline;
																}
																.product-item hr{
																				margin-top:5px;
																				margin-bottom:5px;
																}
																.product-item hr:last-child{
																				margin-top:0px;
																				margin-bottom:0px;
																				display: none;
																}
            </style>
          </head>

          <body>

            <!-- Begin page content -->
            <div class="container">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Телефон</th>
                            <th>Комментарии</th>
                            <th>Счета</th>
                            <th>Продажа</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?
                        foreach(Shell::$app->Get('extensions','EORM')->SelectV2(
                            'pult_ref',
                            Array(
                                'fetchAll',
                                PDO::FETCH_ASSOC
                            ),
                            Array(
                                'admin_pult_ref.users.id',
                                'admin_pult_ref.users.email',
                                'count(admin_pult_billing.invoices.id) as inv_cnt',
                            ),
                            'admin_pult_ref.users',
                            Array(
                                Array('admin_pult_ref.users.id', 'IN', $uid)
                            ),
                            Array(
                                'LEFT JOIN/admin_pult_billing.invoices' => Array(
                                    Array('admin_pult_billing.invoices.usr_id', '=', 'admin_pult_ref.users.id'),
                                    Array('admin_pult_billing.invoices.state', '=', '"success"')
                                )
                            ),
                            Array('admin_pult_ref.users.id'),
                            false,
                            Array('admin_pult_ref.users.id', 'DESC')
                        ) as $user) {
                            $comment_data = Shell::$app->Get('extensions','EORM')->SelectV2(
                                'pult_ref',
                                Array(
                                    'fetch', PDO::FETCH_ASSOC
                                ),
                                Array(
                                    'comment', 'cr_date'
                                ),
                                'user_comments',
                                Array(
                                    Array('user_id', '=', $user['id'])
                                ),
                                false, false, false, Array('id', 'DESC'), Array(0, 1)
                            );

																												$inv_pr_cnt = Shell::$app->Get('extensions','EORM')->SelectV2(
																																'pult_billing',
																																Array(
																																				'fetch', PDO::FETCH_ASSOC
																																),
																																Array(
																																				'count(id) as inv_cnt'
																																),
																																'invoices',
																																Array(
																																				Array('usr_id', '=', $user['id']),
																																				Array('state', 'IN', Array('processed', 'new'))
																																)
																												);

                            $sale_token = (int) $user_processes[$user['id']][1] ? Shell::$app->Get('extensions','EORM')->SelectV2(
                                'pult_processes',
                                Array(
                                    'fetchColumn', 0
                                ),
                                Array(
                                    'token'
                                ),
                                'tags_processes',
                                Array(
                                    Array('user_process_id', '=',  $user_processes[$user['id']][1]),
                                    Array('label_id', '=', "sendmail")
                                ),
                                false, false, false, Array('id', 'DESC'), Array(0, 1)
                            ) : 0;

                            $comment = $comment_data['comment'] ? Shell::$app->Get('components','CUtils')->mbCutString($comment_data['comment'], 50) . ' (' . $comment_data['cr_date'] . ')' : 'Нет';
                            $inv = $user['inv_cnt'] ? $user['inv_cnt'] : 'Нет';
																												$inv_pr = $inv_pr_cnt['inv_cnt'] ? $inv_pr_cnt['inv_cnt'] : 'Нет';
                            ?>
                            <tr <? if ($user['inv_cnt']) { ?>class="warning"<? } ?>>
                                <td><?= $user['id'] ?></td>
                                <td><a href="<?= Shell::$app->conf['protocol'].'://'.Shell::$app->conf['codename'] . Shell::$app->conf['domain'] . Shell::$app->conf['path'] .'user/' . $user['email'] ?>" target="_blank"><?= $user['email'] ?></a></td>
                                <td><?= $users[$user['id']]['firstname'] ?></td>
                                <td><?= $users[$user['id']]['lastname'] ?></td>
                                <td><?= $users[$user['id']]['tel'] ?></td>
                                <td><button data-email="<?= $user['email'] ?>" data-user="<?= $user['id'] ?>" class="user-comments <?= $user['id'] ?> btn btn-<? if (!$comment_data) { ?>default<? } else { ?>primary<? } ?>"><?= $comment ?></button></td>
                                <td><button data-email="<?= $user['email'] ?>" data-user="<?= $user['id'] ?>" class="user-invoices btn btn-<? if (!$user['inv_cnt'] && !$inv_pr_cnt['inv_cnt']) { ?>default disabled<? } else { ?>warning<? } ?>"><?= $inv ?>/<?= $inv_pr ?></button></td>
                                <?
                                if (!$sale_token) {
                                    ?><td>Нет</td><?
                                } else {
                                    ?><td><?= array_search((int) $sale_token, $sale[$user_processes[$user['id']][0]]) === false ? 'Нет' : '<b>Да</b>' ?><br><?= $processes[$user_processes[$user['id']][0]] ?></td><?
                                }
                                ?>
                            </tr>
                            <?
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Comments Modal -->
            <div class="modal fade" id="comments-modal" tabindex="-1" role="dialog" aria-labelledby="comments-modal-label">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="comments-modal-label">Комментарии - <span class="comment-user"></span></h4>
                        </div>
                        <div class="modal-body">
                            <div class="comments-list"></div>
                            <div class="form-comment">
                                <textarea class="form-control" id="new-user-comment" style="width: 100%; height: 80px"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="submit-comment btn btn-primary" data-user="0" type="button">Отправить комментарий</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Modal -->
            <div class="modal fade" id="invoices-modal" tabindex="-1" role="dialog" aria-labelledby="invoices-modal-label">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="invoices-modal-label">Счета -<span class="invoice-user"></span></h4>
                        </div>
                        <div class="modal-body">
                            invoices
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/jquery.min.js"></script>
            <script src="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>
            <script src="https://pult.glamurnenko.ru/public/WebApp/resources/views/full/langs/ru_RU/types/extensions/EProcesses/admin/scheme/ie10-viewport-bug-workaround.js"></script>

            <script>
                $('.user-comments').click(function(){
                    var user = $(this).data('user');
                    var email = $(this).data('email');

                    $('#comments-modal .comment-user').html(email);
                    $('#comments-modal .comments-list').html('Загрузка...');
                    $('#comments-modal .submit-comment').data('user', user);

                    $('#comments-modal').modal('show');

                    $.ajax({
                        type: 'post',
                        url: 'https://pult.glamurnenko.ru/analytics/sales',
                        data: {
                            do: 'comments',
                            user: user
                        },
                        success: function(data) {
                            $('#comments-modal .comments-list').empty();

                            if (data.length) {
                                $.each(data, function() {
                                    $('#comments-modal .comments-list').append('<div class="comment-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b>' + this.cr_date + '</b><br>' + this.comment + '</div>');
                                });
                            }
                        }
                    });
                });

                $('.submit-comment').click(function(){
                    var user = $(this).data('user');
                    var comment = $('#new-user-comment').val();

                    if (comment) {
                        $('.user-comments.' + user).html('Загрузка...');

                        $.ajax({
                            type: 'post',
                            url: 'https://pult.glamurnenko.ru/analytics/sales',
                            data: {
                                do: 'post-comment',
                                user: user,
                                comment: comment
                            },
                            success: function(data) {
                                $('.user-comments.' + user).html(data.short);
                            }
                        });

                        $('#new-user-comment').val('');
                        $('#comments-modal .comments-list').append('<div class="comment-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b>Только что</b><br>' + comment + '</div>');
                    }
                });

                $('.user-invoices:not(.disabled)').click(function(){
                    var user = $(this).data('user');
                    var email = $(this).data('email');

                    $('#invoices-modal .invoice-user').html(email);
                    $('#invoices-modal .modal-body').html('Загрузка...');

                    $('#invoices-modal').modal('show');

                    $.ajax({
                        type: 'post',
                        url: '<?= Shell::$app->conf['protocol'] ?>://<?= Shell::$app->conf['codename'] . Shell::$app->conf['domain'] . Shell::$app->conf['path'] ?>Evildevel/processlabel?do=invoices',
                        data: {
                            user: user
                        },
                        success: function(data) {
                            $('#invoices-modal .modal-body').empty();
																												var inv_s = '';
																												var inv_p = '';
																												var adm_comment = '';
																												var price =0;

                            if (data.length) {
																																$.each(data, function(){
																																				console.log(this.main);
																																				price = 0;
																																				adm_comment = '';

																																				$.each(this.products, function(){
																																								price = price + parseInt(this.price);
																																				});

																																				$.each(this.packages, function(){
																																								price = price + parseInt(this.price);
																																				});

																																				if(this.adm_comment){
																																								$.each(this.adm_comment, function(){
																																												adm_comment += '<div><div style="font-size:12px; display:inline-block;">'+this.cr_date+'<div style="font-size:10px;">('+this.email+')</div></div><div style="display:inline-block;margin-left: 10px;vertical-align: top;">'+this.message+'</div></div><hr>';
																																								});
																																				}

																																				if(this.main.state == 'success'){
																																								inv_s += '<div class="product-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b><a href="https://pult.glamurnenko.ru/billing/admin/invoices/info/' + this.main.id + '" target="_blank">' + this.main.id + '</a>/' + price + '</b>';
																																								$.each(this.products, function(){
																																												inv_s += '<br>' + this.name +' Цена: '+ this.price;
																																								});

																																								$.each(this.packages, function(i, k){
																																												inv_s += '<br>Комплект:' +k.name+' Цена: '+ k.price;
																																												$.each(k.products, function(){
																																																inv_s += '<br>' + this.name;
																																												});
																																								});

																																								(this.comment ? inv_s += '<br><b>Комментарий клиента:</b> <br>'+this.comment : '');
																																								(adm_comment ? inv_s += '<br><b>Комментарии менеджера: </b> <br>'+adm_comment : '');
																																								inv_s += '</div>';
																																				}else{

																																							inv_p += '<div class="product-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b><a href="https://pult.glamurnenko.ru/billing/admin/invoices/info/' + this.main.id + '" target="_blank">' + this.main.id + '</a>/' + price + '</b>';
																																								$.each(this.products, function(){
																																												inv_p += '<br>' + this.name +' Цена: '+ this.price;
																																								});

																																								$.each(this.packages, function(i, k){
																																												inv_p += '<br>Комплект:' +k.name+' Цена: '+ k.price;
																																												$.each(k.products, function(){
																																															inv_p += '<br>' + this.name;
																																												});
																																								});

																																								(this.comment ? inv_p += '<br><b>Комментарий клиента:</b> <br>'+this.comment : '');
																																								(adm_comment ? inv_p += '<br><b>Комментарии менеджера: </b> <br>'+adm_comment : '');
																																								inv_p += '</div>';
																																				}
																																});

																																if(inv_s){
																																				console.log(1);
																																				$('#invoices-modal .modal-body').append('<div style="font-size:18px;font-weight:bold;">Оплаченные счета</div>');
																																				$('#invoices-modal .modal-body').append(inv_s);
																																}

																																if(inv_p){
																																				$('#invoices-modal .modal-body').append('<div style="font-size:18px;font-weight:bold;">Неоплаченные счета</div>');
																																				$('#invoices-modal .modal-body').append(inv_p);
																																}
                            }
                        }
                    });
                });
            </script>
          </body>
        </html>
        <?
    }
    
    public function Settings() {
        APP::Render('analytics/admin/settings');
    }
    
    public function APIDashboard() {
        $tmp = [];
        
        $metrics = [
            'visits',
            'users',
            'pageviews'
        ];
        
        for ($x = $_POST['date']['to']; $x >= $_POST['date']['from']; $x = $x - 86400) {
            foreach ($metrics as $value) {
                $tmp[$value][date('d-m-Y', $x)] = 0;
            }
        }

        foreach (APP::Module('DB')->Select(
            $this->settings['module_analytics_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            [
                'visits',
                'pageviews',
                'users',
                'UNIX_TIMESTAMP(date) AS date'
            ], 
            'analytics_yandex_metrika',
            [['UNIX_TIMESTAMP(date)', 'BETWEEN', $_POST['date']['from'] . ' AND ' . $_POST['date']['to']]]
        ) as $data) {
            $d = date('d-m-Y', $data['date']);
            
            foreach ($metrics as $value) {
                $tmp[$value][$d] = $data[$value];
            }
        }

        $out = [];

        foreach ((array) $tmp as $source => $dates) {
            foreach ((array) $dates as $key => $value) {
                $out[$source][$key] = [strtotime($key) * 1000, $value];
            }
        }
        
        foreach ($out as $key => $value) {
            $out[$key] = array_values($value);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode($out);
        exit;
    }
    
    public function APIUpdateSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_db_connection']], [['item', '=', 'module_analytics_db_connection', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_tmp_dir']], [['item', '=', 'module_analytics_tmp_dir', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_max_execution_time']], [['item', '=', 'module_analytics_max_execution_time', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_yandex_client_id']], [['item', '=', 'module_analytics_yandex_client_id', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_yandex_client_secret']], [['item', '=', 'module_analytics_yandex_client_secret', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_yandex_counter']], [['item', '=', 'module_analytics_yandex_counter', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => isset($_POST['module_analytics_cache'])], [['item', '=', 'module_analytics_cache', PDO::PARAM_STR]]);
        
        APP::Module('Triggers')->Exec('update_analytics_settings', [
            'db_connection' => $_POST['module_analytics_db_connection'],
            'tmp_dir' => $_POST['module_analytics_tmp_dir'],
            'max_execution_time' => $_POST['module_analytics_max_execution_time'],
            'module_analytics_cache' => isset($_POST['module_analytics_cache']),
            'yandex_client_id' => $_POST['module_analytics_yandex_client_id'],
            'yandex_client_secret' => $_POST['module_analytics_yandex_client_secret'],
            'yandex_counter' => $_POST['module_analytics_yandex_counter']
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

}
