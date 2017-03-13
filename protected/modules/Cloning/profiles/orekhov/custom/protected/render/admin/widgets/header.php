<?
$messages_cnt = APP::Module('DB')->Select(
    APP::Module('Messages')->settings['module_messages_db_connection'], ['fetch', PDO::FETCH_COLUMN], 
    ['COUNT(id)'], 
    'messages',
    [
        ['user', '=', APP::Module('Users')->user['id'], PDO::PARAM_INT],
        ['state', '=', 'unread', PDO::PARAM_STR]
    ]
);
?>
<header id="header" class="media">
    <div class="pull-left h-logo">
        <a href="<?= APP::Module('Routing')->root ?>admin">
            <span style="font-size: 21px">PULT</span>   
            <small style="font-size: 15px;">D-E-S-I-G-N.RU</small>
        </a>
        
        <div class="menu-collapse" data-ma-action="sidebar-open" data-ma-target="main-menu">
            <div class="mc-wrap">
                <div class="mcw-line top palette-White bg"></div>
                <div class="mcw-line center palette-White bg"></div>
                <div class="mcw-line bottom palette-White bg"></div>
            </div>
        </div>
    </div>

    <ul class="pull-right h-menu">
        <li class="hidden-xs">
            <a href="<?= APP::Module('Routing')->root ?>messages"><? if ($messages_cnt) { ?><span class="badge bgm-orange" style="position: absolute; top: 0; left: 30px; font-weight: bold;"><?= $messages_cnt ?></span><? } ?><i class="hm-icon zmdi zmdi-comment-text"></i></a>
        </li>
        <li class="dropdown hidden-xs">
            <a data-toggle="dropdown" href=""><i class="hm-icon zmdi zmdi-more-vert"></i></a>
            <ul class="dropdown-menu dm-icon pull-right">
                <li class="hidden-xs">
                    <a data-action="fullscreen" href=""><i class="zmdi zmdi-fullscreen"></i> Полноэкранный режим</a>
                </li>
            </ul>
        </li>
        <li class="hm-alerts" data-user-alert="system-cpu" data-ma-action="sidebar-open" data-ma-target="user-alerts">
            <a href=""><i class="hm-icon zmdi zmdi-settings"></i></a>
        </li>
        <li class="dropdown hm-profile">
            <a data-toggle="dropdown" href="">
                <img src="<?= APP::$conf['location'][0] ?>://www.gravatar.com/avatar/<?= md5(APP::Module('Users')->user['email']) ?>?s=40&d=<?= urlencode(APP::Module('Routing')->root . APP::Module('Users')->settings['module_users_profile_picture']) ?>&t=<?= time() ?>">
            </a>

            <ul class="dropdown-menu pull-right dm-icon">
                <li>
                    <a href="<?= APP::Module('Routing')->root ?>users/profile"><i class="zmdi zmdi-account"></i> Мой профиль</a>
                </li>
                <li>
                    <a href="<?= APP::Module('Routing')->root ?>users/actions/change-password"><i class="zmdi zmdi-key"></i> Изменить пароль</a>
                </li>
                <li>
                    <a href="<?= APP::Module('Routing')->root ?>users/logout"><i class="zmdi zmdi-time-restore"></i> Выйти</a>
                </li>
            </ul>
        </li>
    </ul>

    <div class="media-body h-nav">
        <div class="btn-group btn-group-lg">
            <? foreach ($data as $key => $value) { ?><a href="<?= APP::Module('Routing')->root . $value ?>" class="btn btn-default"><?= $key ?></a><? } ?>
        </div>
    </div>
</header>