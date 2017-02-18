<script type="text/javascript">
    var calculate_cost_api = '{{ action('Test\IndexController@calculateCost') }}';

    
</script>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8"/>
    <title>股票计算器</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta http-equiv="Cache-Control" content="no-transform"/>
    <meta http-equiv="mobile-agent" content="format=html5; url=http://www.xxx.com/"/>
    <link rel="canonical" href="http://www.xxx.com/"/>
    <meta name="keywords" content="关键词,关键词"/>
    <meta name="description" content="描述"/>
    <!-- <link rel="stylesheet" href="http://static.local.liantu.cn/style/dist/test.css"/> -->
    <style>
            *{
                    padding:0;
                    margin:0;
            }
                .hjwsb{
                        width:600px;
                        height:590px;
                        border:2px #fc1 solid;
                        margin:0 auto;
                        position:relative;
                }
                .title{
                        width:100%;
                        height:25px;
                        font-size:20px;
                        text-align:center;
                        color:#f00;
                        line-height:25px;
                        background:#666;
                }
                .computer{
                        width:100%;
                        height:250px;
                        /*background:#fc1;*/
                }
                .computer .obj{
                        width:100%;
                        height:30px;
                        margin-top:10px;
                }
                .computer .obj span{
                        display:inline-block;
                        width:60px;
                        height:30px;
                        margin-left:30px;
                }
                /*.computer .obj span input[]*/
                .computer div .float_p{
                        width:100px;
                        height:30px;
                        float:left;
                        line-height:30px;
                        text-align:center;
                }
                .computer div select{
                        width:100px;
                        height:25px;
                        float:left;
                }
                .computer div  .input-text{
                        width:80px;
                        height:25px;
                        float:left;
                }
                .computer button{
                        width:60px;
                        height:30px;
                        font-size:16px;
                        background-color:#fc1;
                        margin-left:100px;
                        margin-top:10px;
                }
                .answer{
                        width:100%;
                        height:200px;
                }
                .answer table{
                        width:90%;
                        margin:0 auto;
                }
                .answer table tr{
                        width:90%;
                        height:30px;
                }
                .answer table tr td{
                        width:50%;
                        height:30px;
                        text-align:center;
                        border:1px #000 solid;
                }
                .answer table tr td span{
                        display:inline-block;
                        width:100px;
                        height:20px;
                        font-size:14px;
                        border-bottom:1px #666 solid;
                }
                .computer .drowList{
                        width:300px;

                        position:absolute;
                        left:100px;
                        top:90px;
                        /*background:#f00;*/
                }
                .computer .drowList .stock_list{
                        background:#f00;
                        height:25px;
                        background-color:#ccc;
                }

    </style>
</head>
<body>

        <div class="hjwsb">
                <p class="title">交易手续费</p>
                <div class="computer">
                        <div class="obj">
                                <p class="float_p">股票代码</p>
                                <p class="float_p"><input type="text" class="stock input-text"></input></p>
                        </div>
                        <div class="drowList"></div>
                        <div class="obj">
                                <p class="float_p">成交量</p>
                                <p class="float_p"><input type="text" class="buy_number input-text"></input>股</p>
                        </div>
                        <div class="obj">
                                <p class="float_p">股票单价</p>
                                <p class="float_p"><input type="text" class="stock_price input-text" min="0.01"></input>元</p>
                        </div>
                        <div class="obj">
                                <p class="float_p">佣金比例</p>
                                <p class="float_p"><input type="text" class="commission input-text" placeholder="0.3%" readonly="true"></input></p>
                        </div>
                        <div class="obj">
                                 <span><input type="radio" name="type" value="in" id="in" checked="checked"><label for="in">买入</label></span>
                                 <span><input type="radio" name="type" value="out" id="out"><label for="out">卖出</label></span>
                        </div>
                        <button class="btn">计算</button>
                </div>

                <!-- 答案 -->
                <div class="answer">
                        <p class="title">计算结果</p>
                        <table>
                                <tr>
                                        <!-- <td>股票名称<span ></span></td> -->
                                        <td>股票现价<span class="stock_price"></span>元</td>
                                </tr>
                                <tr>
                                        <td>印花税<span class="stamp_duty_money"></span>元</td>
                                </tr>
                                <tr>
                                    <td>佣金<span class="commission_money"></span>元</td>
                                </tr>
                                <tr>
                                        <td>过户费<span class="transfer_fee_money"></span>元</td>
                                </tr>
                                <tr>
                                        <td colspan="2">
                                            股票总金额<span class="stock_total_price"></span>元 &nbsp;<br />
                                            税费合计<span class="charge_total_value"></span>元&nbsp;<br />
                                            交易总额<span class="final_buy_money"></span>元</td>
                                </tr>
                        </table>
                </div>
        </div>


