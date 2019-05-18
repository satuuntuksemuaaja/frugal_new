
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{$title or "Frugal Kitchens"}}</title>
    <meta content="Frugal Dashboard" name="description" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App Icons -->
    <link rel="shortcut icon" href="/assets/images/favicon.ico">

    <!-- App css -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/icons.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/style.css" rel="stylesheet" type="text/css" />
    <link href="/js/sweetalert.css" rel="stylesheet" type="text/css" />
    <link href="/assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="/assets/plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="/js/vendor-bootstrap-switch.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/select2-bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <!-- Date picker -->
    <link href="/assets/css/datetimepicker-bootstrap-4.min.css" rel="stylesheet" type="text/css" />
    <!-- Calendar -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.css" rel="stylesheet" type="text/css" />
    <!-- Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
    <!-- Summernote -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.10/summernote.css" rel="stylesheet" />

    @yield('css')

</head>


<body>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog" style="max-width: 600px;">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Modal Header</h4>
        <button type="button" class="close" data-dismiss="modal" onclick="CloseModal()">&times;</button>
      </div>
      <div id="message_result_modal"></div>
      <div class="modal-body" align="center">
        <p>Some text in the modal.</p>
      </div>
    <div class="modal-footer">
    </div>
    </div>

  </div>
</div>

<!-- Loader -->
<div id="preloader"><div id="status"><div class="spinner"></div></div></div>

