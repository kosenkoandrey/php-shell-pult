<?
APP::$insert['css_datetimepicker'] = ['css', 'file', 'after', '</title>', APP::Module('Routing')->root . 'public/ui/vendors/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css'];
?>
<style>
    #new-users-chart {
        width: 100%;
        height: 400px;
        font-size: 14px;
        line-height: 1.2em;
    }
    
    #new-users-legend{
        background-color: #fff;
        margin-bottom:8px;
        margin:0 auto;
        display:inline-block;
        border-radius: 3px 3px 3px 3px;
        border: 1px solid #E6E6E6;
    }
    
    #new-users-legend td{
        padding: 5px;
    }
</style>
