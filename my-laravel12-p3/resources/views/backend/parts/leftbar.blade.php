<aside class="sidebar-wrapper">
    <div class="sidebar-header">
        <div class="logo-icon">
            <img src="assets/images/logo-icon.png" class="logo-img" alt="">
        </div>
        <div class="logo-name flex-grow-1">
            <h5 class="mb-0">Roksyn</h5>
        </div>
        <div class="sidebar-close ">
            <span class="material-symbols-outlined">close</span>
        </div>
    </div>
    <div class="sidebar-nav" data-simplebar="true">

        <!--navigation-->
        <ul class="metismenu" id="menu">
            <li>
                <a href="{{ route('dashboard') }}">
                    <div class="parent-icon"><span class="material-symbols-outlined">home</span>
                    </div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
            <li>
                <a href="javascript:;" class="has-arrow">
                    <div class="parent-icon"><span class="material-symbols-outlined">shopping_cart</span>
                    </div>
                    <div class="menu-title">eCommerce</div>
                </a>
                <ul>
                    <!-- 📑 ১. Add Product রাউট লিংক যোগ করা হয়েছে -->
                    <li>
                        <a href="{{ route('products.create') }}">
                            <span class="material-symbols-outlined">arrow_right</span>Add Product
                        </a>
                    </li>

                    <!-- 📑 ২. Products (Index Page) রাউট লিংক যোগ করা হয়েছে -->
                    <li>
                        <a href="{{ route('products.index') }}">
                            <span class="material-symbols-outlined">arrow_right</span>Products
                        </a>
                    </li>

                    <!-- বাকি লিংকগুলো আপনার ফিউচার রাউট অনুযায়ী সেট করতে পারবেন -->
                    <li> <a href="ecommerce-customers.html"><span
                                class="material-symbols-outlined">arrow_right</span>Customers</a></li>
                    <li> <a href="ecommerce-customer-details.html"><span
                                class="material-symbols-outlined">arrow_right</span>Customer Details</a></li>
                    <li> <a href="ecommerce-orders.html"><span
                                class="material-symbols-outlined">arrow_right</span>Orders</a></li>
                    <li> <a href="ecommerce-customer-details.html"><span
                                class="material-symbols-outlined">arrow_right</span>Order Details</a></li>
                </ul>
            </li>

            <!-- 📑 নতুন ক্যাটাগরি মেনু (এখানে যুক্ত করা হয়েছে) -->
            <li>
                <a href="{{ route('categories.index') }}">
                    <div class="parent-icon"><span class="material-symbols-outlined">sell</span>
                    </div>
                    <div class="menu-title">Category</div>
                </a>
            </li>

        </ul>

        <!--end navigation-->


    </div>
    <div class="sidebar-bottom dropdown dropup-center dropup">
        <div class="dropdown-toggle d-flex align-items-center px-3 gap-3 w-100 h-100" data-bs-toggle="dropdown">
            <div class="user-img">
                <img src="assets/images/avatars/01.png" alt="">
            </div>
            <div class="user-info">
                <h5 class="mb-0 user-name">Jhon Maxwell</h5>
                <p class="mb-0 user-designation">UI Engineer</p>
            </div>
        </div>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="javascript:;"><span class="material-symbols-outlined me-2">
                        account_circle
                    </span><span>Profile</span></a>
            </li>
            <li><a class="dropdown-item" href="javascript:;"><span class="material-symbols-outlined me-2">
                        tune
                    </span><span>Settings</span></a>
            </li>
            <li><a class="dropdown-item" href="javascript:;"><span class="material-symbols-outlined me-2">
                        dashboard
                    </span><span>Dashboard</span></a>
            </li>
            <li><a class="dropdown-item" href="javascript:;"><span class="material-symbols-outlined me-2">
                        account_balance
                    </span><span>Earnings</span></a>
            </li>
            <li><a class="dropdown-item" href="javascript:;"><span class="material-symbols-outlined me-2">
                        cloud_download
                    </span><span>Downloads</span></a>
            </li>
            <li>
                <div class="dropdown-divider mb-0"></div>
            </li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <li><button class="dropdown-item" href="javascript:;"><span class="material-symbols-outlined me-2">
                            logout
                        </span><span>Logout</span></button>
                </li>
            </form>
        </ul>
    </div>
</aside>
