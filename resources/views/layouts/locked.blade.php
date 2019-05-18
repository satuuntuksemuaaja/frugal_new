
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

            </div>
            <!-- end menu-extras -->

            <div class="clearfix"></div>

        </div> <!-- end container -->
    </div>
    <!-- end topbar-main -->

    <!-- MENU Start -->
    <div class="navbar-custom">
        <div class="container-fluid">

        </div> <!-- end container -->
    </div> <!-- end navbar-custom -->
</header>
<!-- End Navigation Bar-->


<div class="wrapper">
    <div class="container-fluid">
    
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
</script>
@yield('javascript')
</body>
</html>
