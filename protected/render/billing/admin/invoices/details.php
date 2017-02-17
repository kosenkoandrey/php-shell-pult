<?
$filters = htmlspecialchars(isset($_GET['filters']) ? APP::Module('Crypt')->Decode($_GET['filters']) : '{"logic":"intersect","rules":[{"method":"amount","settings":{"logic":">","value":"0"}}]}');
?>
<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Детали счета #<?= $data['invoice']['id'] ?></title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">

        <style>
            .table .table {
                background-color: #f5f5f5 !important;
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
                            <h2>Детали счета #<?= $data['invoice']['id'] ?></h2>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td style="width: 25%;">Сумма</td>
                                        <td><?= $data['invoice']['amount'] ?> руб.</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;">Состояние</td>
                                        <td>
                                            <?
                                            switch ($data['invoice']['state']) {
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
                                        <td><?= $data['invoice']['author'] ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;">Дата обновления</td>
                                        <td><?= $data['invoice']['up_date'] ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;">Дата создания</td>
                                        <td><?= $data['invoice']['cr_date'] ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;">Продукты</td>
                                        <td>
                                            <table class="table">
                                                <tbody>
                                                    <?
                                                    foreach ($data['products'] as $product) {
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
                                                    foreach ($data['tags'] as $tag) {
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
                                                    foreach ($data['payments'] as $payment) {
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
                                                    foreach ($data['details'] as $details) {
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
                                                'id' => $data['invoice']['id'],
                                                'likes' => true,
                                                'class' => [
                                                    'holder' => 'palette-Grey-100 bg p-l-10'
                                                ]
                                            ]);

                                            APP::Render('comments/widgets/default/form', 'include', [
                                                'type' => $comment_object_type,
                                                'id' => $data['invoice']['id'],
                                                'login' => [],
                                                'class' => [
                                                    'holder' => false,
                                                    'list' => 'palette-Grey-100 bg p-l-10'
                                                ]
                                            ]);
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
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

        <? APP::Render('core/widgets/js') ?>
    </body>
</html>