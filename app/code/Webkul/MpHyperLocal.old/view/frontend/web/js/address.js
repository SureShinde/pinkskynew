/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "mage/url",
    "googleMapPlaceLibrary"
],function ($, $t,urlBuilder) {
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
    var autocompleteform;
    autocompleteform = new google.maps.places.Autocomplete(
        /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
        {}
    );

    autocompleteform.addListener('place_changed', fillInAddress);
    function fillInAddress()
    {
        var place = autocompleteform.getPlace();
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
    }

    var saveAction = function () {
        var address = $('#autocomplete');
        if (address.val()) {
            $.ajax({
                url: urlBuilder.build('mphyperlocal/index/setaddress'),
                data: {
                    'address':address.val(),
                    'lat':address.attr('data-lat'),
                    'lng':address.attr('data-lng'),
                    'city':address.attr('data-city'),
                    'state':address.attr('data-state'),
                    'country':address.attr('data-country'),
                    'redirect_url':'cms/index/index',
                    'address-id':$('#autocomplete').attr('data-address-id')
                },
                type: 'POST',
                dataType:'html',
                success: function (transport) {
                    var response = $.parseJSON(transport);
                    if (response.status) {
                        window.location.href = response.redirect_url;
                    } else {
                        // $('.hyper-local-error').remove();
                    }
                }
            });
        }
    }
    $('#autocomplete').keypress(function () {
        $(this).css('border','1px solid #c2c2c2');
        $(this).attr('data-lat','');
        $(this).attr('data-lng','');
    });
    $('body').on('click', '#save-button', saveAction);
    $('body').on('click', '.ship', function() {
        var value = $(this).attr('data-address');
        var city = $(this).attr('data-city');
        var state = $(this).attr('data-state');
        var country = $(this).attr('data-country');
        var addressId = $(this).attr('data-address-id');
        $.ajax({
            url: urlBuilder.build('mphyperlocal/address/coordinates'),
            data: { "address": value },
            type: 'POST',
            dataType: 'html',
            success: function (jsonData) {
                var data = $.parseJSON(jsonData);
                if (data['latitude'] != '') {
                    $('#autocomplete').attr('data-lat', data['latitude']);
                    $('#autocomplete').attr('data-lng', data['longitude']);
                    $('#autocomplete').attr('data-city', city);
                    $('#autocomplete').attr('data-state', state);
                    $('#autocomplete').attr('data-country', country);
                    $('#autocomplete').attr('value', state + ', ' + country);
                    $('#autocomplete').attr('data-address-id', addressId);
                    saveAction();
                } else {
                   // $('.hyper-local-error').remove();
                }
            }
        });
    });
});