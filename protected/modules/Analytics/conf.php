<?
return [
    'routes' => [
        ['admin\/analytics\/settings(\?.*)?',                       'Analytics', 'Settings'],
        
        ['admin\/analytics\/yandex\/get(\?.*)?',                    'Analytics', 'GetYandex'],
        ['admin\/analytics\/yandex\/token(\?.*)?',                  'Analytics', 'GetYandexToken'],
        ['admin\/analytics\/utm\/roi(\?.*)?',                       'Analytics', 'UtmRoi'],
        ['admin\/analytics\/utm(\?.*)?',                            'Analytics', 'Utm'],
        ['admin\/analytics\/open\/letter\/pct(\?.*)?',              'Analytics', 'OpenLettersPct'],
        ['admin\/analytics\/open\/letter\/time(\?.*)?',             'Analytics', 'LetterOpenTime'],
        ['admin\/analytics\/rfm\/billing(\?.*)?',                   'Analytics', 'RfmBilling'],
        ['admin\/analytics\/rfm\/mail\/(?P<event>open|click)(\?.*)?','Analytics', 'RfmMail'],
        ['admin\/analytics\/cohorts(\?.*)?',                        'Analytics', 'Cohorts'],
        ['admin\/analytics\/geo(\?.*)?',                            'Analytics', 'Geo'],
        
        //API
        ['admin\/analytics\/api\/geo\/city\.json(\?.*)?',           'Analytics', 'APIGetGeoCity'],
        ['admin\/analytics\/api\/geo\/country\.json(\?.*)?',        'Analytics', 'APIGetGeoCountry'],
        
        ['admin\/analytics\/api\/dashboard\.json(\?.*)?',           'Analytics', 'APIDashboard'],
        ['admin\/analytics\/api\/settings\/update\.json(\?.*)?',    'Analytics', 'APIUpdateSettings'],
    ],
    'rfm' => [
        'dates' => [
            '≤30' => [
                time() - 2592000,
                time(),
                5
            ],
            '31-60' => [
                time() - 5184000,
                time() - 2592000,
                4
            ],
            '61-120' => [
                time() - 10368000,
                time() - 5184000,
                3
            ],
            '121-365' => [
                time() - 31536000,
                time() - 10368000,
                2
            ],
            '365+' => [
                0,
                time() - 31536000,
                1
            ]
        ],
        'units' => [
            '1' => [
                1,
                1,
                1
            ],
            '2' => [
                2,
                2,
                2
            ],
            '3' => [
                3,
                3,
                3
            ],
            '4-7' => [
                4,
                7,
                4
            ],
            '8-11' => [
                8,
                11,
                5
            ],
            '12+' => [
                12,
                9999,
                5
            ]
        ]
    ],
    'rfm_mail' => [
        'dates' => [
            '≤7' => [
                time() - 86400*7,
                time(),
                5
            ],
            '8-14' => [
                time() - 86400*14,
                time() - 86400*7,
                4
            ],
            '15-30' => [
                time() - 86400*30,
                time() - 86400*14,
                3
            ],
            '31-60' => [
                time() - 86400*60,
                time() - 86400*30,
                2
            ],
            '61+' => [
                0,
                time() - 86400*60,
                1
            ]
        ],
        'units' => [
            '1' => [
                1,
                1,
                1
            ],
            '2-3' => [
                2,
                3,
                2
            ],
            '4-10' => [
                4,
                10,
                3
            ],
            '11-30' => [
                11,
                30,
                4
            ],
            '31+' => [
                31,
                9999,
                5
            ]
        ]
    ]
];
