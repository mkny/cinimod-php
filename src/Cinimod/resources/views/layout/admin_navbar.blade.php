<link rel="stylesheet" type="text/css" href="/css/nav.css">

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">
                <i><img src="/img/mafia-flower.png" style="width:20px;height: 20px;margin-top: -4px;" alt=""></i>
                <b>Cinimod</b>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                @include('cinimod::layout.admin_navbar-menu-items', array('items' => $navBar->roots()))
            </ul>
        </div>
    </div>
</nav>