</body>
<script src="/js/jquery-1.11.3.min.js"></script>

<script>
        $('.btn').on('click',function(){
                $('.btn').attr('disabled','disabled');
                var type = $('input[name="type"]:checked').val();
                console.log($('input[name="type"]:checked').val());
                $.ajax({
                        'url':'/test/stock/calculate-cost',
                        'data' : {
                                'stock':$('.stock').val(),
                                'buy_number':$('.buy_number').val(),
                                'type':$('input[name="type"]:checked').val(),
                                'commission':$('.commission').val(),
                                'stock_price':$('.stock_price').val(),
                        },
                        success:function(json){

                                if(json.code == 0){
                                        // alert(json.data.final_buy_money);
                                        $('.final_buy_money').text(json.data.final_buy_money);
                                        $('.stock_price').text(json.data.stock_price);
                                        $('.charge_total_value').text(json.data.charge_total_value);
                                        $('.commission_money').text(json.data.commission_money);
                                        $('.stamp_duty_money').text(json.data.stamp_duty_money);
                                        $('.transfer_fee_money').text(json.data.transfer_fee_money);
                                        $('.stock_total_price').text(json.data.stock_total_price);
                                }else{
                                        alert(json.msg)
                                        return false;
                                }
                        }
                });

                $('.btn').removeAttr('disabled');


        })

        function GupiaoDrowlist(){
        $('body').on('input propertychange','.stock',function(){
                var $drowList=$('.drowList');
                $drowList.html('');
                var pattern = /^(?=.*\d)[\d]{1,6}$/i;
                if (pattern.test($(this).val())){
                    $drowList.show();
                    $.ajax({
                    url:'http://suggest3.sinajs.cn/suggest/type=11,12,13,14,15&key='+$(this).val()+'&name=suggestdata_1467971622808',
                    dataType:'script',
                    cache:'true',
                    success:function(){
                        var message = suggestdata_1467971622808.split(';');
                        var info = [];
                        var box = [];
                        if (message[0] === ''){
                            $drowList.hide();
                             return false;
                        }
                        var items=[];;
                        for (var i = 0,len = message.length;i < len;i++){
                            box = message[i].split(',');
                            items.push('<p class="stock_list"><span>'+box[0]+'</span>   '+box[4]+'</p>');
                        }
                        $drowList.html(items.join(''));
                    }
                });
                } else {
                    $drowList.hide();
                };
        });
        //鼠标指向触发
        $('body').on('mouseover','div.drowList p',function(){
            $('.drowList p').removeAttr('class');
            $(this).attr('class','node-selected');
        });
        $('body').on('mouseout','div.drowList p',function(){
            $(this).removeAttr('class');
        });
        //鼠标点击下拉框栏目
        $('body').on('click','div.drowList p',function(){
            var $input=$('.code-input');
            $input.val($(this).children().html());
            $('.drowList').hide();
        });
        //失去焦点时下拉框消失
        $('body').on('click',function(){
            $('.drowList').hide();
        });
        //当下拉框消失时点击输入框能再次获得
        $('body').on('click','.code-input',function(){
            var pattern = /^(?=.*\d)[\d]{1,6}$/i;
            var $node = $('.drowList');
            if ($node.children().length !== 0 && pattern.test($(this).val())){
                $node.show();
                return false;
            }
        });
        //键盘控制下拉框栏目
        $('body').on('keydown','.code-input',function(e){
            var $node = $('.node-selected');
            if ($node.length === 0 && window.event.keyCode === 40){
                $node = $('.drowList').children(':first');
                $node.attr('class','node-selected');
                return false;
            }
            if ($node.length === 0 && window.event.keyCode === 38){
                $node = $('.drowList').children(':last-child');
                $node.attr('class','node-selected');
                //防止光标默认向前移动
                e.preventDefault();
                return false;
            }
            if (window.event.keyCode === 40){0
                $node.removeAttr('class');
                if($node.next().length === 0){
                    $node = $('.drowList').children(':first');
                } else {
                    $node = $node.next();
                }
                $node.attr('class','node-selected');
            }
            if (window.event.keyCode === 38){
                $node.removeAttr('class');
                e.preventDefault();
                if ($node.prev().length === 0){
                    $node = $('.drowList').children(':last-child');
                } else {
                    $node = $node.prev();
                }
                $node.attr('class','node-selected');
            }
            if (window.event.keyCode === 13 && $node.length != 0){
                $node.removeAttr('class');
                var $input=$('.code-input');
                $input.val($node.children().html());
                $('.drowList').hide();
                return false;
            }
        });
    };
    // GupiaoDrowlist();

</script>
</html>
