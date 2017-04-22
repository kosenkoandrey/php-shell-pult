<?
$filters = htmlspecialchars(isset(APP::Module('Routing')->get['filters']) ? APP::Module('Crypt')->Decode(APP::Module('Routing')->get['filters']) : '{"logic":"intersect","rules":[{"method":"email","settings":{"logic":"LIKE","value":"%"}}]}');
?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Управление пользователями</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"> 
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/tableexport.js/dist/css/tableexport.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/modules/users/rules.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/modules/tunnels/scheme/letter-selector/style.css" rel="stylesheet">

        <style>
            #users-table-header .actionBar .actions > button {
                display: none;
            }
            .btn-toolbar {
                margin-left: 10px !important;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Пользователи' => 'admin/users'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Управление пользователями</h2>
                            <ul class="actions">
                                <li class="dropdown">
                                    <a href="javascript:void(0)" data-toggle="dropdown">
                                        <i class="zmdi zmdi-more-vert"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/add">Добавить пользователя</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/roles">Управление ролями</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/oauth/clients">OAuth клиенты</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/services">Сервисы</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/auth">Аутентификация</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/passwords">Пароли</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/notifications">Уведомления</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/users/timeouts">Таймауты</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body card-padding">
                            <input type="hidden" name="search" value="<?= $filters ?>" id="search">
                            <div class="btn-group">
                                <button type="button" id="render-table" class="btn btn-default"><i class="zmdi zmdi-check"></i> Сделать выборку</button>
                            
                                <div class="btn-group">
                                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button">
                                        Выполнить действие <span class="caret"></span>
                                    </button>
                                    <ul id="search_results_actions" class="dropdown-menu" role="menu">
                                        <li><a data-action="change_state" href="javascript:void(0)">Изменить состояние</a></li>
                                        <li><a data-action="add_tag" href="javascript:void(0)">Добавить метку</a></li>
                                        <li><a data-action="add_group" href="javascript:void(0)">Добавить в группу</a></li>
                                        <li><a data-action="delete_group" href="javascript:void(0)">Удалить из группы</a></li>
                                        <li class="divider"></li>
                                        <li><a data-action="send_mail" href="javascript:void(0)">Отправить письмо</a></li>
                                        <li class="divider"></li>
                                        <li><a data-action="tunnel_subscribe" href="javascript:void(0)">Подписать на туннель</a></li>
                                        <li><a data-action="tunnel_pause" href="javascript:void(0)">Поставить туннель на паузу</a></li>
                                        <li><a data-action="tunnel_complete" href="javascript:void(0)">Завершить туннель</a></li>
                                        <li><a data-action="tunnel_manually_complete" href="javascript:void(0)">Подписать и завершить туннель</a></li>
                                        <li class="divider"></li>
                                        <li><a data-action="utm" data-ask="no" href="javascript:void(0)">UTM-анализ</a></li>
                                        <li><a data-action="utm_roi" data-ask="no" href="javascript:void(0)">UTM-анализ ROI</a></li>
                                        <li><a data-action="open_letter_pct" data-ask="no" href="javascript:void(0)">Анализ по % открытия</a></li>
                                        <li><a data-action="open_letter_time" data-ask="no" href="javascript:void(0)">Анализ по времени открытия</a></li>
                                        <li><a data-action="rfm" href="javascript:void(0)">RFM анализ</a></li>
                                        <li><a data-action="cohort" data-ask="no" href="javascript:void(0)">Когортный анализ</a></li>
                                        <li><a data-action="geo" data-ask="no" href="javascript:void(0)">Geo анализ</a></li>
                                        <li><a data-action="sales" data-ask="no" href="javascript:void(0)">Sales tool</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-vmiddle" id="users-table">
                                <thead>
                                    <tr>
                                        <th data-column-id="id" data-type="numeric" data-order="desc">ID</th>
                                        <th data-column-id="email" data-formatter="email">E-Mail</th>
                                        <th data-column-id="tel" data-visible="false">Телефон</th>
                                        <th data-column-id="yaregion" data-visible="false">Регион</th>
                                        <th data-column-id="amount" data-visible="false">Выручка</th>
                                        <th data-column-id="social" data-formatter="social" data-visible="false">Social</th>
                                        <th data-column-id="role" data-formatter="role">Роль</th>
                                        <th data-column-id="state" data-formatter="state">Состояние</th>
                                        <th data-column-id="reg_date">Дата регистрации</th>
                                        <th data-column-id="last_visit">Последний визит</th>
                                        <th data-column-id="actions" data-formatter="actions" data-sortable="false">Действия</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            
            <div class="modal fade" id="user-modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body">
                            <form id="user-action-form" method="post" class="form-horizontal"></form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link" id="exec_action">Done</button>
                            <button type="button" class="btn btn-link" data-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

            <? APP::Render('admin/widgets/footer') ?>
        </section>

        <? APP::Render('core/widgets/page_loader') ?>
        <? APP::Render('core/widgets/ie_warning') ?>

        <!-- Javascript Libraries -->
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/json/dist/jquery.json.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        
        <script src="<?= APP::Module('Routing')->root ?>public/modules/tunnels/scheme/letter-selector/script.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/modules/tunnels/scheme/tunnel-selector/script.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/modules/users/rules.js"></script> 
        
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/xlsx-js/xlsx.core.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/file-saverjs/FileSaver.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/tableexport.js/dist/js/tableexport.min.js"></script>
        
        <script src="<?= APP::Module('Routing')->root ?>public/modules/users/rules.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/modules/groups/script.js"></script>
        <? APP::Render('core/widgets/js') ?>
        <script>
            $(document).ready(function() {
                $('#search').RefRulesEditor({
                    'debug': true,
                    'url' : '<?= APP::Module('Routing')->root ?>'
                });
                
                var user_modal = {
                    build : function(action, rules){
                        var modal = $('#user-modal');
                        var form = $('#user-action-form', modal);
                        form.html('');
                        
                        form.append(
                            [
                                "<input type='hidden' value='" + action + "' name='action'>",
                                "<input type='hidden' value='" + rules + "' name='rules'>"
                            ].join('')
                        );
                
                        switch (action) {
                            case 'tunnel_subscribe' :
                                $('.modal-title', modal).html('Подписка на туннель');
                                $('#exec_action').html('Подписать');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Туннель</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input id="tunnel_id" type="text" value="" name="settings[tunnel][0]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Тип объекта</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="select">',
                                                    '<select name="settings[tunnel][1]"  class="form-control">',
                                                        '<option value="actions">действие</option>',
                                                        '<option value="conditions">условие</option>',
                                                        '<option value="timeouts">таймаут</option>',
                                                    '</select>',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">ID объекта</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[tunnel][2]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Таймаут</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[tunnel][3]" class="form-control" placeholder="cек." >',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        /*
                                        '<h4 class="modal-title m-b-20">Индоктринация</h4>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Туннель</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input id="welcome_tunnel_id" type="text" value="" name="settings[welcome][0]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Тип объекта</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="select">',
                                                    '<select name="settings[welcome][1]"  class="form-control">',
                                                        '<option value="actions">действие</option>',
                                                        '<option value="conditions">условие</option>',
                                                        '<option value="timeouts">таймаут</option>',
                                                    '</select>',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">ID объекта</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[welcome][2]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Таймаут</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[welcome][3]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        */
                                        '<h4 class="modal-title m-b-20">Активация</h4>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Письмо</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input id="tunnel_activation_letter_id" type="hidden" value="" name="settings[activation][0]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">URL для переадресации</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[activation][1]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<h4 class="modal-title m-b-20">Разное</h4>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Таймаут очереди</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[queue_timeout]" class="form-control" placeholder="сек">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-4 control-label">Источник</label>',
                                            '<div class="col-sm-8">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[source]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                        
                                    ].join('')
                                );
                        
                                $('#tunnel_id', modal).TunnelSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                $('#welcome_tunnel_id', modal).TunnelSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                $('#tunnel_activation_letter_id', modal).MailingLetterSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                
                                modal.modal('show');
                                break;
                            case 'remove' :
                                var data = form.serialize();
                                user_modal.send(data, false);
                                break;
                            case 'add_tag' :
                                $('.modal-title', modal).html('Добавление метки');
                                $('#exec_action').html('Добавить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Наименование</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[item]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Значение</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="text" value="" name="settings[value]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                modal.modal('show');
                                break;
                            case 'change_state' :
                                $('.modal-title', modal).html('Изменение состояния');
                                $('#exec_action').html('Выполнить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<div class="col-sm-12">',
                                                '<div class="select">',
                                                    '<select name="settings[value]"  class="form-control">',
                                                        '<option value="active">активный</option>',
                                                        '<option value="inactive">неактивный</option>',
                                                        '<option value="blacklist">в черном списке</option>',
                                                        '<option value="dropped">невозможно доставить почту</option>',
                                                    '</select>',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                modal.modal('show');
                                break;
                            case 'send_mail' :
                                $('.modal-title', modal).html('Отправка письма');
                                $('#exec_action').html('Отправить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Письмо</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="hidden" id="in_letter" value="" name="settings[letter]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Сохранять копии</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="toggle-switch m-t-10">',
                                                    '<input id="settings_save_copy" name="settings[save_copy]" type="checkbox" hidden="hidden">',
                                                    '<label for="settings_save_copy" class="ts-helper"></label>',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                $('#in_letter', modal).MailingLetterSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                modal.modal('show');
                                break;
                            case 'tunnel_pause' :
                                $('.modal-title', modal).html('Поставить туннель на паузу');
                                $('#exec_action').html('Выполнить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Туннель</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="hidden" id="tunnel_id" value="" name="settings[tunnel_id]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                $('#tunnel_id', modal).TunnelSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                modal.modal('show');
                                break;
                            case 'tunnel_complete' :
                                $('.modal-title', modal).html('Завершить туннель');
                                $('#exec_action').html('Выполнить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Туннель</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="hidden" id="tunnel_id" value="" name="settings[tunnel_id]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                $('#tunnel_id', modal).TunnelSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                modal.modal('show');
                                break;
                            case 'tunnel_manually_complete' :
                                $('.modal-title', modal).html('Подписать и завершить туннель');
                                $('#exec_action').html('Выполнить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Туннель</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="hidden" id="tunnel_id" value="" name="settings[tunnel_id]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                $('#tunnel_id', modal).TunnelSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                modal.modal('show');
                                break;
                            case 'utm_roi' :
                                form.attr('target', '_blank');
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/analytics/utm/roi');
                                var data = form.serialize();
                                user_modal.send(data, true);
                                break;
                            case 'cohort' :
                                form.attr('target', '_blank');
                                var data = form.serialize();
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/analytics/cohorts');
                                form.append('<input type="hidden" name="group" value="month">');
                                form.append('<input type="hidden" name="indicators[]" value="total_subscribers_active">');
                                form.append('<input type="hidden" name="indicators[]" value="total_subscribers_unsubscribe">');
                                form.append('<input type="hidden" name="indicators[]" value="total_subscribers_dropped">');
                                form.append('<input type="hidden" name="indicators[]" value="total_clients">');
                                form.append('<input type="hidden" name="indicators[]" value="total_orders">');
                                form.append('<input type="hidden" name="indicators[]" value="total_revenue">');
                                form.append('<input type="hidden" name="indicators[]" value="ltv_client">');
                                form.append('<input type="hidden" name="indicators[]" value="cost">');
                                form.append('<input type="hidden" name="indicators[]" value="subscriber_cost">');
                                form.append('<input type="hidden" name="indicators[]" value="client_cost">');
                                form.append('<input type="hidden" name="indicators[]" value="roi">');
                                user_modal.send(data, true);
                                break;
                            case 'open_letter_pct' :
                                form.attr('target', '_blank');
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/analytics/open/letter/pct');
                                var data = form.serialize();
                                user_modal.send(data, true);
                                break;
                            case 'open_letter_time' :
                                form.attr('target', '_blank');
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/analytics/open/letter/time');
                                var data = form.serialize();
                                user_modal.send(data, true);
                                break;
                            case 'rfm' :
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<div class="col-sm-12">',
                                                '<a class="rfm-button btn btn-lg btn-default btn-block" href="<?= APP::Module('Routing')->root ?>admin/analytics/rfm/billing">Покупки</a>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<div class="col-sm-12">',
                                                '<a class="rfm-button btn btn-lg btn-default btn-block" href="<?= APP::Module('Routing')->root ?>admin/analytics/rfm/mail/open">Открытия писем</a>',
                                            '</div>',
                                        '</div>',
                                        '<div class="form-group">',
                                            '<div class="col-sm-12">',
                                                '<a class="rfm-button btn btn-lg btn-default btn-block" href="<?= APP::Module('Routing')->root ?>admin/analytics/rfm/mail/click">Клики в письмах</a>',
                                            '</div>',
                                        '</div>',
                                    ].join('')
                                );
                                $('#send_action').hide();
                                modal.modal('show');
                                break;
                            case 'geo' :
                                form.attr('target', '_blank');
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/analytics/geo');
                                var data = form.serialize();
                                user_modal.send(data, true);
                                break;
                            case 'utm' :
                                form.attr('target', '_blank');
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/analytics/utm');
                                var data = form.serialize();
                                user_modal.send(data, true);
                                break;
                            case 'add_group' :
                                $('.modal-title', modal).html('Добавить в группу');
                                $('#exec_action').html('Добавить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Группа</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="hidden" id="group_id" value="" name="settings[group_id]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                $('#group_id', modal).GroupSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                modal.modal('show');
                                break;
                            case 'delete_group' :
                                $('.modal-title', modal).html('Удалить из группы');
                                $('#exec_action').html('Удалить');
                                
                                form.append(
                                    [
                                        '<div class="form-group">',
                                            '<label for="" class="col-sm-3 control-label">Группа</label>',
                                            '<div class="col-sm-9">',
                                                '<div class="fg-line">',
                                                    '<input type="hidden" id="group_id" value="" name="settings[group_id]" class="form-control">',
                                                '</div>',
                                            '</div>',
                                        '</div>'
                                    ].join('')
                                );
                        
                                $('#group_id', modal).GroupSelector({'url':'<?= APP::Module('Routing')->root ?>'});
                                modal.modal('show');
                                break;
                            case 'sales' :
                                form.attr('target', '_blank');
                                form.attr('action', '<?= APP::Module('Routing')->root ?>admin/billing/sales');
                                var data = form.serialize();
                                user_modal.send(data, true);
                                break;
                        }
                        
                    },
                    send : function(data, submit){
                        if(submit){
                            var modal = $('#user-modal');
                            $('#user-action-form', modal).submit();
                        }else{
                            var modal = $('#user-modal');
                            $.post('<?= APP::Module('Routing')->root ?>admin/users/api/action.json', data, function(res) { 
                                modal.modal('hide');
                                $('#users-table').bootgrid('reload', true);

                                swal({
                                    title: 'Готово',
                                    text: 'Действие было выполнено',
                                    type: 'success',
                                    showCancelButton: false,
                                    confirmButtonText: 'Ok',
                                    closeOnConfirm: false
                                });
                            });
                            return false;
                        }
                    }
                };
                
                $(document).on('click', '#render-table', function () {
                    $('#users-table').bootgrid('reload');
                });
                
                $(document).on('click', '#search_results_actions a', function () {
                    var action = $(this).data('action');
                    var ask = $(this).data('ask') === undefined ? 'yes' : $(this).data('ask');
                    
                    if(ask == 'yes'){
                        swal({
                            title: 'Вы уверены?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#DD6B55',
                            confirmButtonText: 'Да',
                            cancelButtonText: 'Отменить',
                            closeOnConfirm: true,
                            closeOnCancel: true
                        }, function(isConfirm){
                            if (isConfirm) {
                                user_modal.build(action, $('#search').val());
                            }
                        });
                    }else{
                        user_modal.build(action, $('#search').val());
                    }
                });
                
                $(document).on('click', '#exec_action', function(){
                    var modal = $('#user-modal');
                    var form = $('#user-action-form', modal);
                    var data = form.serialize();
                    user_modal.send(data, false);
                    return false;
                });
                
                $('#user-modal').on('hide.bs.modal', function (event) {
                    $('#user-action-form', $(this)).html('');
                });

                var users_table = $("#users-table").bootgrid({
                    requestHandler: function (request) {
                        var model = {
                            search: $('#search').val(),
                            current: request.current,
                            rows: request.rowCount
                        };
                        for (var key in request.sort) {
                            model.sort_by = key;
                            model.sort_direction = request.sort[key];
                        }
                        return JSON.stringify(model);
                    },
                    ajax: true,
                    ajaxSettings: {
                        method: 'POST',
                        cache: false,
                        contentType: 'application/json'
                    },
                    url: '<?= APP::Module('Routing')->root ?>admin/users/api/search.json',
                    css: {
                        icon: 'zmdi icon',
                        iconColumns: 'zmdi-view-module',
                        iconDown: 'zmdi-chevron-down pull-left',
                        iconRefresh: 'zmdi-refresh',
                        iconUp: 'zmdi-chevron-up pull-left'
                    },
                    templates: {
                        search: ""
                    },
                    formatters: {
                        role: function(column, row) {
                            switch (row.role) {
                                case 'new':
                                case 'user': 
                                    return 'подписчик'; break;
                                case 'admin': 
                                    return 'администратор'; break;
                                case 'tech-admin': 
                                    return 'технический администратор'; break;
                                default: 
                                    return row.role; break;
                            }
                        },
                        state: function(column, row) {
                            switch (row.state) {
                                case 'inactive': 
                                    return 'ожидает активации'; break;
                                case 'active': 
                                    return 'активный'; break;
                                case 'pause': 
                                    return 'временно отписан'; break;
                                case 'unsubscribe': 
                                    return 'отписан'; break;
                                case 'blacklist': 
                                    return 'в блэк-листе'; break;
                                case 'dropped': 
                                    return 'невозможно доставить почту'; break;
                                default: 
                                    return row.state; break;
                            }
                        },
                        email: function(column, row) {
                            return  '<a href="<?= APP::Module('Routing')->root ?>admin/users/profile/' + row.id + '" target="_blank">' + row.email + '</a>';
                        },
                        actions: function(column, row) {
                            return  '<a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users/edit/' + row.user_id_token + '" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-edit"></span></a> ' + 
                                    '<a href="javascript:void(0)" class="btn btn-sm btn-default btn-icon waves-effect waves-circle remove-user" data-user-id="' + row.id + '"><span class="zmdi zmdi-delete"></span></a>';
                        },
                        social: function(column, row) {
                            var html = '';
                            $.each(row.social, function(i, j){
                                switch(j.service){
                                    case 'vk' :
                                        html += '<a target="_blank" href="https://vk.com/id'+j.extra+'" class="btn btn-sm btn-default btn-icon waves-effect waves-circle">'+j.service+'</a>';
                                        break;
                                    case 'fb' :
                                        html += '<a target="_blank" href="http://facebook.com/'+j.extra+'" class="btn btn-sm btn-default btn-icon waves-effect waves-circle">'+j.service+'</a>';
                                        break;
                                }
                            });
                            return html;
                        }
                    }
                }).on('loaded.rs.jquery.bootgrid', function () {
                    export_table.update();
                    
                    users_table.find('.remove-user').on('click', function (e) {
                        var user_id = $(this).data('user-id');
                        swal({
                            title: 'Вы действительно хотите удалить пользователя?',
                            text: 'Это действие будет невозможно отменить',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#DD6B55',
                            confirmButtonText: 'Да',
                            cancelButtonText: 'Отмена',
                            closeOnConfirm: false,
                            closeOnCancel: true
                        }, function(isConfirm){
                            if (isConfirm) {
                                $.post('<?= APP::Module('Routing')->root ?>admin/users/api/remove.json', {
                                    id: user_id
                                }, function() { 
                                    users_table.bootgrid('reload', true);
                                    
                                    swal({
                                        title: 'Готово!',
                                        text: 'Пользователь был успешно удален',
                                        type: 'success',
                                        showCancelButton: false,
                                        confirmButtonText: 'Ok',
                                        closeOnConfirm: false
                                    });
                                });
                            }
                        });
                    });
                });
                
                
                var export_table = $("#users-table").tableExport({
                    fileName: 'export_users',
                    formats: ['xlsx', 'csv', 'txt']
                });
                
                export_table.xlsx.buttonContent = 'Excel';
                export_table.csv.buttonContent = 'CSV';
                export_table.txt.buttonContent = 'TEXT';
            });
        </script>
    </body>
  </html>
