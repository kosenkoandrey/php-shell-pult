<?
define('DEFAULT_DOMAIN', 'pult.d-e-s-i-g-n.ru');

return [
    'location'          => ['http', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : DEFAULT_DOMAIN, '/'],
    'encoding'          => 'UTF-8',
    'locale'            => 'ru_RU',
    'timezone'          => 'Etc/GMT-3',
    'memory_limit'      => '512M',
    'error_reporting'   => E_ALL,
    'debug'             => false,
    'install'           => false,
    'logs'              => '/var/log/phpshell/orekhov',
];
