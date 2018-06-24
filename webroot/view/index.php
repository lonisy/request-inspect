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
    <link href="assets/css/style.css" rel="stylesheet">
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
<a id="skippy" class="sr-only sr-only-focusable" href="javascript:void(0);">
    <div class="container"><span class="skiplink-text">Skip to main content</span></div>
</a>

<!-- Docs master nav -->
<header class="navbar navbar-static-top bs-docs-nav" id="top">
    <div class="container">
        <div class="navbar-header">
            <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target="#bs-navbar"
                    aria-controls="bs-navbar" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="index.php<?php echo !empty($request->server['query_string']) ? ("?" . $request->server['query_string']) : ''; ?>"
               class="navbar-brand">Request Inspect</a>
        </div>
        <ul class="nav navbar-nav">
            <li>
                <a href="javascript:void(0);">文档</a>
            </li>
        </ul>
        <nav id="bs-navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="javascript:void(0);">About</a></li>
            </ul>
        </nav>
    </div>
</header>


<!-- Page content of course! -->
<main class="bs-docs-masthead" id="content" tabindex="-1">
    <div class="container">
        <span class="bs-docs-booticon bs-docs-booticon-lg bs-docs-booticon-outline">RI</span>
        <p class="lead">Request Inspect 可用于移动应用API开发调试。服务端需要接入Request Report。让移动开发更迅速、简单。</p>
        <p class="lead">
            <a href="inspect.php<?php echo !empty($request->server['query_string']) ? ("?" . $request->server['query_string']) : ''; ?>"
               class="btn btn-outline-inverse btn-lg">开始调试</a>
        </p>
        <p class="version">当前版本： v1.0.2 | 更新于：2018-06-13</p>
    </div>
</main>

<div class="bs-docs-featurette">
    <div class="container">
        <h2 class="bs-docs-featurette-title">为移动开发的调试问题，提供便利。</h2>
        <p class="lead">Request Inspect 基于 swoole 实现基础服务、其高性能特点决定了服务的可靠性，以及稳定性。</p>
        <hr class="half-rule">
        <div class="row">
            <div class="col-sm-4">
                <h3>请求实时推送</h3>
                <p>Inspect 基于 WebSocket 实时获取移动应用服务端程序的被请求信息，以及响应数据。</p>
            </div>
            <div class="col-sm-4">
                <h3>自定义数据分类</h3>
                <p>移动应用服务端 Report 数据时，可自定义数据包，Inspect 端可根据数据包分类显示。</p>
            </div>
            <div class="col-sm-4">
                <h3>自定义条件过滤</h3>
                <p>Inspect 端可以基于用户自定义信息过滤服务端程序的被请求信息。以达到精确调试目的。</p>
            </div>
        </div>
    </div>
    <div class="beta-bar"><a href="https://github.com/lonisy/request-inspect">Fork Github</a></div>
</div>

<footer class="bs-docs-footer">
    <div class="container">
        <ul class="bs-docs-footer-links">
            <li><a href="https://github.com/lonisy">GitHub 仓库</a></li>
            <li><a href="https://github.com/lonisy/#examples">实例</a></li>
            <li><a href="https://github.com/lonisy/#about">关于</a></li>
        </ul>
        <p>本项目源码受 <a rel="license" href="https://github.com/twbs/bootstrap/blob/master/LICENSE" target="_blank">MIT</a>开源协议保护，文档受
            <a rel="license" href="https://creativecommons.org/licenses/by/3.0/" target="_blank">CC BY 3.0</a> 开源协议保护。
        </p>
    </div>
</footer>

<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="assets/js/vendor/jquery.min.js"><\/script>')</script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="assets/js/ie10-viewport-bug-workaround.js"></script>
</body>
</html>