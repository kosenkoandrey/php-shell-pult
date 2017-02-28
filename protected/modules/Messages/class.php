<?
class Messages {

    public $settings;
    
    function __construct($conf) {
        foreach ($conf['routes'] as $route) APP::Module('Routing')->Add($route[0], $route[1], $route[2]);
    }
    
    public function Init() {
        $this->settings = APP::Module('Registry')->Get([
            'module_messages_db_connection',
            'module_messages_users'
        ]);
    }
    
    public function Add($group, $title = false, $message = false, $state = 'unread') {
        $out = [];
        
        foreach (json_decode($this->settings['module_messages_users']) as $user) {
            $out[] = APP::Module('DB')->Insert(
                $this->settings['module_messages_db_connection'], 'messages',
                Array(
                    'id' => 'NULL',
                    'user' => [$user, PDO::PARAM_INT],
                    'group_alias' => [$group, PDO::PARAM_STR],
                    'state' => [$state, PDO::PARAM_STR],
                    'title' => $title ? [$title, PDO::PARAM_STR] : 'NULL',
                    'message' => $message ? [$message, PDO::PARAM_STR] : 'NULL',
                    'cr_date' => 'NOW()',
                    'read_date' => ['0000-00-00 00:00:00', PDO::PARAM_STR]
                )
            );
        }
    }
    
    public function Manage() {
        $out = [];
        
        foreach (APP::Module('DB')->Select(
            $this->settings['module_messages_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
            [
                'COUNT(messages.id) AS count',
                'messages.group_alias',
                'MAX(messages.cr_date) AS date',
                'messages_groups.name AS group_name'
            ], 
            'messages',
            [
                ['user', '=', APP::Module('Users')->user['id'], PDO::PARAM_INT]
            ],
            [
                'join/messages_groups' => [
                    ['messages_groups.alias', '=', 'messages.group_alias']
                ]
            ],
            ['messages.group_alias'],
            false,
            ['date', 'DESC']
        ) as $value) {
            $out[] = [
                'group' => [$value['group_alias'], $value['group_name']],
                'date' => $value['date'],
                'total' => $value['count'],
                'unread' => APP::Module('DB')->Select(
                    APP::Module('Messages')->settings['module_messages_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
                    ['COUNT(id)'], 
                    'messages',
                    [
                        ['group_alias', '=', $value['group_alias'], PDO::PARAM_STR],
                        ['user', '=', APP::Module('Users')->user['id'], PDO::PARAM_INT],
                        ['state', '=', 'unread', PDO::PARAM_STR]
                    ]
                )
            ];
        }
        
        APP::Render('messages/manage', 'include', [
            'messages' => $out
        ]);
    }
    
    public function ViewGroup() {
        $out = [];
        $group_alias = APP::Module('Crypt')->Decode(APP::Module('Routing')->get['group_id_hash']);
        
        APP::Render('messages/view/group', 'include', [
            'group' => APP::Module('DB')->Select(
                $this->settings['module_messages_db_connection'], ['fetch', PDO::FETCH_ASSOC], 
                [
                    'alias',
                    'name'
                ], 
                'messages_groups',
                [
                    ['alias', '=', $group_alias, PDO::PARAM_INT]
                ]
            ),
            'messages' => APP::Module('DB')->Select(
                $this->settings['module_messages_db_connection'], ['fetchAll', PDO::FETCH_ASSOC], 
                [
                    'id',
                    'state',
                    'title',
                    'message',
                    'cr_date'
                ], 
                'messages',
                [
                    ['user', '=', APP::Module('Users')->user['id'], PDO::PARAM_INT],
                    ['group_alias', '=', $group_alias, PDO::PARAM_INT]
                ],
                false,
                false,
                false,
                ['cr_date', 'DESC']
            )
        ]);
    }
    
    public function ViewMessage() {
        $message = APP::Module('DB')->Select(
            $this->settings['module_messages_db_connection'], ['fetch', PDO::FETCH_ASSOC], 
            [
                '*'
            ], 
            'messages',
            [
                ['id', '=', APP::Module('Crypt')->Decode(APP::Module('Routing')->get['message_id_hash']), PDO::PARAM_INT]
            ]
        );
        
        if ($message['state'] == 'unread') {
            APP::Module('DB')->Update(
                $this->settings['module_messages_db_connection'], 'messages', 
                [
                    'state' => 'read',
                    'read_date' => date('Y-m-d H:i:s')
                ], 
                [
                    ['id', '=', $message['id'], PDO::PARAM_INT]
                ]
            );
        }

        APP::Render('messages/view/message', 'include', [
            'message' => $message
        ]);
    }

    /*
    public function Admin() {
        return APP::Render('messages/admin/nav', 'content');
    }
    
    public function Settings() {
        APP::Render('messages/admin/index');
    }

    public function APIUpdateSettings() {
        APP::Module('Registry')->Update(['value' => $_POST['module_messages_xxx']], [['item', '=', 'module_messages_xxx', PDO::PARAM_STR]]);

        APP::Module('Triggers')->Exec('update_messages_settings', [
            'xxx' => $_POST['module_messages_xxx']
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
     */

    
    
    
}