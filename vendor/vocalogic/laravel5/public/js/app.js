/*
|--------------------------------------------------------------------------
| Vocalogic Reusable Front-End Goodies To Make You Happy
|--------------------------------------------------------------------------
|
| Standing on the shoulders of giants:
| "jQuery", "Vue", Moment.js", "Bootstrap",
| "Sweet Alerts" and "DataTables"
|
*/

/**
 * APP is the only outside world accessible point to all the goodies.
 * Call APP.init() to initialize it.
 * You may pass an options object as parameter, customizing your APP.
 *
 * @param  object options   Customization options
 * @return void
 */
var APP = { init: function(options) {

    // This will rewrite APP with the return value of the following function,
    // which receives global jQuery as parameter.
    APP = (function($) {

        // Internally, we will use this "app" object,
        // which will be returned by this function,
        // becoming the globally accessible APP.
        var app = {};

        // Code became huge and bloated. Now we just trigger
        // the app.init event and send the app object, so
        // init may happen at many specialized places.
        $(document).trigger('app.init', [app]);

        // After building our app object, we allow it to be overrided
        // by the user-provided customization options.
        // Anything can be overrided!
        $.extend(true, app, options);

        // On Document Ready
        function boot()
        {
            $(document).trigger('app.beforeBoot', [app]);

            // Use options.boot to specify this function
            if (typeof app.boot == 'function') {
                app.boot(app);
            }

            $(document).trigger('app.afterBoot', [app]);
        }
        $(boot);   // jQuery's On Document Ready

        return app;   // internal app becomes global APP

    })(jQuery);   // global jQuery becomes internal $

}};

/*
|--------------------------------------------------------------------------
| Generic Utilities and Helpers
|--------------------------------------------------------------------------
*/

(function($) {

    function init(e, app) {

        // Default options
        app.dateFormat        = 'll';
        app.sourceDateFormat  = 'YYYY-MM-DD HH:mm:ss';
        app.invalidDate       = 'n/a';
        app.formSubmitAttr    = 'form-submit';
        app.dataTableDefaults = {};
        app.scriptRegex       = /<script[\s\S]*?>[\s\S]*?<\/script>/g;

        // Helper function - Format date using moment.js
        app.formatDate = function(dateString, dateFormat, sourceDateFormat, invalidDate)
        {
            dateFormat = dateFormat || app.dateFormat;
            sourceDateFormat = sourceDateFormat || app.sourceDateFormat;
            invalidDate = invalidDate || app.invalidDate;
            return moment(dateString, sourceDateFormat).format(dateFormat).replace('Invalid date', invalidDate);
        };

         // Return a Y or N based on boolean value.
        app.yn = function(value)
        {
          if (value == 1)
          {
              return "Y";
          }
            else
          {
              return "N";
          }
        };

        app.malefemale = function(value)
        {
            switch (value)
            {
                case 'M' : return "Male";
                case 'F' : return "Female";
                case 'B' : return "Both";
                case "N" : return "Neither";
            }
        }

        // Helper function - Empty an array
        app.empty = function(arr) {
            arr.splice(0, arr.length);
        };

        // Helper function - Display success message
        app.success = function(message, callback)
        {
            swal({
                    title: "Success!",
                    text: message,
                    type: "success",
                    timer: 1000
                },
                callback);
        };

        // Helper function - Display error message
        app.errorMsg = function(message, callback)
        {
            swal({
                    title: "Oops...",
                    text: message,
                    type: "error"
                },
                callback);
        };

        // This function sets up "click" event handling for "form-submit" links and buttons
        app.setFormSubmitHandler = function(selector, formSubmitAttr)
        {
            selector = selector || document;
            formSubmitAttr = formSubmitAttr || app.formSubmitAttr;
            var formSubmitHandler = function(e) {
                e.preventDefault();
                $($(this).attr(formSubmitAttr)).submit();
            };
            $(selector).on('click', '[' + formSubmitAttr + ']', formSubmitHandler);
        };

        // Apply default DataTables options
        if ($.fn.dataTable)
        {
            // Silent DataTables error handling
            // https://datatables.net/reference/event/error
            $.fn.dataTable.ext.errMode = 'none';

            // Options for all DataTables
            $.extend( $.fn.dataTable.defaults, app.dataTableDefaults );
        }
    }

    function boot(e, app) {
        app.setFormSubmitHandler(document, app.formSubmitAttr);
    }

    $(document).one('app.init', init);
    $(document).one('app.beforeBoot', boot);

})(jQuery);