<!-- Navigation Bar-->
<header id="topnav">
    <div class="topbar-main">
        <div class="container-fluid">

            <!-- Logo container-->
            <div class="logo">
                <!-- Text Logo -->
                <!--<a href="index.html" class="logo">-->
                <!--Upcube-->
                <!--</a>-->
                <!-- Image Logo -->
                Frugal 3
            </div>
            <!-- End Logo container-->


            <div class="menu-extras topbar-custom">

                <!-- Search input -->
                <div class="search-wrap" id="search-wrap">
                    <div class="search-bar">
                        <input class="search-input" type="search" placeholder="Search" />
                        <a href="#" class="close-search toggle-search" data-target="#search-wrap">
                            <i class="mdi mdi-close-circle"></i>
                        </a>
                    </div>
                </div>

                <ul class="list-inline float-right mb-0">
                    <!-- Search -->
                    <li class="list-inline-item dropdown notification-list">
                        <a class="nav-link waves-effect toggle-search" href="#"  data-target="#search-wrap">
                            <i class="mdi mdi-magnify noti-icon"></i>
                        </a>
                    </li>
                    <!-- Messages-->
                    <li class="list-inline-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <i class="mdi mdi-email-outline noti-icon"></i>
                            <span class="badge badge-danger noti-icon-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-menu-lg">
                            <!-- item-->
                            <div class="dropdown-item noti-title">
                                <h5><span class="badge badge-danger float-right">745</span>Messages</h5>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon"><img src="/assets/images/users/avatar-2.jpg" alt="user-img" class="img-fluid rounded-circle" /> </div>
                                <p class="notify-details"><b>Charles M. Jones</b><small class="text-muted">Dummy text of the printing and typesetting industry.</small></p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon"><img src="/assets/images/users/avatar-3.jpg" alt="user-img" class="img-fluid rounded-circle" /> </div>
                                <p class="notify-details"><b>Thomas J. Mimms</b><small class="text-muted">You have 87 unread messages</small></p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon"><img src="/assets/images/users/avatar-4.jpg" alt="user-img" class="img-fluid rounded-circle" /> </div>
                                <p class="notify-details"><b>Luis M. Konrad</b><small class="text-muted">It is a long established fact that a reader will</small></p>
                            </a>

                            <!-- All-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                View All
                            </a>

                        </div>
                    </li>
                    <!-- notification-->
                    <li class="list-inline-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <i class="mdi mdi-bell-outline noti-icon"></i>
                            <span class="badge badge-danger noti-icon-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-arrow dropdown-menu-lg">
                            <!-- item-->
                            <div class="dropdown-item noti-title">
                                <h5>Notification (3)</h5>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item active">
                                <div class="notify-icon bg-success"><i class="mdi mdi-cart-outline"></i></div>
                                <p class="notify-details"><b>Your order is placed</b><small class="text-muted">Dummy text of the printing and typesetting industry.</small></p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-warning"><i class="mdi mdi-message"></i></div>
                                <p class="notify-details"><b>New Message received</b><small class="text-muted">You have 87 unread messages</small></p>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <div class="notify-icon bg-info"><i class="mdi mdi-martini"></i></div>
                                <p class="notify-details"><b>Your item is shipped</b><small class="text-muted">It is a long established fact that a reader will</small></p>
                            </a>

                            <!-- All-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                View All
                            </a>

                        </div>
                    </li>
                    <!-- User-->
                    <li class="list-inline-item dropdown notification-list">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button"
                           aria-haspopup="false" aria-expanded="false">
                            <img src="/assets/images/users/avatar-1.jpg" alt="user" class="rounded-circle">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                            <a class="dropdown-item" href="#"><i class="dripicons-user text-muted"></i> Profile</a>
                            <a class="dropdown-item" href="#"><i class="dripicons-wallet text-muted"></i> My Wallet</a>
                            <a class="dropdown-item" href="#"><span class="badge badge-success pull-right m-t-5">5</span><i class="dripicons-gear text-muted"></i> Settings</a>
                            <a class="dropdown-item" href="#"><i class="dripicons-lock text-muted"></i> Lock screen</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#"><i class="dripicons-exit text-muted"></i> Logout</a>
                        </div>
                    </li>
                    <li class="menu-item list-inline-item">
                        <!-- Mobile menu toggle-->
                        <a class="navbar-toggle nav-link">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                        <!-- End mobile menu toggle-->
                    </li>

                </ul>
            </div>
            <!-- end menu-extras -->

            <div class="clearfix"></div>

        </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->

    <!-- MENU Start -->
    <div class="navbar-custom">
        <div class="container-fluid">
            <div id="navigation">
                <!-- Navigation Menu-->
                <ul class="navigation-menu">

                    <li class="has-submenu">
                        <a href="{{ route('dashboard') }}"><i class="ti-home"></i>Dashboard</a>
                    </li>


                    @if(userCan("admin.users"))
                    <li class="has-submenu">
                        <a href="#"><i class="ti-light-bulb"></i>Admin</a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <li class="drawer-menu-item "><a href="/admin/payments">Payments</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/users">Users</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/groups">Groups</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/acls">Access Control</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/quote_types">Quote Types</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/appliances">Appliances</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/authorizations">Authorizations</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/faqs">Faqs</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/promotions">Promotions</a></li>
                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <li class="drawer-menu-item "><a href="/admin/lead_sources">Lead Sources</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/vendors">Vendors</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/granites">Granites</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/countertops">Countertop Options</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/sinks">Sinks</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/hardware">Hardware</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/cabinets">Cabinets</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/responsibilities">Responsibilities</a></li>
                                    <li class="drawer-menu-item "><a href="/payouts">Payouts</a></li>
                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <li class="drawer-menu-item "><a href="/admin/addons">Addons</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/questions">Questionaire</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/punches">Punch List Questions</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/stores">Stores</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/accessories">Accessories</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/statuses">Statuses</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/pricing">Pricing</a></li>
                                    <li class="drawer-menu-item "><a href="/admin/dynamic">Dynamic</a></li>
                                    <li class="drawer-menu-item "><a href="{{ route('sync_database') }}">Sync old DB (FK2)</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    @endif

                    <li class="has-submenu">
                        <a href="/reports"><i class="ti-home"></i>Reports</a>
                    </li>

                    <li class="has-submenu">
                        <a href="/"><i class="ti-home"></i>Home</a>
                    </li>

                    <li class="has-submenu">
                        <a href="/customers"><i class="ti-home"></i>Customers</a>
                    </li>

                    <li class="has-submenu">
                        <a href="#"><i class="ti-light-bulb"></i>Others</a>
                        <ul class="submenu megamenu">
                            <li>
                                <ul>
                                    <li class="drawer-menu-item "><a href="/leads"><i class="ti-home"></i>Leads</a>
                                    <li class="drawer-menu-item "><a href="/quotes"><i class="ti-home"></i>Quotes</a>
                                    <li class="drawer-menu-item "><a href="/jobs"><i class="ti-home"></i>Jobs</a>
                                    <li class="drawer-menu-item "><a href="/tasks"><i class="ti-home"></i>Tasks</a>
                                    <li class="drawer-menu-item "><a href="/ffts"><i class="ti-home"></i>Final Touch</a>
                                    <li class="drawer-menu-item "><a href="/warranties"><i class="ti-home"></i>Warranty</a>

                                </ul>
                            </li>
                            <li>
                                <ul>
                                    <li class="drawer-menu-item "><a href="/service"><i class="ti-home"></i>Service</a>
                                    <li class="drawer-menu-item "><a href="/changes"><i class="ti-home"></i>Change Orders</a>
                                    <li class="drawer-menu-item "><a href="/pos"><i class="ti-home"></i>Purchase Orders</a>
                                    <li class="drawer-menu-item "><a href="/receiving"><i class="ti-home"></i>Receiving</a>
                                    <li class="drawer-menu-item "><a href="/buildup"><i class="ti-home"></i>Buildup</a>
                                    <li class="drawer-menu-item "><a href="/files"><i class="ti-home"></i>Files</a>

                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li class="has-submenu">
                        <a href="/logout"><i class="ti-arrow-right"></i>Logout</a>
                    </li>


                </ul>
                <!-- End navigation menu -->
            </div> <!-- end #navigation -->
        </div> <!-- end container -->
    </div> <!-- end navbar-custom -->
