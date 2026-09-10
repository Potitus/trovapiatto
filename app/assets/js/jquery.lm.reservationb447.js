
/*
 * LM Reservation - jQuery Plugin
 */

'use strict';

(function($) {

    $.fn.LMReservation = function(options) {

        //*****************//
        // GLOBAL Settings //
        //*****************//

        // Default settings
        var defaults = {

            // Globals
            PREFIX: '_lm_',
            BODY_DATA_MENU_ID: 'data-menu-id',

            // Reservation
            RES_FORM: '#prenotazione',
            RES_FORM_INPUT_DATE: '#data-prenotazione',
            RES_FORM_ERROR_DATE: '#data-prenotazione-errore',
            RES_FORM_INPUT_MARKETING_LM: '#marketing-leggimenu',
            RES_FORM_INPUT_MARKETING_GEST: '#marketing-gestore',
            RES_FORM_INPUT_PRIVACY: '#privacy-box',
            RES_FORM_ERROR_PRIVACY: '#privacy-error',
            RES_FORM_INPUT_SUBMIT: '#invia-prenotazione',

        };

        // Extend Settings
        var settings = $.extend({}, defaults, options);

        // Internal Vars
        var menuID = $('body').attr(settings.BODY_DATA_MENU_ID);
        var reservationPrefix = settings.PREFIX + 'prenotazione_' + menuID + '-';


        //***********************//
        // RESERVATION Functions //
        //***********************//

        // Get Reservations Data
        var getReservationsData = function(){

            var localData = localStorage;
            var filteredData = {};

            // Filter data by prefix
            Object.keys(localStorage).sort().reverse().forEach(function(key){
                if( key.indexOf(reservationPrefix) !== -1 ) {
                    filteredData[key] = localData.getItem(key);
                }
            });

            return filteredData;

        }

        // Submit Reservation
        var submitReservation = function(e, form){

            // Stop form from submitting normally
            e.preventDefault();
            var submit = true;

            // Check week days
            var weekDaySelected = getWeekDay( $(settings.RES_FORM_INPUT_DATE).val() ).toString();
            var weekDaysOpen = $(settings.RES_FORM_INPUT_DATE).data('giorni-settimana');

            if( !isNaN(weekDaySelected) && weekDaysOpen.length && $.inArray(weekDaySelected, weekDaysOpen) == '-1' ){

                console.log('sono in');

                $(settings.RES_FORM_INPUT_DATE).addClass('fieldHasError');
                $(settings.RES_FORM_ERROR_DATE).text($(settings.RES_FORM_ERROR_DATE).data('text')).removeClass('d-none');
                submit = false;

                $([document.documentElement, document.body]).animate({
                    scrollTop: $(settings.RES_FORM_ERROR_DATE).offset().top-150
                }, 200);

            }else{

                $(settings.RES_FORM_INPUT_DATE).removeClass('fieldHasError');
                $(settings.RES_FORM_ERROR_DATE).text('').addClass('d-none');

                // Check privacy acceptance
                if( $(settings.RES_FORM_INPUT_PRIVACY).length > 0 ) {

                    if( $(settings.RES_FORM_INPUT_PRIVACY+':checked').length > 0 ){
                        submit = true;
                    }else{
                        $(settings.RES_FORM_ERROR_PRIVACY).html( $(settings.RES_FORM_INPUT_PRIVACY).data('error-message') );
                        submit = false;
                    }

                }

            }

            // Send Action if submit == true
            if( submit === true ) {

                // Show Spin icon on submit button
                $(settings.RES_FORM_INPUT_SUBMIT+' .spinner-border').removeClass('d-none');

                // Send the data using post
                var posting = $.post('/app/admin/api.php?action=submit-reservation', form.serialize());

                // Put the results in a div
                posting.done(function(data) {

                    setTimeout(function () {

                        $(settings.RES_FORM_INPUT_SUBMIT+' .spinner-border').addClass('d-none');
                        $(settings.RES_FORM_INPUT_SUBMIT+' .icon-success').removeClass('d-none');

                        setTimeout(function () {

                            if( data.id ) {

                                // Reload Page and see reservation details
                                var formActionUrl =  $(settings.RES_FORM).attr('action');

                                window.location.href = formActionUrl + data.id;

                            }

                        }, 500);

                    }, 1000);

                });

            }

        }


        //******************//
        // HELPER Functions //
        //******************//

        // Get week day from date string
        function getWeekDay( dateString ){
            return new Date(dateString).getDay();
        }

        // Random number
        function randNumber(min, max){
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min + 1) + min);
        }

        // Format Date
        function currentDate(time= false) {

            var result = '';

            var d = new Date(),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear(),
                hours = d.getHours(),
                minutes = ('0' + d.getMinutes()).slice(-2);

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            result = [day, month, year].join('/');
            result += ' - ' + hours + ':' + minutes;

            if( time ) {
                return d.getTime();
            }else{
                return result;
            }

        }

        // Convert Object in Array
        function objectToInputData(object){

            var result = [];

            // Build Array
            for(var key in object) {

                var objectData = JSON.parse(object[key]);
                var objectItem = [];

                for(var objKey in objectData){

                    objectItem.push(objectData[objKey]);

                }

                result.push(objectItem);

            }

            // Reorder array
            result.sort(function(a, b) {
                return a[6] - b[6];
            });

            result = JSON.stringify(result);

            return result;

        }


        //*************//
        // Plugin INIT //
        //*************//

        // Init Function
        this.init = function() {

            //********//
            // EVENTS //
            //********//

            // Submit Reservation
            $(settings.RES_FORM).submit(function(e) {
                submitReservation(e, $(this));
            });

            return this;

        };

        // Init Plugin
        return this.init();

    };

    // Window Load event
    $(document).ready(function(){

        // Init LM Cart
        $(window).LMReservation();

    });

}(jQuery));