/*
|--------------------------------------------------------------------------
| jQuery Ajax - Setup and Utilities
|--------------------------------------------------------------------------
*/

(function($) {

    function ajaxInit(e, app) {

        // Default options
        app.ajaxActionAttr   = 'ajax-action';
        app.ajaxConfirmAttr  = 'ajax-confirm';
        app.ajaxInputAttr    = 'ajax-input';
        app.ajaxCallbackAttr = 'ajax-callback';
        app.ajaxDeleteMethod = 'ajax-delete';
        app.ajaxFormSelector = 'form[ajax]';
        app.callbacks        = {
            reload: function() {
                window.location.reload();
            },
            redirect: function(url) {
                window.location.href = url;
            }
        };

        // We will always send the X-CSRF-TOKEN header at all Ajax requests
        // The token shall be available at a meta HTML tag like
        // <meta name="csrf-token" content="token here">
        // See: http://laravel.com/docs/master/routing#csrf-x-csrf-token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        });

        // This function sets up "click" event handling for "ajax-action" links and buttons
        app.setAjaxActionHandler = function(selector, ajaxActionAttr)
        {
            selector = selector || document;
            ajaxActionAttr = ajaxActionAttr || app.ajaxActionAttr;
            var data = {};
            var ajaxCall = app.ajaxCaller({
                getUrl: function(el) { return $(el).attr(ajaxActionAttr); },
                getData: function() { return data; }
            });
            $(selector).on('click', '[' + ajaxActionAttr + ']', app.ajaxCallHandler(ajaxCall, data));
        };

        // This function sets up ajax form submission
        app.setAjaxFormHandler = function(selector, ajaxFormSelector)
        {
            selector = selector || document;
            ajaxFormSelector = ajaxFormSelector || app.ajaxFormSelector;
            var data = {};
            var ajaxCall = app.ajaxCaller({
                getUrl: function(el) { return $(el).attr('action'); },
                getData: function(el) { return $(el).serialize(); }
            });
            $(selector).on('submit', app.ajaxFormSelector, app.ajaxCallHandler(ajaxCall, data));
        };

        // Returns DOM event handler for the HTML tag containing the "ajax" attributes
        app.ajaxCallHandler = function(ajaxCall, data) {
            return function(e) {
                var element = this, $element = $(element);
                var color = ($element.hasClass('danger') || $element.hasClass('btn-danger') ? "#DD6B55" : "#75B045");
                e.preventDefault();

                // ajax-confirm (optional attribute) - opens confirm dialog using the attribute value as confirmation text
                if ($element.is('['+app.ajaxConfirmAttr+']')) {
                    return swal({
                        title: "Are you sure?",
                        text:  $element.attr(app.ajaxConfirmAttr),
                        type:  "warning",
                        showCancelButton:   true,
                        confirmButtonColor: color,
                        confirmButtonText:  "Yes, I am sure."
                    },
                    $.proxy(ajaxCall, element));
                }

                // and for usage with buttons, not forms:
                // ajax-input (optional attribute) - opens prompt dialog using the attribute value as follows:
                // "name:text" - where "name" is the name of the input field which will be posted in the ajax call
                // and "text" is the input field label, which will appear in the prompt dialog;
                // either the element title attribute or the element text will be used as the title of the dialog
                else if ($element.is('['+app.ajaxInputAttr+']')) {
                    var input = $element.attr(app.ajaxInputAttr).split(':');
                    swal({
                        title: $element.attr('title') || $element.text(),
                        text:  input[1],
                        type:  "input",
                        animation: "slide-from-top",
                        showCancelButton: true,
                        inputPlaceholder:   $element.attr('placeholder'),
                        confirmButtonColor: color,
                        confirmButtonText:  "Submit"
                    },
                    function (inputValue) {
                        if (inputValue === false) return false;
                        data[input[0]] = inputValue;
                        ajaxCall.call(element);
                    });
                }

                // if there is no "ajax-confirm" nor "ajax-input" , we'll immediately make the ajax call
                else {
                    ajaxCall.call(element);
                }
            };
        }

        // Returns function which actually makes the Ajax request, using helper's getUrl and getData
        app.ajaxCaller = function(helper) {
            return function() {
                // the "this" here will be determined at ajaxCallHandler,
                // where this current function will be effectively called
                var self = this, $self = $(this);

                // if there is a callback reference, we will call it after the ajax call
                var callback = app.callbacks[$self.attr(app.ajaxCallbackAttr)];

                // if there is a dataTable reference, we will refresh it after the ajax call
                var datatableSelector = '#' + ($self.attr('datatable-id') || $self.closest('.dataTable').attr('id'));
                $(datatableSelector + '_processing').show();

                // avoid multiple calls (double-click, impatient people)
                if (self.processing) return;
                self.processing = true;

                // finally, we are doing the ajax call!
                $.post(helper.getUrl(self), helper.getData(self))

                // calling the callback after the call is done (success)
                .done(function(result) {
                    if (typeof callback == 'function') {
                        callback(result);
                    }
                })

                // always revert "processing" flag, and refresh dataTable (if applicable)
                .always(function() {
                    self.processing = false;
                    $(datatableSelector + '_processing').hide();
                    $(datatableSelector).DataTable().ajax.reload();
                });
            };
        };

        // Display error or success message based on jQuery ajax call responseJSON
        app.ajaxFeedback = function(response)
        {
            if (typeof response != 'object') {
                return;
            }
            var callback = undefined;
            if (typeof response.callback == 'string') {
                var colonIndex = response.callback.indexOf(':');
                if (colonIndex > 0) {
                    var name = response.callback.substr(0, colonIndex);
                    var args = response.callback.slice(colonIndex + 1).split(',');
                    callback = function() {
                        app.callbacks[name].apply(response, args);
                    };
                } else {
                    var name = response.callback;
                    callback = function() {
                        app.callbacks[name].apply(response);
                    };
                }
            }
            if (typeof response.success == 'string') {
                return app.success(response.success, callback);
            }
            if (typeof response.error == 'string') {
                return app.errorMsg(response.error, callback);
            }
            if (typeof callback == 'function') {
                return callback();
            }
        };
    }

    function ajaxBoot(e, app) {

            // Custom global ajax response handler may be defined at options.ajaxFeedback
            // Otherwise, our default one will be used
            // https://api.jquery.com/ajaxComplete/
            $(document).ajaxComplete(function (event, xhr, settings) {
                app.ajaxFeedback(xhr.responseJSON);
            });

            app.setAjaxActionHandler(document, app.ajaxActionAttr);
            app.setAjaxFormHandler(document, app.ajaxFormSelector);
    }

    $(document).one('app.init', ajaxInit);
    $(document).one('app.beforeBoot', ajaxBoot);

})(jQuery);

