<?
APP::$insert['css_datetimepicker'] = ['css', 'file', 'after', '</title>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css'];
APP::$insert['css_selectpicker'] = ['css', 'file', 'after', '</title>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.css'];

?>

<style>
    .select-mail-block{
        width: 300px;
        display:inline-block;
    }
    
    .select-mail, .select-sender{
        height: 30px;
        padding-left: 12px!important;
    }
    
    .select-mail-block .bootstrap-select > .btn-default{
        border-bottom: none!important;
    }
</style>
