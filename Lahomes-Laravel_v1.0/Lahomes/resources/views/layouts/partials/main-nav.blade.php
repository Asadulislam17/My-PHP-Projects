<div class="main-nav">
     <!-- Sidebar Logo Box -->
     <div class="logo-box">
          <!-- 👑 রিয়েল প্রজেক্ট স্টাইল: রোল অনুযায়ী লোগো ক্লিক রিডাইরেকশন -->
          @php
               $homeRoute = match(auth()->user()->role) {
                   'admin' => route('admin.dashboard'),
                   'agent' => route('agent.dashboard'),
                   default => route('dashboard')
               };
          @endphp

          <a href="{{ $homeRoute }}" class="logo-dark">
               <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
               <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
          </a>

          <a href="{{ $homeRoute }}" class="logo-light">
               <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
               <img src="/images/logo-light.png" class="logo-lg" alt="logo light">
          </a>
     </div>

     <!-- Menu Toggle Button (sm-hover) -->
     <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
          <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
     </button>

     <div class="scrollbar" data-simplebar>
          <ul class="navbar-nav" id="navbar-nav">
               <li class="menu-title">Menu</li>

               <!-- 🔗 ড্যাশবোর্ড লিংক (রোল ভিত্তিক ডাইনামিক লিংক) -->
               <li class="nav-item">
                    <a class="nav-link" href="{{ $homeRoute }}">
                         <span class="nav-icon"><i class="ri-dashboard-2-line"></i></span>
                         <span class="nav-text"> My Dashboard </span>
                    </a>
               </li>
               <!-- 🔐 শুধুমাত্র ADMIN এবং AGENT দেখতে পাবে -->
               @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent')
               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarProperty" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarProperty">
                         <span class="nav-icon"><i class="ri-community-line"></i></span>
                         <span class="nav-text"> Property </span>
                    </a>
                    <div class="collapse" id="sidebarProperty">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Property Grid</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Property List</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Property Details</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Add Property</a></li>
                         </ul>
                    </div>
               </li>
               @endif

               <!-- 🔐 শুধুমাত্র ADMIN দেখতে পাবে -->
               @if(auth()->user()->role === 'admin')
               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAgents" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAgents">
                         <span class="nav-icon"><i class="ri-group-line"></i></span>
                         <span class="nav-text"> Agents </span>
                    </a>
                    <div class="collapse" id="sidebarAgents">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">List View</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Grid View</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Agent Details</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Add Agent</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCustomers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCustomers">
                         <span class="nav-icon"><i class="ri-contacts-book-3-line"></i></span>
                         <span class="nav-text"> Customers </span>
                    </a>
                    <div class="collapse" id="sidebarCustomers">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">List View</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Grid View</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Customer Details</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Add Customer</a></li>
                         </ul>
                    </div>
               </li>
               @endif
               <!-- 🔗 অর্ডার মেনু (সব রোলই দেখতে পাবে) -->
               <li class="nav-item">
                    <a class="nav-link" href="#!">
                         <span class="nav-icon"><i class="ri-home-office-line"></i></span>
                         <span class="nav-text">Orders</span>
                    </a>
               </li>

               <!-- 🔗 ট্রানজেকশন (সব রোলই দেখতে পাবে) -->
               <li class="nav-item">
                    <a class="nav-link" href="#!">
                         <span class="nav-icon"><i class="ri-arrow-left-right-line"></i></span>
                         <span class="nav-text">Transactions</span>
                    </a>
               </li>

               <!-- 🔐 রিভিউ এবং পোস্ট ম্যানেজমেন্ট (ADMIN এবং AGENT এর জন্য) -->
               @if(auth()->user()->role === 'admin' || auth()->user()->role === 'agent')
               <li class="nav-item">
                    <a class="nav-link" href="#!">
                         <span class="nav-icon"><i class="ri-chat-quote-line"></i></span>
                         <span class="nav-text">Reviews</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarBlog" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBlog">
                         <span class="nav-icon"><i class="ri-news-line"></i></span>
                         <span class="nav-text">Post </span>
                    </a>
                    <div class="collapse" id="sidebarBlog">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Post</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Post Details</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Create Post</a></li>
                         </ul>
                    </div>
               </li>
               @endif

               <!-- 🔗 কাস্টমার কমিউনিকেশন (সবাই পাবে) -->
               <li class="nav-item">
                    <a class="nav-link" href="#!">
                         <span class="nav-icon"><i class="ri-discuss-line"></i></span>
                         <span class="nav-text">Messages</span>
                    </a>
               </li>
               <li class="nav-item">
                    <a class="nav-link" href="#!">
                         <span class="nav-icon"><i class="ri-inbox-line"></i></span>
                         <span class="nav-text">Inbox</span>
                    </a>
               </li>

               <!-- 🔐 কাস্টম ইউটিলিটি পেজ (শুধুমাত্র ADMIN কাস্টমাইজেশনের জন্য দেখতে পাবে) -->
               @if(auth()->user()->role === 'admin')
               <li class="menu-title">Custom</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarPages" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarPages">
                         <span class="nav-icon"><i class="ri-pages-line"></i></span>
                         <span class="nav-text"> Pages </span>
                    </a>
                    <div class="collapse" id="sidebarPages">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Welcome</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Calendar</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Invoice</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">FAQs</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Coming Soon</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Timeline</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Pricing</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Maintenance</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">404 Error</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">404 Error (alt)</a></li>
                         </ul>
                    </div>
               </li>
               @endif
               <!-- 🔐 অথেন্টিকেশন, উইজেটস এবং লেআউট মেনু (শুধুমাত্র ADMIN দেখতে পাবে) -->
               @if(auth()->user()->role === 'admin')
               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarAuthentication" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarAuthentication">
                         <span class="nav-icon"><i class="ri-lock-password-line"></i></span>
                         <span class="nav-text"> Authentication </span>
                    </a>
                    <div class="collapse" id="sidebarAuthentication">
                         <ul class="nav sub-navbar-nav">
                              <!-- 🛠️ Breeze এর অফিশিয়াল রাউট নাম ব্যবহার করা হলো -->
                              <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('login') }}">Sign In</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('register') }}">Sign Up</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="{{ route('password.request') }}">Reset Password</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Lock Screen</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link" href="#!">
                         <span class="nav-icon"><i class="ri-shapes-line"></i></span>
                         <span class="nav-text">Widgets</span>
                         <span class="badge bg-danger badge-pill text-end">Hot</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarLayouts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarLayouts">
                         <span class="nav-icon"><i class="ri-layout-line"></i></span>
                         <span class="nav-text"> Layouts </span>
                    </a>
                    <div class="collapse" id="sidebarLayouts">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Dark Sidenav</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Dark Topnav</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Simple Sidenav</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Small Sidenav</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Small Hover</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Small Hover Active</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!" target="_blank">Hidden Sidenav</a></li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link" target="_blank" href="#!">
                                        <span class="nav-text">Dark Mode</span>
                                        <span class="badge badge-soft-danger badge-pill text-end">Hot</span>
                                   </a>
                              </li>
                         </ul>
                    </div>
               </li>
               @endif
               <!-- 🔐 কম্পোনেন্ট এবং বেস ইউআই (শুধুমাত্র ADMIN দেখতে পাবে) -->
               @if(auth()->user()->role === 'admin')
               <li class="menu-title">Components</li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarBaseUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarBaseUI">
                         <span class="nav-icon"><i class="ri-contrast-drop-line"></i></span>
                         <span class="nav-text"> Base UI </span>
                    </a>
                    <div class="collapse" id="sidebarBaseUI">
                         <ul class="nav sub-navbar-nav">
                              <!-- 🛠️ ওল্ড রাউট পরিবর্তন করে হ্যাশ (#!) করা হলো যেন ক্র্যাশ না করে -->
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Accordion</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Alerts</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Avatar</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Badge</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Breadcrumb</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Buttons</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Card</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Carousel</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Collapse</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Dropdown</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">List Group</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Modal</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Tabs</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Offcanvas</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Pagination</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Placeholders</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Popovers</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Progress</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Scrollspy</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Spinners</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Toasts</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Tooltips</a></li>
                         </ul>
                    </div>
               </li>
               @endif
               <!-- 🔐 অ্যাডভান্সড ইউআই, চার্ট, ফর্ম এবং টেবিল (শুধুমাত্র ADMIN দেখতে পাবে) -->
               @if(auth()->user()->role === 'admin')
               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarExtendedUI" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarExtendedUI">
                         <span class="nav-icon"><i class="ri-briefcase-line"></i></span>
                         <span class="nav-text"> Advanced UI </span>
                    </a>
                    <div class="collapse" id="sidebarExtendedUI">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Ratings</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Sweet Alert</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Swiper Slider</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Scrollbar</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Toastify</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCharts" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCharts">
                         <span class="nav-icon"><i class="ri-bar-chart-line"></i></span>
                         <span class="nav-text"> Charts </span>
                    </a>
                    <div class="collapse" id="sidebarCharts">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Area</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Bar</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Bubble</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Candlestick</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Column</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Heatmap</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Line</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Mixed</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Timeline</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Boxplot</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Treemap</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Pie</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Radar</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">RadialBar</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Scatter</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Polar Area</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarForms" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarForms">
                         <span class="nav-icon"><i class="ri-survey-line"></i></span>
                         <span class="nav-text"> Forms </span>
                    </a>
                    <div class="collapse" id="sidebarForms">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Basic Elements</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Checkbox &amp; Radio</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Choice Select</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Clipboard</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Flatepicker</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Validation</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Wizard</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">File Upload</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Editors</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Input Mask</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Slider</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarTables" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarTables">
                         <span class="nav-icon"><i class="ri-table-line"></i></span>
                         <span class="nav-text"> Tables </span>
                    </a>
                    <div class="collapse" id="sidebarTables">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Basic Tables</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Grid Js</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarIcons" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarIcons">
                         <span class="nav-icon"><i class="ri-pencil-ruler-2-line"></i></span>
                         <span class="nav-text"> Icons </span>
                    </a>
                    <div class="collapse" id="sidebarIcons">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Remix Icons</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Solar Icons</a></li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarMaps" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMaps">
                         <span class="nav-icon"><i class="ri-road-map-line"></i></span>
                         <span class="nav-text"> Maps </span>
                    </a>
                    <div class="collapse" id="sidebarMaps">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Google Maps</a></li>
                              <li class="sub-nav-item"><a class="sub-nav-link" href="#!">Vector Maps</a></li>
                         </ul>
                    </div>
               </li>
               @endif
               <!-- 🔐 স্টাইল এবং ডেমো মাল্টি-লেভেল মেনু (শুধুমাত্র ADMIN দেখতে পাবে) -->
               @if(auth()->user()->role === 'admin')
               <li class="menu-title">Style</li>

               <li class="nav-item">
                    <a class="nav-link" href="javascript:void(0);">
                         <span class="nav-icon"><i class="ri-shield-star-line"></i></span>
                         <span class="nav-text">Badge Menu</span>
                         <span class="badge bg-primary badge-pill text-end">1</span>
                    </a>
               </li>

               <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarMultiLevelDemo" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarMultiLevelDemo">
                         <span class="nav-icon"><i class="ri-share-line"></i></span>
                         <span class="nav-text"> Menu Items </span>
                    </a>
                    <div class="collapse" id="sidebarMultiLevelDemo">
                         <ul class="nav sub-navbar-nav">
                              <li class="sub-nav-item"><a class="sub-nav-link" href="javascript:void(0);">Menu Item 1</a></li>
                              <li class="sub-nav-item">
                                   <a class="sub-nav-link menu-arrow" href="#sidebarItemDemoSubItem" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarItemDemoSubItem">
                                        <span> Menu Item 2 </span>
                                   </a>
                                   <div class="collapse" id="sidebarItemDemoSubItem">
                                        <ul class="nav sub-navbar-nav">
                                             <li class="sub-nav-item"><a class="sub-nav-link" href="javascript:void(0);">Menu Sub item</a></li>
                                        </ul>
                                   </div>
                              </li>
                         </ul>
                    </div>
               </li>

               <li class="nav-item">
                    <a class="nav-link disabled" href="javascript:void(0);">
                         <span class="nav-icon"><i class="ri-prohibited-2-line"></i></span>
                         <span class="nav-text"> Disable Item </span>
                    </a>
               </li>
               @endif
          </ul>
     </div>
</div>
