<?
class TaskManager {
    public $settings;
    private $search;
    
    function __construct($conf) {
        foreach ($conf['routes'] as $route) APP::Module('Routing')->Add($route[0], $route[1], $route[2]);
    }

    function Init() {
        $this->settings = APP::Module('Registry')->Get([
            'module_taskmanager_db_connection',
            'module_taskmanager_complete_lifetime',
            'module_taskmanager_max_execution_time',
            'module_taskmanager_memory_limit',
            'module_taskmanager_tmp_dir'
        ]);
        
        $this->search = new TaskManagerSearch();
    }
    
    public function Search($rules) {
        $out = Array();

        foreach ((array) $rules['rules'] as $rule) {
            $out[] = array_flip((array) $this->search->{$rule['method']}($rule['settings']));
        }
        
        if (array_key_exists('children', (array) $rules)) {
            $out[] = array_flip((array) $this->Search($rules['children']));
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
    
    public function Add($module, $method, $exec_date, $args = '[]', $token = false, $state = 'wait') {
        return APP::Module('DB')->Insert(
            $this->settings['module_taskmanager_db_connection'], 'task_manager',
            [
                'id'            => 'NULL',
                'token'         => $token ? [$token, PDO::PARAM_STR] : '""',
                'module'        => [$module, PDO::PARAM_STR],
                'method'        => [$method, PDO::PARAM_STR],
                'args'          => $args ? [$args, PDO::PARAM_STR] : '"[]"',
                'state'         => $state ? [$state, PDO::PARAM_STR] : '"wait"',
                'cr_date'       => 'NOW()',
                'exec_date'     => [$exec_date, PDO::PARAM_STR],
                'complete_date' => '"0000-00-00 00:00:00"',
            ]
        );
    }
    
    public function Exec() {
        $lock = fopen($this->settings['module_taskmanager_tmp_dir'] . '/module_taskmanager_exec.lock', 'w'); 
        
        if (flock($lock, LOCK_EX|LOCK_NB)) { 
            set_time_limit($this->settings['module_taskmanager_max_execution_time']);
            ini_set('memory_limit', $this->settings['module_taskmanager_memory_limit']);

            foreach (APP::Module('DB')->Select(
                $this->settings['module_taskmanager_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
                ['id', 'module', 'method', 'args'], 'task_manager',
                [
                    ['state', '=', 'wait', PDO::PARAM_STR],
                    ['UNIX_TIMESTAMP(exec_date)', '<=', time(), PDO::PARAM_INT]
                ]
            ) as $task) {
                APP::Module('Triggers')->Exec('taskmanager_exec', [
                    'task' => $task,
                    'result' => call_user_func_array([APP::Module($task['module']), $task['method']], json_decode($task['args'], true))
                ]);
                
                APP::Module('DB')->Update($this->settings['module_taskmanager_db_connection'], 'task_manager', [
                    'state' => 'complete',
                    'complete_date' => date('Y-m-d H:i:s', time())
                ], [['id', '=', $task['id'], PDO::PARAM_INT]]);
            }
        } else {
            exit;
        }
        
        fclose($lock);
    }
    
    public function GC() {
        $lock = fopen($this->settings['module_taskmanager_tmp_dir'] . '/module_taskmanager_gc.lock', 'w'); 
        
        if (flock($lock, LOCK_EX|LOCK_NB)) { 
            APP::Module('DB')->Delete(
                $this->settings['module_taskmanager_db_connection'], 'task_manager',
                [
                    ['state', '=', 'complete', PDO::PARAM_STR],
                    ['UNIX_TIMESTAMP(complete_date)', '<=', strtotime('-' . $this->settings['module_taskmanager_complete_lifetime']) , PDO::PARAM_INT]
                ]
            );
        } else { 
            exit;
        }
        
        fclose($lock);
    }
    
    
    public function Admin() {
        return APP::Render('taskmanager/admin/nav', 'content');
    }
    
    
    public function TaskManage() {
        APP::Render('taskmanager/admin/index');
    }
    
    public function AddTask() {
        APP::Render('taskmanager/admin/add');
    }
    
    public function EditTask() {
        APP::Render('taskmanager/admin/edit', 'include', APP::Module('DB')->Select(
            $this->settings['module_taskmanager_db_connection'], ['fetch', PDO::FETCH_ASSOC], 
            ['module', 'method', 'exec_date', 'args', 'token', 'state'], 'task_manager',
            [['id', '=', APP::Module('Crypt')->Decode(APP::Module('Routing')->get['task_id_hash']), PDO::PARAM_INT]]
        ));
    }
    
    public function Settings() {
        APP::Render('taskmanager/admin/settings');
    }
    
    public function APISearchTask() {
        $request = json_decode(file_get_contents('php://input'), true);
        $out = $this->Search(json_decode($request['search'], 1));
        $rows = [];
        
        $where[] = ['id', 'IN', $out, PDO::PARAM_INT];
        if($request['searchPhrase']){
            $where[] = ['token', 'LIKE', $request['searchPhrase'] . '%' ]; 
        }

        foreach (APP::Module('DB')->Select(
            $this->settings['module_taskmanager_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            ['id', 'token', 'module', 'method', 'args', 'state', 'cr_date', 'exec_date', 'complete_date'], 'task_manager',
            $where,false, false, false,
            [$request['sort_by'], $request['sort_direction']],
            $request['rows'] === -1 ? false : [($request['current'] - 1) * $request['rows'], $request['rows']]
        ) as $row) {
            $row['task_token'] = APP::Module('Crypt')->Encode($row['id']);
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
    
    public function APITaskList() {
        $rows = [];
        
        foreach (APP::Module('DB')->Select(
            $this->settings['module_taskmanager_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            ['id', 'token', 'module', 'method', 'args', 'state', 'cr_date', 'exec_date', 'complete_date'], 'task_manager',
            $_POST['searchPhrase'] ? [['token', 'LIKE', $_POST['searchPhrase'] . '%' ]] : false, 
            false, false, false,
            [array_keys($_POST['sort'])[0], array_values($_POST['sort'])[0]],
            $_POST['rowCount'] == -1 ? false : [($_POST['current'] - 1) * $_POST['rowCount'], $_POST['rowCount']]
        ) as $row) {
            $row['task_token'] = APP::Module('Crypt')->Encode($row['id']);
            array_push($rows, $row);
        }
        
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode([
            'current' => $_POST['current'],
            'rowCount' => $_POST['rowCount'],
            'rows' => $rows,
            'total' => APP::Module('DB')->Select($this->settings['module_taskmanager_db_connection'], ['fetchColumn', 0], ['COUNT(id)'], 'task_manager', $_POST['searchPhrase'] ? [['token', 'LIKE', $_POST['searchPhrase'] . '%' ]] : false)
        ]);
        exit;
    }
    
    public function APIAddTask() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (empty($_POST['module'])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }
        
        if (empty($_POST['method'])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }
        
        if (empty($_POST['exec_date'])) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        }

        if ($out['status'] == 'success') {
            $out['task_id'] = $this->Add(
                $_POST['module'], 
                $_POST['method'], 
                $_POST['exec_date'], 
                $_POST['args'] ? $_POST['args'] : '[]', 
                $_POST['token'] ? $_POST['token'] : false, 
                $_POST['state']
            );
            
            APP::Module('Triggers')->Exec('taskmanager_add', [
                'id' => $out['task_id'],
                'module' => $_POST['module'],
                'method' => $_POST['method'],
                'exec_date' => $_POST['exec_date'],
                'args' => $_POST['args'],
                'token' => $_POST['token'],
                'state' => $_POST['state']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode($out);
        exit;
    }
    
    public function APIUpdateTask() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];
        
        $task_id = APP::Module('Crypt')->Decode($_POST['task']);

        if (!APP::Module('DB')->Select(
            $this->settings['module_taskmanager_db_connection'], ['fetchColumn', 0], 
            ['COUNT(id)'], 'task_manager',
            [['id', '=', $task_id, PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }
        
        if (empty($_POST['module'])) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }
        
        if (empty($_POST['method'])) {
            $out['status'] = 'error';
            $out['errors'][] = 2;
        }
        
        if (empty($_POST['exec_date'])) {
            $out['status'] = 'error';
            $out['errors'][] = 3;
        }
        
        if ($out['status'] == 'success') {
            APP::Module('DB')->Update($this->settings['module_taskmanager_db_connection'], 'task_manager', [
                'module' => $_POST['module'],
                'method' => $_POST['method'],
                'exec_date' => $_POST['exec_date'],
                'args' => $_POST['args'] ? $_POST['args'] : '[]',
                'token' => $_POST['token'] ? $_POST['token'] : false,
                'state' => $_POST['state']
            ], [['id', '=', $task_id, PDO::PARAM_INT]]);
            
            APP::Module('Triggers')->Exec('taskmanager_update', [
                'id' => $task_id,
                'module' => $_POST['module'],
                'method' => $_POST['method'],
                'exec_date' => $_POST['exec_date'],
                'args' => $_POST['args'],
                'token' => $_POST['token'],
                'state' => $_POST['state']
            ]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode($out);
        exit;
    }

    public function APIRemoveTask() {
        $out = [
            'status' => 'success',
            'errors' => []
        ];

        if (!APP::Module('DB')->Select(
            $this->settings['module_taskmanager_db_connection'], ['fetchColumn', 0], 
            ['COUNT(id)'], 'task_manager',
            [['id', '=', $_POST['id'], PDO::PARAM_INT]]
        )) {
            $out['status'] = 'error';
            $out['errors'][] = 1;
        }
        
        if ($out['status'] == 'success') {
            $out['count'] = APP::Module('DB')->Delete($this->settings['module_taskmanager_db_connection'], 'task_manager',[['id', '=', $_POST['id'], PDO::PARAM_INT]]);
            APP::Module('Triggers')->Exec('taskmanager_remove', ['id' => $_POST['id'], 'result' => $out['count']]);
        }

        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type');
        header('Access-Control-Allow-Origin: ' . APP::$conf['location'][1]);
        header('Content-Type: application/json');
        
        echo json_encode($out);
        exit;
    }
    
    public function APIUpdateSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_taskmanager_db_connection']], [['item', '=', 'module_taskmanager_db_connection', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_taskmanager_complete_lifetime']], [['item', '=', 'module_taskmanager_complete_lifetime', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_taskmanager_max_execution_time']], [['item', '=', 'module_taskmanager_max_execution_time', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_taskmanager_memory_limit']], [['item', '=', 'module_taskmanager_memory_limit', PDO::PARAM_STR]]);
        APP::Module('Registry')->Update(['value' => $_POST['module_taskmanager_tmp_dir']], [['item', '=', 'module_taskmanager_tmp_dir', PDO::PARAM_STR]]);
        
        APP::Module('Triggers')->Exec('taskmanager_update_settings', [
            'db_connection' => $_POST['module_taskmanager_db_connection'],
            'complete_lifetime' => $_POST['module_taskmanager_complete_lifetime'],
            'max_execution_time' => $_POST['module_taskmanager_max_execution_time'],
            'memory_limit' => $_POST['module_taskmanager_memory_limit'],
            'tmp_dir' => $_POST['module_taskmanager_tmp_dir']
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

class TaskManagerSearch{
    public function token($settings) {
        return APP::Module('DB')->Select(
            APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'task_manager',
            [['token', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function module($settings) {
        return APP::Module('DB')->Select(
            APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'task_manager',
            [['module', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function method($settings) {
        return APP::Module('DB')->Select(
            APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'task_manager',
            [['method', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function args($settings) {
        return APP::Module('DB')->Select(
            APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'task_manager',
            [['args', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
    public function state($settings) {
        return APP::Module('DB')->Select(
            APP::Module('TaskManager')->settings['module_taskmanager_db_connection'], 
            ['fetchAll', PDO::FETCH_COLUMN], 
            ['id'], 'task_manager',
            [['state', $settings['logic'], $settings['value'], PDO::PARAM_STR]]
        );
    }
    
}