/**
 * Created by chris on 12/22/15.
 */
APP.init(
    {
        vueEl: '.page-wrapper',

        boot: function (app) {

            /**
             * Confirm Sweet Alert uses
             * data-method: PUT/GET/POST
             * data-message: The message to display on the confirmation
             * href: when confirmed.
             *
             */
            $('body').on(
                'click', '.confirm', function (e) {
                    var that = this;
                    $.ajaxSetup({
                        headers: { 'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content') },
                    });

                    e.preventDefault();
                    var method = $(this).attr('data-method') ? $(this).attr('data-method') : "PUT";
                    var title = $(this).attr('data-title') ? $(this).attr('data-title') : "Are you sure?";
                    var type = $(this).attr('data-type') ? $(this).attr('data-type') : "info";
                    var confirmColor = $(this).attr('data-color') ? $(this).attr('data-color') : "green";
                    var confirmText = $(this).attr('data-confirm') ? $(this).attr('data-confirm') : "Proceed";
                    // Show confirmation.
                    swal(
                        {
                            title: title,
                            text: $(this).attr('data-message'),
                            type: type,
                            showCancelButton: true,
                            confirmButtonColor: confirmColor,
                            confirmButtonText: confirmText,
                            closeOnConfirm: false,
                            showLoaderOnConfirm: true,
                        },
                        function () {
                            $.ajax(
                                {
                                    url: $(that).attr('href'),
                                    type: method,
                                    datatype: 'json',
                                    success: function (response) {
                                        app.ajaxFeedback(response);
                                    }
                                }
                            ); // success
                        });
                })
        }



    });
