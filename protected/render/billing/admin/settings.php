<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PHP-shell - Billing</title>

        <!-- Vendor CSS -->
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/animate.css/animate.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/material-design-iconic-font/dist/css/material-design-iconic-font.min.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet">        
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/google-material-color/dist/palette.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
        <link href="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.css" rel="stylesheet">
        
        <? APP::Render('core/widgets/css') ?>
    </head>
    <body data-ma-header="teal">
        <? 
        APP::Render('admin/widgets/header', 'include', [
            'Billing settings' => 'admin/billing/settings'
        ]);
        ?>
        <section id="main">
            <? APP::Render('admin/widgets/sidebar') ?>

            <section id="content">
                <div class="container">
                    <div class="card">
                        <form id="update-settings" class="form-horizontal" role="form">
                            <div class="card-header">
                                <h2>Settings</h2>
                            </div>

                            <div class="card-body card-padding">
                                <ul class="tab-nav m-b-15" role="tablist" data-tab-color="teal">
                                    <li class="active"><a href="#settings-main" role="tab" data-toggle="tab">Main</a></li>
                                    <li role="presentation"><a href="#settings-sales" role="tab" data-toggle="tab">Sales Tool</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active animated fadeIn in" id="settings-main">
                                        <div class="form-group">
                                            <label for="module_billing_db_connection" class="col-sm-2 control-label">DB connection</label>
                                            <div class="col-sm-2">
                                                <div class="fg-line">
                                                    <select id="module_billing_db_connection" name="module_billing_db_connection" class="selectpicker">
                                                        <? foreach (array_keys(APP::Module('DB')->conf['connections']) as $connection) { ?><option value="<?= $connection ?>"><?= $connection ?></option><? } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div role="tabpanel" class="tab-pane animated fadeIn" id="settings-sales">
                                        <div class="form-group">
                                            <div id="sales-tool">

                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-sm-2">
                                                <button type="button" class="btn btn-primary btn-sm m-t-5 waves-effect add-sale-block">Добавить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="col-sm-offset-2 col-sm-5">
                                        <button type="submit" class="btn palette-Teal bg waves-effect btn-lg">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
        <script src="<?= APP::Module('Routing')->root ?>public/ui/vendors/bower_components/bootstrap-sweetalert/lib/sweet-alert.min.js"></script>
        
        <? APP::Render('core/widgets/js') ?>
        
        <script>
            $(document).ready(function() {
                $('#module_billing_db_connection').val('<?= APP::Module('Billing')->settings['module_billing_db_connection'] ?>');
                
                sales_tool = {
                    sale_block : 0,
                    tunnels: function(callback){
                        $.post('<?= APP::Module('Routing')->root ?>admin/tunnels/api/manage.json', function(resp){
                            callback(resp);
                        });
                    },
                    add: function(settings){
                        settings = typeof settings !== 'undefined' ? settings : {"":[]};
                        $.each(settings, function(j, i){
                            sales_tool.build(j, i);
                        });
                    },
                    build: function(tunnel_id, products){
                        sales_tool.tunnels(function(tunnels){
                            sales_tool.sale_block += 1;

                            select = '<select name="module_billing_sales_tool_tunnel[]" class="form-control">';
                            $.each(tunnels, function(j, i){
                                if(tunnel_id == i.id){
                                    select += '<option selected value="'+i.id+'">'+i.name+'</option>';
                                }else{
                                    select += '<option value="'+i.id+'">'+i.name+'</option>';
                                }
                               
                            });
                            select += '</select>';

                            $('#sales-tool').append([
                                '<div class="sale-block-'+sales_tool.sale_block+' col-sm-12">',
                                    '<div class="col-sm-2">',
                                        '<div class="fg-line" id="sales-tool">',
                                            select,
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-2">',
                                        '<div class="fg-line" id="sales-tool">',
                                            '<input name="module_billing_sales_tool_product[]" type="text" value="'+products.join(',')+'" class="form-control" />',
                                        '</div>',
                                    '</div>',
                                    '<div class="col-sm-2">',
                                        '<button type="button" data-block="'+sales_tool.sale_block+'" class="btn btn-danger btn-sm m-t-5 waves-effect delete-sale-block">x</button>',
                                    '</div>',
                                '</div>'].join(''));
                        });
                    },
                    remove: function(index){
                        $('.sale-block-'+index).remove();
                        sales_tool.sale_block -= 1;
                    }
                };

                sales_tool.add(JSON.parse('<?= APP::Module('Billing')->settings['module_billing_sales_tool'] ?>'));

                $(document).on('click', '.add-sale-block', function(e){
                    sales_tool.add();
                    return false;
                });

                $(document).on('click', '.delete-sale-block', function(e){
                    sales_tool.remove($(this).data('block'));
                    return false;
                });


                $('#update-settings').submit(function(event) {
                    event.preventDefault();

                    var module_billing_db_connection = $(this).find('#module_billing_db_connection');
                    
                    module_billing_db_connection.closest('.form-group').removeClass('has-error has-feedback').find('.form-control-feedback, .help-block').remove();
                    
                    if (module_billing_db_connection.val() === '') { module_billing_db_connection.closest('.form-group').addClass('has-error has-feedback').find('.col-sm-2').append('<span class="zmdi zmdi-close form-control-feedback"></span><small class="help-block">Not specified</small>'); return false; }
                    
                    $(this).find('[type="submit"]').html('Processing...').attr('disabled', true);

                    $.ajax({
                        type: 'post',
                        url: '<?= APP::Module('Routing')->root ?>admin/billing/api/settings/update.json',
                        data: $(this).serialize(),
                        success: function(result) {
                            switch(result.status) {
                                case 'success':
                                    swal({
                                        title: 'Done!',
                                        text: 'Billing settings has been updated',
                                        type: 'success',
                                        showCancelButton: false,
                                        confirmButtonText: 'Ok',
                                        closeOnConfirm: true
                                    });
                                    break;
                                case 'error': 
                                    $.each(result.errors, function(i, error) {});
                                    break;
                            }

                            $('#update-settings').find('[type="submit"]').html('Save changes').attr('disabled', false);
                        }
                    });
                });
            });
        </script>
    </body>
</html>