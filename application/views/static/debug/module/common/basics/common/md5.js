define(function(require, exports) {
    require("md5");
    exports.md5 = function(element){
        var i = 0;
        element.find('input').each(function(index){
            if($(this).attr('type')=='password'){
                var input_name = $(this).attr('name');
                if(input_name==undefined) input_name = $(this).attr('md5_name');
                if(input_name) i = i + 1;
                var input_id = input_name + i;
                var pass_hidden = '<input class="text-input" id="' + input_id + '_md5" name="' + input_name + '" type="hidden">';
                $('#' + input_id + '_md5').remove();
                $(this).parent().append(pass_hidden);
                $('#' + input_id + '_md5').val(tizi_md5($(this).val()));
                $(this).attr('md5_name',input_name).removeAttr('name');
            }
        });
    };
    exports.reset_md5 = function(element){
        var i = 0;
        $(element).find('input').each(function(index){
            if($(this).attr('type')=='password'){
                input_name = $(this).attr('md5_name');
                if(input_name) i = i + 1;
                var input_id = input_name + i;
                $('#' + input_id + '_md5').remove();
                $(this).attr('name',input_name).removeAttr('md5_name');
            }
        });
    };
});