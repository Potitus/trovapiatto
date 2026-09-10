
/*
 * LM Cart - jQuery Plugin
 */

'use strict';

(function($) {

    $.fn.LMCart = function(options) {

        //*****************//
        // GLOBAL Settings //
        //*****************//

        // Default settings
        var defaults = {

            // Globals
            PREFIX: '_lm_',
            BODY_DATA_MENU_ID: 'data-menu-id',
            BODY_DATA_CURRENCY: 'data-menu-currency',

            // Add to Cart Button
            ADD_CLASS: '.lmcart-add',
            ADD_DATA_ID: 'data-id',
            ADD_DATA_TITLE: 'data-title',
            ADD_DATA_CAT: 'data-cat',
            ADD_DATA_CAT_ORDER: 'data-cat-order',
            ADD_DATA_PRICE: 'data-price',
            ADD_DATA_PRICE_LABEL: 'data-price-label',
            ADD_DATA_CUSTOM: 'data-custom',
            ADD_DATA_ING: 'data-ing',
            ADD_DATA_NOTE: 'data-note',

            // Add to Cart Popup
            POPUP_ID: '#add-cart-popup',
            POPUP_TITLE: '#add-cart-popup .voce-title',
            POPUP_CUSTOM: '#add-cart-popup #custom-product',
            POPUP_ING: '#add-cart-popup #custom-ing',
            POPUP_ING_INPUT: '#add-cart-popup .ing-list-item input',
            POPUP_TOTAL: '#add-cart-popup .voce-total',
            POPUP_NOTE: '#add-cart-popup .note-block',
            POPUP_ADDITIONAL_BTN: '#add-cart-popup .additional-btn',

            // Cart
            CART_ITEMS_LIST: '.lmcart-items',
            CART_ITEMS_COUNT: '.lmcart-totalitems',
            CART_TOTAL: '.lmcart-total',
            CART_ADD_ITEM: '.lmcart-add-item',
            CART_REMOVE_ITEM: '.lmcart-remove-item',
            CART_REMOVE_ALL: '.lmcart-remove-all',
            CART_EMPTY: '#empty-lmcart',

            // Order
            ORDER_BOX: '#riepilogo-ordine',
            ORDER_SHIPPING: '#costo-consegna-ordine',
            ORDER_SHIPPING_VALUE: '#costo-consegna-ordine-valore',
            ORDER_SUBTOTAL: '#subtotale-ordine',
            ORDER_TOTAL: '#totale-ordine',
            ORDER_FORM: '#ordine',
            ORDER_FORM_TABLE: '#ordine.tavolo',
            ORDER_FORM_TAKEAWAY: '#ordine.asporto',
            ORDER_FORM_DELIVERY: '#ordine.delivery',
            ORDER_FORM_INPUT_ORDER: '#input-ordine',
            ORDER_FORM_INPUT_TOTAL: '#input-totale',
            ORDER_FORM_INPUT_PRIVACY: '#privacy-box',
            ORDER_FORM_ERROR_PRIVACY: '#privacy-error',
            ORDER_FORM_INPUT_SUBMIT: '#invia-ordine',
            ORDER_LATEST_ORDERS: '#ultimi-ordini',

        };

        // Extend Settings
        var settings = $.extend({}, defaults, options);

        // Internal Vars
        var menuID = $('body').attr(settings.BODY_DATA_MENU_ID);
        var currency = $('body').attr(settings.BODY_DATA_CURRENCY);
        var cartPrefix = settings.PREFIX + 'comanda_' + menuID + '-';
        var orderPrefix = settings.PREFIX + 'ordine_' + menuID + '-';


        //****************//
        // CART Functions //
        //****************//

        // Get Cart Data
        var getCartData = function(){

            var sessionData = sessionStorage;
            var filteredData = {};

            // Filter data by prefix
            for(const key in sessionData) {
                if( key.indexOf(cartPrefix) !== -1 ) {
                    filteredData[key] = sessionData.getItem(key);
                }
            }

            return filteredData;

        }

        // Add to Cart Action
        var addToCart = function(el){

            // Init Cart
            var cart = getCartData();

            // Setup new item to Add
            var newItem = {
                qty: 1,
                item_id: el.attr(settings.ADD_DATA_ID),
                price: parseFloat(el.attr(settings.ADD_DATA_PRICE)).toFixed(2),
                price_label: el.attr(settings.ADD_DATA_PRICE_LABEL),
                title: el.attr(settings.ADD_DATA_TITLE),
                cat: el.attr(settings.ADD_DATA_CAT),
                cat_order: el.attr(settings.ADD_DATA_CAT_ORDER),
                custom: el.attr(settings.ADD_DATA_CUSTOM),
                custom_ing: el.attr(settings.ADD_DATA_ING),
                note: el.attr(settings.ADD_DATA_NOTE),
                currency: currency
            }

            // Search for duplicate
            $.each(cart, function(key, item){
                item = JSON.parse(item);
                if( newItem.item_id === item.item_id && newItem.price === item.price && JSON.stringify(newItem.custom_ing) === JSON.stringify(item.custom_ing) && newItem.note === item.note ){

                    // Update Qty
                    newItem.qty = item.qty + 1;

                    // Set Key
                    newItem.key = key;

                }
            });

            // Update Session Storage
            if( newItem.key ){
                sessionStorage.setItem(newItem.key, JSON.stringify(newItem));
            }else{
                newItem.key = cartPrefix + randNumber(100, 999);
                sessionStorage.setItem(newItem.key, JSON.stringify(newItem));
            }

        }

        // Add to Cart Animation
        var addToCartAnimation = function(el){

            // Actions on template markup
            $('.toast, .snackbar-toast, .notification').toast('hide');
            $('#cart-added').toast('show');
            $('.collapse').removeClass('show');
            $('i', el).toggleClass('loading fa-spin');

            $('i', el).delay(500).queue(function(){
                $('.cart-icon').toggleClass('scale-icon-1');
                $(this).toggleClass('loading fa-spin checked').dequeue();
            });

            $('i', el).delay(300).queue(function(){
                $('.cart-icon').toggleClass('scale-icon-1');
                $(this).toggleClass('checked').dequeue();
                // Close Menu
                var menu = $('.menu'), menuHider = $('body').find('.menu-hider'), headerAndContent = $('.header, .page-content, #footer-bar');
                menu.removeClass('menu-active');
                menuHider.removeClass('menu-active menu-active-clear');
                headerAndContent.css('transform','translate(0,0)');
                menuHider.css('transform','translate(0,0)');
                $('#footer-bar').removeClass('footer-menu-hidden');
                $('body').removeClass('modal-open');
                $('#page').css({overflow: 'initial', height: ''});
            });

        }

        // Build Cart list
        var buildCart = function(){

            // Init Cart
            var cart = getCartData();
            var cartTotal = 0;
            var cartCount = 0;

            // Empty items list
            $(settings.CART_ITEMS_LIST).html('');

            // Populate items list
            $.each(cart, function(key, item){

                $(settings.CART_ITEMS_LIST).append([JSON.parse(item)].map(function(item){

                    // Update Price
                    item.price = parseFloat(item.price * item.qty).toFixed(2);
                    cartTotal += parseFloat(item.price);

                    // Update qty Count
                    cartCount += parseInt(item.qty);

                    // Custom product
                    var customIng = '';
                    if( item.custom && item.custom_ing ){
                        customIng = JSON.parse(decodeURIComponent(item.custom_ing));
                        customIng = customIng.join(", ");
                        customIng = `<p class="mb-0 mt-0 d-block"><i class="fas fa-plus"></i> ${customIng}</p>`;
                    }

                    // Note
                    var note = '';
                    if( item.note ){
                        note = decodeURIComponent(item.note);
                        note = `<p class="mb-0 mt-0 d-block font-italic"><i class="fas fa-comment-dots"></i> ${note}</p>`;
                    }

                    return `
                    <div class="content mt-0 mb-0 lmcart-item" id="id_${item.item_id}" data-id="${item.item_id}" data-price="${item.price}" data-title="${item.title}">
                        <div class="divider mt-3 mb-2"></div>
                        <div class="d-flex">
                            <div class="mr-3" style="max-width: 70%;">
                                <p class="mb-0" id="cat_${item.item_id}" data-cat-order="${item.cat_order}"><i class="fas fa-folder-open"></i> ${item.cat}</p>   
                                <h4 class="font-600"><span class="lmcart-item-amount">${item.qty}</span> x ${item.title}</h4>                                                           
                                ${customIng}
                                ${note}                     
                                <div class="d-flex mt-1">
                                    <button class="lmcart-remove-item icon icon-xs rounded-xs bg-dark color-white mr-2" data-key="${item.key}">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button class="lmcart-remove-all icon icon-xs rounded-xs bg-danger color-white mr-2" data-key="${item.key}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="lmcart-add-item icon icon-xs rounded-xs bg-dark color-white mr-2" data-key="${item.key}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="ml-auto text-center">
                                <h2 class="notranslate">${item.currency} <span class="all-cart-items-price">${item.price}</span></h2>
                                <small>${item.price_label}</small>
                            </div>
                        </div>
                    </div>`;

                }).join());

            });

            // Fill the items count
            $(settings.CART_ITEMS_COUNT).text(cartCount);

            // Fill the total count
            $(settings.CART_TOTAL).text(parseFloat(cartTotal).toFixed(2));

            //*****************//
            // Specific EVENTS //
            //*****************//

            // Add Item Event
            $(settings.CART_ADD_ITEM).on('click', function(){
                addItem($(this));
                buildCart();
            });

            // Remove Item Event
            $(settings.CART_REMOVE_ITEM).on('click', function(){
                removeItem($(this));
                buildCart();
            });

            // Remove All Event
            $(settings.CART_REMOVE_ALL).on('click', function(){
                removeAll($(this));
                buildCart();
            });

        }

        // +1 item on Cart
        var addItem = function(el){

            // Get Item Key
            var itemKey = el.data('key');

            // Get Session Item
            var sessionItem = sessionStorage.getItem(itemKey);
            sessionItem = JSON.parse(sessionItem);

            // Add 1 item on qty
            sessionItem.qty = parseInt(sessionItem.qty) + 1;

            // Save in Storage
            sessionStorage.setItem(itemKey, JSON.stringify(sessionItem));

        }

        // -1 item on Cart
        var removeItem = function(el){

            // Get Item Key
            var itemKey = el.data('key');

            // Get Session Item
            var sessionItem = sessionStorage.getItem(itemKey);
            sessionItem = JSON.parse(sessionItem);

            // Add 1 item on qty
            sessionItem.qty = parseInt(sessionItem.qty) - 1;

            // Save in Storage
            if(sessionItem.qty > 0) {
                sessionStorage.setItem(itemKey, JSON.stringify(sessionItem));
            }else{
                sessionStorage.removeItem(itemKey);
            }

        }

        // Remove all of single item on Cart
        var removeAll = function(el){

            // Get Item Key
            var itemKey = el.data('key');

            // Remove item from session storage
            sessionStorage.removeItem(itemKey);

        }

        // Empty Cart
        var emptyCart = function(){

            // Init Cart
            var cart = getCartData();

            // Remove all cart items
            $.each(cart, function(key){
                sessionStorage.removeItem(key);
            });

        }


        //*****************//
        // POPUP Functions //
        //*****************//

        // Build Popup
        var buildPopup = function(el){

            // Collect data from Popup button
            var data = {
                id: el.data('id'),
                title: el.data('title'),
                cat: el.data('cat'),
                carOrder: el.data('cat-order'),
                price: el.data('price'),
                price_label: el.data('price-label'),
                custom: el.data('custom'),
                ing: el.data('ing'),
                note: el.data('note')
            };

            // Build Title
            if(data.price_label){
                var priceLabel = ' (' + data.price_label + ')';
            }else{
                var priceLabel = '';
            }
            $(settings.POPUP_TITLE).html('1 x ' + data.title + '<br><small> ' + currency + ' ' + data.price + priceLabel + '</small>');
console.log('popup');
            // Build Ingredients
            if(data.ing && data.ing.length > 0){

                // Show Additional BTN on top
                $(settings.POPUP_ADDITIONAL_BTN).removeClass('d-none');

                // Show Custom Product section
                $(settings.POPUP_CUSTOM).removeClass('d-none');

                // Empty previous Ing
                $(settings.POPUP_ING).html('');

                // Loop new Ing
                $.each(data.ing, function(key, ing){

                    $(settings.POPUP_ING).append([ing].map(function(ing){

                        return `
                        <div id="ing-${ing.item}" class="ing-list-item">
                            <div class="divider mb-0"></div>
                            <div class="fac fac-checkbox fac-default">
                                <span></span>
                                <input id="ing-field-${ing.item}" name="ingredienti" type="checkbox" data-title="${ing.title}" value="${parseFloat(ing.price).toFixed(2)}">
                                <label for="ing-field-${ing.item}">${ing.title} (${parseFloat(ing.price).toFixed(2)} ${currency})</label>
                            </div>
                        </div>`;

                    }).join());

                });

            }else{

                // Hide Additional BTN on top
                $(settings.POPUP_ADDITIONAL_BTN).addClass('d-none');

                // Hide Custom Product section
                $(settings.POPUP_CUSTOM).addClass('d-none');

            }

            // Enable Note feature
            if(data.note > 0 ){
                $(settings.POPUP_NOTE).removeClass('d-none');
            }else{
                $(settings.POPUP_NOTE).addClass('d-none');
            }
            $(settings.POPUP_NOTE).attr('data-voce-id', data.id);
            $(settings.POPUP_NOTE + ' textarea').val('');

            // Transfer all data to Confirm button
            $(settings.ADD_CLASS).attr({
                'id': 'add_' + data.id,
                'data-id':  data.id,
                'data-title': data.title,
                'data-cat': data.cat,
                'data-cat-order': data.carOrder,
                'data-price': data.price,
                'data-price-label': data.price_label,
                'data-custom': data.custom,
                'data-ing':  '',
                'data-note':  '',
            });

            // Set Base price in Total
            var basePrice = parseFloat(data.price);
            $(settings.POPUP_TOTAL).attr( 'data-base-price', basePrice).data('base-price', basePrice ).text(parseFloat(basePrice).toFixed(2));

            // Calculate Ingredients price and set it in Total
            $(settings.POPUP_ING_INPUT).on('change', function(){

                var baseTotal = parseFloat($(settings.POPUP_TOTAL).data('base-price'));

                var ingTotal = 0;
                var ingList = [];

                // Loop all checkboxes to calculate ingTotal
                $(settings.POPUP_ING_INPUT).each(function(){
                    if(this.checked){
                        ingTotal += parseFloat($(this).val());
                        ingList.push($(this).data('title'));
                    }
                });

                $(settings.POPUP_TOTAL).text( parseFloat(baseTotal+ingTotal).toFixed(2) );
                $(settings.ADD_CLASS).attr('data-price', parseFloat(baseTotal+ingTotal).toFixed(2));

                if( ingList.length > 0 ){
                    $(settings.ADD_CLASS).attr('data-custom', 1);
                    $(settings.ADD_CLASS).attr('data-ing', encodeURIComponent(JSON.stringify(ingList)));
                }else{
                    $(settings.ADD_CLASS).attr('data-custom', 0);
                    $(settings.ADD_CLASS).attr('data-ing', '{}');
                }

            });

            // Bind note message to confirm button
            $(settings.POPUP_NOTE + ' textarea').bind('input propertychange', function() {

                var target = $(settings.ADD_CLASS);
                if(target.length > 0 ){
                    target.attr('data-note', this.value);
                }

            });

        }


        //*****************//
        // ORDER Functions //
        //*****************//

        // Get Orders Data
        var getOrdersData = function(){

            var localData = localStorage;
            var filteredData = {};

            // Filter data by prefix
            Object.keys(localStorage).sort().reverse().forEach(function(key){
                if( key.indexOf(orderPrefix) !== -1 ) {
                    filteredData[key] = localData.getItem(key);
                }
            });

            return filteredData;

        }

        // Build Order
        var buildOrder = function(){

            // Set Data
            var orderData = getCartData();


            var cartTotal = 0;
            var orderShipping = parseFloat($(settings.ORDER_SHIPPING_VALUE).data('value')).toFixed(2);
            if( !orderShipping || isNaN(orderShipping) ){ orderShipping = 0.00; }
            var orderMin = parseFloat($(settings.ORDER_BOX).data('ordine-minimo')).toFixed(2);


            // Setup order page
            if( Object.keys(orderData).length > 0 ){

                // Empty order box
                $(settings.ORDER_BOX).html('');

                // Populate order box
                $.each(orderData, function(key, item){

                    $(settings.ORDER_BOX).append([JSON.parse(item)].map(function(item){

                        // Update Price
                        item.price = parseFloat(item.price * item.qty).toFixed(2);
                        cartTotal += parseFloat(item.price);

                        // Custom product
                        var customIng = '';
                        if( item.custom && item.custom_ing ){
                            customIng = JSON.parse(decodeURIComponent(item.custom_ing));
                            customIng = customIng.join(", ");
                            customIng = `<p class="mb-0 mt-0 d-block"><i class="fas fa-plus"></i> ${customIng}</p>`;
                        }

                        // Note
                        var note = '';
                        if( item.note ){
                            note = decodeURIComponent(item.note);
                            note = `<p class="mb-0 mt-0 d-block font-italic"><i class="fas fa-comment-dots"></i> ${note}</p>`;
                        }

                        return `
                        <div class="content mt-0 mb-0 lmcart-item" id="id_${item.item_id}" data-id="${item.item_id}" data-price="${item.price}" data-title="${item.title}">
                            <div class="divider mt-3 mb-2"></div>
                            <div class="d-flex">
                                <div class="mr-3" style="max-width: 70%;">
                                    <p class="mb-0" id="cat_${item.item_id}" data-cat-order="${item.cat_order}"><i class="fas fa-folder-open"></i> ${item.cat}</p>   
                                    <h4 class="font-600"><span class="lmcart-item-amount">${item.qty}</span> x ${item.title}</h4>                                                           
                                    ${customIng}
                                    ${note}   
                                </div>
                                <div class="ml-auto text-center">
                                    <h2 class="notranslate">${item.currency} <span class="all-cart-items-price">${item.price}</span></h2>
                                    <small>${item.price_label}</small>
                                </div>
                            </div>
                        </div>`;

                    }).join());

                });

                // Setup Subtotal
                $(settings.ORDER_SUBTOTAL).html('' +
                '<div class="content m-0">' +
                '   <div class="d-flex">' +
                '       <div class="mr-3">' +
                '           <h2 class="font-600">' + $(settings.ORDER_SUBTOTAL).data('string') + '</h2>' +
                '       </div>' +
                '       <div class="ml-auto text-center">' +
                '           <h2 class="notranslate">' + currency + ' ' + parseFloat(cartTotal).toFixed(2) + '</h2>' +
                '       </div>' +
                '   </div>' +
                '</div>');
                $(settings.ORDER_SUBTOTAL).removeClass('d-none');

                // Setup Total
                var orderTotal = (parseFloat(cartTotal) + parseFloat(orderShipping)).toFixed(2);
                $(settings.ORDER_TOTAL).html('' +
                '<div class="content m-0">' +
                '   <div class="d-flex">' +
                '       <div class="mr-3">' +
                '           <h1 class="font-600">' + $(settings.ORDER_TOTAL).data('string') + '</h1>' +
                '       </div>' +
                '       <div class="ml-auto text-center">' +
                '           <h1 class="notranslate">' + currency + ' ' + orderTotal + '</h1>' +
                '       </div>' +
                '   </div>' +
                '</div>');
                $(settings.ORDER_TOTAL).removeClass('d-none');

                // Setup Form
                $(settings.ORDER_FORM).removeClass('d-none');

            }else{

                // Reset order box
                $(settings.ORDER_BOX).html( $(settings.ORDER_BOX).data('string') );

                // Hide items
                $(settings.ORDER_SUBTOTAL).addClass('d-none');
                $(settings.ORDER_SHIPPING).addClass('d-none');
                $(settings.ORDER_TOTAL).addClass('d-none');
                $(settings.ORDER_FORM).addClass('d-none');

            }

            // Disable form for minimal order
            if( parseFloat(orderTotal) < parseFloat(orderMin) ){

                $(settings.ORDER_FORM_DELIVERY).html('' +
                    '<div class="alert rounded-s bg-red2-dark " role="alert">\n' +
                    '  <span class="alert-icon"><i class="fa fa-times-circle font-18"></i></span>\n' +
                    '  <h4 class="text-uppercase color-white">' + $(settings.ORDER_FORM_DELIVERY).data('string-1') + '</h4>\n' +
                    '  <strong class="alert-icon-text">' + $(settings.ORDER_FORM_DELIVERY).data('string-2') + ' ' + currency + orderMin + '.</strong>\n' +
                    '</div>');

            }

            // Setup Form inputs
            orderData = objectToInputData(orderData);
            $(settings.ORDER_FORM_INPUT_ORDER).val(orderData);
            $(settings.ORDER_FORM_INPUT_TOTAL).val(orderTotal);

            // Setup Latest Orders
            latestOrders();

        }

        // Latest Orders
        var latestOrders = function(){

            // Get latest orders
            var orders = getOrdersData();

            // Setup orders list
            if( Object.keys(orders).length > 0 ){

                // Init Orders Table
                var ordersHTML =  '' +
                '<table class="table table-borderless text-center rounded-sm shadow-l" style="overflow: hidden;">\n' +
                '<thead>\n' +
                '<tr class="bg-gray1-dark">\n' +
                '<th scope="col" class="color-theme">' + $(settings.ORDER_LATEST_ORDERS).data('string-2') + '</th>\n' +
                '<th scope="col" class="color-theme">' + $(settings.ORDER_LATEST_ORDERS).data('string-3') + '</th>\n' +
                '<th scope="col" class="color-theme">' + $(settings.ORDER_LATEST_ORDERS).data('string-4') + '</th>\n' +
                '</tr>\n' +
                '</thead>' +
                '<tbody>';

                // Populate orders list
                $.each(orders, function(key, item){

                    item = JSON.parse(item);

                    ordersHTML += '' +
                    '<tr>\n' +
                    '<th scope="row">' + item.date + '</th>\n' +
                    '<td class="color-green1-dark">' + item.type + '</td>\n' +
                    '<td>' + item.status + '</td>\n' +
                    '</tr>';

                });

                // End Table of Orders
                ordersHTML += '' +
                '</tbody>' +
                '</table>';

                // Insert HTML to latest orders box
                $(settings.ORDER_LATEST_ORDERS).html( ordersHTML );

            }else{

                // Reset latest orders list
                $(settings.ORDER_LATEST_ORDERS).html( $(settings.ORDER_LATEST_ORDERS).data('string-1') );

            }


        }

        // Submit Order
        var submitOrder = function(e, form){

            // Stop form from submitting normally
            e.preventDefault();
            var submit = true;

            // Check privacy acceptance
            if( $(settings.ORDER_FORM_INPUT_PRIVACY).length > 0 ) {

                if( $(settings.ORDER_FORM_INPUT_PRIVACY+':checked').length > 0 ){
                    submit = true;
                }else{
                    $(settings.ORDER_FORM_ERROR_PRIVACY).html( $(settings.ORDER_FORM_INPUT_PRIVACY).data('error-message') );
                    submit = false;
                }

            }

            // Send Action if submit == true
            if( submit === true ) {

                // Show Spin icon on submit button
                $(settings.ORDER_FORM_INPUT_SUBMIT+' .spinner-border').removeClass('d-none');

                // Send the data using post
                var posting = $.post('/app/admin/api.php?action=submit-order', form.serialize());

                // Put the results in a div
                posting.done(function(data) {

                    setTimeout(function () {

                        $(settings.ORDER_FORM_INPUT_SUBMIT+' .spinner-border').addClass('d-none');
                        $(settings.ORDER_FORM_INPUT_SUBMIT+' .icon-success').removeClass('d-none');

                        setTimeout(function () {

                            // Save Order on local storage
                            var newOrderKey = orderPrefix + parseInt(new Date().getTime()/1000);
                            var currentOrder = {
                                date: currentDate(),
                                type: data['tipo'],
                                status: 'Inviato'
                            };
                            localStorage.setItem(newOrderKey, JSON.stringify(currentOrder));

                            // Empty actual Cart
                            emptyCart();

                            // Reload Page
                            location.reload();

                        }, 500);

                    }, 1000);

                });

            }

        }


        //******************//
        // HELPER Functions //
        //******************//

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

            // Build Cart list
            buildCart();

            //********//
            // EVENTS //
            //********//

            // Open Popup
            $(settings.POPUP_ID).on('modalOpen', function(popup, button){
                buildPopup(button);
            });

            // Add to Cart
            $(settings.ADD_CLASS).on('click', function(){
                addToCartAnimation($(this));
                addToCart($(this));
                buildCart();
            });

            // Empty Cart
            $(settings.CART_EMPTY).on('click', function(){
                emptyCart();
                buildCart();
            });

            // Build Order
            if( $(settings.ORDER_BOX).length ) {
                buildOrder();
            }

            // Submit Order
            $(settings.ORDER_FORM).submit(function(e) {
                submitOrder(e, $(this));
            });

            return this;

        };

        // Init Plugin
        return this.init();

    };

    // Window Load event
    $(document).ready(function(){

        // Init LM Cart
        $(window).LMCart();

    });

}(jQuery));