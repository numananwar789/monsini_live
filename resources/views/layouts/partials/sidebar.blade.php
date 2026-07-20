<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = '';
}
$arrx = explode('/', $_SERVER['REQUEST_URI']);

$menuStart = isset($arrx[1]) ? $arrx[1] : '';
// print_r($arrx);
// die;

// $admin_products_search = array_search('admin-products.php', $arrx);
// $edit_products_search = array_search('edit-product.php?id=' . $id, $arrx);
// $add_products_search = array_search('add-product.php', $arrx);

// $admin_products = $arrx[$admin_products_search];
// $admin_products_edit = $arrx[$edit_products_search];
// $admin_products_add = $arrx[$add_products_search];

// $admin_orders_search = array_search('admin-orders.php', $arrx);
// $admin_orders = $arrx[$admin_orders_search];
// $edit_order_search = array_search('edit-order.php?id=' . $id, $arrx);
// $admin_orders_edit = $arrx[$edit_order_search];
// $add_order_search = array_search('add-order.php', $arrx);
// $admin_orders_add = $arrx[$add_order_search];

// $admin_email_body_search = array_search('email_body.php', $arrx);
// $admin_email_body = $arrx[$admin_email_body_search];

// $admin_finalorders_search = array_search('admin-finalorders.php', $arrx);
// $admin_finalorders = $arrx[$admin_finalorders_search];

// $admin_customer_search = array_search('admin-customer.php', $arrx);
// $admin_customer = $arrx[$admin_customer_search];

// $admin_vendors_search = array_search('admin-vendors.php', $arrx);
// $admin_vendors = $arrx[$admin_vendors_search];
// $edit_vendor_search = array_search('edit-vendor.php?id=' . $id, $arrx);
// $edit_vendor = $arrx[$edit_vendor_search];

// $add_vendor_search = array_search('add-vendor.php', $arrx);
// $add_vendor = $arrx[$add_vendor_search];

// $all_admin_search = array_search('all-admin.php', $arrx);
// $all_admin = $arrx[$all_admin_search];

// $edit_admin_search = array_search('edit-admin.php?id=' . $id, $arrx);
// $edit_admin = $arrx[$edit_admin_search];

// $all_admin_search = array_search('add-admin.php', $arrx);
// $add_admin = $arrx[$all_admin_search];

// $order_allocation_search = array_search('order-allocation.php', $arrx);
// $order_allocation = $arrx[$order_allocation_search];

// $order_history_list = array_search('history-list.php', $arrx);
// $order_history_list1 = $arrx[$order_history_list];

// $order_inventory = array_search('inventory.php', $arrx);
// $order_order_inventory1 = $arrx[$order_inventory];

// echo $order_order_inventory1;die;

?>
<style>
    .nav-link:hover {
        color: #04A9F5 !important;
    }
</style>

