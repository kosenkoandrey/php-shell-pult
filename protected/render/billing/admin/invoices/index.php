<?
$filters = htmlspecialchars(isset($_GET['filters']) ? APP::Module('Crypt')->Decode($_GET['filters']) : '{"logic":"intersect","rules":[{"method":"amount","settings":{"logic":">","value":"0"}}]}');
?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Счета</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/modules/billing/invoices/rules.css" rel="stylesheet">

        <style>
            #invoices-table-header .actionBar .actions > button {
                display: none;
            }
        </style>

        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Счета' => 'admin/billing/invoices'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Управление счетами</h2>
                            <ul class="actions">
                                <li class="dropdown">
                                    <a href="javascript:void(0)" data-toggle="dropdown">
                                        <i class="zmdi zmdi-more-vert"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a href="<?= APP::Module('Routing')->root ?>admin/billing/invoices/add">Создать счет</a></li>
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
                                        <li><a data-action="remove" href="javascript:void(0)">Удалить</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-vmiddle" id="invoices-table">
                                <thead>
                                    <tr>
                                        <th data-column-id="id" data-type="numeric" data-order="desc">ID</th>
                                        <th data-column-id="user_id" data-formatter="user">Пользователь</th>
                                        <th data-column-id="amount" data-formatter="amount">Сумма</th>
                                        <th data-column-id="author" data-formatter="author">Автор</th>
                                        <th data-column-id="state" data-formatter="state">Состояние</th>
                                        <th data-column-id="up_date">Дата</th>
                                        <th data-column-id="actions" data-formatter="actions" data-sortable="false">Действия</th>
                                    </tr>
                                </thead>
                            </table>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/json/dist/jquery.json.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.updated.min.js"></script>

        <script src="<?= APP::Module('Routing')->root ?>public/modules/billing/invoices/rules.js"></script>

        <? APP::Render('core/widgets/js') ?>

        <script>
            $(document).ready(function() {
                $('#search').BillingInvoicesRules({
                    'debug': true
                });

                $(document).on('click', '#search_results_actions a', function () {
                    var action = $(this).data('action');

                    swal({
                        title: 'Вы уверены?',
                        text: 'Это действие будет невозможно отменить',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Да',
                        cancelButtonText: 'Нет',
                        closeOnConfirm: false,
                        closeOnCancel: true
                    }, function(isConfirm){
                        if (isConfirm) {
                            $.post('<?= APP::Module('Routing')->root ?>admin/billing/invoices/api/action.json', {
                                action: action,
                                rules: $('#search').val()
                            }, function() {
                                invoices_table.bootgrid('reload', true);
                                swal('Готово!', 'Действие было успешно выполнено', 'success');
                            });
                        }
                    });
                });

                $(document).on('click', '#render-table', function () {
                    $('#invoices-table').bootgrid('reload');
                });

                var invoices_table = $("#invoices-table").bootgrid({
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
                    ajaxSettings: {
                        method: 'POST',
                        cache: false,
                        contentType: 'application/json'
                    },
                    ajax: true,
                    url: '<?= APP::Module('Routing')->root ?>admin/billing/invoices/api/search.json',
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
                        user: function(column, row) {
                            return '<a href="<?= APP::Module('Routing')->root ?>admin/users/profile/' + row.user_id + '" target="_blank">' + row.user_email + '</a>';
                        },
                        author: function(column, row) {
                            return parseInt(row.author) ? '<a href="<?= APP::Module('Routing')->root ?>admin/users/profile/' + row.author + '" target="_blank">' + row.author_email + '</a>' : '-';
                        },
                        amount: function(column, row) {
                            return row.amount + ' руб.';
                        },
                        state: function(column, row) {
                            switch (row.state) {
                                case 'new': return 'новый'; break;
                                case 'processed': return 'в работе'; break;
                                case 'success': return 'оплачен'; break;
                                case 'revoked': return 'аннулирован'; break;
                            }
                        },
                        actions: function(column, row) {
                            return  '<a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/billing/invoices/details/' + row.invoice_id_token + '" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-info-outline"></span></a> ' +
                                    //'<a target="_blank" href="<?= APP::Module('Routing')->root ?>admin/billing/invoices/edit/' + row.invoice_id_token + '" class="btn btn-sm btn-default btn-icon waves-effect waves-circle"><span class="zmdi zmdi-edit"></span></a> ' +
                                    '<a href="javascript:void(0)" class="btn btn-sm btn-default btn-icon waves-effect waves-circle remove-invoice" data-invoice-id="' + row.id + '"><span class="zmdi zmdi-delete"></span></a>';
                        }
                    }
                }).on('loaded.rs.jquery.bootgrid', function () {
                    invoices_table.find('.remove-invoice').on('click', function (e) {
                        var invoice_id = $(this).data('invoice-id');

                        swal({
                            title: 'Вы действительно хотите удалить счет?',
                            text: 'Это действие будет невозможно отменить',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#DD6B55',
                            confirmButtonText: 'Удалить',
                            cancelButtonText: 'Отмена',
                            closeOnConfirm: false,
                            closeOnCancel: true
                        }, function(isConfirm){
                            if (isConfirm) {
                                $.post('<?= APP::Module('Routing')->root ?>admin/billing/invoices/api/remove.json', {
                                    id: invoice_id
                                }, function() {
                                    invoices_table.bootgrid('reload', true);
                                    swal({
                                        title: 'Готово!',
                                        text: 'Счет был успешно удален',
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
            });
        </script>
    </body>
</html>