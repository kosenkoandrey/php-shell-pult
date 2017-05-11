<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Импорт счетов</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        
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
                        <?
                        switch ($data['action']) {
                            case 'form':
                                ?>
                                <form enctype="multipart/form-data" method="POST" class="form-horizontal" role="form">
                                    <div class="card-header">
                                        <h2>Выберите файл для импорта</h2>
                                    </div>
                                    <div class="card-body card-padding p-b-0">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <span class="btn btn-default btn-file m-r-10">
                                                <span class="fileinput-new">Выберите файл</span>
                                                <span class="fileinput-exists">Изменить</span>
                                                <input type="file" name="invoices">
                                            </span>
                                            <span class="fileinput-filename"></span>
                                            <a href="#" class="close fileinput-exists" data-dismiss="fileinput">&times;</a>
                                        </div>
                                    </div>
                                    <div class="card-header">
                                        <h2>Выберите период сверки</h2>
                                    </div>
                                    <div class="card-body card-padding">
                                        <div class="form-group">
                                            <div class="col-sm-1">
                                                <select name="date_day_from" class="selectpicker">
                                                    <?
                                                    for ($i = 1; $i <= 31; $i ++) {
                                                        ?><option value="<?= $i ?>"><?= $i ?></option><?
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <select name="date_month_from" class="selectpicker">
                                                    <option value="01">январь</option>
                                                    <option value="02">февраль</option>
                                                    <option value="03">март</option>
                                                    <option value="04">апрель</option>
                                                    <option value="05">май</option>
                                                    <option value="06">июнь</option>
                                                    <option value="07">июль</option>
                                                    <option value="08">август</option>
                                                    <option value="09">сентябрь</option>
                                                    <option value="10">октябрь</option>
                                                    <option value="11">ноябрь</option>
                                                    <option value="12">декабрь</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <select name="date_year_from" class="selectpicker">
                                                    <?
                                                    for ($i = date('Y'); $i >= 2016; $i --) {
                                                        ?><option value="<?= $i ?>"><?= $i ?></option><?
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-1 text-center">
                                                по
                                            </div>
                                            <div class="col-sm-1">
                                                <select name="date_day_to" class="selectpicker">
                                                    <?
                                                    for ($i = 1; $i <= 31; $i ++) {
                                                        ?><option value="<?= $i ?>"><?= $i ?></option><?
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <select name="date_month_to" class="selectpicker">
                                                    <option value="01">январь</option>
                                                    <option value="02">февраль</option>
                                                    <option value="03">март</option>
                                                    <option value="04">апрель</option>
                                                    <option value="05">май</option>
                                                    <option value="06">июнь</option>
                                                    <option value="07">июль</option>
                                                    <option value="08">август</option>
                                                    <option value="09">сентябрь</option>
                                                    <option value="10">октябрь</option>
                                                    <option value="11">ноябрь</option>
                                                    <option value="12">декабрь</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-1">
                                                <select name="date_year_to" class="selectpicker">
                                                    <?
                                                    for ($i = date('Y'); $i >= 2016; $i --) {
                                                        ?><option value="<?= $i ?>"><?= $i ?></option><?
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <button type="submit" class="btn palette-Teal bg waves-effect btn-lg">Импортировать</button>
                                            </div>
                                        </div>
                                        <a href="<?= APP::Module('Routing')->root ?>admin/blog/import-invoices">Инструкция по импорту счетов</a>
                                    </div>
                                </form>
                                <?
                                break;
                            case 'import':
                                if ((count($data['users'])) || (count($data['imported']))) {
                                    if (count($data['imported'])) {
                                        ?>
                                        <div class="card-header">
                                            <h2>
                                                Автоматически импортированные счета
                                                <small>Счета пользователя автоматически импортируются если сумма счетов в Пульте равна нулю</small>
                                            </h2>
                                        </div>
                                        <div class="card-body">
                                            <table class="table">
                                                <tr>
                                                    <th>Пользователь</th>
                                                    <th>Продукт</th>
                                                    <th>Сумма</th>
                                                    <th>Оплата</th>
                                                    <th>Дата</th>
                                                    <th>Комментарий</th>
                                                </tr>
                                                <?
                                                foreach ($data['imported'] as $invoice) {
                                                    unset($invoice[5]);
                                                    unset($invoice[6]);
                                                    unset($invoice[7]);
                                                    unset($invoice[8]);
                                                    unset($invoice[9]);
                                                    unset($invoice[11]);
                                                    
                                                    echo '<tr><td width="10%">' . implode('</td><td width="10%">', $invoice) . '</td></tr>';
                                                }
                                                ?>
                                            </table>
                                        </div>
                                        <?
                                    }

                                    if (count($data['users'])) {
                                        ?>
                                        <div class="card-header">
                                            <h2>
                                                Самостоятельно выберите необходимые счета
                                                <small>Необходимо самостоятельно выбрать счета которые отсутствуют в Пульте</small>
                                            </h2>
                                        </div>
                                        <div class="card-body">
                                            <table class="table">
                                                <tr>
                                                    <th>Информация о пользователе</th>
                                                    <th>Счета из отчета</th>
                                                </tr>
                                                <?
                                                foreach ($data['users'] as $user_id => $user_data) {
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <p><a class="btn palette-Teal bg waves-effect" target="_blank" href="<?= APP::Module('Routing')->root ?>admin/users/profile/<?= $user_data['email'] ?>"><i class="zmdi zmdi-account"></i> <?= $user_data['email'] ?></a></p>
                                                            <p>Дата регистрации &mdash; <?= date('Y-m-d', $user_data['reg_date']) ?></p>
                                                            <p>Сумма счетов из отчета &mdash; <?= array_sum($user_data['sum']) ?> руб.</p>
                                                            <p>Сумма всех счетов в Пульте &mdash; <?= $user_data['pult_sum'] ?> руб.</p>
                                                            <p>Не хватает &mdash; <?= $user_data['diff_sum'] ?> руб.</p>
                                                        </td>
                                                        <td>
                                                            <table class="table">
                                                                <?
                                                                foreach ($user_data['invoices'] as $invoice) {
                                                                    unset($invoice[0]);
                                                                    unset($invoice[5]);
                                                                    unset($invoice[6]);
                                                                    unset($invoice[7]);
                                                                    unset($invoice[8]);
                                                                    unset($invoice[9]);
                                                                    unset($invoice[11]);
                                                                    
                                                                    echo '<tr><td width="10%">' . implode('</td><td width="10%">', $invoice) . '</td><td width="10%"><a class="btn palette-Orange bg waves-effect" target="_blank" href="' . APP::Module('Routing')->root . 'admin/billing/invoices/add?user=' . $user_data['user_id'] . '&state=success&comment=import' . date('Ymd') . '"><i class="zmdi zmdi-plus"></i> Добавить счет</a></td></tr>';
                                                                }
                                                                ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <?
                                                }
                                            ?>
                                            </table>
                                        </div>
                                        <?
                                    }
                                } else {
                                    ?>
                                    <div class="card-header">
                                        <h2>Импорт счетов</h2>
                                    </div>
                                    <div class="card-body card-padding">
                                        Все счета были успешно импортированы
                                    </div>
                                    <?
                                }
                                break;
                        }
                        ?>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/fileinput/fileinput.min.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>

        <? APP::Render('core/widgets/js') ?>
    </body>
</html>