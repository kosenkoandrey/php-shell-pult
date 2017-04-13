<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Почта - Журнал</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
        
        <style>
            #mail-list-chart {
                width: 100%;
                height: 300px;
                font-size: 14px;
                line-height: 1.2em;
            }
            #log-table-header .actionBar .actions > button {
                display: none;
            }
        </style>
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Почта' => 'admin/mail/log',
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Журнал</h2>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-vmiddle" id="log-table">
                                <thead>
                                    <tr>
                                        <th data-column-id="id" data-visible="false" data-type="numeric" data-order="desc">ID</th>
                                        <th data-column-id="email" data-formatter="user">Получатель</th>
                                        <th data-column-id="letter" data-formatter="letter">Письмо</th>
                                        <th data-column-id="copies" data-formatter="copies" data-css-class="text-uppercase">Копии</th>
                                        <th data-column-id="sender" data-formatter="sender">Отправитель</th>
                                        <th data-column-id="transport" data-formatter="transport" data-visible="false">Транспорт</th>
                                        <th data-column-id="state" data-css-class="text-uppercase" data-formatter="state">Состояние</th>
                                        <th data-column-id="result">Результат</th>
                                        <th data-column-id="retries" data-visible="false">Попытки</th>
                                        <th data-column-id="ping" data-visible="false">Время ответа</th>
                                        <th data-column-id="cr_date">Дата</th>
                                        <th data-column-id="actions" data-formatter="actions">Действия</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <? APP::Render('admin/widgets/footer') ?>
        </section>
        
        <div class="modal fade" id="mail-events-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">События письма</h4>
                    </div>
                    <div class="modal-body" id="accordion"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>
            </div>
        </div>

        <? APP::Render('core/widgets/page_loader') ?>
        <? APP::Render('core/widgets/ie_warning') ?>

        <!-- Javascript Libraries -->
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/Waves/dist/waves.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/flot/jquery.flot.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/flot/jquery.flot.resize.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/flot/jquery.flot.time.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/moment/min/moment.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        
        <? APP::Render('core/widgets/js') ?>
        
        <script>
            $(document).ready(function() {
                var log_table = $("#log-table").bootgrid({
                    ajax: true,
                    ajaxSettings: {
                        method: 'POST',
                        cache: false
                    },
                    url: '<?= APP::Module('Routing')->root ?>admin/mail/api/log/list.json',
                    css: {
                        icon: 'zmdi icon',
                        iconColumns: 'zmdi-view-module',
                        iconDown: 'zmdi-chevron-down pull-left',
                        iconRefresh: 'zmdi-refresh',
                        iconUp: 'zmdi-chevron-up pull-left'
                    },
                    formatters: {
                        user: function(column, row) {
                            return  '<a href="<?= APP::Module('Routing')->root ?>admin/users/profile/' + row.user + '" target="_blank">' + row.email + '</a>';
                        },
                        letter: function(column, row) {
                            return  '<a href="<?= APP::Module('Routing')->root ?>admin/mail/letters/' + row.letter_group[1] + '/edit/' + row.letter[1] + '" target="_blank">' + row.subject + '</a>';
                        },
                        copies: function(column, row) {
                            return  parseInt(row.copies) ? '<div class="btn-group btn-group-xs" role="group"><a href="<?= APP::Module('Routing')->root ?>mail/html/' + row.id_token + '" target="_blank" class="btn btn-default waves-effect">HTML</a><a href="<?= APP::Module('Routing')->root ?>mail/plaintext/' + row.id_token + '" target="_blank" class="btn btn-default waves-effect">PLAINTEXT</a></div>' : 'none';
                        },
                        sender: function(column, row) {
                            return  '<a href="<?= APP::Module('Routing')->root ?>admin/mail/senders/' + row.sender_group[1] + '/edit/' + row.sender[1] + '" target="_blank">' + row.sender_name + ' (' + row.sender_email + ')</a>';
                        },
                        transport: function(column, row) {
                            return  '<a href="<?= APP::Module('Routing')->root ?>' + row.transport_settings + '" target="_blank">' + row.transport_module + ' / ' + row.transport_method + '</a>';
                        },
                        state: function(column, row) {
                            switch (row.state) {
                                case 'wait': return 'В очереди';
                                case 'success': return 'Отправлено';
                                case 'error': return 'Ошибка';
                            }
                        },
                        actions: function(column, row) {
                            var events_icon = parseInt(row.events) ? 'notifications-active' : 'notifications-none';
                            
                            return  '<a href="javascript:void(0)" data-token="' + row.id_token + '" class="mail-events btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-' + events_icon + '"></span></a> ' +
                                    '<a href="javascript:void(0)" class="btn btn-sm btn-default btn-icon waves-effect waves-circle remove-log" data-log-id="' + row.id + '"><span class="zmdi zmdi-delete"></span></a>';
                        }
                    }
                }).on('loaded.rs.jquery.bootgrid', function () {
                    log_table.find('.mail-events').on('click', function (e) {
                        var token = $(this).data('token');
                        
                        $('#mail-events-modal .modal-body').html('<center><div class="preloader pl-xxl"><svg class="pl-circular" viewBox="25 25 50 50"><circle class="plc-path" cx="50" cy="50" r="20" /></svg></div></center>');
                        $('#mail-events-modal').modal('show');
                        
                        $.ajax({
                            type: 'post',
                            url: '<?= APP::Module('Routing')->root ?>admin/mail/api/events/list.json',
                            data: {
                                token: token
                            },
                            success: function(events) {
                                if (events.length) {
                                    $('#mail-events-modal .modal-body').empty();
                                
                                    $.each(events, function(key, event) {
                                        var details = event.details !== 'NULL' ? JSON.stringify(JSON.parse(event.details), undefined, 4) : 'Details not found';
                                        
                                        $('#mail-events-modal .modal-body').append([
                                            '<div class="panel panel-collapse">',
                                                '<div class="panel-heading" role="tab" id="heading-mail-event-' + key + '">',
                                                    '<h4 class="panel-title">',
                                                        '<a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse-mail-event-' + key + '" aria-expanded="false" aria-controls="collapse-mail-event-' + key + '"><span class="pull-right">' + event.cr_date + '</span>' + event.event + '</a>',
                                                    '</h4>',
                                                '</div>',
                                                '<div id="collapse-mail-event-' + key + '" class="collapse" role="tabpanel" aria-labelledby="collapse-mail-event-' + key + '">',
                                                    '<div class="panel-body"><pre>' + details + '</pre></div>',
                                                '</div>',
                                            '</div>'
                                        ].join(''));
                                    });
                                } else {
                                    $('#mail-events-modal .modal-body').html('<div class="alert alert-warning" role="alert">События не найдены</div>');
                                }
                            }
                        });
                    });
                });
            });
        </script>
    </body>
</html>