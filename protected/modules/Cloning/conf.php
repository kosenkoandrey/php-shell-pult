<?

return [
    'routes' => [
        ['admin\/cloning(\?.*)?',                     'Cloning', 'NewCloning'],
        ['admin\/cloning\/log(\?.*)?',                'Cloning', 'Log'],
        
        ['admin\/cloning\/api\/exec\.json(\?.*)?',    'Cloning', 'APICloning'],
    ],
    'profiles' => [
        'orekhov' => [
            'name' => 'Орехов', 
            'path' => '/var/www/domains/clients/pult.d-e-s-i-g-n.ru'
        ],
        'yurkovskaya' => [
            'name' => 'Юрковская', 
            'path' => '/var/www/domains/clients/pult.yurkovskaya.com'
        ],
        'webtrening' => [
            'name' => 'webtrening', 
            'path' => '/var/www/domains/clients/pult.webtrening.ru'
        ],
        '6sekretov' => [
            'name' => '6sekretov', 
            'path' => '/var/www/domains/clients/pult.6sekretov.ru'
        ],
        'shcolarazyma' => [
            'name' => 'shcolarazyma', 
            'path' => '/var/www/domains/clients/pult.shcolarazyma.ru'
        ]
    ]
];