/*
|--------------------------------------------------------------------------
| Vue - utilities, global VM, components, directives and filters
|--------------------------------------------------------------------------
*/

(function($, Vue) {

    // Exit if Vue is not loaded
    if (typeof Vue != 'function') return;

    function vueInit(e, app) {
        app.vue       = {};
        app.vueEl     = '';
        app.vueErrors = 'errors';

        /**
         * FORM VALIDATION "KIT"
         *
         * Step 1: add "v-on" attribute to your form   v-on="submit: onSubmit"
         * Step 2: add "validate" method to your vm methods
         * Step 3: wrap your methods using APP.methods
         *
         * methods: APP.methods({
         *     // your methods
         *     myMethod1: function() {
         *     },
         *     myMethod2: function() {
         *     },
         *     // the required validate method
         *     validate: function() {
         *         // sample validation
         *         if (this.name.length == 0) {
         *             // if fails, pass field name and error message to this.err
         *             this.err('name', 'Please provide a name for the project');
         *         }
         *     }
         * })
         */

        // Returns function to be used as "onSubmit" Vue method (form submission event handler)
        // It will call the "validate" method internally
        app.vueOnSubmit = function(validated) {
            return function(e) {
                // empty errors
                app.empty(this[app.vueErrors]);
                // validate
                this.validate();
                validated.already = true;
                // avoid submit if invalid
                if (this[app.vueErrors].length > 0) {
                    e.preventDefault();
                }
            };
        };

        // Returns function to be used as "onKeyUp" Vue method
        // It will call the "validate" function internally
        app.vueOnKeyUp = function(validated) {
            return function(e) {
                // validates only if form has been validated once (ie, form failed validation on submit)
                if (validated.already) {
                    // empty errors
                    app.empty(this[app.vueErrors]);
                    // validate
                    this.validate();
                }
            };
        };

        // Function to be used as "hasError" Vue method
        app.vueHasError = function(field) {
            var result = false;
            $.each(this[app.vueErrors], function(index, error) {
                if (error.field == field) {
                    result = true;
                    return false;
                }
            });
            return result;
        };

        // Function to be used as "errorMessage" Vue method
        app.vueErrorMessage = function(field) {
            var result = '';
            $.each(this[app.vueErrors], function(index, error) {
                if (error.field == field) {
                    result = error.message;
                    return false;
                }
            });
            return result;
        };

        // Push an "error object" into errors array
        app.vueErr = function(field, message) {
            this[app.vueErrors].push({ "field": field, "message": message });
        };

        // Add "onSubmit", "onKeyUp", hasError", "errorMessage" and "err" methods to the list of methods
        // A "validate" method is required - it will be used at "onSubmit" and "onKeyUp"
        app.methods = function(methods) {
            var validated = { already: false };
            methods.onSubmit = app.vueOnSubmit(validated);
            methods.onKeyUp = app.vueOnKeyUp(validated);
            methods.hasError = app.vueHasError;
            methods.errorMessage = app.vueErrorMessage;
            methods.err = app.vueErr;
            return methods;
        };

        /*** END OF FORM VALIDATION "KIT" ***/

        /**
         * Returns a Vue component factory function,
         * requesting the named component to the specified route (defaults to current),
         * using the response as the component template.
         * <script> code in the response will run.
         * @param  string   name   component "name"
         * @param  string   url    ajax url to load the component
         */
        app.component = function(name, url) {
            return function(resolve, reject) {
                var failure = function() { reject('Failed to load component from server.'); };
                var loaded = function(data) {
                    var script = data.match(app.scriptRegex);
                    if (script) $('body').append($(script[0]));
                    resolve($.extend({ template: data }, app.vueComponentOptions));
                };
                $.post(url || '', { component: name }).fail(failure).done(loaded);
            };
        };

        /**
         * Stores Vue component options for later usage
         * At <script> tag of ajax component loaded by APP.component,
         * instead of "new Vue(options);" use "new APP.Vue(options);"
         */
        app.Vue = function(options) {
            if (options.data)
            {
                var originalData = options.data;
                options.data = function() { return originalData; };
            }
            app.vueComponentOptions = options;
        };

        /**
         * VM method - ajax load HTML into DOM
         * @param  string   source     ajax URL route to load the html
         * @param  string   selector   optional selector to place the html
         * @param  function callback   optional callback function
         */
        app.vueHtmlLoad = function(source, selector, callback) {
            var self = this;
            $.get(source, function(html) {
                // get jQuery object of the html
                var $html = $(html);

                // get v-ref attribute
                var ref = $html.attr('v-ref');

                // if no selector was provided
                if (!selector) {
                    // append html to the vm
                    $html.appendTo(self.$el);
                } else {
                    // otherwise replace selector's html
                    $html.appendTo($(selector).empty());
                }

                // compile the HTML, vuefying it
                self.$compile($html.get(0));

                // if html v-ref has a loaded function, call it
                if (ref && (typeof self.$[ref].loaded == 'function')) {
                    self.$[ref].loaded();
                }

                // if there is a callback, call it
                if (typeof callback == 'function') {
                    callback.call(self, html);
                }
            });
        }; // vueHtmlLoad

        /**
         * VM method
         * @param  string source      ajax URL route to load the object
         * @param  string property    vm data property to receive the loaded entity
         * @param  string reference   v-ref of a component instance whose "loaded" method will be called
         * @return void
         */
        app.vueJsonLoad = function(source, property, reference) {
            var self = this;
            // if only a single parameter has been provided, let's make it the reference
            if (!property && !reference) {
                reference = source;
            }
            var hasReference = (reference && (typeof self.$[reference] == 'object'));
            if (hasReference) {
                var $referenceEl = $(self.$[reference].$el);
                if (!source || (reference == source)) {
                    source = self.$interpolate($referenceEl.attr('ajax-url'));
                }
                if (!property) {
                    property = self.$interpolate($referenceEl.attr('ajax-model'));
                }
            }
            $.get(source, function(object) {
                if (property) {
                    if (self.hasOwnProperty(property)) {
                        self[property] = object;
                    } else {
                        self.$add(property, object);
                    }
                }
                if (hasReference && (typeof self.$[reference].loaded == 'function')) {
                    self.$[reference].loaded();
                }
            });
        }; // jsonLoad
    }

    function vueBoot(e, app) {
        var vueOptions = {
            el: app.vueEl,
            data: {},
            methods: {
                htmlLoad: app.vueHtmlLoad,
                jsonLoad: app.vueJsonLoad
            }
        };
        vueOptions.data[app.vueErrors] = [];
        $.extend(true, vueOptions, app.vue);
        if (!vueOptions.el) return;
        app.vm = new Vue(vueOptions);
    }

    $(document).one('app.init', vueInit);
    $(document).one('app.beforeBoot', vueBoot);

    /**
     * "modal" component
     */
    Vue.component('modal', {
        props: {
            'title': {},
            'open': { type: Boolean, twoWay: true }
        },
        watch: {
            'open': function(val) { $(this.$el).modal(val ? 'show' : 'hide'); }
        },
        methods: {
            show: function() { this.open = true; },
            hide: function() { this.open = false; },
            loaded: function() { this.show(); }
        },
        attached: function() {
            var self = this;
            $(this.$el).on('hidden.bs.modal', function () { self.open = false; });
        },
        template:
            '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">\n' +
            '    <div class="modal-dialog">\n' +
            '        <div class="modal-content">\n' +
            '            <div class="modal-header">\n' +
            '                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>\n' +
            '                <h4 class="modal-title">{{title}}</h4>\n' +
            '            </div>\n' +
            '            <div class="modal-body">\n' +
            '                <content select="div:first-child"></content>\n' +
            '            </div>\n' +
            '            <div class="modal-footer">\n' +
            '                <content select="footer"></content>\n' +
            '            </div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>'
    });

    /**
     * "vlform" component
     */
    Vue.component('vlform', {
        props: ['$data'],
        ready: function() {
            var parent = this.$el.parentNode;
            var form = $(this.$options.el.outerHTML.replace('<vlform', '<form').replace('</vlform>', '<form>')).get(0);
            this.$root.$compile(form);
            this.$remove();
            parent.appendChild(form);
            this.$appendTo(form);
        }
    });

    /**
     * "v-ajax-model" directive
     */
    Vue.directive('ajax-model', {
        isLiteral: true,
        bind: function () {
            if (this._isDynamicLiteral) return;
            APP.vueJsonLoad.call(this.vm, this.expression, this.arg, $(this.el).attr('ajax-ref'));
        },
        update: function (value) {
            APP.vueJsonLoad.call(this.vm, value, this.arg, $(this.el).attr('ajax-ref'));
        },
    })

    /**
     * "formatDate" filter
     */
    Vue.filter('formatDate', function (value, dateFormat, sourceDateFormat, invalidDate) {
        return APP.formatDate(value, dateFormat, sourceDateFormat, invalidDate);
    });

    Vue.filter('yn', function(value)
    {
       return APP.yn(value);
    });

    Vue.filter('malefemale', function(value)
    {
       return APP.malefemale(value);
    });

})(jQuery, Vue);
