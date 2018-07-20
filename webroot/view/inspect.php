<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Lonisy@163.com">
    <link rel="icon" href="favicon.ico">
    <title>Request Inspect</title>
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <link href="assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="assets/css/style.min.css" rel="stylesheet">
    <link href="assets/css/inspect.min.css?t=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/Font-Awesome-3.2.1/css/font-awesome.min.css" rel="stylesheet">
    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]>
    <script src="assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="bs-docs-home">

<div id="inspect-app">
    <a id="skippy" class="sr-only sr-only-focusable" href="javascript:void(0);">
        <div class="container"><span class="skiplink-text">Skip to main content</span></div>
    </a>
    <!-- Docs master nav -->
    <header class="navbar bs-docs-nav navbar-fixed-top">
        <div class="container inspect-navbar">
            <div class="item" style="width: 20%">
                <div class="navbar-header">
                    <button class="navbar-toggle collapsed" type="button" data-toggle="collapse"
                            data-target="#bs-navbar"
                            aria-controls="bs-navbar" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a href="index.php<?php echo !empty($request->server['query_string']) ? ("?" . $request->server['query_string']) : ''; ?>"
                       class="navbar-brand">Request Inspect</a>
                </div>
            </div>
            <div class="item" style="width: 50%">
                <div class="inspect-search">
                    <div class="icon-button"><i class="icon-search"></i></div>
                    <input type="text" id="searchInput" name="searchInput" v-model="filter" placeholder="请输入您要过滤的内容">
                    <div class="icon-button" v-show="filter" v-on:click="clearSearchInput()"><i
                                class="icon-remove-sign"></i></div>
                </div>
            </div>
            <div class="item" style="width: 25%">
                <nav id="bs-navbar" class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right" v-cloak>
                        <li><a href="javascript:void(0);">请求次数: {{inspect_report_num}}</a></li>
                        <li><a href="javascript:void(0);">使用人次: {{inspect_user_num}}</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Page content of course! -->
    <main class="inspect-main" id="content" tabindex="-1">
        <div class="container">

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">请求列表 <span class="pull-right refresh-inspect" v-on:click="refreshTrigger()">刷新</span>
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">

                            <table class="table table-hover inspect-table">
                                <thead>
                                <tr>
                                    <th>Fd</th>
                                    <th>ID</th>
                                    <th>请求时间</th>
                                    <th width="30%">接口地址</th>
                                    <th>请求方法</th>
                                    <th>状态</th>
                                    <th width="18%">主机/域名</th>
                                    <th>协议</th>
                                    <th>大小</th>
                                    <th>响应时长</th>
                                </tr>
                                </thead>
                                <tbody>

                                <template v-for="(item, index) in items">
                                    <tr v-on:click="showItemTrigger(index)"
                                        v-bind:class="{ 'selected': item.selected }">
                                        <td scope="row"><i
                                                    v-bind:class="[item.show ? 'icon-eye-open' : 'icon-eye-close']"></i>
                                        </td>
                                        <td>{{index+1}}</td>
                                        <td>{{item.info.RequestTime}}</td>
                                        <td>{{item.info.PathInfo}}</td>
                                        <td>{{item.info.Method}}</td>
                                        <td>{{item.info.Status}}</td>
                                        <td>{{item.info.Host}}</td>
                                        <td>{{item.info.Port||'80'}}</td>
                                        <td>{{item.info.Len||'0kb'}}</td>
                                        <td>{{item.info.RunTime}}</td>
                                    </tr>
                                    <tr v-if="item.show">
                                        <td colspan="10">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="bs-example bs-example-tabs bs-example-data-view">
                                                        <ul id="myTabs" class="nav nav-tabs" role="tablist">
                                                            <template v-if="tab != 'show'"
                                                                      v-for="(tabContent, tab) in item">

                                                                <li v-bind:class="[tab =='info' ? 'active' : '']"
                                                                    role="tab">

                                                                    <a v-bind:href="'#data-' + index + '-' + tab"
                                                                       v-bind:id="'data-' + index + '-' + tab + '-tab'"
                                                                       v-bind:aria-expanded="[tab =='info' ? 'true' : 'false']"
                                                                       role="tab"
                                                                       data-toggle="tab">{{tab}}</a></li>
                                                            </template>
                                                        </ul>
                                                        <div id="myTabContent" class="tab-content">
                                                            <template v-if="tab != 'show'"
                                                                      v-for="(tabContent, tab) in item"
                                                            >
                                                                <div role="tabpanel" class="tab-pane fade"
                                                                     v-bind:class="[tab =='info' ? 'active in' : '']"
                                                                     v-bind:id="'data-' + index + '-' + tab"
                                                                     v-bind:aria-labelledby="'data-' + index + '-' + tab + '-tab'"
                                                                >
                                                                    <ul class="data-view-row">
                                                                        <template v-for="(val,key) in tabContent">
                                                                            <li v-if="typeof val != 'object'"><strong>{{key}}
                                                                                    : </strong>{{val}}
                                                                            </li>
                                                                            <template v-if="typeof val == 'object'"
                                                                                      v-for="(sval,skey,sindex) in val">
                                                                                <li v-if="sindex == 0"
                                                                                    class="data-view-title"><strong>{{key}}</strong>
                                                                                </li>
                                                                                <div v-if="sindex == 0"
                                                                                     v-if="typeof val == 'object'"
                                                                                     class="inspect-item-detail">
                                                                                    <pre>{{ showItem(val) }}</pre>
                                                                                </div>
                                                                            </template>
                                                                        </template>
                                                                    </ul>

                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <tr>
                                    <td colspan="10">
                                        <div align="center">
                                            <div class="loader">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bs-docs-footer">
        <div class="container">
            <ul class="bs-docs-footer-links">
                <li><a href="https://github.com/lonisy">GitHub 仓库</a></li>
                <li><a href="https://github.com/lonisy/#examples">实例</a></li>
                <li><a href="https://github.com/lonisy/#about">关于</a></li>
            </ul>
            <p>本项目源码受 <a rel="license" href="https://github.com/twbs/bootstrap/blob/master/LICENSE"
                         target="_blank">MIT</a>开源协议保护，文档受
                <a rel="license" href="https://creativecommons.org/licenses/by/3.0/" target="_blank">CC BY 3.0</a>
                开源协议保护。
            </p>
        </div>
    </footer>
</div>
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="assets/js/ie10-viewport-bug-workaround.js"></script>
<script src="assets/js/swfobject.js"></script>
<script src="assets/js/web-socket.js"></script>
<script src="assets/js/vue.js"></script>
<script src="assets/js/app/inspect.app.js?t=<?php echo time(); ?>"></script>
<!--<script src="assets/js/app/inspect.js"></script>-->
</body>
</html>