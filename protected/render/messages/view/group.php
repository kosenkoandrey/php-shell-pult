<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Просмотр группы сообщений</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Сообщения' => 'messages'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2><?= $data['group']['name'] ?></h2>
                        </div>
                        <?
                        if (count($data['messages'])) {
                            ?>
                            <div class="card-body">
                                <table class="table table-hover table-vmiddle">
                                    <tbody>
                                        <?
                                        foreach ($data['messages'] as $message) {
                                            ?>
                                            <tr>
                                                <td style="width: 60px;">
                                                    <?
                                                    if ($message['state'] == 'unread') {
                                                        ?><span class="badge bgm-orange" style="font-weight: bold; position: absolute; left: 55px; margin-top: -5px"><i class="zmdi zmdi-notifications-active"></i></span><?
                                                    }
                                                    ?>
                                                    <span style="display: inline-block" class="avatar-char palette-Teal-400 bg"><i class="zmdi zmdi-email"></i></span>
                                                </td>
                                                <td style="font-size: 16px;">
                                                    <a target="_blank" style="color: #4C4C4C" href="<?= APP::Module('Routing')->root ?>messages/view/<?= APP::Module('Crypt')->Encode($message['id']) ?>">
                                                        <?
                                                        if ($message['title']) {
                                                            echo $message['title'];
                                                        } else {
                                                            $message_data = json_decode($message['message']);
                                                            
                                                            switch ($data['group']['alias']) {
                                                                case 'php-errors':
                                                                    switch ($message_data[1]) {
                                                                        case 0: echo $message_data[2][1]; break;
                                                                        case 1: echo $message_data[2][0]; break;
                                                                        case 2: echo $message_data[2]->message; break;
                                                                    }
                                                                    break;
                                                                default:
                                                                    echo $data['group']['name'];
                                                                    break;
                                                            }
                                                        }
                                                        ?>
                                                    </a>
                                                    <div style="font-size: 11px;"><?= $message['cr_date'] ?></div>
                                                </td>
                                            </tr>
                                            <?
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <?
                        } else {
                            ?>
                            <div class="card-body card-padding">
                                <div class="alert alert-warning" style="margin-bottom: 0;">Нет сообщений</div>
                            </div>
                            <?
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

        <? APP::Render('core/widgets/js') ?>
    </body>
  </html>