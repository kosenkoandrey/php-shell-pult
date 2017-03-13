(function($) {
    var active_group = 0;
    var group_obj = new Object();
    var settings;
    
    var methods = {
        init: function(options) { 
            var group_selector = $(this);
            var data = group_selector.data();
            var name = group_selector.attr('name');
            
            settings = $.extend( {
                'debug': false,
                'url' : 'http://pult2.glamurnenko.ru/',
                 callback: function() {}
            }, options);
            
            var selector = $('<select/>', {
                'id': 'group_id',
                'class': 'form-control'
            });
            
            $.ajax({
                type: 'post',
                url: settings.url + 'admin/groups/api/list.json',
                data: {'rows':-1, 'current':1},
                success: function(groups) {
                    $.each(groups.rows, function(index, item) {
                        if (item.id == group_selector.val()) {
                            selector.append($('<option/>', {
                                'value': item.id
                            }).attr('selected','selected').html(item.name));
                        } else {
                            selector.append($('<option/>', {
                                'value': item.id
                            }).html(item.name));
                        }
                    });
                    
                    if(name){
                        selector.attr('name', name);
                    }
                    
                    $.each(data, function(k, i){
                        selector.attr('data-'+k, i);
                    });
                    
                    group_selector.replaceWith(selector);
                }
            });
            
            return this;
        }
    };

    $.fn.GroupSelector = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('GroupSelector: Unknown method: ' +  method);
        } 
    };
})(jQuery);