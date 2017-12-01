/**
 * (I`m not good at English, hope you can understand:D)
 * 
 * hi, I`m finding a plugin which support l5-repository but failed,so I wrote some js to build l5 supported url query string,which solved this problem:  
 * When form submits, l5 plugin wont automaticly recognize these query string :'?user_name=aa&source=pc'
 * instead,it recognize these string:'?search=user_name:aa;source:pc&searchFields=user_name:=;source:=';
 * so this js will turn normal form submit query string to l5 recognized query string, just add some attributes in the inputs:
 * 
 * @requires jquery plugin
 * 
 * @requires 
 * html element should like this:
 * {!! Form::open(['class' => 'searchForm', 'route' => ['admin.users.index'], 'method' => 'get']) !!}
    <input data-search-type="like" name="user_name" placeholder="user name" width="120px" type="text">
    reg time：
    <input class="date" autocomplete="off" data-search-type=">=" data-is-between="1" name="created_at" placeholder="reg time start" width="120px" type="text">
    <input class="date" autocomplete="off" data-search-type="<=" data-is-between="1" name="created_at" placeholder="reg time end" width="120px" type="text">
    reg source：{!! Form::select('source', $source, null, ['class' => 'form-select']) !!}
    
    <input type="hidden" data-order-by="id" data-sorted-by="desc">

    <input type="submit" value="submit">
    {!! Form::close() !!}
 *
 * input attributes instruction:
 * @param {string} data-search-type  just give data-search-type a specify value like >=,<=,=,like.. which will use in laravel query build where method`s second param: ->where('user_name', '=', 'a')
 * @param {name}   name              database table field, if want Eloquent query,just use:user.user_name, which not contains in current table
 * @param {string} data-is-between   this need two input, they have same name, it will create a query string like 'created_at between "2017-03-17" and "2017-03-18"'
 * @param {string} data-order-by id
 *                 data-sorted-by desc
 *                                   these will make a laravel method like : ->orderBy('id', 'desc'); *                                   
 * @param {string} data-ignore       will build normal query string like : a=1&b=2
 * @param {string} data-default-value       if user`s input is the default value then skip
 * @param {string} data-trigger-before-submit 
 *                 data-before-submit 
 *                                   see instruction below
 *                                   
 */


