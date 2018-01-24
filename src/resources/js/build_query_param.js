/**
 * 将表单拼接成l5-repository识别的字符串形式，表单class需包含searchForm，类似如下：
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
 * input参数说明:
 * @param {string} data-search-type  参数值为laravel的where方法的第二个参数(>,=,<..), 不指定则默认表示=, 会形成类似查询: $query->where('user_name', '=', 'a');
 * @param {name}   name              直接传数据库表字段如user_name表示搜索本表的字段；也可以带上关系，即代表查询关联表的字段如: order.order_sn
 * @param {string} data-is-between   这参数用于between查询，也就是可以查询区间，需要2个有同样name的input框指定，如果用户都输入了则会形成如下查询：'created_at between "2017-03-17" and "2017-03-18"',只输一个则会根据指定的data-search-type查询，如可能会形成："created_at >= '2017-03-17'"
 * @param {string} data-order-by id
 *                 data-sorted-by desc
 *                                   这两个input框用于排序，会形成类似查询: $query->orderBy('id', 'desc');                                
 * @param {string} data-ignore       忽略字段，会生成原始查询，如: a=1&b=2
 * @param {string} data-default-value       如果输入框有默认值，且用户提交的值跟默认值一样则会忽略
 * @param {string} data-trigger-before-submit 
 *                 data-before-submit 
 *                                   见下方详细说明
 *                                   
 */


// 表单搜索参数拼成插件自动识别的格式
$(".searchForm").submit(function(e) {
    var url = $(this).attr('action');
    var search = [], // 键值 如user_id:1;xx:2
        searchFields = [], // 字段 如user_id:=;xx:>
        orderBy = [], // 排序字段 如id|x_id
        sortedBy = [], // 排序 如desc
        ignore  = '', //额外件值
        todo = {}; // 用于处理between这样的特殊查询

    /**
     * 如果指定了提交前执行的代码 且触发submit事件的来源元素设置了触发 则执行提交前代码 根据返回值判断是否提交
     * 表单需指定自定义验证函数名：data-before-submit="func"
     * 触发自定义验证逻辑的submit按钮需包含data-trigger-before-submit="1"
     * @type {[type]}
     */
    var beforeSubmit = $(this).data('before-submit'),
        triggerBeofreSubmit = $(document.activeElement || e.originalEvent.explicitOriginalTarget).data('trigger-before-submit') == 1;

    // 执行自定义验证
    if(typeof beforeSubmit != 'undefined' && triggerBeofreSubmit){
        var result = eval(beforeSubmit).call(null);
        // 验证失败则不提交
        if(result === false){
            return false;
        }
    }

    // 设置单一查询参数为直接拼接形式
    var set_query_param_direct = function(input) {
        //排除字段
        if (input.attr('data-is-exclude') == '1') {
            input.val('');
        }
        var key = input.attr('name'),
            val = input.val(),
            searchType = input.data('search-type') || '=';
        if (input.attr('data-ignore') == '1') {
            return true;
        }

        if (!val) return true;
        search.push(key + ':' + val);
        searchFields.push(key + ':' + searchType);
    };
    // 设置between查询
    var set_query_param_between = function(inputs) {
        for (var input_name in inputs) {
            // 只有2个参数都有值才会使用between
            var all_input_filled = true,
                obj_input_filled,
                tempSearch = [];

            for (var k in inputs[input_name]) {
                var input = inputs[input_name][k],
                    val = input.val();
                // 如果其中有个值为空 那么就不需要between查询
                if (!val) {
                    all_input_filled = false;
                    continue;
                } else {
                    // 记录下有值的input
                    obj_input_filled = input;
                }
                tempSearch.push(val);
            }

            if (!all_input_filled || inputs[input_name].length == 1) {
                // 使用单个
                set_query_param_direct(obj_input_filled);
            } else {
                // 使用between
                search.push(input_name + ':' + tempSearch.join(','));
                searchFields.push(input_name + ':' + 'between');
            }
        }
    }

    try {
        // 获取输入框值并拼接
        $(this).find('[name]').each(function() {
            var key = $(this).attr('name'),
                default_value = $(this).data('default-value');

            // 如果有指定默认值 那么以指定的默认值来判断是否跳过
            if(typeof default_value != 'undefined'){
                if($(this).val() == default_value){
                    return true;
                }
            }else{
                if (!$(this).val()) {
                    return true;
                }
            }
            if ($(this).data('ignore') == '1') {
                if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
                    return true;
                }
                if ($(this).val() == '' ) {
                    return true;
                }
                ignore += '&' + key + '=' + $(this).val();
                return true;
            }


            // 标示为查询区间的在后面代码里处理
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
        // 获取排序
        // todo..
        // 插件貌似不支持单表多字段排序
        $(this).find('[data-order-by]').each(function() {
            var tag_name = $(this)[0].tagName.toLowerCase();

            // 排序值支持隐藏域和下拉框形式
            var sorted_by = tag_name == 'select' ? $(this).val() : $(this).data('sorted-by');

            if(sorted_by == ''){
                return true;
            }

            orderBy.push($(this).data('order-by'));
            sortedBy.push(sorted_by);
            
            return false;
        });

        for (var type in todo) {
            switch (type) {
                // 处理between查询
                case 'between':
                    set_query_param_between(todo[type]);
                    break;
            }

        }

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

// js填充搜索数据到输入框...
(function() {
    if (!location.search || location.search.match(/^\?page=\d+$/)) {
        return;
    }
    var searchForm = $(".searchForm");
    if (searchForm.length == 0) {
        return;
    }

    try {
        var url = decodeURIComponent(location.search),
            match = url.match(/search=(.*?)&searchFields=(.*?)(?:&|$)/),
            search = match[1].split(';'),
            searchFields = match[2].split(';');

        $(search).each(function(k, v) {
            var temp = v.split(':'),
                input_name = temp[0],
                tempSearchFields = searchFields[k].split(':'),
                tempSearchType = tempSearchFields[1];

            var input_val = v.replace(input_name + ':', ''),
                input_val = input_val.split(',');

            switch (tempSearchType) {
                // 如果是between搜索 按参数顺序对应input顺序填充
                case 'between':
                    searchForm.find("[name='" + input_name + "']").each(function(k, v) {
                        $(this).val(input_val[k]);
                    });
                    break;
                case 'in':
                    // 这地方没有break @todo.. 可优化
                    input_val[0] = input_val.join(',');
                default:
                    tempSearchType = tempSearchType == '=' ? '' : '[data-search-type="' + tempSearchType + '"]';

                    var obj_input = searchForm.find("[name='" + input_name + "']" + tempSearchType);

                    obj_input.val(input_val[0]);

                    // 兼容省市区联动
                    if($.inArray(obj_input.attr('id'), ['province', 'city', 'area']) != -1){
                        obj_input.change();
                    }
            }

        });

    } catch (e) {
        log_system('fill in input with url query param failed.' + e.message);
    }
})();

/**
 * @param  {string} info
 */
function log_system(info) {
    console.log(info);
    // $.post('log/system', {'info': info});
}
