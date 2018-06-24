// 如果浏览器不支持websocket，会使用这个flash自动模拟websocket协议，此过程对开发者透明
WEB_SOCKET_SWF_LOCATION = "../../swf/WebSocketMain.swf";
// 开启flash的websocket debug
WEB_SOCKET_DEBUG        = true;

var ImWebSocket = {
    protocol : 'ws',
    host     : document.domain,
    port     : 9501,
    ws       : null,
    connect  : function () {
        this.ws           = new WebSocket(this.protocol + "://" + this.host + ":" + this.port);
        this.ws.onopen    = this.onOpen;
        this.ws.onmessage = this.onMessage;
        this.ws.onclose   = this.onClose;
        this.ws.onerror   = this.onError;
        // ws.onclose = function () {
        //     console.log("连接关闭，定时重连");
        //     connect();
        // };
        // ws.onerror = function () {
        //     console.log("出现错误");
        // };
    },
    onOpen   : function (e) {
        // var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '","room_id":"123123"}';
        // console.log("websocket握手成功，发送登录数据:" + login_data);
        // ws.send(login_data);
        console.log(e);
    },
    onMessage: function (event) {
        console.log(event.data);
        if (typeof event.data === String) {
            console.log("Received data string");
        }
        if (event.data instanceof ArrayBuffer) {
            var buffer = event.data;
            console.log("Received arraybuffer");
        }
    },
    onClose  : function (e) {
        console.log(e);
    },
    onError  : function (e) {
        console.log(e);
    }
};

var ImEvents = {
    login      : function () {
        // var login_data = '';
        var login_data = '{"type":"login","token":"' + this.randomRange(10000, 90000) + '","user_info":{"name":"LiLEI","avatar":"xx"},"room_id":"1"}';
        console.log("websocket握手成功，发送登录数据:" + login_data);
        ImWebSocket.ws.send(login_data);
        $("#chatbox").fadeIn();
    },
    say        : function (content) {
        ImWebSocket.ws.send('{"type":"say","content":"' + content + '"}');
        ImWebSocket.ws.send('{"type":"say","to_client_id":"1","content":"' + content + '"}');
        ImWebSocket.ws.send('{"type":"say","to_client_id":"all","content":"' + content + '","user_info":{"name":"匿名","avatar":"xx"}}');
    },
    randomRange: function (under, over) {
        switch (arguments.length) {
            case 1:
                return parseInt(Math.random() * under + 1);
            case 2:
                return parseInt(Math.random() * (over - under + 1) + under);
            default:
                return 0;
        }
    }
};

ImWebSocket.connect();

