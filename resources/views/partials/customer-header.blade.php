<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = "";
}
$arrx = explode("/", $_SERVER['REQUEST_URI']);


$products_search  = array_search("products.php", $arrx);
$products = $arrx[$products_search];

$orders_search  = array_search("orders.php", $arrx);
$orders = $arrx[$orders_search];

$history_search  = array_search("orders.php", $arrx);
$history = $arrx[$history_search];

?>
<style>
    .pcoded-navbar a:hover {
        color: #04A9F5 !important;
    }
</style>
<!-- [ navigation menu ] start -->
<nav class="pcoded-navbar">
    <div class="navbar-wrapper">
        <div class="navbar-brand header-logo">
            <a href="{{ route('customer.products.index') }}" class="b-brand">
                <img src="/assets/images/monsiniprom-logo.png" class="img-fluid w-75" alt="logo">
            </a>
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
        </div>
        <div class="navbar-content scroll-div">
            <ul class="nav pcoded-inner-navbar">
                  <li data-username="landing page" title="Products" class="nav-item {{ request()->routeIs('customer.products') ? 'active' : '' }}">
                    <a href="{{ route('customer.products.index') }}" class="nav-link">
                        <span class="pcoded-micon"><i class="feather icon-box"></i></span>
                        <span class="pcoded-mtext">Products</span>
                    </a>
                </li>

                    <li data-username="landing page" title="Your Orders" class="nav-item {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}">
                        <a href="{{ route('customer.orders.index') }}" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-shopping-cart"></i></span>
                            <span class="pcoded-mtext">Orders</span>
                        </a>
                    </li>
                    <li data-username="landing page" title="Your History" class="nav-item {{ request()->routeIs('customer.history') ? 'active' : '' }}">
                        <a href="{{ route('customer.history') }}" class="nav-link">
                            <span class="pcoded-micon"><i class="feather icon-clock"></i></span>
                            <span class="pcoded-mtext">History</span>
                        </a>
                    </li>

                <li data-username="landing page" title="Sign out" class="nav-item"><a href="{{ route('logout') }}" class="nav-link"><span class="pcoded-micon"><i class="feather icon-log-out"></i></span><span class="pcoded-mtext">Sign out</span></a></li>
            </ul>
        </div>
    </div>
</nav>
<!-- [ navigation menu ] end -->

<!-- [ Header ] start -->
<header class="navbar pcoded-header navbar-expand-lg navbar-light">
    <div class="m-header">
        <a class="mobile-menu" id="mobile-collapse1" href="#!"><span></span></a>
        <a href="{{ route('customer.products.index') }}" class="b-brand">
            <img src="/assets/images/monsiniprom-logo.png" class="img-fluid w-50" alt="logo">
        </a>
    </div>
    <a class="mobile-menu" id="mobile-header" href="#!">
        <i class="feather icon-more-horizontal"></i>
    </a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li>
                <a href="{{ route('logout') }}" class="dud-logout" title="Sign out">
                    <i class="feather icon-log-out"></i> Sign Out
                </a>
            </li>
        </ul>
    </div>
</header>