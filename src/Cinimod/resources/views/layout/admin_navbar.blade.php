<!-- <link rel="stylesheet" type="text/css" href="/css/nav.css"> -->

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ route('adm::index') }}">
                <i><img src="/img/mafia-flower.png" style="width:20px;height: 20px;margin-top: -4px;" alt=""></i>
                <b>Cinimod</b>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                @if (isset($navBar))
                @include('cinimod::layout.admin_navbar-menu-items', array('items' => $navBar->roots()))
                @endif
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Profile <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <!-- <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li> -->
                    <li role="separator" class="divider"></li>
                    <li><a href="/">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
</nav>