</header>
<!-- End Navigation Bar-->


<div class="wrapper">
    <div class="container-fluid">
    @if(!empty($crumbs))
        <!-- Page-Title -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <div class="btn-group pull-right">
                            <ol class="breadcrumb hide-phone p-0 m-0">
                                <li class="breadcrumb-item"><a href="/">Dashboard</a></li>

                            @foreach($crumbs as $crumb)
                                    @if(empty($crumb['url']))
                                        <li class="breadcrumb-item active">{{$crumb['text']}}</li>
                                    @else
                                        <li class="breadcrumb-item"><a href="{{$crumb['url']}}">{{$crumb['text']}}</a></li>
                                    @endif
                                @endforeach
                            </ol>
                        </div>
                        @if(!empty($title))
                            <h4 class="page-title">{{$title}}</h4>
                        @endif
                    </div>
                </div>
            </div>
            <!-- end page title end breadcrumb -->
        @endif

        <div id="div_status_message"></div>
        @yield('content')

    </div> <!-- end container -->
</div>
<!-- end wrapper -->


<!-- Footer -->
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                © 2018 Frugal Kitchens and Cabinets <i class="mdi mdi-heart text-danger"></i> by Vocalogic.
            </div>
        </div>
    </div>
</footer>
<!-- End Footer -->


<!-- jQuery  -->
<script src="/assets/js/jquery.min.js"></script>
<script src="/js/vue.min.js"></script>
<script src="/js/vapp.js"></script>
<script src="/assets/js/popper.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/modernizr.min.js"></script>
<script src="/assets/js/waves.js"></script>
<script src="/assets/js/jquery.slimscroll.js"></script>
<script src="/assets/js/jquery.nicescroll.js"></script>
<script src="/assets/js/jquery.scrollTo.min.js"></script>

<script src="/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>


<script src="/js/bootstrap-switch.min.js"></script>
<script src="/js/blockUI.min.js"></script>
<script src="/js/sweetalert.min.js"></script>
<script src="/js/select2.full.min.js"></script>
<script src="/js/components.js"></script>
<script src="/js/plugins.js"></script>

<!-- Date picker -->
<script src="/js/moment.js"></script>
<script src="/js/datetimepicker-bootstrap-4.min.js"></script>

<!-- App js -->
<script src="/assets/js/app.js"></script>

<!-- Siganture pad js -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>

<!-- Moment js -->
<script src="/assets/js/moment.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.min.js"></script>

<!-- Select2 js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<!-- Summernote -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.10/summernote.js"></script>

<script>
function setMessageModal(type, title, message)
{
  $('#message_result_modal').html('\
                                    <div class="alert alert-' + type + ' alert-dismissable">\
                                      <strong>'+ title + '!</strong> ' + message + '\
                                      <a href="#" data-dismiss="alert" aria-label="close">&times;</a>\
                                    </div>\
                                  ');
}
function removeMessageModal()
{
  $('#message_result_modal').html('');
}
function CloseModal()
{
    $('.modal-dialog').attr('style', 'max-width: 600px;');
    $('#myModal').modal('hide');
}
function setStatusMessage(type, title, message)
{
  $('#div_status_message').html('\
                                    <div class="alert alert-' + type + ' alert-dismissable">\
                                      <strong>'+ title + '!</strong> ' + message + '\
                                      <a href="#" data-dismiss="alert" aria-label="close">&times;</a>\
                                    </div>\
                                  ');
}
function removeStatusMessage()
{
  $('#div_status_message').html('');
}
</script>
@yield('javascript')
</body>
</html>
