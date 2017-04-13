<?
$card = 'Пользователи';

$tabs = [
    'new' => 'Новые',
    'all' => 'Сводка'
];

$out = [];

foreach ($tabs as $view => $tab) {
    $hash = md5($card . $tab);
    
    $out[] = [
        [$card, $tab],
        [
            APP::Render('users/admin/dashboard/' . $view . '/html', 'content', compact('hash')),
            APP::Render('users/admin/dashboard/' . $view . '/css', 'content', compact('hash')),
            APP::Render('users/admin/dashboard/' . $view . '/js', 'content', compact('hash'))
        ]
    ];
}

return $out;