$(function () {



    var ELE_NICKNAME  = $("#username");
    var ELE_USERLOGIN = $("#userlogin");
    var ELE_LOGINBOX  = $("#loginbox");
    var ELE_CHATBOX   = $("#chatbox");
    var ELE_USERLIST  = $("#userlist");
    var ELE_NUM       = $("#num");
    var ELE_SHOWBOX   = $("#showbox");
    var ELE_MYUSER    = $("#myuser");
    var ELE_SEND      = $("#send");
    var ELE_MSGBOX    = $("#msgbox");
    var ELE_SHOWMSG   = $("#showmsg");
    var ELE_EXPRE     = $("#expre");
    var ELE_EXPREBOX  = $("#exprebox");
    var ELE_SHAKE     = $("#shake");
    var ELE_SENDIMG   = $("#img");

    ELE_SEND.on("click", function () {
        var msgContent = ELE_MSGBOX.val();
        msgContent     = msgContent.replace(/\n/g, "<br\/>");
        msgContent     = msgContent.replace(/\s/g, "&nbsp;");

        if (msgContent.length > 0) {
            ImEvents.say(msgContent)
        }
        ELE_MSGBOX.focus();
        ELE_MSGBOX.val('');
    })

    ELE_USERLOGIN.on("click", function () {
        var nickName = ELE_USERLOGIN.val();
        if (nickName.trim().length != 0) {
            ImEvents.login();
        } else {
        }
    });


    // connect(function () {
    //     $(".loginwarp").fadeIn();
    // });

    if (typeof console == "undefined") {
        this.console = {
            log: function (msg) {
            }
        };
    }

    var ws, name, client_list = {};

    // 连接服务端
    function connect(callback) {
        // 创建websocket
        ws           = new WebSocket("ws://" + document.domain + ":9501");
        // 当socket连接打开时，输入用户名
        ws.onopen    = onopen;
        // 当有消息时根据消息类型显示不同信息
        ws.onmessage = onmessage;
        ws.onclose   = function () {
            console.log("连接关闭，定时重连");
            connect();
        };
        ws.onerror   = function () {
            console.log("出现错误");
        };
        callback();
    }

    // 连接建立时发送登录信息
    function onopen() {
        if (!name) {
            show_prompt();
        }
        // 登录
        var login_data = '{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '","room_id":"123123"}';
        console.log("websocket握手成功，发送登录数据:" + login_data);
        ws.send(login_data);
    }

    // 服务端发来消息时
    function onmessage(e) {
        console.log(e.data);
        var data = JSON.parse(e.data);
        switch (data['type']) {
            // 服务端ping客户端
            case 'ping':
                ws.send('{"type":"pong"}');
                break;
            // 登录 更新用户列表
            case 'login':
                //{"type":"login","client_id":xxx,"client_name":"xxx","client_list":"[...]","time":"xxx"}
                say(data['client_id'], data['client_name'], data['client_name'] + ' 加入了聊天室', data['time']);
                if (data['client_list']) {
                    client_list = data['client_list'];
                }
                else {
                    client_list[data['client_id']] = data['client_name'];
                }
                flush_client_list();
                console.log(data['client_name'] + "登录成功");
                break;
            // 发言
            case 'say':
                //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                say(data['from_client_id'], data['from_client_name'], data['content'], data['time']);
                break;
            // 用户退出 更新用户列表
            case 'logout':
                //{"type":"logout","client_id":xxx,"time":"xxx"}
                say(data['from_client_id'], data['from_client_name'], data['from_client_name'] + ' 退出了', data['time']);
                delete client_list[data['from_client_id']];
                flush_client_list();
        }
    }

    // 输入姓名
    function show_prompt() {
        name = prompt('输入你的名字：', '');
        if (!name || name == 'null') {
            name = '游客';
        }
    }

    // 提交对话
    function onSubmit() {
        var input          = document.getElementById("textarea");
        var to_client_id   = $("#client_list option:selected").attr("value");
        var to_client_name = $("#client_list option:selected").text();
        ws.send('{"type":"say","to_client_id":"' + to_client_id + '","to_client_name":"' + to_client_name + '","content":"' + input.value.replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r') + '"}');
        input.value = "";
        input.focus();
    }

    // 刷新用户列表框
    function flush_client_list() {
        var userlist_window     = $("#userlist");
        var client_list_slelect = $("#client_list");
        userlist_window.empty();
        client_list_slelect.empty();
        userlist_window.append('<h4>在线用户</h4><ul>');
        client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
        for (var p in client_list) {
            userlist_window.append('<li id="' + p + '">' + client_list[p] + '</li>');
            client_list_slelect.append('<option value="' + p + '">' + client_list[p] + '</option>');
        }
        $("#client_list").val(select_client_id);
        userlist_window.append('</ul>');
    }

    // 发言
    function say(from_client_id, from_client_name, content, time) {
        $("#dialog").append('<div class="speech_item"><img src="http://lorempixel.com/38/38/?' + from_client_id + '" class="user_icon" /> ' + from_client_name + ' <br> ' + time + '<div style="clear:both;"></div><p class="triangle-isosceles top">' + content + '</p> </div>');
    }

    $(function () {
        select_client_id = 'all';
        $("#client_list").change(function () {
            select_client_id = $("#client_list option:selected").attr("value");
        });
    });
});
