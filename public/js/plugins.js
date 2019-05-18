$( document ).ready(function() {

    $('[data-toggle="popover"]').popover();




// Pace Setup
    window.paceOptions = {
        // Disable the 'elements' source
        elements: false,
        ajax: true,
        document: true,
        eventLag: true,

        // Only show the progress on regular and ajax-y page navigation,
        // not every request
        restartOnRequestAfter: false
    }

    //$('.select2').select2({
    //    theme: 'bootstrap',
    //    placeholder: 'Select an option'
    //});


    $(".switch").bootstrapSwitch();


    /**
     * Initialize Datatable methods
     */
    $('.datatable').DataTable({
        "aaSorting": [],
        "iDisplayLength": 50
    });




    /**
     * Block UI for processing data.
     * Specify what element to block via data-el or will use the default "blockable" class.
     */
    $('body').on(
        'click', '.block, .uiblock', function (e) {
            var that = $(this);
            var element = (that.attr('data-el')) ? that.attr('data-el') : ".blockable";
            $(element).block(
                {
                    message: that.attr('data-message'),
                    overlayCSS: {
                        background: 'rgba(142, 159, 167, 0.8)',
                        opacity: 1,
                        cursor: 'wait'
                    },
                    css: {
                        width: '50%',
                        border: 'none',
                        padding: '15px',
                        backgroundColor: '#000',
                        '-webkit-border-radius': '10px',
                        '-moz-border-radius': '10px',
                        opacity: .5,
                        color: '#fff'
                    },
                    blockMsgClass: 'block-msg-default'
                }
            );
        }
    );

    /**
     * Modal Ajax Call takes a url and spits the output into a modal body.
     */
    $('body').on('click', '.mjax', function (e) {
        var url = $(this).attr('data-href');

        $('.modal').html("<center><img style='position:absolute;top:25%;left:40%;width:300px;' src='/images/loading.gif'></center>");
        $('.modal').modal({
            keyboard: true
        }).show();

        $.get(url, function (data) {
            $('.modal').html(data);
        });
    });

    $('body').on('mouseenter', '.bwInCounter', function(e)
    {
        var obj = $(this);
        console.log(obj);
        var uuid = $(this).attr('data-uuid');
        var outObj = $('.bwOutCounter').filter('[data-uuid="'+uuid+'"]');
        obj.html("<img width=\"50\" src=\"/assets/images/bwloading.gif\">");
        outObj.html("<img width=\"50\" src=\"/assets/images/bwloading.gif\">");
        $.get("/devices/" + uuid + "?bw=true", function(data)
        {
             obj.html(data.in);
            outObj.html(data.out);

        });

    });

    $('body').on('click', '.serverLabel', function(e)
    {
        $(this).closest('.card').block(
            {
                message: "Loading...",
                fadeIn: 500,
                faeOut: 500,
                showOverlay: true,
                overlayCSS: {
                    background: 'rgba(142, 159, 167, 0.8)',
                    opacity: 1,
                    cursor: 'wait'
                },
                css: {
                    width: '50%',
                    border: 'none',
                    padding: '15px',
                    backgroundColor: '#000',
                    '-webkit-border-radius': '10px',
                    '-moz-border-radius': '10px',
                    opacity: .5,
                    color: '#fff'
                },
            }
        );
    });

    /**
     * Call internal API
     * data-action = Internal API action to call
     * data-revert (optional) set to true to revert the source element back to its original text
     * data-message (optional) Set a temporary loading message. Default is "Checking.."
     * data-target (optional) set where you want output to go. By default its the source element
     * field-* - Set post fields to send. Anything field-{name} will have the name and value sent.
     * @param el Source element
     */
    function iapi(el)
    {
        var oldState = $(el).html();
        var loading = $(el).attr('data-message') ? $(el).attr('data-message') : "Checking...";
        $(el).html(loading);
        var action = $(el).attr('data-action');
        var target = $(el).attr('data-target') ? $(el).attr('data-target') : el;
        // Build a parameter list based on field-{param} = value.
        var parameters = {};
        parameters.action = action;
        $.each(el.attributes, function (index, attr) // loop through attributes and find field-
        {
            var name = attr.nodeName;
            var value = attr.nodeValue;
            var regex = /field-/;
            if (name.match(regex))
            {
                var actualName = name.split("field-");
              //  console.log("Adding Field " + actualName + " with value " + value);
                parameters[actualName] = value;
            }
        });
       // console.log(parameters);
        $.ajax({
            type : "POST",
            url : "/iapi",
            data : parameters,
            success : function(data)
            {
                $(target).html(data.data.payload).hide().fadeIn();
                if ($(el).attr('data-revert').length)
                {
                    $(el).html(oldState); // Revert back so the button doesn't look stupid.
                }
            },
            dataType: 'json'
        });
    }
    // Autoloaders
    if ($('.iapi').length > 0)
    {
        $('.iapi').each(function (i, el)
        {
           iapi(el);
        });
    }
    // Click events for iapi
    $('body').on('click', '.iclick', function(e)
    {
        e.preventDefault();
        iapi(e.target);
    });

});
