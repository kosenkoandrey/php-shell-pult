<!--=== Header v8 ===-->
<div class="header-v8 header-sticky">
    <!-- Topbar blog -->
    <div class="blog-topbar">
        <div class="topbar-search-block">
            <div class="container">
                <form action="">
                    <input type="text" class="form-control" placeholder="Search">
                    <div class="search-close"><i class="icon-close"></i></div>
                </form>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-8 col-xs-8">
                    <div class="topbar-time"><?= strftime("%a, %e %b %Y") ?></div>
                    <div class="topbar-toggler"><span class="fa fa-angle-down"></span></div>
                    <ul class="topbar-list topbar-menu">
                        <?
                        switch (APP::Module('Users')->user['role']) {
                            case 'default':
                                ?>
                                <li class="cd-log_reg hidden-sm hidden-md hidden-lg"><strong><a class="cd-signin" href="javascript:void(0);">Login</a></strong></li>
                                <li class="cd-log_reg hidden-sm hidden-md hidden-lg"><strong><a class="cd-signup" href="javascript:void(0);">Register</a></strong></li>
                                <?
                                break;
                            default:
                                ?>
                                <li class="hidden-sm hidden-md hidden-lg">
                                    <a href="javascript:void(0);"><?= APP::Module('Users')->user['email'] ?></a>
                                    <ul class="topbar-dropdown">
                                        <li><a href="<?= APP::Module('Routing')->root ?>users/profile">Profile</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>users/logout">Logout</a></li>
                                    </ul>
                                </li>
                                <?
                                break;
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-sm-4 col-xs-4 clearfix">
                    <!-- <i class="fa fa-search search-btn pull-right"></i> -->
                    <ul class="topbar-list topbar-menu topbar-log_reg pull-right visible-sm-block visible-md-block visible-lg-block">
                        <?
                        switch (APP::Module('Users')->user['role']) {
                            case 'default':
                                ?>
                                <li class="cd-log_reg home"><a class="cd-signin" href="javascript:void(0);">Login</a></li>
                                <li class="cd-log_reg"><a class="cd-signup" href="javascript:void(0);">Register</a></li>
                                <?
                                break;
                            default:
                                ?>
                                <li class="home">
                                    <a href="javascript:void(0);"><?= APP::Module('Users')->user['email'] ?></a>
                                    <ul class="topbar-dropdown">
                                        <li><a href="<?= APP::Module('Routing')->root ?>users/profile">Profile</a></li>
                                        <li><a href="<?= APP::Module('Routing')->root ?>users/logout">Logout</a></li>
                                    </ul>
                                </li>
                                <?
                                break;
                        }
                        ?>
                    </ul>
                </div>
            </div><!--/end row-->
        </div><!--/end container-->
    </div>
    <!-- End Topbar blog -->

    <!-- Navbar -->
    <div class="navbar mega-menu" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="res-container">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <div class="navbar-brand">
                    <a href="<?= APP::Module('Routing')->root . Blog::URI ?>" style="font-size: 40px; color: #111111;">
                        Mail IQ
                    </a>
                </div>
            </div><!--/end responsive container-->

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-responsive-collapse">
                <div class="res-container">
                    <ul class="nav navbar-nav">
                        <?
                        foreach ($data as $key => $value) {
                            ?>
                            <li class="dropdown mega-menu-fullwidth">
                                <a href="<?= APP::Module('Routing')->root . APP::Module('Blog')->uri . $value[0] ?>"><?= $value[1] ?></a>
                            </li>
                            <?
                        }
                        ?>
                    </ul>
                </div><!--/responsive container-->
            </div><!--/navbar-collapse-->
        </div><!--/end contaoner-->
    </div>
    <!-- End Navbar -->
</div>
<!--=== End Header v8 ===-->