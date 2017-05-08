<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sales tool</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bootgrid/jquery.bootgrid.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/modules/billing/invoices/rules.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"> 

        <style>

        </style>

        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <?
        APP::Render('admin/widgets/header', 'include', [
            'Sales tool' => 'admin/billing/sales'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2>Управление счетами</h2>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover table-vmiddle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Имя</th>
                                        <th>Фамилия</th>
                                        <th>Телефон</th>
                                        <th>Комментарии</th>
                                        <th>Счета</th>
                                        <th>Продажа</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? foreach($data['users_data'] as $user){ ?>
                                        <tr <? if ($user['inv_cnt']) { ?>class="warning"<? } ?>>
                                            <td><?= $user['id'] ?></td>
                                            <td><a href="<?= APP::Module('Routing')->root ?>admin/users/profile/<?= $user['id'] ?>" target="_blank"><?= $user['email'] ?></a></td>
                                            <td><?= isset($data['users'][$user['id']]['firstname']) ? $data['users'][$user['id']]['firstname'] : '' ?></td>
                                            <td><?= isset($data['users'][$user['id']]['lastname']) ? $data['users'][$user['id']]['lastname'] : '' ?></td>
                                            <td><?= isset($data['users'][$user['id']]['tel']) ? $data['users'][$user['id']]['tel'] : '' ?></td>
                                            <td><button data-email="<?= $user['email'] ?>" data-user="<?= $user['id'] ?>" class="user-comments <?= $user['id'] ?> btn btn-<? if (!$user['comment_data']) { ?>default<? } else { ?>primary<? } ?>"><?= $user['comment'] ?></button></td>
                                            <td><button data-email="<?= $user['email'] ?>" data-user="<?= $user['id'] ?>" class="user-invoices btn btn-<? if (!$user['inv_cnt'] && !$user['inv_pr_cnt']['inv_cnt']) { ?>default disabled<? } else { ?>warning<? } ?>"><?= $user['inv'] ?>/<?= $user['inv_pr'] ?></button></td>
                                            <? if (!$user['sale_token']) { ?>
                                                <td>Нет</td>
                                            <? } else { ?>
                                                <td><?= array_search((int) $user['sale_token'], $data['sale'][$data['user_tunnels'][$user['id']][0]]) === false ? 'Нет' : '<b>Да</b>' ?><br><?= $data['tunnels'][$data['user_tunnels'][$user['id']][0]] ?></td>
                                            <? } ?>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            

                <!-- Comments Modal -->
                <div class="modal fade" id="comments-modal" tabindex="-1" role="dialog" aria-labelledby="comments-modal-label">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="comments-modal-label">Комментарии - <span class="comment-user"></span></h4>
                            </div>
                            <div class="modal-body">
                                <div class="comments-list"></div>
                                <div class="form-comment">
                                    <textarea class="form-control" id="new-user-comment" style="width: 100%; height: 80px"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="submit-comment btn btn-primary" data-user="0" type="button">Отправить комментарий</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Modal -->
                <div class="modal fade" id="invoices-modal" tabindex="-1" role="dialog" aria-labelledby="invoices-modal-label">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="invoices-modal-label">Счета -<span class="invoice-user"></span></h4>
                            </div>
                            <div class="modal-body">
                                invoices
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
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

        <? APP::Render('core/widgets/js') ?>

        <script>
            $(document).ready(function() {
                $('.user-comments').click(function () {
                    var user = $(this).data('user');
                    var email = $(this).data('email');

                    $('#comments-modal .comment-user').html(email);
                    $('#comments-modal .comments-list').html('Загрузка...');
                    $('#comments-modal .submit-comment').attr("data-user", user);

                    $('#comments-modal').modal('show');

                    $.ajax({
                        type: 'post',
                        url: '<?= APP::Module('Routing')->root ?>admin/billing/sales',
                        data: {
                            do: 'comments',
                            user: user
                        },
                        success: function (data) {
                            $('#comments-modal .comments-list').empty();

                            if (data.length) {
                                $.each(data, function () {
                                    $('#comments-modal .comments-list').append('<div class="comment-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b>' + this.cr_date + '</b><br>' + this.comment + '</div>');
                                });
                            }
                        }
                    });
                });

                $('.submit-comment').click(function () {
                    var user = $(this).data('user');
                    var comment = $('#new-user-comment').val();

                    if (comment) {
                        $('.user-comments.' + user).html('Загрузка...');

                        $.ajax({
                            type: 'post',
                            url: '<?= APP::Module('Routing')->root ?>admin/billing/sales',
                            data: {
                                do: 'post-comment',
                                user: user,
                                comment: comment
                            },
                            success: function (data) {
                                $('.user-comments.' + user).html(data.short);
                            }
                        });

                        $('#new-user-comment').val('');
                        $('#comments-modal .comments-list').append('<div class="comment-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b>Только что</b><br>' + comment + '</div>');
                    }
                });

                $('.user-invoices:not(.disabled)').click(function () {
                    var user = $(this).data('user');
                    var email = $(this).data('email');

                    $('#invoices-modal .invoice-user').html(email);
                    $('#invoices-modal .modal-body').html('Загрузка...');

                    $('#invoices-modal').modal('show');

                    $.ajax({
                        type: 'post',
                        url: '<?= APP::Module('Routing')->root ?>admin/billing/sales',
                        data: {
                            do: 'invoices',
                            user: user
                        },
                        success: function (data) {
                            $('#invoices-modal .modal-body').empty();
                            var inv_s = '';
                            var inv_p = '';
                            var adm_comment = '';
                            var amount = 0;

                            if (data.length) {
                                $.each(data, function () {
                                    console.log(this.main);
                                    amount = 0;
                                    adm_comment = '';

                                    $.each(this.products, function () {
                                        amount = amount + parseInt(this.amount);
                                    });

                                    if (this.adm_comment) {
                                        $.each(this.adm_comment, function () {
                                            adm_comment += '<div><div style="font-size:12px; display:inline-block;">' + this.cr_date + '<div style="font-size:10px;">(' + this.email + ')</div></div><div style="display:inline-block;margin-left: 10px;vertical-align: top;">' + this.message + '</div></div><hr>';
                                        });
                                    }

                                    if (this.main.state == 'success') {
                                        inv_s += '<div class="product-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b><a href="https://pult.glamurnenko.ru/billing/admin/invoices/info/' + this.main.id + '" target="_blank">' + this.main.id + '</a>/' + amount + '</b>';
                                        $.each(this.products, function () {
                                            inv_s += '<br>' + this.name + ' Цена: ' + this.amount;
                                        });

                                        (this.comment ? inv_s += '<br><b>Комментарий клиента:</b> <br>' + this.comment : '');
                                        (adm_comment ? inv_s += '<br><b>Комментарии менеджера: </b> <br>' + adm_comment : '');
                                        inv_s += '</div>';
                                    } else {

                                        inv_p += '<div class="product-item" style="border-bottom: 1px solid #e3e3e3; margin-bottom: 10px; padding-bottom: 10px;"><b><a href="https://pult.glamurnenko.ru/billing/admin/invoices/info/' + this.main.id + '" target="_blank">' + this.main.id + '</a>/' + amount + '</b>';
                                        $.each(this.products, function () {
                                            inv_p += '<br>' + this.name + ' Цена: ' + this.amount;
                                        });

                                        (this.comment ? inv_p += '<br><b>Комментарий клиента:</b> <br>' + this.comment : '');
                                        (adm_comment ? inv_p += '<br><b>Комментарии менеджера: </b> <br>' + adm_comment : '');
                                        inv_p += '</div>';
                                    }
                                });

                                if (inv_s) {
                                    console.log(1);
                                    $('#invoices-modal .modal-body').append('<div style="font-size:18px;font-weight:bold;">Оплаченные счета</div>');
                                    $('#invoices-modal .modal-body').append(inv_s);
                                }

                                if (inv_p) {
                                    $('#invoices-modal .modal-body').append('<div style="font-size:18px;font-weight:bold;">Неоплаченные счета</div>');
                                    $('#invoices-modal .modal-body').append(inv_p);
                                }
                            }
                        }
                    });
                });
            });
        </script>
    </body>
</html>
