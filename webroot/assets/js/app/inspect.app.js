var inspect = new Vue({
                          el     : '#inspect-app',
                          data   : {
                              filter            : '',
                              ws                : null,
                              loading           : true,
                              items             : [],
                              maxlen            : 200,
                              inspect_report_num: 0,
                              inspect_user_num  : 0,
                          },
                          methods: {
                              initWebSocket   : function () {
                                  var that        = this;
                                  var ImWebSocket = {
                                      protocol : 'ws',
                                      host     : document.domain,
                                      port     : 9511,
                                      connect  : function () {
                                          that.ws           = new WebSocket(this.protocol + "://" + this.host + ":" + this.port);
                                          that.ws.onopen    = this.onOpen;
                                          that.ws.onmessage = this.onMessage;
                                          that.ws.onclose   = this.onClose;
                                          that.ws.onerror   = this.onError;
                                      },
                                      onOpen   : function (e) {
                                          console.log(e);
                                          ImEvents.login();
                                          heartCheck.start();
                                      },
                                      onMessage: function (event) {
                                          heartCheck.reset();
                                          if (event.data == 'pong') {
                                              return;
                                          }

                                          // 示例
                                          var demoMessage = '{"type":"report","data":{"info":{"Url":"\/v1.1\/api\/user","BaseUrl":"","PathInfo":"\/v1.1\/api\/user","Port":"80","Status":"200","Method":"POST","UserIP":"172.18.0.1","ContentType":"application\/x-www-form-urlencoded; charset=utf-8","Host":"http:\/\/your.api.domain.com","UserAgent":"Paw\/3.1.5 (Macintosh; OS X\/10.13.5) GCDHTTPRequest","RequestTime":"20:43:39","RequestTime2":"1529844219","RequestTime3":"1529844221","RunTime":"0.271s"},"request":{"POST":{"nickname":"eyJvc2RrX2dhbWVfaWQiOiIxOTYzNzc1MzUiLCJ1c2VyX2lkIjoiMjI3IiwibG9naW5fc2RrX25hbWUiOiJEZW1vIiwiY2hhbm5lbF9pZCI6IjAiLCJleHRlbmQiOiIyMTUwfDI1fDAiLCJhY2NvdW50X3N5c3RlbV9pZCI6IjAwNjEwMDAiLCJvc2RrX3VzZXJfaWQiOiIwMDYxMDAwXzIyNyIsImlwIjoiNjEuMTc0LjE1LjIxNyIsImNvdW50cnkiOiJDTiIsInRpbWUiOjE1MzIwMDM3MDMsInNpZ24iOiIyZDlhYmFmMzFmODU3MzJhMTEwZGY1ZTA2MmM2ZGRiMSJ9"},"GET":{"test":"off"},"HEADERS":{"CONTENT-LENGTH":"19","USER-AGENT":"Paw\/3.1.5 (Macintosh; OS X\/10.13.5) GCDHTTPRequest","CONNECTION":"close","HOST":"your.api.domain.com","COOKIE":"PHPSESSID=ih9t9fjl5nbptgbgqsqtd0i7i5;","CONTENT-TYPE":"application\/x-www-form-urlencoded; charset=utf-8","USER-TOKEN":"167i0wow2418o3gvr"},"COOKIES":{"readOnly":"1"}},"response":{"BODY":{"code":"1","msg":"success","data":{"user_id":"9999999999","nickname":"Hello"}},"COOKIES":{"readOnly":"0"}}}}';
                                          var demoMessage = JSON.parse(demoMessage);
                                          that.items.push(demoMessage.data);

                                          var demoMessage = '{"type":"report","data":{"info":{"Url":"\/v1.1\/api\/user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1user1","BaseUrl":"","PathInfo":"\/v1.1\/api\/user","Port":"80","Status":"200","Method":"POST","UserIP":"172.18.0.1","ContentType":"application\/x-www-form-urlencoded; charset=utf-8","Host":"http:\/\/your.api.domain.com","UserAgent":"Paw\/3.1.5 (Macintosh; OS X\/10.13.5) GCDHTTPRequest","RequestTime":"20:43:42","RequestTime2":"1529844219","RequestTime3":"1529844221","RunTime":"0.283s"},"request":{"POST":{"nickname":"Hello1"},"GET":{"test":"off"},"HEADERS":{"CONTENT-LENGTH":"19","USER-AGENT":"Paw\/3.1.5 (Macintosh; OS X\/10.13.5) GCDHTTPRequest","CONNECTION":"close","HOST":"your.api.domain.com","COOKIE":"PHPSESSID=ih9t9fjl5nbptgbgqsqtd0i7i5;","CONTENT-TYPE":"application\/x-www-form-urlencoded; charset=utf-8","USER-TOKEN":"167i0wow2418o3gvr"},"COOKIES":{"readOnly":"1"}},"response":{"BODY":{"code":"1","msg":"success","data":{"user_id":"9999999999","nickname":"Hello1"}},"COOKIES":{"readOnly":"0"}}}}';
                                          var demoMessage = JSON.parse(demoMessage);
                                          that.items.push(demoMessage.data);
                                          // 示例

                                          // var data = eval("(" + event.data + ")");
                                          var message = JSON.parse(event.data);
                                          if (message.type == 'report') {

                                              that.inspect_report_num = message.info.inspect_report_num;
                                              that.inspect_user_num   = message.info.inspect_user_num;

                                              // 如果达到100条，重新开始监听
                                              if (that.items.length >= that.maxlen) {
                                                  that.items = [];
                                              }
                                              // 搜索业务
                                              // 后端传输过来的 json 中含有 前倒符号
                                              if (that.filter != '') {
                                                  // 只有在匹配到的时候才高亮
                                                  if ((JSON.stringify(message).indexOf(that.filter) != -1)) {
                                                      message.data.selected = true;
                                                  }
                                              }
                                              that.items.push(message.data);
                                              if (that.loading) {
                                                  that.loading = false;
                                              }
                                          } else if (message.type == 'login') {
                                              that.inspect_report_num = message.inspect_report_num;
                                              that.inspect_user_num   = message.inspect_user_num;
                                          }

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
                                      },
                                  };


                                  var ImEvents = {
                                      login      : function () {
                                          var room_id = util.getURLParameter('inspect', location.search);
                                          if (room_id == null) {
                                              room_id = 1;
                                          }
                                          var data = '{"type":"login","token":"' + this.randomRange(10000, 90000) + '","room_id":"' + room_id + '"}';
                                          that.ws.send(data);
                                      },
                                      say        : function (content) {
                                          that.ws.send('{"type":"say","content":"' + content + '"}');
                                          that.ws.send('{"type":"say","to_client_id":"1","content":"' + content + '"}');
                                          that.ws.send('{"type":"say","to_client_id":"all","content":"' + content + '","user_info":{"name":"匿名","avatar":"xx"}}');
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

                                  var heartCheck = {
                                      timeout         : 60000,//60ms
                                      timeoutObj      : null,
                                      serverTimeoutObj: null,
                                      reset           : function () {
                                          clearTimeout(this.timeoutObj);
                                          clearTimeout(this.serverTimeoutObj);
                                          this.start();
                                      },
                                      start           : function () {
                                          var self        = this;
                                          this.timeoutObj = setTimeout(function () {
                                              that.ws.send('{"type":"ping"}');
                                              self.serverTimeoutObj = setTimeout(function () {
                                                  that.ws.close();//如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
                                              }, self.timeout)
                                          }, this.timeout)
                                      },
                                  }
                                  ImWebSocket.connect();
                              },
                              showItemTrigger : function (index) {
                                  var vm                = this
                                  var currentShowStatus = vm.items[index].show;
                                  vm.items[index].show  = currentShowStatus ? false : true;
                                  vm.items              = Object.assign([], vm.items);
                              },
                              refreshTrigger  : function () {
                                  this.items = [];
                              },
                              showItem        : function (item) {
                                  return JSON.stringify(item, null, 4);
                              },
                              clearSearchInput: function () {
                                  this.filter = '';
                              }
                          },
                          created: function () {
                              this.initWebSocket();
                          }
                      });


util = {
    urlRE       : /https?:\/\/([-\w\.]+)+(:\d+)?(\/([^\s]*(\?\S+)?)?)?/g,
    //  html sanitizer
    toStaticHTML: function (inputHtml) {
        inputHtml = inputHtml.toString();
        return inputHtml.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    },
    //pads n with zeros on the left,
    //digits is minimum length of output
    //zeroPad(3, 5); returns "005"
    //zeroPad(2, 500); returns "500"
    zeroPad     : function (digits, n) {
        n = n.toString();
        while (n.length < digits)
            n = '0' + n;
        return n;
    },
    //it is almost 8 o'clock PM here
    //timeString(new Date); returns "19:49"
    timeString  : function (date) {
        var minutes = date.getMinutes().toString();
        var hours   = date.getHours().toString();
        return this.zeroPad(2, hours) + ":" + this.zeroPad(2, minutes);
    },

    //does the argument only contain whitespace?
    isBlank        : function (text) {
        var blank = /^\s*$/;
        return (text.match(blank) !== null);
    },
    getURLParameter: function (name, search) {
        search    = search || location.search
        var param = search.match(
            RegExp(name + '=' + '(.+?)(&|$)'))
        return param ? decodeURIComponent(param[1]) : null
    },

    getCookie    : function (c_name) {
        if (document.cookie.length > 0) {
            c_start = document.cookie.indexOf(c_name + "=")
            if (c_start != -1) {
                c_start = c_start + c_name.length + 1
                c_end   = document.cookie.indexOf(";", c_start)
                if (c_end == -1) c_end = document.cookie.length
                return unescape(document.cookie.substring(c_start, c_end))
            }
        }
        return ""
    },
    addCookie    : function (name, value, expiresHours) {
        var cookieString = name + "=" + escape(value);
        //判断是否设置过期时间
        if (expiresHours > 0) {
            var date = new Date();
            date.setTime(date.getTime + expiresHours * 3600 * 1000);
            cookieString = cookieString + "; expires=" + date.toGMTString();
        }
        document.cookie = cookieString;
    },
    getSign      : function (headers, requestData, appSecret) {
        var singData   = Object.assign(headers, requestData);
        var urlEncode  = function (param, key, encode) {
            if (param == null) return '';
            var urlStr = '';
            var t      = typeof (param);
            if (t == 'string' || t == 'number' || t == 'boolean') {
                urlStr += '&' + key + '=' + ((encode == null || encode) ? encodeURIComponent(param) : param);
            } else {
                for (var i in param) {
                    var k = key == null ? i : key + (param instanceof Array ? '[' + i + ']' : '.' + i);
                    urlStr += urlEncode(param[i], k, encode);
                }
            }
            return urlStr;
        };
        var objKeySort = function (obj) {
            var newkey = Object.keys(obj).sort();
            var newObj = {};
            for (var i = 0; i < newkey.length; i++) {
                newObj[newkey[i]] = obj[newkey[i]];
            }
            return newObj;
        }
        singData       = urlEncode(objKeySort(singData)) + appSecret;
        var md5        = function (string) {
            // TODO 引入 MD5类
        };
        return md5(singData);
    },
    decodeUnicode: function (str) {
        str = str.replace(/\\/g, "%");
        return unescape(str);
    }
};