/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal",
    "googleMapPlaceLibrary"
    ],
    function ($, $t, modal) {
    "use strict";
    $.widget(
        'affiliate.register',
        {
            _create: function () {
                var optionsData = this.options;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    width:'200px',
                    title: $t(optionsData.popupHeading),
                    buttons: [{
                        text: $.mage.__('Go to shop'),
                        class: 'go-to-shop',
                        click: function () {
                            var address = $('#autocomplete');
                            if (address.val()) {
                                var conf = confirm($t('On location address change cart will empty.'));
                                if (conf) {
                                    $('.go-to-shop').before($('<span />').addClass('loader'));
                                    $.ajax({
                                        url: optionsData.saveAction,
                                        data: {
                                            'address':address.val(),
                                            'lat':address.attr('data-lat'),
                                            'lng':address.attr('data-lng'),
                                            'city':address.attr('data-city'),
                                            'state':address.attr('data-state'),
                                            'country':address.attr('data-country')
                                        },
                                        type: 'POST',
                                        dataType:'html',
                                        success: function (transport) {
                                            var response = $.parseJSON(transport);
                                            if (response.status) {
                                                window.location.href = response.redirect_url;
                                            } else {
                                                $('.hyper-local-error').remove();
                                                $('.modal-footer .loader').removeClass("loader");
                                                $('#select-address-popup').before($('<span class="message-error error message hyper-local-error"/>').text(response.msg));
                                            }
                                        }
                                    });
                                }
                            } else {
                                address.focus();
                                address.css('border', '1px solid red');

                            }
                        }
                    }]
                };

                if (optionsData.isAddressSet == 0) {
                    var cont = $('<div />').append($('#select-address-popup'));
                    modal(options, cont);
                    cont.modal('openModal');
                }
                $.ajax({
                    url: optionsData.getAction,
                    type: 'POST',
                    dataType:'json',
                    success: function (data) {
                        if (data.address != undefined) {
                            $("#wkautocomplete").attr('data-lat', data.latitude);
                            $("#wkautocomplete").attr('value', data.address);
                            $("#wkautocomplete").attr('data-lng', data.longitude);
                            $("#wkautocomplete").attr('data-city', data.city);
                            $("#wkautocomplete").attr('data-state', data.state);
                            $("#wkautocomplete").attr('data-country', data.country);
                        }
                    }
                });
                var autocomplete;
                var address_Type = {
                    locality: 'long_name',
                    administrative_area_level_1: 'long_name',
                    country: 'long_name'
                  };
                var addressMap = {
                    locality: 'city',
                    administrative_area_level_1: 'state',
                    country: "country"
                  };
                var selector = {
                    city: 1,
                    state: 2,
                    country: 3
                  };
                // if (optionsData.isAddressSet == 0) {
                    autocomplete = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(document.getElementById('wkautocomplete')),
                        {}
                    );
                    autocomplete.addListener('place_changed', fillInAddress);
                // }
                var autocompleteform;
                autocompleteform = new google.maps.places.Autocomplete(
                    /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
                    {}
                );

                // When the user selects an address from the dropdown, populate the address
                // fields in the form.
                autocompleteform.addListener('place_changed', fillInAddress);

                function fillInAddress()
                {
                    var place = autocompleteform.getPlace();
                    if (place == undefined) {                        
                        var place = autocomplete.getPlace();
                    }
                    var data = {};
                    var custAddressType = {
                        'locality' : 'long_name',
                        'administrative_area_level_1' : 'long_name',
                        'country' : 'long_name'
                    };
                    var addressMap = {
                        'locality' : 'city',
                        'administrative_area_level_1' : 'state',
                        'country' : 'country'
                    };
                    if (place != undefined) {
                        $('#autocomplete').attr('data-lat', place.geometry.location.lat());
                        $('#autocomplete').attr('data-lng', place.geometry.location.lng());
                    }
                    var address_components = place.address_components;
                    for (var i=0; i<address_components.length; i++) {
                        var addressType = address_components[i]['types'][0];
                        if (typeof custAddressType[addressType] !== 'undefined') {
                            data[addressMap[addressType]] = address_components[i][custAddressType[addressType]];
                        }
                    }
                    if (data.city) {
                        $('#autocomplete').attr('data-city', data.city);
                    } else {
                        $('#autocomplete').attr('data-city', '');
                    }
                    if (data.state) {
                        $('#autocomplete').attr('data-state', data.state);
                    } else {
                        $('#autocomplete').attr('data-state', '');
                    }
                    if (data.country) {
                        $('#autocomplete').attr('data-country', data.country);
                    } else {
                        $('#autocomplete').attr('data-country', '');
                    }
                    if (place.formatted_address != null) {
                        $('#autocomplete').attr('value', place.formatted_address);
                    } else {
                        $('#autocomplete').attr('value', '');
                    }
                    saveAction();
                }

                if ($('#autocompleteform').length > 0) {
                    var autocompleteform;
                    autocompleteform = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(document.getElementById('autocompleteform')),
                        {types: ['geocode']}
                    );

                    // When the user selects an address from the dropdown, populate the address
                    // fields in the form.
                    autocompleteform.addListener('place_changed', fillInPopupAddress);

                    function fillInPopupAddress()
                    {
                    // Get the place details from the autocomplete object.
                        var placepopup = autocompleteform.getPlace();
                        var address = ($('#autocompleteform').val()).split(",");
                        // console.log(placepopup.address_components);
                        for (var i = 0; i < placepopup.address_components.length; i++) {
                          var addressType = placepopup.address_components[i].types[0];
                          if (address_Type[addressType]) {
                            var val = placepopup.address_components[i][address_Type[addressType]];
                            if (val == address[0]) {
                                // console.log(addressMap[addressType]);
                                $('#address_type>option:eq('+selector[addressMap[addressType]]+')').prop('selected', true);
                            }
                          }
                        }
                        $('#latitude').val(placepopup.geometry.location.lat());
                        $('#longitude').val(placepopup.geometry.location.lng());
                       // saveAction();
                    }
                }


                function saveAction () {
                    var address = $('#autocomplete');
                    if (address.val()) {
                        var conf = confirm($t('On location address change cart will empty.'));
                        if (conf) {
                            $('.go-to-shop').before($('<span />').addClass('loader'));
                            $.ajax({
                                url: optionsData.saveAction,
                                data: {
                                    'address':address.val(),
                                    'lat':address.attr('data-lat'),
                                    'lng':address.attr('data-lng'),
                                    'city':address.attr('data-city'),
                                    'state':address.attr('data-state'),
                                    'country':address.attr('data-country')
                                },
                                type: 'POST',
                                dataType:'html',
                                success: function (transport) {
                                    var response = $.parseJSON(transport);
                                    if (response.status) {
                                        window.location.href = response.redirect_url;
                                    } else {
                                        $('.hyper-local-error').remove();
                                        $('.modal-footer .loader').removeClass("loader");
                                        $('#select-address-popup').before($('<span class="message-error error message hyper-local-error"/>').text(response.msg));
                                    }
                                }
                            });
                        }
                    } else {
                        address.focus();
                        address.css('border', '1px solid red');

                    }
                }
                // $('#selected-location, .my_location').on('click', function () {
                //     var cont = $('<div />').append($('#select-address-popup'));
                //     modal(options, cont);
                //     cont.modal('openModal');
                // });
                $('#autocomplete').keypress(function () {
                    $(this).css('border','1px solid #c2c2c2');
                    $(this).attr('data-lat','');
                    $(this).attr('data-lng','');
                });

                $('.store_option').change(function() {
                    var option_text = $($('select[name*="option_time"]').find('option')[0]);
                    if(option_text.text() == 'Select Pickup Time') {
                        option_text.text('Select Delivery Time');
                    } else {
                        option_text.text('Select Pickup Time');
                    }
                });
            }
        }
    );
    return $.affiliate.register;
    }
);