<nav class="pcoded-navbar">
    <div class="navbar-wrapper">
        <div class="navbar-brand header-logo">
            <a href="{{ route('products.index') }}" class="b-brand">
                <img src="/assets/images/monsiniprom-logo.png"
                    class="img-fluid w-75" alt="logo" />
            </a>
            <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
        </div>
        <div class="navbar-content scroll-div">
            <ul class="nav pcoded-inner-navbar">
                <li data-username="landing page" title="Products"
                    class="nav-item {{ $menuStart == 'products' ? 'active' : '' }}">
                    <a href="{{ route('products.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-box"></i>
                        </span>
                        <span class="pcoded-mtext">Products</span>
                    </a>
                </li>
                <li data-username="landing page" title="Your Orders"
                    class="nav-item {{ $menuStart == 'orders' ? 'active' : '' }}">
                    <a href="{{ route('orders.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-shopping-cart"></i>
                        </span>
                        <span class="pcoded-mtext">Orders</span>
                    </a>
                </li>

                <li data-username="landing page" title="Final Orders" class="nav-item {{ $menuStart == 'final-orders' ? 'active' : '' }}">
                    <a href="{{ route('final-orders.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-shopping-cart"></i>
                        </span>
                        <span class="pcoded-mtext">Final Orders</span>
                    </a>
                </li>

                <li data-username="landing page" title="Order Allocation"
                    class="nav-item {{ $menuStart == 'order-allocations' ? 'active' : '' }}">
                    <a href="{{ route('order-allocations.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-shopping-cart"></i>
                        </span>
                        <span class="pcoded-mtext">Order Allocation</span>
                    </a>
                </li>

                <li data-username="landing page" title="Order History"
                    class="nav-item {{ $menuStart == 'order-histories' ? 'active' : '' }}">
                    <a href="{{ route('order-histories.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-shopping-cart"></i>
                        </span>
                        <span class="pcoded-mtext">Order History</span>
                    </a>
                </li>

                <li data-username="landing page" title="Email Body"
                    class="nav-item {{ $menuStart == 'email-templates' ? 'active' : '' }}">
                    <a href="{{ route('email-templates.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-shopping-cart"></i>
                        </span>
                        <span class="pcoded-mtext">Email Body</span>
                    </a>
                </li>


                <li data-username="landing page" title="Customer"
                    class="nav-item {{ $menuStart == 'customers' ? 'active' : '' }}">
                    <a href="{{ route('customers.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-users"></i>
                        </span>
                        <span class="pcoded-mtext">Customer</span>
                    </a>
                </li>

                <li data-username="landing page" title="Inventory"
                    class="nav-item {{ $menuStart == 'inventories' ? 'active' : '' }}">
                    <a href="{{ route('inventories.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-users"></i>
                        </span>
                        <span class="pcoded-mtext">Inventory</span>
                    </a>
                </li>
                <li data-username="landing page" title="Vendors"
                    class="nav-item {{ $menuStart == 'vendors' ? 'active' : '' }}">
                    <a href="{{ route('vendors.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-layers"></i>
                        </span>
                        <span class="pcoded-mtext">Vendors</span>
                    </a>
                </li>

                <?php if(auth()->user()->admin_role == "superadmin" || auth()->user()->user_name == 'admin1' || true) { ?>
                <li data-username="landing page" title="Admins"
                    class="nav-item  {{ $menuStart == 'users' ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="nav-link">
                        <span class="pcoded-micon">
                            <i class="feather icon-users"></i>
                        </span>
                        <span class="pcoded-mtext">Admins</span>
                    </a>
                </li>
                <?php  } ?>

                <li data-username="report page" title="Report Page"
                    class="nav-item {{ $menuStart == 'reports' ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}" class="nav-link">
                        <span class="pcoded-micon"><i class="feather icon-layers"></i></span><span
                            class="pcoded-mtext">Report</span>
                    </a>
                </li>

                <li data-username="Archive Page Products" title="Archive Page Products"
                    class="nav-item {{ $menuStart == 'product-archives' ? 'active' : '' }}">
                    <a href="{{ route('product-archives.index') }}" class="nav-link">
                        <span class="pcoded-micon"><i class="feather icon-layers"></i></span><span
                            class="pcoded-mtext">Archive Products</span>
                    </a>
                </li>

                <li data-username="Archive Page Orders" title="Archive Page Orders"
                    class="nav-item {{ $menuStart == 'order-history-archives' ? 'active' : '' }}">
                    <a href="{{ route('order-history-archives.index') }}" class="nav-link">
                        <span class="pcoded-micon"><i class="feather icon-layers"></i></span><span
                            class="pcoded-mtext">Archive Orders</span>
                    </a>
                </li>

                <li data-username="Cancelled Orders" title="Cancelled Orders"
                    class="nav-item {{ $menuStart == 'cancelled-orders' ? 'active' : '' }}">
                    <a href="{{ route('cancelled-orders') }}" class="nav-link">
                        <span class="pcoded-micon"><i class="feather icon-layers"></i></span><span
                            class="pcoded-mtext">Cancelled Orders</span>
                    </a>
                </li>



                <li data-username="landing page" title="Sign out" class="nav-item">
                    <a href="{{ route('logout.get') }}" class="nav-link">
                        <span class="pcoded-micon"><i class="feather icon-log-out"></i></span><span
                            class="pcoded-mtext">Sign out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- [ navigation menu ] end -->
<!-- [ Header ] start -->
<header class="navbar pcoded-header navbar-expand-lg navbar-light">
    <div class="m-header">
        <a class="mobile-menu" id="mobile-collapse1" href="#!"><span></span></a>
        <a href="{{ route('products.index') }}" class="b-brand">
            <img src="/assets/images/monsiniprom-logo.png"
                class="img-fluid w-50" alt="logo" />
        </a>
    </div>
    <a class="mobile-menu" id="mobile-header" href="#!">
        <i class="feather icon-more-horizontal"></i>
    </a>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li>
                <a href="{{ route('logout.get') }}" class="dud-logout" title="Sign out"> <i class="feather icon-log-out"></i>
                    Sign Out </a>
            </li>
        </ul>
    </div> 
</header>
