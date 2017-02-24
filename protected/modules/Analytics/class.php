<?
class Analytics {

    public $settings;

    function __construct($conf) {
        foreach ($conf['routes'] as $route)
            APP::Module('Routing')->Add($route[0], $route[1], $route[2]);
    }

    public function Init() {
        $this->settings = APP::Module('Registry')->Get([
            'module_analytics_db_connection',
            'module_analytics_tmp_dir',
            'module_analytics_max_execution_time',
            'module_analytics_memory_limit',
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
        set_time_limit($this->settings['module_analytics_max_execution_time']);
        ini_set('memory_limit', $this->settings['module_analytics_memory_limit']);
        // Выходные данные
        $out = [];

        // Фильтр для выборки пользователей
        $users_filter = json_decode($_POST['rules'], true);

        // Способ компоновки данных: day|week|month
        $group_by = isset($_POST['group']) ? $_POST['group'] : 'month';

        // UTM-метки
        $utm_labels = [];

        foreach ($users_filter['rules'] as $rule) {
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

        // Выборка целевых пользователей
        $users = APP::Module('Users')->UsersSearch($users_filter);

        // Сохранение целевых пользователей во временную таблицу
        APP::Module('DB')->Open(APP::Module('Users')->settings['module_users_db_connection'])->query('INSERT INTO analytics_cohorts_tmp (user) VALUES (' . implode('),(', $users) . ')');

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
                ['id'], 'users',[['id', 'IN', 'SELECT user FROM analytics_cohorts_tmp', PDO::PARAM_INT], ['UNIX_TIMESTAMP(cr_date)', 'BETWEEN', implode(' AND ', $values['date']), PDO::PARAM_STR]]
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
        APP::Module('DB')->Open(APP::Module('Users')->settings['module_users_db_connection'])->query('TRUNCATE TABLE analytics_cohorts_tmp');

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
                            APP::Module('DB')->Open(APP::Module('Users')->settings['module_users_db_connection'])->query('INSERT INTO analytics_cohorts_tmp (user) VALUES (' . implode('),(', $l_values['users']) . ')');

                            $out[(int) $index]['indicators'][(int) $l_index][$indicator] = (int) APP::Module('DB')->Select(
                                APP::Module('Billing')->settings['module_billing_db_connection'],
                                ['fetchAll', PDO::FETCH_COLUMN], ['SUM(amount)'], 'billing_invoices',
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
                APP::Module('Users')->settings['module_users_db_connection'],
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
                'users_cost_extra',
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
                APP::Module('Users')->settings['module_users_db_connection'],
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
                    $out[$key]['indicators'][$index]['roi'] = ($cost ? round((($out[$key]['indicators'][$index]['total_revenue'] - $cost) / $cost) * 100, 2) : $out[$key]['indicators'][$index]['total_revenue']);
                }
            }
        }

        APP::Render('analytics/admin/cohorts', 'include', $out);
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
        APP::Module('Registry')->Update(['value' => $_POST['module_analytics_memory_limit']], [['item', '=', 'module_analytics_memory_limit', PDO::PARAM_STR]]);
        
        APP::Module('Triggers')->Exec('update_analytics_settings', [
            'db_connection' => $_POST['module_analytics_db_connection'],
            'tmp_dir' => $_POST['module_analytics_tmp_dir'],
            'max_execution_time' => $_POST['module_analytics_max_execution_time'],
            'module_analytics_memory_limit' => $_POST['module_analytics_memory_limit'],
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