// After document loaded we just need to listen submit event,and stop it submiting, use l5 query string like ?search=John&searchFields=name:like to redirect.
$(".searchForm").submit(function () {
    var url = $(this).attr('action');
    var search = [], 
        searchFields = [], 
        orderBy = [], // eg: id
        sortedBy = [], // eg: desc
        ignore  = '',// will build normal query string like : a=1&b=2
        todo = {}; // to deal query like between and further more

    /**
     * if specifies user defined validation function, belows code will run it and submit or not depends on code result.
     * 
     * @example scene: there are two submit button in the form, and the other is for downloading excel, but download data need condition in case of the php script crashes while downloading the whole table.
     * 
     * @usuase: 
     * 1. specify the submit input an attribute like : data-trigger-before-submit="1"
     * 2. specify the form an attribute like : data-before-submit="funcName", funcName is a user defined function return true/false, when false the submit event is stopped.
     * 
     */
    var beforeSubmit = $(this).data('before-submit'),
        triggerBeofreSubmit = $(e.originalEvent.explicitOriginalTarget || document.activeElement).data('trigger-before-submit') == 1;

    // run user function
    if(typeof beforeSubmit != 'undefined' && triggerBeofreSubmit){
        var result = eval(beforeSubmit).call(null);
        // submit stopped if user funciton returns false
        if(result === false){
            return false;
        }
    }

    // simple querys just direct join
    var set_query_param_direct = function (input) {
        var key = input.attr('name'),
            val = input.val(),
            searchType = input.data('search-type') || '=';

        if (!val) return true;
        search.push(key + ':' + val);
        searchFields.push(key + ':' + searchType);
    };
    // between query
    var set_query_param_between = function (inputs) {
        for (var input_name in inputs) {
            // only two param can use between query
            var all_input_filled = true,
                obj_input_filled,
                tempSearch = [];

            for (var k in inputs[input_name]) {
                var input = inputs[input_name][k],
                    val = input.val();
                // if one value is empty, we dont need between query
                if (!val) {
                    all_input_filled = false;
                    continue;
                } else {
                    // remark which has value
                    obj_input_filled = input;
                }
                tempSearch.push(val);
            }

            if (!all_input_filled || inputs[input_name].length == 1) {
                // one is empty,use direct query
                set_query_param_direct(obj_input_filled);
            } else {
                // use between query
                search.push(input_name + ':' + tempSearch.join(','));
                searchFields.push(input_name + ':' + 'between');
            }
        }
    }

    try {
        // find all has name attribute inputs, for db query usuage
        $(this).find('[name]').each(function () {
            var key = $(this).attr('name'),
                default_value = $(this).data('default-value');
            
            // if specifies data-default-value and user input is the default value then skip
            if(typeof default_value != 'undefined'){
                if($(this).val() == default_value){
                    return true;
                }
            }else{
                // otherwise check the value if is fullfiled
                if (!$(this).val()) {
                    return true;
                }
            }

            // if specifies data-ignore and will use normal query string like : a=1&b=2
            if ($(this).data('ignore') == '1') {
                // checkbox should be checked
                if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
                    return true;
                }
                if ($(this).val() == '' ) {
                    return true;
                }
                ignore += '&' + key + '=' + $(this).val();
                return true;
            }

            // if this input has data-is-between,then skip and mark it
            if ($(this).data('is-between') == '1') {
                if (typeof todo['between'] == 'undefined') {
                    todo['between'] = {};
                }
                if (typeof todo['between'][key] == 'undefined') {
                    todo['between'][key] = [];
                }

                todo['between'][key].push($(this));
                return true;
            }

            set_query_param_direct($(this));
        });

        // order by query
        // todo..
        // l5 plugin seems not support multi order by, like 'order by is_hot desc,created_at desc'
        $(this).find('[data-order-by]').each(function () {
            orderBy.push($(this).data('order-by'))
            sortedBy.push($(this).data('sorted-by'));
            return false;
        });

        for (var type in todo) {
            switch (type) {
                // deal with between query
                case 'between':
                    set_query_param_between(todo[type]);
                    break;
            }

        }

        // redirect
        var op = url.indexOf('?') != -1 ? '&' : '?',
            redirect_url = url + op + 'search=' + search.join(';') + '&searchFields=' + searchFields.join(';') + ignore;

        if (orderBy.length > 0) {
            redirect_url += '&orderBy=' + orderBy.join('|') + '&sortedBy=' + sortedBy.join('|');
        }

        location.href = redirect_url;
        return false;

    } catch (e) {
        log_system('search form build query param failed.' + e.message);
    }

});

// this js function makes url query param fill in the inputs after serach results showed
(function () {
    // if no query or just simple query like '?page=' just ignore.
    if (!location.search || location.search.match(/^\?page=\d+$/)) {
        return;
    }
    // no form find  ignored.
    var searchForm = $(".searchForm");
    if (searchForm.length == 0) {
        return;
    }

    // fill in the input value
    try {
        var url = decodeURIComponent(location.search),
            match = url.match(/search=(.*?)&searchFields=(.*?)(?:&|$)/),
            search = match[1].split(';'),
            searchFields = match[2].split(';');

        $(search).each(function (k, v) {
            var temp = v.split(':'),
                input_name = temp[0],
                input_val = temp[1],
                tempSearchFields = searchFields[k].split(':'),
                tempSearchType = tempSearchFields[1];

            switch (tempSearchType) {
                // if its between query, use param order to fill in the input
                case 'between':
                    var temp_input_val = input_val.split(',');
                    searchForm.find("[name='" + input_name + "']").each(function (k, v) {
                        $(this).val(temp_input_val[k]);
                    });
                    break;
                default:
                    tempSearchType = tempSearchType == '=' ? '' : '[data-search-type="' + tempSearchType + '"]';
                    searchForm.find("[name='" + input_name + "']" + tempSearchType).val(input_val);
            }

        });

    } catch (e) {
        log_system('fill in the inputs with url query param failed.' + e.message);
    }
})();

/**
 * @param  {string} info
 */
function log_system(info) {
    console.log(info);
    // $.post('log/system', {'info': info});
}
