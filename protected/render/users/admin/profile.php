<?
//print_r($data); exit;
?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Личная карточка - <?= $data['user']['email'] ?></title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        
        <? APP::Render('core/widgets/css') ?>
        
        <style>
            .user-nav > .list-group-item {
                padding: 15px 25px;
                font-size: 13px;
            }
            .user-nav > .list-group-item.active {
                font-weight: 600;
            }
            #tab-tunnels .table > thead > tr:first-child > th, 
            #tab-tunnels .table > tbody > tr:first-child > th, 
            #tab-tunnels .table > tfoot > tr:first-child > th, 
            #tab-tunnels .table > thead > tr:first-child > td, 
            #tab-tunnels .table > tbody > tr:first-child > td, 
            #tab-tunnels .table > tfoot > tr:first-child > td {
                    border-top: none;
            }
            .table .table {
                background-color: #f5f5f5;
            }
        </style>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Личная карточка' => 'admin/users'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="c-header">
                        <h2><?= $data['user']['email'] ?>
                            <small>
                                <?
                                switch ($data['user']['role']) {
                                    case 'new':
                                    case 'user': 
                                        echo 'ПОДПИСЧИК'; break;
                                    case 'admin': echo 'АДМИНИСТРАТОР'; break;
                                    case 'tech-admin': echo 'ТЕХНИЧЕСКИЙ АДМИНИСТРАТОР'; break;
                                    default: echo $data['user']['role']; break;
                                }
                                ?> (<?
                                if (isset($data['about']['state'])) {
                                    switch ($data['about']['state']) {
                                        case 'inactive': echo 'ОЖИДАЕТ АКТИВАЦИИ'; break;
                                        case 'active': echo 'АКТИВНЫЙ'; break;
                                        case 'pause': echo 'ВРЕМЕННО ОТПИСАН'; break;
                                        case 'unsubscribe': echo 'ОТПИСАН'; break;
                                        case 'blacklist': echo 'В ЧЕРНОМ СПИСКЕ'; break;
                                        case 'dropped': echo 'НЕВОЗМОЖНО ДОСТАВИТЬ ПОЧТУ'; break;
                                        default: echo $data['about']['state']; break;
                                    }
                                } else {
                                    echo 'СОСТОЯНИЕ НЕ ИЗВЕСТНО';
                                }
                                ?>)
                            </small>
                        </h2>
                    </div>

                    <div class="card" id="profile-main">
                        <div class="pm-overview c-overflow">
                            <div class="pmo-pic m-b-20">
                                <div class="p-relative">
                                    <img class="img-responsive " src="<?= APP::$conf['location'][0] ?>://www.gravatar.com/avatar/<?= md5($data['user']['email']) ?>?s=180&d=<?= urlencode(APP::Module('Routing')->root . APP::Module('Users')->settings['module_users_profile_picture']) ?>&t=<?= time() ?>">
                                </div>
                            </div>

                            <div class="user-nav list-group">
                                <a href="#tab-about" role="tab" data-toggle="tab" class="list-group-item active">Основное</a>
                                <? if ($data['mail']) { ?><a href="#tab-mail" aria-controls="tab-mail" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" style="margin: 0 3px"><?= count($data['mail']) ?></span>Письма</a><? } ?>
                                <a href="#tab-tunnels" aria-controls="tab-tunnels" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" data-toggle="tooltip" data-placement="top" title="Доступные" style="margin: 0 3px"><?= count($data['tunnels']['allow']) ?></span> <span class="badge bgm-teal" data-toggle="tooltip" data-placement="top" title="Очередь" style="margin: 0 3px"><?= count($data['tunnels']['queue']) ?></span> <span class="badge bgm-teal" data-toggle="tooltip" data-placement="top" title="Подписки" style="margin: 0 3px"><?= count($data['tunnels']['subscriptions']) ?></span> Туннели</a>
                                <? if ($data['tags']) { ?><a href="#tab-tags" aria-controls="tab-tags" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" style="margin: 0 3px"><?= count($data['tags']) ?></span>Теги</a><? } ?>
                                <? if ($data['utm']) { ?><a href="#tab-utm" aria-controls="tab-utm" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" data-toggle="tooltip" data-placement="top" title="Серии" style="margin: 0 3px"><?= count($data['utm']) ?></span>UTM-метки</a><? } ?>
                                <? if ($data['comments']) { ?><a href="#tab-comments" aria-controls="tab-comments" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" style="margin: 0 3px"><?= count($data['comments']) ?></span>Комментарии</a><? } ?>
                                <? if ($data['likes']) { ?><a href="#tab-likes" aria-controls="tab-likes" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" style="margin: 0 3px"><?= count($data['likes']) ?></span>Оценки</a><? } ?>
                                <? if ($data['premium']) { ?><a href="#tab-premium" aria-controls="tab-premium" role="tab" data-toggle="tab" class="list-group-item">Платные материалы</a><? } ?>
                                <? if ($data['invoices']) { ?><a href="#tab-invoices" aria-controls="tab-invoices" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" style="margin: 0 3px"><?= count($data['invoices']) ?></span>Счета</a><? } ?>
                                <? if ($data['polls']) { ?><a href="#tab-polls" aria-controls="tab-polls" role="tab" data-toggle="tab" class="list-group-item"><span class="badge bgm-teal" style="margin: 0 3px"><?= count($data['polls']) ?></span>Опросы</a><? } ?>
                            </div>
                        </div>

                        <div class="pm-body clearfix">
                            <div class="tab-content" style="padding: 0;">
                                <div role="tabpanel" class="tab-pane active" id="tab-about">
                                    <div class="pmb-block">
                                        <div class="pmbb-header">
                                            <h2><i class="zmdi zmdi-account m-r-5"></i> Основное</h2>

                                            <ul class="actions">
                                                <li class="dropdown">
                                                    <a href="javascript:void(0)" data-toggle="dropdown"><i class="zmdi zmdi-more-vert"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li><a class="toggle-basic" href="javascript:void(0)">Редарктировать</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="p-l-25">
                                            <div id="view-basic" class="pmbb-view">
                                                <dl class="dl-horizontal">
                                                    <dt>ID</dt>
                                                    <dd id="about-username-value"><?= $data['user']['id'] ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>E-Mail</dt>
                                                    <dd id="about-username-value"><?= $data['user']['email'] ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Имя пользователя</dt>
                                                    <dd id="about-username-value"><?= isset($data['about']['username']) ? $data['about']['username'] : 'user' . $data['user']['id'] ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Регистрация</dt>
                                                    <dd id="about-username-value"><?= $data['user']['reg_date'] ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Последняя активность</dt>
                                                    <dd id="about-username-value"><?= $data['user']['last_visit'] ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Роль</dt>
                                                    <dd id="about-username-value">
                                                        <?
                                                        switch ($data['user']['role']) {
                                                            case 'new':
                                                            case 'user': 
                                                                echo 'подписчик'; break;
                                                            case 'admin': echo 'администратор'; break;
                                                            case 'tech-admin': echo 'технический администратор'; break;
                                                            default: echo $data['user']['role']; break;
                                                        }
                                                        ?>
                                                    </dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Состояние</dt>
                                                    <dd id="about-state-value">
                                                        <?
                                                        if (isset($data['about']['state'])) {
                                                            switch ($data['about']['state']) {
                                                                case 'inactive': echo 'ожидает активации'; break;
                                                                case 'active': echo 'активный'; break;
                                                                case 'pause': echo 'временно отписан'; break;
                                                                case 'unsubscribe': echo 'отписан'; break;
                                                                case 'blacklist': echo 'в черном списке'; break;
                                                                case 'dropped': echo 'невозможно доставить почту'; break;
                                                                default: echo $data['about']['state']; break;
                                                                
                                                            }
                                                        } else {
                                                            echo 'не изветстно';
                                                        }
                                                        ?>
                                                    </dd>
                                                </dl>
                                            </div>
                                            <form id="form-basic" class="pmbb-edit">
                                                <input type="hidden" name="user" value="<?= APP::Module('Crypt')->Encode($data['user']['id']) ?>">
                                                
                                                <dl class="dl-horizontal">
                                                    <dt class="p-t-10">Имя пользователя</dt>
                                                    <dd>
                                                        <div class="fg-line">
                                                            <input type="text" id="about_username" name="about[username]" class="form-control" placeholder="user<?= $data['user']['id'] ?>">
                                                        </div>
                                                    </dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt class="p-t-10">Состояние</dt>
                                                    <dd>
                                                        <div class="fg-line" style="width: 50%;">
                                                            <select id="about_state" name="about[state]" class="selectpicker">
                                                                <option value="unknown">не изветстно</option>
                                                                <option value="active">активный</option>
                                                                <option value="inactive">неактивный</option>
                                                                <option value="blacklist">в черном списке</option>
                                                                <option value="dropped">дропнутый</option>
                                                            </select>
                                                        </div>
                                                    </dd>
                                                </dl>
                                                <div class="m-t-30">
                                                    <button type="submit" class="btn palette-Teal bg waves-effect">Сохранить</button>
                                                    <button type="button" class="toggle-basic btn btn-link c-black">Отмена</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="pmb-block">
                                        <div class="pmbb-header">
                                            <h2><i class="zmdi zmdi-phone m-r-5"></i> Контакты</h2>

                                            <ul class="actions">
                                                <li class="dropdown">
                                                    <a href="javascript:void(0)" data-toggle="dropdown"><i class="zmdi zmdi-more-vert"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li><a class="toggle-contact" href="javascript:void(0)">Редактировать</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="p-l-25">
                                            <div id="view-contact" class="pmbb-view">
                                                <dl class="dl-horizontal">
                                                    <dt>Телефон</dt>
                                                    <dd id="about-mobile-phone-value"><?= isset($data['about']['mobile_phone']) ? $data['about']['mobile_phone'] : 'нет' ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Twitter</dt>
                                                    <dd id="about-twitter-value"><?= isset($data['about']['twitter']) ? $data['about']['twitter'] : 'нет' ?></dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt>Skype</dt>
                                                    <dd id="about-skype-value"><?= isset($data['about']['skype']) ? $data['about']['skype'] : 'нет' ?></dd>
                                                </dl>
                                            </div>

                                            <form id="form-contact" class="pmbb-edit">
                                                <input type="hidden" name="user" value="<?= APP::Module('Crypt')->Encode($data['user']['id']) ?>">
                                                
                                                <dl class="dl-horizontal">
                                                    <dt class="p-t-10">Телефон</dt>
                                                    <dd>
                                                        <div class="fg-line">
                                                            <input type="text" id="about_mobile_phone" name="about[mobile_phone]" class="form-control input-mask" data-mask="+000000000000" maxlength="15" autocomplete="off">
                                                        </div>
                                                    </dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt class="p-t-10">Twitter</dt>
                                                    <dd>
                                                        <div class="fg-line">
                                                            <input type="text" id="about_twitter" name="about[twitter]" class="form-control">
                                                        </div>
                                                    </dd>
                                                </dl>
                                                <dl class="dl-horizontal">
                                                    <dt class="p-t-10">Skype</dt>
                                                    <dd>
                                                        <div class="fg-line">
                                                            <input type="text" id="about_skype" name="about[skype]" class="form-control">
                                                        </div>
                                                    </dd>
                                                </dl>
                                                <div class="m-t-30">
                                                    <button type="submit" class="btn palette-Teal bg waves-effect">Сохранить</button>
                                                    <button type="button" class="toggle-contact btn btn-link c-black">Отмена</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <?
                                    if (count($data['social-profiles'])) {
                                        ?>
                                        <div class="pmb-block">
                                            <div class="pmbb-header">
                                                <h2><i class="zmdi zmdi-account-box-o m-r-5"></i> Социальные сети</h2>
                                            </div>
                                            <div class="p-l-25">
                                                <div id="view-contact" class="pmbb-view">
                                                    <?
                                                    foreach ($data['social-profiles'] as $profile) {
                                                        switch ($profile['service']) {
                                                            case 'vk': 
                                                                ?>
                                                                <dl class="dl-horizontal">
                                                                    <dt>ВКонтакте</dt>
                                                                    <dd id="social-profile-vk"><a href="https://vk.com/id<?= $profile['extra'] ?>" target="_blank" class="c-teal"><?= $profile['extra'] ?></a></dd>
                                                                </dl>
                                                                <? 
                                                                break;
                                                            case 'fb': 
                                                                ?>
                                                                <dl class="dl-horizontal">
                                                                    <dt>Facebook</dt>
                                                                    <dd id="social-profile-facebook"><a href="http://facebook.com/profile.php?id=<?= $profile['extra'] ?>" target="_blank"><?= $profile['extra'] ?></a></dd>
                                                                </dl>
                                                                <? 
                                                                break;
                                                            case 'google': 
                                                                ?>
                                                                <dl class="dl-horizontal">
                                                                    <dt>Google+</dt>
                                                                    <dd id="social-profile-google-plus"><a href="https://plus.google.com/u/0/<?= $profile['extra'] ?>" target="_blank"><?= $profile['extra'] ?></a></dd>
                                                                </dl>
                                                                <? 
                                                                break;
                                                            case 'ya': 
                                                                ?>
                                                                <dl class="dl-horizontal">
                                                                    <dt>Яндекс</dt>
                                                                    <dd id="social-profile-yandex"><?= $profile['extra'] ?></dd>
                                                                </dl>
                                                                <? 
                                                                break;
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?
                                    }

                                    if (isset(APP::$modules['Comments'])) {
                                        ?>
                                        <div class="p-t-25">
                                            <?
                                            $comment_object_type = APP::Module('DB')->Select(APP::Module('Comments')->settings['module_comments_db_connection'], ['fetchColumn', 0], ['id'], 'comments_objects', [['name', '=', "UserAdmin", PDO::PARAM_STR]]);
                                            
                                            APP::Render('comments/widgets/default/list', 'include', [
                                                'type' => $comment_object_type,
                                                'id' => $data['user']['id'],
                                                'likes' => true,
                                                'class' => [
                                                    'holder' => 'palette-Grey-100 bg p-l-10'
                                                ]
                                            ]);

                                            APP::Render('comments/widgets/default/form', 'include', [
                                                'type' => $comment_object_type,
                                                'id' => $data['user']['id'],
                                                'login' => [],
                                                'class' => [
                                                    'holder' => false,
                                                    'list' => 'palette-Grey-100 bg p-l-10'
                                                ]
                                            ]);
                                            ?>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                                <?
                                if ($data['mail']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-mail">
                                        <div class="pmb-block">
                                            <div class="pmbb-header">
                                                <h2><i class="zmdi zmdi-mail-send m-r-5"></i> Всего отправлено <?= count($data['mail']) ?> писем</h2>
                                            </div>
                                        </div>
                                        <table class="table table-hover table-vmiddle">
                                            <tbody>
                                                <?
                                                foreach ($data['mail'] as $item) {
                                                    $mail_icon = false;
                                                    
                                                    switch ($item['log']['state']) {
                                                        case 'wait': $mail_icon = ['Grey-400', 'time']; break;
                                                        case 'error': $mail_icon = ['Red-400', 'close']; break;
                                                        case 'success': $mail_icon = ['Teal-500', 'email']; break;
                                                    }
                                                    
                                                    $mail_tags = array_reverse($item['tags']);
                                                    ?>
                                                    <tr>
                                                        <td style="width: 60px;">
                                                            <span style="display: inline-block" class="avatar-char palette-<?= $mail_icon[0] ?> bg"><i class="zmdi zmdi-<?= $mail_icon[1] ?>"></i></span>
                                                        </td>
                                                        <td style="font-size: 16px;">
                                                            <a class="mail_events" data-id="<?= $item['log']['id'] ?>" style="color: #4C4C4C" href="javascript:void(0)"><?= $item['log']['letter_subject'] ?></a>
                                                            <div style="font-size: 11px;"><?= $item['log']['cr_date'] ?></div>
                                                            <div style="font-size: 12px; margin-top: 5px;"><?= count($mail_tags) ? implode(' <i class="zmdi zmdi-long-arrow-right"></i> ', $mail_tags) : 'Нет событий' ?></div>
                                                        </td>
                                                        <td>
                                                            <a target="_blank" href="<?= APP::Module('Routing')->root ?>mail/html/<?= APP::Module('Crypt')->Encode($item['log']['id']) ?>" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-code-setting"></span></a>
                                                            <a target="_blank" href="<?= APP::Module('Routing')->root ?>mail/plaintext/<?= APP::Module('Crypt')->Encode($item['log']['id']) ?>" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-text-format"></span></a>
                                                        </td>
                                                    </tr>
                                                    <?
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                
                                    <div class="modal fade" id="mail-events-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Подробности отправки письма</h4>
                                                </div>
                                                <div class="details">
                                                    <table class="table table-hover">
                                                        <tbody>
                                                            <tr>
                                                                <td>ID отправки</td>
                                                                <td class="mail_id"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Состояние</td>
                                                                <td class="mail_state"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Ответ</td>
                                                                <td class="mail_result"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Кол-во попыток</td>
                                                                <td class="mail_retries"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Время ответа</td>
                                                                <td class="mail_ping"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Дата отправки</td>
                                                                <td class="mail_cr_date"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Тема письма</td>
                                                                <td class="mail_letter_subject"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Приоритет</td>
                                                                <td class="mail_letter_priority"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Имя отправителя</td>
                                                                <td class="mail_sender_name"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>E-Mail отправителя</td>
                                                                <td class="mail_sender_email"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Модуль транспорта</td>
                                                                <td class="mail_transport_module"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Метод транспорта</td>
                                                                <td class="mail_transport_method"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="modal-header">
                                                    <h4 class="modal-title">События связанные с письмом</h4>
                                                </div>
                                                <div class="modal-body events"></div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-dismiss="modal">Закрыть</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?
                                }
                                ?>
                                <div role="tabpanel" class="tab-pane" id="tab-tunnels">
                                    <ul class="tab-nav" data-tab-color="teal">
                                        <li class="active waves-effect"><a href="#tab-tunnels-subscriptions" aria-controls="tab-about" role="tab" data-toggle="tab">Подписки</a></li>
                                        <li class="waves-effect"><a href="#tab-tunnels-queue" aria-controls="tab-mail" role="tab" data-toggle="tab">Очередь</a></li>
                                        <li class="waves-effect"><a href="#tab-tunnels-allow" aria-controls="tab-mail" role="tab" data-toggle="tab">Доступные</a></li>
                                    </ul>

                                    <div class="tab-content" style="padding: 0;">
                                        <div role="tabpanel" class="tab-pane active" id="tab-tunnels-subscriptions">
                                            <?
                                            if ($data['tunnels']['subscriptions']) {
                                                ?>
                                                <table class="table table-hover table-vmiddle">
                                                    <tbody>
                                                        <?
                                                        foreach ($data['tunnels']['subscriptions'] as $item) {
                                                            $tunnel_icon = false;

                                                            switch ($item['info']['state']) {
                                                                case 'pause': $tunnel_icon = ['Grey-400', 'time']; break;
                                                                case 'complete': $tunnel_icon = ['Teal-400', 'check']; break;
                                                                case 'active': $tunnel_icon = ['Orange-400', 'arrow-split']; break;
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td style="width: 60px;">
                                                                    <span style="display: inline-block" class="avatar-char palette-<?= $tunnel_icon[0] ?> bg"><i class="zmdi zmdi-<?= $tunnel_icon[1] ?>"></i></span>
                                                                </td>
                                                                <td style="font-size: 16px;">
                                                                    <a class="tunnel_tags" data-id="<?= $item['info']['id'] ?>" style="color: #4C4C4C" href="javascript:void(0)"><?= $item['info']['tunnel_name'] ?></a>
                                                                    <div style="font-size: 11px;"><?= count($item['tags']) ?> событий</div>
                                                                </td>
                                                                <!--
                                                                <td>
                                                                    <a target="_blank" href="#" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-code-setting"></span></a>
                                                                    <a target="_blank" href="#" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-text-format"></span></a>
                                                                    <a target="_blank" href="#" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-text-format"></span></a>
                                                                </td>
                                                                -->
                                                            </tr>
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <?
                                            } else {
                                                ?>
                                                <table class="table table-vmiddle">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 60px;">
                                                                <span style="display: inline-block" class="avatar-char avatar-char palette-Teal-200 bg"><i class="zmdi zmdi-close"></i></span>
                                                            </td>
                                                            <td style="font-size: 16px; color: #4C4C4C">
                                                                Нет подписок на туннели
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <?
                                            }
                                            ?>
                                        </div>
                                        <div role="tabpanel" class="tab-pane" id="tab-tunnels-queue">
                                            <?
                                            if ($data['tunnels']['queue']) {
                                                ?>
                                                <table class="table table-hover table-vmiddle">
                                                    <tbody>
                                                        <?
                                                        foreach ($data['tunnels']['queue'] as $item) {
                                                            ?>
                                                            <tr>
                                                                <td style="width: 60px;">
                                                                    <span style="display: inline-block" class="avatar-char palette-Teal-400 bg"><i class="zmdi zmdi-time"></i></span>
                                                                </td>
                                                                <td style="font-size: 16px;">
                                                                    <a class="tunnel_queue" data-id="<?= $item['id'] ?>" style="color: #4C4C4C" href="javascript:void(0)"><?= $item['tunnel_name'] ?></a>
                                                                    <div style="font-size: 11px;"><?= $item['object_id'] ?></div>
                                                                </td>
                                                            </tr>
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <?
                                            } else {
                                                ?>
                                                <table class="table table-vmiddle">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 60px;">
                                                                <span style="display: inline-block" class="avatar-char avatar-char palette-Teal-200 bg"><i class="zmdi zmdi-close"></i></span>
                                                            </td>
                                                            <td style="font-size: 16px; color: #4C4C4C">
                                                                Нет туннелей в очереди
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <?
                                            }
                                            ?>
                                        </div>
                                        <div role="tabpanel" class="tab-pane" id="tab-tunnels-allow">
                                            <?
                                            if ($data['tunnels']['allow']) {
                                                ?>
                                                <table class="table table-hover table-vmiddle">
                                                    <tbody>
                                                        <?
                                                        foreach ($data['tunnels']['allow'] as $item) {
                                                            ?>
                                                            <tr>
                                                                <td style="width: 60px;">
                                                                    <span style="display: inline-block" class="avatar-char palette-Teal-400 bg"><i class="zmdi zmdi-check"></i></span>
                                                                </td>
                                                                <td style="font-size: 16px; color: #4C4C4C">
                                                                    <?= $item['name'] ?>
                                                                    <div style="font-size: 11px;">
                                                                        <?
                                                                        switch ($item['type']) {
                                                                            case 'static': echo 'статический'; break;
                                                                            case 'dynamic': echo 'динамический'; break;
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <?
                                            } else {
                                                ?>
                                                <table class="table table-vmiddle">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 60px;">
                                                                <span style="display: inline-block" class="avatar-char avatar-char palette-Teal-200 bg"><i class="zmdi zmdi-close"></i></span>
                                                            </td>
                                                            <td style="font-size: 16px; color: #4C4C4C">
                                                                Нет доступных туннелей
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <?
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="tunnel-tags-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Подробности подписки на туннель</h4>
                                            </div>
                                            <div class="details">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <td>ID подписки</td>
                                                            <td class="tunnel_uid"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Состояние</td>
                                                            <td class="tunnel_state"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Тип туннеля</td>
                                                            <td class="tunnel_type"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>ID туннеля</td>
                                                            <td class="tunnel_id"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Наименование туннеля</td>
                                                            <td class="tunnel_name"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="modal-header">
                                                <h4 class="modal-title">События связанные с туннелем</h4>
                                            </div>
                                            <div class="modal-body tags"></div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-link" data-dismiss="modal">Закрыть</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="tunnel-queue-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Подробности туннеля в очереди</h4>
                                            </div>
                                            <div class="details">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <tr>
                                                            <td>ID очереди</td>
                                                            <td class="tunnel_queue_id"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Дата добавления в очередь</td>
                                                            <td class="tunnel_queue_cr_date"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>ID туннеля</td>
                                                            <td class="tunnel_queue_tunnel_id"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Тип туннеля</td>
                                                            <td class="tunnel_queue_tunnel_type"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Наименование туннеля</td>
                                                            <td class="tunnel_queue_tunnel_name"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Объект туннеля</td>
                                                            <td class="tunnel_queue_object_id"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Таймаут подписки</td>
                                                            <td class="tunnel_queue_timeout"></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Параметры</td>
                                                            <td class="tunnel_queue_settings"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-link" data-dismiss="modal">Закрыть</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?
                                if ($data['tags']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-tags">
                                        <div class="pmb-block">
                                            <div class="pmbb-header">
                                                <h2><i class="zmdi zmdi-labels m-r-5"></i> Всего <?= count($data['tags']) ?> тег</h2>
                                            </div>
                                        </div>
                                        <table class="table table-hover table-vmiddle">
                                            <tbody>
                                                <?
                                                foreach ($data['tags'] as $item) {
                                                    ?>
                                                    <tr>
                                                        <td style="width: 60px;">
                                                            <span style="display: inline-block" class="avatar-char palette-Orange-400 bg"><i class="zmdi zmdi-label"></i></span>
                                                        </td>
                                                        <td style="font-size: 16px;">
                                                            <a class="tags" data-id="<?= $item['id'] ?>" style="color: #4C4C4C" href="javascript:void(0)"><?= $item['item'] ?></a>
                                                            <div style="font-size: 11px;"><?= $item['cr_date'] ?></div>
                                                        </td>
                                                        <!--
                                                        <td>
                                                            <a target="_blank" href="#" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-code-setting"></span></a>
                                                            <a target="_blank" href="#" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-text-format"></span></a>
                                                            <a target="_blank" href="#" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-text-format"></span></a>
                                                        </td>
                                                        -->
                                                    </tr>
                                                    <?
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                
                                    <div class="modal fade" id="tags-modal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Детали тега</h4>
                                                </div>
                                                <div class="details">
                                                    <table class="table table-hover">
                                                        <tbody>
                                                            <tr>
                                                                <td>ID тега</td>
                                                                <td class="tag_id"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Наименование</td>
                                                                <td class="tag_item"></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Значение</td>
                                                                <td class="tag_value">
                                                                    <pre></pre>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Дата создания</td>
                                                                <td class="tag_cr_date"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-dismiss="modal">Закрыть</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?
                                }
                                
                                if ($data['utm']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-utm">
                                        <div class="pmb-block">
                                            <div class="pmbb-header">
                                                <h2><i class="zmdi zmdi-labels m-r-5"></i> Всего <?= count($data['utm'][1]) ?> первичных UTM-меток</h2>
                                            </div>
                                        </div>
                                        <table class="table table-hover table-vmiddle">
                                            <tbody>
                                                <?
                                                foreach ($data['utm'][1] as $label => $label_data) {
                                                    ?>
                                                    <tr>
                                                        <td style="width: 30%; font-size: 14px;"><?= $label ?></td>
                                                        <td style="width: 35%;font-size: 14px;"><?= $label_data[0] ?></td>
                                                        <td style="width: 35%;font-size: 14px;"><?= $label_data[1] ?></td>
                                                    </tr>
                                                    <?
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <?
                                        if (count($data['utm']) > 1) {
                                            foreach ($data['utm'] as $index => $item) {
                                                if ($index == 1) continue; 
                                                ?>
                                                <div class="pmb-block">
                                                    <div class="pmbb-header">
                                                        <h2>Серия #<?= $index ?></h2>
                                                    </div>
                                                </div>
                                                <table class="table table-hover table-vmiddle">
                                                    <tbody>
                                                        <?
                                                        foreach ($item as $label => $label_data) {
                                                            ?>
                                                            <tr>
                                                                <td style="width: 30%; font-size: 14px;"><?= $label ?></td>
                                                                <td style="width: 35%;font-size: 14px;"><?= $label_data[0] ?></td>
                                                                <td style="width: 35%;font-size: 14px;"><?= $label_data[1] ?></td>
                                                            </tr>
                                                            <?
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                                <?
                                            }
                                        }
                                        ?>
                                    </div>
                                    <?
                                }
                                
                                if ($data['comments']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-comments">
                                        <?
                                        foreach ($data['comments'] as $comment) {
                                            ?>
                                            <div class="media p-t-10 p-l-25 p-r-25">
                                                <div class="pull-left">
                                                    <a href="<?= $comment['url'] ?>#comment-<?= APP::Module('Crypt')->Encode($comment['id']) ?>" target="_blank" class="btn btn-default btn-icon waves-effect waves-circle waves-float"><i class="zmdi zmdi-comment-text"></i></a>
                                                </div>
                                                <div class="media-body">
                                                    <h4 class="media-heading">
                                                        <p class="m-b-5 f-12 c-gray"><i class="zmdi zmdi-calendar"></i> <?= date('Y-m-d H:i:s', $comment['up_date']) ?></p>
                                                    </h4>
                                                    <p style="white-space: pre-wrap" class="m-b-10"><?= $comment['message'] ?></p>
                                                    <p><a href="<?= $comment['url'] ?>#comment-<?= APP::Module('Crypt')->Encode($comment['id']) ?>" target="_blank" class="btn palette-Teal bg waves-effect btn-xs"><i class="zmdi zmdi-open-in-new"></i> Перейти</a></p>
                                                </div>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>
                                    <?
                                }
                                
                                if ($data['likes']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-likes">
                                        <?
                                        foreach ($data['likes'] as $like) {
                                            ?>
                                            <div class="media p-t-10 p-l-25 p-r-25">
                                                <div class="pull-left">
                                                    <a href="<?= $like['url'] ?>" target="_blank" class="btn btn-default btn-icon waves-effect waves-circle waves-float"><i class="zmdi zmdi-favorite"></i></a>
                                                </div>
                                                <div class="media-body">
                                                    <h4 class="media-heading">
                                                        <p class="m-b-5 f-12 c-gray"><i class="zmdi zmdi-calendar"></i> <?= date('Y-m-d H:i:s', $like['up_date']) ?></p>
                                                    </h4>
                                                    <p><a href="<?= $like['url'] ?>" target="_blank" class="btn palette-Teal bg waves-effect btn-xs"><i class="zmdi zmdi-open-in-new"></i> Перейти</a></p>
                                                </div>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>
                                    <?
                                }
                                
                                if ($data['premium']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-premium">
                                        <div class="pmb-block">
                                            <div class="pmbb-header">
                                                <h2><i class="zmdi zmdi-lock-open m-r-5"></i> У вас есть доступ к следующим материалам</h2>
                                            </div>
                                        </div>
                                        <table class="table table-hover table-vmiddle">
                                            <tbody>
                                                <?
                                                foreach ($data['premium'] as $item) {
                                                    switch ($item['type']) {
                                                        case 'g':
                                                            ?>
                                                            <tr>
                                                                <td style="font-size: 16px"><span style="display: inline-block" class="avatar-char palette-Teal bg m-r-5"><i class="zmdi zmdi-folder"></i></span> <a style="color: #4C4C4C" href="<?= APP::Module('Routing')->root ?>admin/members/pages/<?= APP::Module('Crypt')->Encode($item['id']) ?>" target="_blank"><?= $item['title'] ?></a></td>
                                                            </tr>
                                                            <?
                                                            break;
                                                        case 'p':
                                                            ?>
                                                            <tr>
                                                                <td style="font-size: 16px;"><span style="display: inline-block" class="avatar-char palette-Orange-400 bg m-r-5"><i class="zmdi zmdi-file"></i></span> <a style="color: #4C4C4C" href="<?= APP::Module('Routing')->root ?>admin/members/page/<?= APP::Module('Crypt')->Encode($item['id']) ?>" target="_blank"><?= $item['title'] ?></a></td>
                                                            </tr>
                                                            <?
                                                            break;
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?
                                }
                                
                                if ($data['invoices']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-invoices">
                                        <?
                                        foreach ($data['invoices'] as $invoice) {
                                            ?>
                                            <div class="pmb-block">
                                                <div class="pmbb-header">
                                                    <h2><i class="zmdi zmdi-shopping-cart m-r-5"></i> Счет #<?= $invoice['invoice']['id'] ?></h2>
                                                </div>
                                            </div>
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td style="width: 25%;">Сумма</td>
                                                        <td><?= $invoice['invoice']['amount'] ?> руб.</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Состояние</td>
                                                        <td>
                                                            <?
                                                            switch ($invoice['invoice']['state']) {
                                                                case 'new': echo 'новый'; break;
                                                                case 'processed': echo 'в работе'; break;
                                                                case 'success': echo 'оплачен'; break;
                                                                case 'revoked': echo 'аннулирован'; break;
                                                                default: echo 'неизвестно'; break;
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Автор</td>
                                                        <td><?= $invoice['invoice']['author'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Дата обновления</td>
                                                        <td><?= $invoice['invoice']['up_date'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Дата создания</td>
                                                        <td><?= $invoice['invoice']['cr_date'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Продукты</td>
                                                        <td>
                                                            <table class="table">
                                                                <tbody>
                                                                    <?
                                                                    foreach ($invoice['products'] as $product) {
                                                                        ?>
                                                                        <tr>
                                                                            <td style="width: 20%">
                                                                                <?
                                                                                switch ($product['type']) {
                                                                                    case 'primary': echo 'первичный'; break;
                                                                                    case 'secondary': echo 'вторичный'; break;
                                                                                    default: echo 'неизвестно'; break;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 30%"><?= $product['name'] ?></td>
                                                                            <td style="width: 20%"><?= $product['amount'] ?> руб.</td>
                                                                            <td style="width: 30%"><?= $product['cr_date'] ?></td>
                                                                        </tr>
                                                                        <?
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">История</td>
                                                        <td>
                                                            <table class="table">
                                                                <tbody>
                                                                    <?
                                                                    foreach ($invoice['tags'] as $tag) {
                                                                        ?>
                                                                        <tr>
                                                                            <td style="width: 35%">
                                                                                <?
                                                                                switch ($tag['action']) {
                                                                                    case 'create_invoice': echo 'создание счета'; break;
                                                                                    case 'success_open_access': echo 'успешное открытие доступа'; break;
                                                                                    case 'add_secondary_product': echo 'добавление вторичного продукта'; break;
                                                                                    default: $tag['action']; break;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 35%">
                                                                                <?
                                                                                $action_data = json_decode($tag['action_data'], true);
                                                                                
                                                                                switch ($tag['action']) {
                                                                                    case 'create_invoice': 
                                                                                        ?>
                                                                                        <a href="javascript:void(0)" onclick="$('#invoice_histoty_item_<?= $tag['id'] ?>').toggle()">подробности</a>
                                                                                        <pre id="invoice_histoty_item_<?= $tag['id'] ?>" style="display: none"><? print_r($action_data) ?></pre>
                                                                                        <? 
                                                                                        break;
                                                                                    case 'success_open_access': 
                                                                                        switch ($action_data[0]) {
                                                                                            case 'g': echo 'группа #' . $action_data[1]; break;
                                                                                            case 'p': echo 'страница #' . $action_data[1]; break;
                                                                                        }
                                                                                        break;
                                                                                    case 'add_secondary_product': 
                                                                                        echo 'продукт #' . $action_data['product']; 
                                                                                        break;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 30%"><?= $tag['cr_date'] ?></td>
                                                                        </tr>
                                                                        <?
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Платежи</td>
                                                        <td>
                                                            <table class="table">
                                                                <tbody>
                                                                    <?
                                                                    foreach ($invoice['payments'] as $payment) {
                                                                        ?>
                                                                        <tr>
                                                                            <td style="width: 35%">
                                                                                <?
                                                                                switch ($payment['method']) {
                                                                                    case 'admin': echo 'вручную администратором'; break;
                                                                                    default: echo $payment['method']; break;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td style="width: 35%">
                                                                                <a href="javascript:void(0)" onclick="$('#invoice_payment_details_<?= $payment['id'] ?>').toggle()">подробности</a>
                                                                                <pre id="invoice_payment_details_<?= $payment['id'] ?>" style="display: none"><? print_r([]) ?></pre>
                                                                            </td>
                                                                            <td style="width: 30%"><?= $product['cr_date'] ?></td>
                                                                        </tr>
                                                                        <?
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Контактные данные</td>
                                                        <td>
                                                            <table class="table">
                                                                <tbody>
                                                                    <?
                                                                    foreach ($invoice['details'] as $details) {
                                                                        ?>
                                                                        <tr>
                                                                            <td style="width: 35%">
                                                                                <?
                                                                                switch ($details['item']) {
                                                                                    case 'name': echo 'имя'; break;
                                                                                    case 'phone': echo 'телефон'; break;
                                                                                    default: echo $details['item']; break;
                                                                                }
                                                                                ?>
                                                                            </td>
                                                                            <td><?= $details['value'] ?></td>
                                                                        </tr>
                                                                        <?
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 25%;">Комментарии</td>
                                                        <td>
                                                            <?
                                                            $comment_object_type = APP::Module('DB')->Select(APP::Module('Comments')->settings['module_comments_db_connection'], ['fetchColumn', 0], ['id'], 'comments_objects', [['name', '=', "Invoice", PDO::PARAM_STR]]);

                                                            APP::Render('comments/widgets/default/list', 'include', [
                                                                'type' => $comment_object_type,
                                                                'id' => $invoice['invoice']['id'],
                                                                'likes' => false,
                                                                'reply' => false,
                                                                'class' => [
                                                                    'holder' => 'palette-Grey-100 bg p-l-10'
                                                                ]
                                                            ]);
                                                            ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <?
                                        }
                                        ?>
                                    </div>
                                    <?
                                }
                                
                                if ($data['polls']) {
                                    ?>
                                    <div role="tabpanel" class="tab-pane" id="tab-polls">
                                        <?
                                        foreach ($data['polls'] as $poll) {
                                            ?>
                                            <div class="pmb-block">
                                                <div class="pmbb-header">
                                                    <h2><i class="zmdi zmdi-check-all m-r-5"></i> <?= $poll['poll']['name'] ?></h2>
                                                </div>
                                            </div>
                                            <table class="table table-hover table-vmiddle">
                                                <tbody>
                                                    <?
                                                    foreach ($poll['answers'] as $answer) {
                                                        ?>
                                                        <tr>
                                                            <td style="width: 40%;"><?= $answer['question'] ?></td>
                                                            <td style="width: 40%;"><?= $answer['answer'] ?></td>
                                                            <td style="width: 20%;"><?= $answer['date'] ?></td>
                                                        </tr>
                                                        <?
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                            <?
                                        }
                                        ?>
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <? APP::Render('admin/widgets/footer') ?>
        </section>

        <? APP::Render('core/widgets/page_loader') ?>
        <? APP::Render('core/widgets/ie_warning') ?>

        <!-- Javascript Libraries -->
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootstrap-growl/bootstrap-growl.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/input-mask/input-mask.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
        
        <? APP::Render('core/widgets/js') ?>
        
        <script>
            $(document).ready(function() {
                $('body').on('click', '.user-nav > a', function() {
                    $('.user-nav > a').removeClass('active');
                    $(this).addClass('active');
                });
                
                $('#about_username').val('<?= isset($data['about']['username']) ? $data['about']['username'] : '' ?>');
                $('#about_state').val('<?= isset($data['about']['state']) ? $data['about']['state'] : 'unknown' ?>');
                $('#about_mobile_phone').val('<?= isset($data['about']['mobile_phone']) ? $data['about']['mobile_phone'] : '' ?>');
                $('#about_twitter').val('<?= isset($data['about']['twitter']) ? $data['about']['twitter'] : '' ?>');
                $('#about_skype').val('<?= isset($data['about']['skype']) ? $data['about']['skype'] : '' ?>');

                $('body').on('click', '.toggle-basic', function() {
                    $('#view-basic').toggle();
                    $('#form-basic').toggle();
                });
                
                $('body').on('click', '.toggle-contact', function() {
                    $('#view-contact').toggle();
                    $('#form-contact').toggle();
                });

                $('#form-basic').submit(function(event) {
                    event.preventDefault();

                    $(this).find('[type="submit"]').html('Подождите...').attr('disabled', true);
                    $(this).find('.toggle-basic').hide();

                    $.ajax({
                        type: 'post',
                        url: '<?= APP::Module('Routing')->root ?>admin/users/api/about/update.json',
                        data: $(this).serialize(),
                        success: function() {
                            swal({
                                title: 'Основная информация была обновлена',
                                type: 'success',
                                timer: 2500,
                                showConfirmButton: false
                            });

                            $('#form-basic').find('[type="submit"]').html('Save').attr('disabled', false);
                            $('#form-basic').find('.toggle-basic').show();
                            
                            var about_username = $('#about_username').val();
                            var about_state = $('#about_state').val();
                            
                            $('#about-username-value').html(about_username ? about_username : 'user<?= $data['user']['id'] ?>');
                            $('#about-state-value').html(about_state);
                            
                            $('#view-basic').toggle();
                            $('#form-basic').toggle();
                        }
                    });
                });
                
                $('#form-contact').submit(function(event) {
                    event.preventDefault();

                    $(this).find('[type="submit"]').html('Подождите...').attr('disabled', true);
                    $(this).find('.toggle-contact').hide();
                    
                    $.ajax({
                        type: 'post',
                        url: '<?= APP::Module('Routing')->root ?>admin/users/api/about/update.json',
                        data: $(this).serialize(),
                        success: function() {
                            swal({
                                title: 'Контактная информация была обновлена',
                                type: 'success',
                                timer: 2500,
                                showConfirmButton: false
                            });

                            $('#form-contact').find('[type="submit"]').html('Сохранить').attr('disabled', false);
                            $('#form-contact').find('.toggle-contact').show();
                            
                            var about_mobile_phone = $('#about_mobile_phone').val();
                            var about_twitter = $('#about_twitter').val();
                            var about_skype = $('#about_skype').val();
                            
                            $('#about-mobile-phone-value').html(about_mobile_phone ? about_mobile_phone : 'нет');
                            $('#about-twitter-value').html(about_twitter ? about_twitter : 'нет');
                            $('#about-skype-value').html(about_skype ? about_skype : 'нет');
                            
                            $('#view-contact').toggle();
                            $('#form-contact').toggle();
                        }
                    });
                });
                
                var mail_events = <?= json_encode($data['mail']) ?>;
                
                $('body').on('click', '.mail_events', function() {
                    var id = $(this).data('id');
                    
                    $('#mail-events-modal .details .mail_id').html(mail_events[id].log.id);
                    $('#mail-events-modal .details .mail_state').html(mail_events[id].log.state);
                    $('#mail-events-modal .details .mail_result').html(mail_events[id].log.result);
                    $('#mail-events-modal .details .mail_retries').html(mail_events[id].log.retries);
                    $('#mail-events-modal .details .mail_ping').html(mail_events[id].log.ping);
                    $('#mail-events-modal .details .mail_cr_date').html(mail_events[id].log.cr_date);
                    $('#mail-events-modal .details .mail_letter_subject').html(mail_events[id].log.letter_subject);
                    $('#mail-events-modal .details .mail_letter_priority').html(mail_events[id].log.letter_priority);
                    $('#mail-events-modal .details .mail_sender_name').html(mail_events[id].log.sender_name);
                    $('#mail-events-modal .details .mail_sender_email').html(mail_events[id].log.sender_email);
                    $('#mail-events-modal .details .mail_transport_module').html(mail_events[id].log.transport_module);
                    $('#mail-events-modal .details .mail_transport_method').html(mail_events[id].log.transport_method);
                    
                    $('#mail-events-modal .events').empty();
                    
                    if (mail_events[id].events.length) {
                        $.each(mail_events[id].events, function(key, event) {
                            var details = event.details !== 'NULL' ? JSON.stringify(JSON.parse(event.details), undefined, 4) : 'Details not found';

                            $('#mail-events-modal .events').append([
                                '<div class="panel panel-collapse">',
                                    '<div class="panel-heading" role="tab" id="heading-mail-event-' + event.id + '">',
                                        '<h4 class="panel-title">',
                                            '<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse-mail-event-' + event.id + '" aria-expanded="false" aria-controls="collapse-mail-event-' + event.id + '"><span class="pull-right">' + event.cr_date + '</span>' + event.event + '</a>',
                                        '</h4>',
                                    '</div>',
                                    '<div id="collapse-mail-event-' + event.id + '" class="collapse" role="tabpanel" aria-labelledby="collapse-mail-event-' + event.id + '">',
                                        '<div class="panel-body"><pre>' + details + '</pre></div>',
                                    '</div>',
                                '</div>'
                            ].join(''));
                        });
                    } else {
                        $('#mail-events-modal .events').html('<div class="alert alert-warning" role="alert">События не найдены</div>');
                    }
                    
                    $('#mail-events-modal').modal('show');
                });
                
                var tunnel_tags = <?= json_encode($data['tunnels']['subscriptions']) ?>;
                
                $('body').on('click', '.tunnel_tags', function() {
                    var id = $(this).data('id');
                    
                    $('#tunnel-tags-modal .details .tunnel_uid').html(tunnel_tags[id].info.id);
                    $('#tunnel-tags-modal .details .tunnel_state').html(tunnel_tags[id].info.state);
                    $('#tunnel-tags-modal .details .tunnel_type').html(tunnel_tags[id].info.tunnel_type);
                    $('#tunnel-tags-modal .details .tunnel_id').html(tunnel_tags[id].info.tunnel_id);
                    $('#tunnel-tags-modal .details .tunnel_name').html(tunnel_tags[id].info.tunnel_name);
                    
                    $('#tunnel-tags-modal .tags').empty();
                    
                    if (tunnel_tags[id].tags.length) {
                        $.each(tunnel_tags[id].tags, function(key, tag) {
                            console.log(tag);
                            var info = tag.info !== 'NULL' ? JSON.stringify(JSON.parse(tag.info), undefined, 4) : 'Подробная информация отсутствует';

                            $('#tunnel-tags-modal .tags').append([
                                '<div class="panel panel-collapse">',
                                    '<div class="panel-heading" role="tab" id="heading-mail-event-' + tag.id + '">',
                                        '<h4 class="panel-title">',
                                            '<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse-mail-event-' + tag.id + '" aria-expanded="false" aria-controls="collapse-mail-event-' + tag.id + '"><span class="pull-right">' + tag.cr_date + '</span>' + tag.label_id + '</a>',
                                        '</h4>',
                                    '</div>',
                                    '<div id="collapse-mail-event-' + tag.id + '" class="collapse" role="tabpanel" aria-labelledby="collapse-mail-event-' + tag.id + '">',
                                        '<div class="panel-body"><pre>' + info + '</pre></div>',
                                    '</div>',
                                '</div>'
                            ].join(''));
                        });
                    } else {
                        $('#tunnel-tags-modal .tags').html('<div class="alert alert-warning" role="alert">События не найдены</div>');
                    }
                    
                    $('#tunnel-tags-modal').modal('show');
                });
                
                
                
                
                
                var tunnel_queue = <?= json_encode($data['tunnels']['queue']) ?>;
                
                $('body').on('click', '.tunnel_queue', function() {
                    var id = $(this).data('id');
                    
                    $('#tunnel-queue-modal .details .tunnel_queue_id').html(tunnel_queue[id].id);
                    $('#tunnel-queue-modal .details .tunnel_queue_tunnel_id').html(tunnel_queue[id].tunnel_id);
                    $('#tunnel-queue-modal .details .tunnel_queue_object_id').html(tunnel_queue[id].object_id);
                    $('#tunnel-queue-modal .details .tunnel_queue_timeout').html(tunnel_queue[id].timeout);
                    $('#tunnel-queue-modal .details .tunnel_queue_settings').html(tunnel_queue[id].settings);
                    $('#tunnel-queue-modal .details .tunnel_queue_cr_date').html(tunnel_queue[id].cr_date);
                    $('#tunnel-queue-modal .details .tunnel_queue_tunnel_type').html(tunnel_queue[id].tunnel_type);
                    $('#tunnel-queue-modal .details .tunnel_queue_tunnel_name').html(tunnel_queue[id].tunnel_name);
                    
                    $('#tunnel-queue-modal').modal('show');
                });
                
                
                
                
                
                
                var tags = <?= json_encode($data['tags']) ?>;
                
                $('body').on('click', '.tags', function() {
                    var id = $(this).data('id');
                    var value = tags[id].value !== 'NULL' ? JSON.stringify(JSON.parse(tags[id].value), undefined, 4) : 'Подробная информация отсутствует';
                    
                    
                    $('#tags-modal .details .tag_id').html(tags[id].id);
                    $('#tags-modal .details .tag_item').html(tags[id].item);
                    $('#tags-modal .details .tag_value pre').html(tags[id].value);
                    $('#tags-modal .details .tag_cr_date').html(tags[id].cr_date);

                    $('#tags-modal').modal('show');
                });
            });
        </script>
    </body>
</html>