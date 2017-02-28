<?
return [
    'routes' => [
        ['messages', 'Messages', 'Manage'],
        ['messages\/group\/(?P<group_id_hash>.*)', 'Messages', 'ViewGroup'],
        ['messages\/view\/(?P<message_id_hash>.*)', 'Messages', 'ViewMessage'],
    ]
];