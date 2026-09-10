
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
            BODY_DATA_BACKEND: 'data-backend-url',

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
            POPUP_ADD_CLASS: '#add-cart-popup .lmcart-add',
            POPUP_ADDITIONAL_BTN: '#add-cart-popup .additional-btn',

            // Cart
            CART_ITEMS_LIST: '.lmcart-items',
            CART_ITEMS_COUNT: '.lmcart-totalitems',
            CART_TOTAL: '.lmcart-total',
            CART_ADD_ITEM: '.lmcart-add-item',
            CART_REMOVE_ITEM: '.lmcart-remove-item',
            CART_REMOVE_ALL: '.lmcart-remove-all',
            CART_EMPTY: '#empty-lmcart',
            CART_MENU_POPUP: '#menu-cart',
            CART_WHATSAPP_BUTTON: '#ordine-whatsapp',

            // Order
            ORDER_BOX: '#riepilogo-ordine',
            ORDER_SHIPPING: '#costo-consegna-ordine',
            ORDER_SHIPPING_VALUE: '#costo-consegna-ordine-valore',
            ORDER_SUBTOTAL: '#subtotale-ordine',
            ORDER_SERVIZI_TAVOLO: '.ordine-tavolo #servizi-aggiuntivi',
            ORDER_SERVIZI_FRASE_TAVOLO: '.ordine-tavolo .servizi-aggiuntivi-frase',
            ORDER_SERVIZI_ASPORTO: '.ordine-asporto #servizi-aggiuntivi',
            ORDER_SERVIZI_DELIVERY: '.ordine-delivery #servizi-aggiuntivi',
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

            // Delivery CAP whitelist (Premium Plus)
            ORDER_CAP_WRAPPER: '#cap-select-wrapper',
            ORDER_CAP_SELECT: '#cap-select-wrapper select[name="cap"]',
            ORDER_CAP_NOTA_INFO: '#cap-nota-info',
            ORDER_CAP_NOTA_TEXT: '#cap-nota-info .cap-nota-text',
            ORDER_CAP_ERROR: '#cap-whitelist-errore',

        };

        // Extend Settings
        var settings = $.extend({}, defaults, options);

        // Internal Vars
        var menuID = $('body').attr(settings.BODY_DATA_MENU_ID);
        var currency = $('body').attr(settings.BODY_DATA_CURRENCY);
        var cartPrefix = settings.PREFIX + 'comanda_' + menuID + '-';
        var orderPrefix = settings.PREFIX + 'ordine_' + menuID + '-';
        const backendURL = decodeURIComponent( $('body').attr(settings.BODY_DATA_BACKEND) );

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

            // Order keys by timestamp
            var orderedData = {};
            Object.keys(filteredData)
                .sort() // ordina A-Z (timestamp ascendente)
                .forEach(function(key){
                    orderedData[key] = filteredData[key];
                });

            return orderedData;

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

            // Setup Stats Item
            var statsItem = {
                user_id: menuID,
                item_id: newItem.item_id,
                item_price: newItem.price,
                item_price_label: newItem.price_label,
                client_id: localStorage.getItem('_lm_client_id'),
                event: 'add'
            }

            // Send stats to DB
            saveStats(statsItem);

            // Search for duplicate
            $.each(cart, function(key, item){
                item = JSON.parse(item);
                if( newItem.item_id === item.item_id && newItem.price === item.price && newItem.price_label === item.price_label && JSON.stringify(newItem.custom_ing) === JSON.stringify(item.custom_ing) && newItem.note === item.note ){

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

                newItem.key = cartPrefix + Date.now() + '_' + Math.floor(Math.random()*1000);
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
                headerAndContent.css('transform','');
                menuHider.css('transform','');
                $('#footer-bar').removeClass('footer-menu-hidden');
                setTimeout(function(){ $('#floating-cart-bar').removeClass('footer-menu-hidden'); }, 150);
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
                                <div class="d-flex mt-2">
                                    <button aria-label="lmcart-remove-item" class="lmcart-remove-item icon icon-xs rounded-xs bg-dark color-white mr-3" data-key="${item.key}">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button aria-label="lmcart-remove-all" class="lmcart-remove-all icon icon-xs rounded-xs bg-danger color-white mr-3" data-key="${item.key}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button aria-label="lmcart-add-item" class="lmcart-add-item icon icon-xs rounded-xs bg-dark color-white mr-3" data-key="${item.key}">
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

            // Floating Cart Bar visibility
            var $fcb = $('#floating-cart-bar');
            if( $fcb.length ){
                if( cartCount > 0 ){
                    $fcb.addClass('fcb-show');
                    $('body').addClass('fcb-active');
                } else {
                    $fcb.removeClass('fcb-show');
                    $('body').removeClass('fcb-active');
                }
            }

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

            // Setup Stats Item
            var statsItem = {
                user_id: menuID,
                item_id: sessionItem.item_id,
                item_price: sessionItem.price,
                item_price_label: sessionItem.price_label,
                client_id: localStorage.getItem('_lm_client_id'),
                event: 'add'
            }

            // Send stats to DB
            saveStats(statsItem);


        }

        // -1 item on Cart
        var removeItem = function(el){

            // Get Item Key
            var itemKey = el.data('key');

            // Get Session Item
            var sessionItem = sessionStorage.getItem(itemKey);
            sessionItem = JSON.parse(sessionItem);

            // Remove 1 item on qty
            sessionItem.qty = parseInt(sessionItem.qty) - 1;

            // Save in Storage
            if(sessionItem.qty > 0) {
                sessionStorage.setItem(itemKey, JSON.stringify(sessionItem));
            }else{
                sessionStorage.removeItem(itemKey);
            }

            // Setup Stats Item
            var statsItem = {
                user_id: menuID,
                item_id: sessionItem.item_id,
                item_price: sessionItem.price,
                item_price_label: sessionItem.price_label,
                client_id: localStorage.getItem('_lm_client_id'),
                event: 'remove'
            }

            // Send stats to DB
            saveStats(statsItem);

        }

        // Remove all of single item on Cart
        var removeAll = function(el){

            // Get Item Key
            var itemKey = el.data('key');

            // Get Session Item
            var sessionItem = sessionStorage.getItem(itemKey);
            sessionItem = JSON.parse(sessionItem);

            // Setup Stats Item
            var statsItem = {
                user_id: menuID,
                item_id: sessionItem.item_id,
                item_price: sessionItem.price,
                item_price_label: sessionItem.price_label,
                client_id: localStorage.getItem('_lm_client_id'),
                event: 'remove_all'
            }

            // Send stats to DB
            saveStats(statsItem);


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
            $(settings.POPUP_ADD_CLASS).attr({
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
                $(settings.POPUP_ADD_CLASS).attr('data-price', parseFloat(baseTotal+ingTotal).toFixed(2));

                if( ingList.length > 0 ){
                    $(settings.POPUP_ADD_CLASS).attr('data-custom', 1);
                    $(settings.POPUP_ADD_CLASS).attr('data-ing', encodeURIComponent(JSON.stringify(ingList)));
                }else{
                    $(settings.POPUP_ADD_CLASS).attr('data-custom', 0);
                    $(settings.POPUP_ADD_CLASS).attr('data-ing', '{}');
                }

            });

            // Bind note message to confirm button
            $(settings.POPUP_NOTE + ' textarea').bind('input propertychange', function() {

                var target = $(settings.POPUP_ADD_CLASS);
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
                        <div class="lmcart-item" id="id_${item.item_id}" data-id="${item.item_id}" data-price="${item.price}" data-title="${item.title}">
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

                // Add Servizi Aggiuntivi
                $(settings.ORDER_SERVIZI_ASPORTO + ' .servizio-aggiuntivo, ' + settings.ORDER_SERVIZI_DELIVERY + ' .servizio-aggiuntivo').each(function(){

                    var costoTipo = $(this).data('costo-tipo');
                    var costoSegno = $(this).data('costo-segno');
                    var costo = $(this).data('costo');
                    var addebito = $(this).data('addebito');

                    // Only po (per ordine) can change the total price
                    if( addebito == 'po' ){

                        if( costoTipo == 'perc' ) {

                            costo = (parseFloat(cartTotal) / 100 * costo).toFixed(2);

                        }

                        if( costoSegno == '+' ){

                            orderTotal = (parseFloat(orderTotal) + parseFloat(costo)).toFixed(2);

                        }else{

                            orderTotal = (parseFloat(orderTotal) - parseFloat(costo)).toFixed(2);

                        }

                    }

                });

                $(settings.ORDER_TOTAL).html('' +
                    '<div class="content m-0">' +
                    '   <div class="d-flex">' +
                    '       <div class="mr-3">' +
                    '           <h2 class="font-600">' + $(settings.ORDER_TOTAL).data('string') + '</h2>' +
                    '       </div>' +
                    '       <div class="ml-auto text-center">' +
                    '           <h2 class="notranslate">' + currency + ' ' + orderTotal + '</h2>' +
                    '       </div>' +
                    '   </div>' +
                    '</div>');
                $(settings.ORDER_TOTAL).removeClass('d-none');

                // Show Servizi Aggiuntivi
                $(settings.ORDER_SERVIZI_TAVOLO).removeClass('d-none');
                $(settings.ORDER_SERVIZI_FRASE_TAVOLO).removeClass('d-none');
                $(settings.ORDER_SERVIZI_ASPORTO).removeClass('d-none');
                $(settings.ORDER_SERVIZI_DELIVERY).removeClass('d-none');

                // Setup Form
                $(settings.ORDER_FORM).removeClass('d-none');

            }else{

                // Reset order box
                $(settings.ORDER_BOX).html( $(settings.ORDER_BOX).data('string') );

                // Hide items
                $(settings.ORDER_SUBTOTAL).addClass('d-none');
                $(settings.ORDER_SHIPPING).addClass('d-none');
                $(settings.ORDER_TOTAL).addClass('d-none');
                $(settings.ORDER_SERVIZI_TAVOLO).addClass('d-none');
                $(settings.ORDER_SERVIZI_FRASE_TAVOLO).addClass('d-none');
                $(settings.ORDER_SERVIZI_ASPORTO).addClass('d-none');
                $(settings.ORDER_SERVIZI_DELIVERY).addClass('d-none');
                $(settings.ORDER_FORM).addClass('d-none');

            }

            // Disable form for minimal order (check on cart subtotal, not on total with shipping)
            // Additive alert: keep form intact so user can still change CAP / fields
            var $minAlert = $('#ordine-minimo-alert');
            var $minSubmit = $(settings.ORDER_FORM_INPUT_SUBMIT);
            if( $minAlert.length && parseFloat(cartTotal) < parseFloat(orderMin) ){

                $minAlert.html('' +
                    '<div class="alert rounded-s bg-red2-dark" role="alert">\n' +
                    '  <span class="alert-icon"><i class="fa fa-times-circle font-18"></i></span>\n' +
                    '  <h4 class="text-uppercase color-white">' + $(settings.ORDER_FORM_DELIVERY).data('string-1') + '</h4>\n' +
                    '  <strong class="alert-icon-text">' + $(settings.ORDER_FORM_DELIVERY).data('string-2') + ' ' + currency + orderMin + '.</strong>\n' +
                    '</div>').removeClass('d-none');
                $minSubmit.hide();

            }else if( $minAlert.length ){

                $minAlert.html('').addClass('d-none');
                $minSubmit.show();

            }

            // Setup Form inputs
            orderData = objectToInputData(orderData);
            $(settings.ORDER_FORM_INPUT_ORDER).val(orderData);

            // Filter "Online" payment option based on minimum order threshold
            // Coerente con il check "ordine minimo classico": confronto su cartTotal (solo prodotti),
            // escludendo costo consegna e servizi aggiuntivi (po).
            applyPagamentoOnlineSoglia(cartTotal);

            $(settings.ORDER_FORM_INPUT_TOTAL).val(orderTotal);
            $('#input-costo-consegna').val(parseFloat(orderShipping).toFixed(2));

            // Setup Latest Orders
            latestOrders();

        }

        // Filter "Online" payment option based on minimum order threshold
        var applyPagamentoOnlineSoglia = function(orderTotal){

            var $select = $(settings.ORDER_FORM + ' select[name="pagamento"]');
            var $option = $select.find('option[value="Online"]');
            var $hint = $('#pagamento-online-soglia-hint');

            if( !$option.length ){ return; }

            var soglia = parseFloat( $option.attr('data-min-totale') );
            if( isNaN(soglia) || soglia <= 0 ){
                $option.prop('disabled', false).prop('hidden', false).removeClass('d-none');
                if( $hint.length ){ $hint.addClass('d-none'); }
                return;
            }

            var totale = parseFloat(orderTotal) || 0;

            if( totale < soglia ){

                var wasOnlineSelected = ( $select.val() === 'Online' );

                $option.prop('disabled', true).prop('hidden', true).addClass('d-none');

                if( wasOnlineSelected ){
                    var $fallback = $select.find('option:not(:disabled)').not('[value=""]').first();
                    if( $fallback.length ){
                        $select.val( $fallback.val() ).trigger('change');
                        localStorage.setItem('_lm_globals_pagamento', $fallback.val());
                    }else{
                        $select.val('').trigger('change');
                        localStorage.removeItem('_lm_globals_pagamento');
                    }
                }

                if( $hint.length ){
                    var template = $hint.attr('data-template') || '';
                    $hint.html(
                        template
                            .replace('{soglia}', soglia.toFixed(2))
                            .replace('{valuta}', currency)
                    ).removeClass('d-none');
                }

            }else{

                $option.prop('disabled', false).prop('hidden', false).removeClass('d-none');
                if( $hint.length ){ $hint.addClass('d-none'); }

            }

        }

        // Build WhatsApp Order
        var buildWhappOrder = function(){

            // Set Data
            var whatsappNumber = $(settings.CART_WHATSAPP_BUTTON).data('whatsapp');
            var orderData = getCartData();
            var cartTotal = 0;
            var orderURL = 'https://wa.me/' + whatsappNumber + '?text=';
            var prodList = [];
            var message = "Salve,\nscrivo per effettuare un ordine.\n\nEcco la lista dei piatti:\n\n";

            // Populate order
            $.each(orderData, function(key, item) {

                var item = JSON.parse(item);

                // Update Price
                item.price = parseFloat(item.price * item.qty).toFixed(2);
                cartTotal += parseFloat(item.price);

                // Custom product
                var customIng = '';
                if (item.custom && item.custom_ing) {
                    customIng = JSON.parse(decodeURIComponent(item.custom_ing));
                    customIng = customIng.join(", ");

                    if (customIng) {
                        item.custom_ing = "\n+ " + customIng
                    } else {
                        item.custom_ing = '';
                    }

                }

                // Note
                if (item.note) {
                    item.note = "\n" + "Nota: " + decodeURIComponent(item.note);
                } else {
                    item.note = '';
                }
                prodList.push(item.cat + "\n" + item.qty + " x " + item.title + " - " + currency + item.price + item.custom_ing + item.note + "\n" + "\u{2500}\u{2500}\u{2500}\u{2500}\u{2500}" + "\n");


            });

            // Compile Message
            prodList.forEach(function(product) {
                message += product;
            });

            // Add Cart Total to message
            message += "\nTotale: " + currency + cartTotal.toFixed(2);

            // Add Message to url
            orderURL += encodeURIComponent(message);

            return orderURL;

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

        // Proceed Send Order (extracted for reuse)
        var proceedSendOrder = function(form){

            // Save personal data in Local Storage
            var formData = form.serializeArray();

            localStorage.setItem( '_lm_globals_marketing_lm', '0' );
            localStorage.setItem( '_lm_globals_marketing_gest', '0' );
            $.each(formData, function(index, field) {

                if( field.name == 'nominativo' ){
                    localStorage.setItem('_lm_globals_nominativo', field.value);
                }

                if( field.name == 'pref' ){
                    localStorage.setItem('_lm_globals_prefisso', field.value);
                }

                if( field.name == 'telefono' ){
                    localStorage.setItem('_lm_globals_telefono', field.value);
                }

                if( field.name == 'email' ){
                    localStorage.setItem('_lm_globals_email', field.value);
                }

                if( field.name == 'cf' ){
                    localStorage.setItem('_lm_globals_cf', field.value);
                }

                if( field.name == 'indirizzo' ){
                    localStorage.setItem('_lm_globals_indirizzo', field.value);
                }

                if( field.name == 'cap' ){
                    localStorage.setItem('_lm_globals_cap', field.value);
                }

                if( field.name == 'citta' ){
                    localStorage.setItem('_lm_globals_citta', field.value);
                }

                if( field.name == 'pagamento' ){
                    localStorage.setItem('_lm_globals_pagamento', field.value);
                }

                if( field.name == 'marketing_lm' ){
                    localStorage.setItem('_lm_globals_marketing_lm', field.value);
                }

                if( field.name == 'marketing_gest' ){
                    localStorage.setItem('_lm_globals_marketing_gest', field.value);
                }

            });

            // Show Spin icon on submit button
            $(settings.ORDER_FORM_INPUT_SUBMIT+' .spinner-border').removeClass('d-none');

            if( $(settings.ORDER_FORM).data('debug-post') ){

                let urlParams = new URLSearchParams(form.serialize());
                let unserializedData = {};
                for (let [key, value] of urlParams) {
                    unserializedData[key] = value;
                }

                console.log( '*******************' );
                console.log( '* DEBUG FORM POST *' );
                console.log( '*******************' );
                console.log( unserializedData );

                // Enable submit button
                $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);

            }else{

                // Send the data using post
                var posting = $.post(backendURL+'api/order.php', form.serialize());

                // Put the results in a div
                posting.done(function(data) {

                    if( $(settings.ORDER_FORM).data('debug-result') ){

                        console.log( '*********************' );
                        console.log( '* DEBUG FORM RESULT *' );
                        console.log( '*********************' );
                        console.log( data );

                        return;

                    }

                    // Backend logical error: show modal "Torna all'ordine", preserve cart
                    if( data && typeof data === 'object' && data.status === 'errore' ){

                        $(settings.ORDER_FORM_INPUT_SUBMIT + ' .spinner-border').addClass('d-none');
                        $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);

                        var $errModal = $('#menu-errore-ordine');
                        if( $errModal.length ){
                            $('#menu-errore-ordine-message').text( data.message || '' );
                            $('#menu-errore-ordine-trigger').trigger('click');
                        }else{
                            alert( data.message || 'Errore' );
                        }
                        return;

                    }

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

                            // Check link pagamento
                            if( data['link_pagamento'] && data['link_pagamento'] !== '' ) {

                                // Redirect to link pagamento
                                window.location.href = data['link_pagamento'];

                            }else{

                                // Reload Page
                                var currentUrl = window.location.href;
                                if(currentUrl.includes('?')) {
                                    currentUrl += '&status=sent';
                                }else{
                                    currentUrl += '?status=sent';
                                }
                                window.location.href = currentUrl;

                            }

                        }, 500);

                    }, 1000);

                }).fail(function(jqXHR, textStatus, errorThrown) {

                    // Show erro in conmsole
                    console.error('Error in POST request:', textStatus, errorThrown);
                    console.log('Response text:', jqXHR.responseText);

                    // Disable loader
                    $(settings.ORDER_FORM_INPUT_SUBMIT + ' .spinner-border').addClass('d-none');

                    // Enable submit button
                    $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);

                });

            }

        }

        // Filter times for order (called on date change)
        var filterTimesOrder = function(cfg){

            var daySelected = $(cfg.dateInput).val();
            var $timeInput = $(cfg.timeInput);
            var timeInputFirstOption = $('option:first', $timeInput);

            $(cfg.availabilityError).text('').addClass('d-none');

            $.ajax({
                url: backendURL+'api/order-times/',
                method: 'GET',
                dataType: 'json',
                data: {
                    user_id: menuID,
                    selected_date: daySelected,
                    tipo: cfg.tipo
                }
            }).done(function(response) {

                if( response.status == 'errore' ){

                    $('option', $timeInput).not(timeInputFirstOption).remove();
                    $(cfg.timesError).removeClass('d-none');

                }else{

                    if( Array.isArray(response) && response.length > 0) {

                        $(cfg.timesError).addClass('d-none');
                        $('option', $timeInput).not(timeInputFirstOption).remove();

                        var motivi = $(cfg.timesError).data('motivi-sospensione') || {};

                        response.forEach(function(time) {
                            var orario = time[0];
                            var label = time[1];
                            var status = time[2];
                            var notaSospensione = time[3] || '';

                            var optionText = label;
                            var isDisabled = false;

                            if( status && status !== 'available' ){
                                isDisabled = true;
                                var statusLabel = motivi[status] || status;
                                if( notaSospensione ){
                                    optionText = label + ' (' + statusLabel + ': ' + notaSospensione + ')';
                                }else{
                                    optionText = label + ' (' + statusLabel + ')';
                                }
                            }

                            if( optionText.length > 45 ){
                                optionText = optionText.substring(0, 45) + '...';
                            }

                            var $option = $('<option></option>')
                                .val(orario)
                                .text(optionText);

                            if( isDisabled ){
                                $option.prop('disabled', true);
                            }

                            $timeInput.append($option);
                        });

                    }else{

                        $('option', $timeInput).not(timeInputFirstOption).remove();
                        $(cfg.timesError).removeClass('d-none');

                    }

                }

            }).fail(function() {
                $('option', $timeInput).not(timeInputFirstOption).remove();
                $(cfg.timesError).removeClass('d-none');
            });

        }

        // Update delivery pricing/minimo when CAP changes (Premium Plus - delivery_caps whitelist)
        var updateDeliveryPricingByCap = function(){

            var $wrapper = $(settings.ORDER_CAP_WRAPPER);
            if( !$wrapper.length ){ return; }

            var $select = $(settings.ORDER_CAP_SELECT);
            var $option = $select.find('option:selected');

            var globalShipping = parseFloat( $wrapper.attr('data-delivery-prezzo') );
            var globalMin = parseFloat( $wrapper.attr('data-delivery-min') );
            if( isNaN(globalShipping) ){ globalShipping = 0; }
            if( isNaN(globalMin) ){ globalMin = 0; }

            var optionValue = $option.val();
            var optionCosto = ( optionValue && $option.attr('data-costo') !== '' ) ? parseFloat( $option.attr('data-costo') ) : NaN;
            var optionMin = ( optionValue && $option.attr('data-ordine-minimo') !== '' ) ? parseFloat( $option.attr('data-ordine-minimo') ) : NaN;
            var optionNota = optionValue ? ( $option.attr('data-nota') || '' ) : '';

            var effectiveShipping = !isNaN(optionCosto) ? optionCosto : globalShipping;
            var effectiveMin = !isNaN(optionMin) ? optionMin : globalMin;

            effectiveShipping = parseFloat( effectiveShipping ).toFixed(2);
            effectiveMin = parseFloat( effectiveMin ).toFixed(2);

            // Update shipping cost (DOM + jQuery data cache)
            var $shippingValue = $(settings.ORDER_SHIPPING_VALUE);
            if( $shippingValue.length ){
                $shippingValue.attr('data-value', effectiveShipping);
                $shippingValue.data('value', effectiveShipping);
                $shippingValue.text( currency + effectiveShipping );
            }

            // Show/hide shipping box based on effective cost
            var $shippingBox = $(settings.ORDER_SHIPPING);
            if( $shippingBox.length ){
                if( parseFloat(effectiveShipping) > 0 ){
                    $shippingBox.removeClass('d-none');
                }else{
                    $shippingBox.addClass('d-none');
                }
            }

            // Update minimum order (DOM + jQuery data cache)
            var $orderBox = $(settings.ORDER_BOX);
            if( $orderBox.length ){
                $orderBox.attr('data-ordine-minimo', effectiveMin);
                $orderBox.data('ordine-minimo', effectiveMin);
            }

            // Dynamic nota CAP echo
            if( optionNota ){
                $(settings.ORDER_CAP_NOTA_TEXT).text( optionNota );
                $(settings.ORDER_CAP_NOTA_INFO).removeClass('d-none');
            }else{
                $(settings.ORDER_CAP_NOTA_INFO).addClass('d-none');
                $(settings.ORDER_CAP_NOTA_TEXT).text('');
            }

            // Clear whitelist error on valid change
            if( optionValue ){
                $(settings.ORDER_CAP_ERROR).addClass('d-none');
            }

            // Rebuild order to recalculate total + min check
            if( $orderBox.length ){
                buildOrder();
            }

        }

        // Submit Order
        var submitOrder = function(e, form){

            // Stop form from submitting normally
            e.preventDefault();
            var submit = true;

            // Temporary disable submit button
            $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', true);

            // CAP whitelist safety net (Premium Plus - delivery_caps)
            if( $(settings.ORDER_CAP_WRAPPER).length ){
                var $capSelect = $(settings.ORDER_CAP_SELECT);
                var capVal = $capSelect.val();
                var isValidCap = capVal && $capSelect.find('option[value="'+capVal+'"]').length > 0 && capVal !== '';
                if( !isValidCap ){
                    var $capError = $(settings.ORDER_CAP_ERROR);
                    $capError.text( $capError.data('text') ).removeClass('d-none');
                    $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);
                    return;
                }
            }

            // Availability check via API (asporto + delivery)
            var $availabilityDate = $('#data-asporto.availability-enabled, #data-delivery.availability-enabled').first();
            if( $availabilityDate.length ){

                var isDelivery = $availabilityDate.attr('id') === 'data-delivery';
                var cfg = isDelivery
                    ? { dateInput: '#data-delivery', timeInput: '#ora-delivery', timesError: '#delivery-times-errore', availabilityError: '#delivery-availability-errore', tipo: 'delivery' }
                    : { dateInput: '#data-asporto', timeInput: '#ora-asporto', timesError: '#asporto-times-errore', availabilityError: '#asporto-availability-errore', tipo: 'asporto' };

                // Privacy check
                if( $(settings.ORDER_FORM_INPUT_PRIVACY).length && !$(settings.ORDER_FORM_INPUT_PRIVACY).is(':checked') ){
                    $(settings.ORDER_FORM_ERROR_PRIVACY).html( $(settings.ORDER_FORM_INPUT_PRIVACY).data('error-message') );
                    $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);
                    return;
                }

                var dayVal = $(cfg.dateInput).val();
                var hourVal = $(cfg.timeInput).val();

                if( !dayVal || !hourVal ){
                    $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);
                    return;
                }

                $(cfg.availabilityError).text('').addClass('d-none');
                $(cfg.timesError).addClass('d-none');

                $.ajax({
                    url: backendURL+'api/order-availability/',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        user_id: menuID,
                        selected_date: dayVal,
                        selected_time: hourVal,
                        tipo: cfg.tipo
                    }
                }).done(function(response) {
                    if( response.status == 'success' ){
                        proceedSendOrder(form);
                    }else{
                        $(cfg.availabilityError)
                            .text($(cfg.availabilityError).data('text'))
                            .removeClass('d-none');
                        $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);
                    }
                }).fail(function() {
                    $(cfg.availabilityError)
                        .text($(cfg.availabilityError).data('text'))
                        .removeClass('d-none');
                    $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);
                });

                return;
            }

            // Check privacy acceptance
            if( submit === true && $(settings.ORDER_FORM_INPUT_PRIVACY).length && !$(settings.ORDER_FORM_INPUT_PRIVACY).is(':checked') ){
                $(settings.ORDER_FORM_ERROR_PRIVACY).html( $(settings.ORDER_FORM_INPUT_PRIVACY).data('error-message') );
                submit = false;
            }

            // Send Action if submit == true
            if( submit === true ) {

                proceedSendOrder(form);

            }else{

                // Enable submit button after error
                $(settings.ORDER_FORM_INPUT_SUBMIT).prop('disabled', false);

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

        // Save Stats trough API
        function saveStats(data) {

            var formData = new FormData();

            // Aggiungi i dati al FormData
            formData.append("user_id", data.user_id);
            formData.append("item_id", data.item_id);
            formData.append("item_price", data.item_price);
            formData.append("item_price_label", data.item_price_label);
            formData.append("client_id", data.client_id);
            formData.append("event", data.event);

            return $.ajax({
                url: backendURL + "api/save_menu_stats.php",
                type: "POST",
                data: formData,
                dataType: "json",
                cache: false,
                processData: false,
                contentType: false
            }).done(function(response) {
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error in POST request:", textStatus, errorThrown);
                console.log("Response text:", jqXHR.responseText);
            });

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

            // Add local storage data in fields

            // Nominativo
            var localNominativo = localStorage.getItem('_lm_globals_nominativo');
            if(localNominativo){
                $('#ordine input[name="nominativo"]').val(localNominativo);
            }


            // Prefisso
            var localPrefisso = localStorage.getItem('_lm_globals_prefisso');
            if(localPrefisso){
                $('#ordine select[name="pref"]').val(localPrefisso);
            }

            // Telefono
            var localTelefono = localStorage.getItem('_lm_globals_telefono');
            if(localTelefono){
                $('#ordine input[name="telefono"]').val(localTelefono);
            }


            // Email
            var localEmail = localStorage.getItem('_lm_globals_email');
            if(localEmail){
                $('#ordine input[name="email"]').val(localEmail);
            }


            // CF
            var localCf = localStorage.getItem('_lm_globals_cf');
            if(localCf){
                $('#ordine input[name="cf"]').val(localCf);
            }


            // Indirizzo
            var localIndirizzo = localStorage.getItem('_lm_globals_indirizzo');
            if(localIndirizzo){
                $('#ordine input[name="indirizzo"]').val(localIndirizzo);
            }


            // Cap
            var localCap = localStorage.getItem('_lm_globals_cap');
            if(localCap){
                var $capSelectInit = $(settings.ORDER_CAP_SELECT);
                if( $capSelectInit.length ){
                    if( $capSelectInit.find('option[value="'+localCap+'"]').length ){
                        $capSelectInit.val(localCap);
                    }else{
                        $capSelectInit.val('');
                        localStorage.removeItem('_lm_globals_cap');
                    }
                }else{
                    $('#ordine input[name="cap"]').val(localCap);
                }
            }


            // Città
            var localCitta = localStorage.getItem('_lm_globals_citta');
            if(localCitta){
                $('#ordine input[name="citta"]').val(localCitta);
            }


            // Pagamento
            var localPagamento = localStorage.getItem('_lm_globals_pagamento');
            if(localPagamento) {
                if($('#ordine select[name="pagamento"]').find('option[value="' + localPagamento + '"]').length) {
                    $('#ordine select[name="pagamento"]').val(localPagamento);
                }else{
                    $('#ordine select[name="pagamento"]').find('option:first').prop('selected', true);
                }
            }else{
                $('#ordine select[name="pagamento"] option:first').prop('selected', true);
            }


            // Marketing LM
            var localMarketingLM = localStorage.getItem('_lm_globals_marketing_lm');
            if(localMarketingLM !== null){
                var checkedLM = (localMarketingLM === '1' || localMarketingLM === 'true');
                $('input[name="marketing_lm"]').prop('checked', checkedLM);
            }


            // Marketing Gest
            var localMarketingGest = localStorage.getItem('_lm_globals_marketing_gest');
            if(localMarketingGest !== null){
                var checkedGest = (localMarketingGest === '1' || localMarketingGest === 'true');
                $('input[name="marketing_gest"]').prop('checked', checkedGest);
            }


            // Open Popup
            $(settings.POPUP_ID).on('modalOpen', function(popup, button){

                if( button.data('menu') == $(settings.POPUP_ID).attr('id') ) {
                    buildPopup(button);
                }

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

            // Filter times on date change (asporto)
            if( $('#data-asporto').hasClass('availability-enabled') ){
                $('#data-asporto').on('change', function(){
                    filterTimesOrder({
                        dateInput: '#data-asporto', timeInput: '#ora-asporto',
                        timesError: '#asporto-times-errore', availabilityError: '#asporto-availability-errore',
                        tipo: 'asporto'
                    });
                });
                if( $('#data-asporto').val() ){
                    $('#data-asporto').trigger('change');
                }
            }

            // Filter times on date change (delivery)
            if( $('#data-delivery').hasClass('availability-enabled') ){
                $('#data-delivery').on('change', function(){
                    filterTimesOrder({
                        dateInput: '#data-delivery', timeInput: '#ora-delivery',
                        timesError: '#delivery-times-errore', availabilityError: '#delivery-availability-errore',
                        tipo: 'delivery'
                    });
                });
                if( $('#data-delivery').val() ){
                    $('#data-delivery').trigger('change');
                }
            }

            // CAP whitelist change handler (Premium Plus - delivery_caps)
            if( $(settings.ORDER_CAP_WRAPPER).length ){
                $(settings.ORDER_CAP_SELECT).on('change', updateDeliveryPricingByCap);
                // Sync on init (after potential localStorage restore)
                updateDeliveryPricingByCap();
            }

            // Build WhatsApp Order - #chicchinero
            $(settings.CART_WHATSAPP_BUTTON).on('click', function(e) {

                e.preventDefault();
                var whappUrl = buildWhappOrder();
                window.open(whappUrl, '_blank');

                // Submit WhatsApp Order - #chicchinero - da decidere se è il caso di svuotare cart o meno

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

        // Reload page on "Torna all'ordine" click (menu-errore-ordine modal)
        $(document).on('click', '#menu-errore-ordine-back', function(){
            window.location.reload();
        });

    })

    $(window).on('pageshow', function(event) {

        if( event.originalEvent.persisted ) {

            // Forza il ricaricamento della pagina senza cache
            if( performance.navigation.type === 2  ) {
                var actualUrl = window.location.href;
                var separator = actualUrl.includes('?') ? '&' : '?';
                var newUrl = actualUrl + separator + 'nocache=true';
                window.location.replace(newUrl);
            }

        }

    });

}(jQuery));