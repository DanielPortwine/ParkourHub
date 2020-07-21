require('./bootstrap');
require('../../node_modules/select2/dist/js/select2.min');

import 'ol/ol.css';
import Map from 'ol/Map';
import View from 'ol/View';
import {Tile as TileLayer, Vector as VectorLayer} from 'ol/layer';
import OSM from 'ol/source/OSM';
import Overlay from 'ol/Overlay';
import Feature from 'ol/Feature';
import Point from 'ol/geom/Point';
import VectorSource from 'ol/source/Vector';
import {Fill, Stroke, Style} from 'ol/style';
import {fromLonLat, toLonLat} from 'ol/proj';
import {boundingExtent} from 'ol/extent';
import GeoJSON from 'ol/format/GeoJSON';
import CircleStyle from "ol/style/Circle";

function showSpot(feature, viewSpotPopup, viewSpotOverlay, urlParams, map = null)
{
    $('.ol-popup').hide();
    var coordinates = feature.getGeometry().getCoordinates();
    $.ajax({
        url: '/spots/spot/' + feature.get('id'),
        type: 'GET',
        success: function(response) {
            $('#view-spot-popup').html(response);
            $(viewSpotPopup).show();
            viewSpotOverlay.setPosition(coordinates);
            $(viewSpotPopup).css('top', -10);
        }
    });
    if (map != null) {
        map.getView().setCenter(coordinates);
    }
    if (urlParams.has('latLon')) {
        urlParams.delete('latLon');
    }
    if (urlParams.has('coords')) {
        urlParams.delete('coords');
    }
    if (urlParams.has('bounding')) {
        urlParams.delete('bounding');
    }
    if (urlParams.has('spot')) {
        urlParams.set('spot', feature.get('id'));
    } else {
        urlParams.append('spot', feature.get('id'));
    }
    window.history.pushState('obj', '', window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + urlParams);
}

function searchAddress(search) {
    $.ajax({
        url: '/ajax/searchAddress/' + search,
        type: 'GET',
        success: function(response) {
            if (response.length > 0) {
                var $addressResults = $('#address-results');
                $addressResults.html('');
                for (var address in response) {
                    address = (response[address]);
                    $addressResults.append($('<div class="row btn text-white w-100 text-left" onclick="window.location = `/spots?search=' + search + '&bounding=' + address.boundingbox + '`"><div class="col">' + address.display_name + '</div></div>'));
                }
                $('#map-search-results').removeClass('d-none').show();
                $('#map-search-results-addresses').removeClass('d-none');
                $('#map-search-results-addresses-count').text(response.length);
            } else {
                $('#map-search-results-addresses').addClass('d-none');
            }
        }
    })
}

function searchSpot(search) {
    $.ajax({
        url: '/spots/search',
        type: 'GET',
        data: 'search=' + search,
        success: function(response) {
            if (response.length > 0) {
                var $spotResults = $('#spot-results');
                $spotResults.html('');
                for (var spot in response) {
                    spot = (response[spot]);
                    $spotResults.append($('<div class="row btn text-white w-100 text-left" onclick="window.location = `/spots?search=' + search + '&spot=' + spot.id + '`"><div class="col">' + spot.name + ' by ' + spot.user.name + '</div></div>'));
                }
                $('#map-search-results').removeClass('d-none').show();
                $('#map-search-results-spots').removeClass('d-none');
                $('#map-search-results-spots-count').text(response.length);
            } else {
                $('#map-search-results-spots').addClass('d-none');
            }
        }
    })
}

function searchHometown(hometown) {
    if (hometown == null) {
        $('#hometown-results-container').addClass('d-none');
    } else {
        $.ajax({
            url: '/ajax/searchHometown/' + hometown,
            type: 'GET',
            success: function (response) {
                $('#hometown-results-container').removeClass('d-none');
                if (response.length > 0) {
                    var $hometownResults = $('#hometown-results');
                    $hometownResults.html('');
                    for (var city in response) {
                        city = (response[city]);
                        $hometownResults.append($('<option value="' + city.display_name + '|' + city.boundingbox + '">' + city.display_name + '</option>'));
                    }
                    $('#hometown-results-count').text(response.length < 10 ? response.length : '10+');
                } else {
                    $('#hometown-results-count').html('No');
                    $('hometown-results').html('');
                }
            }
        })
    }
}

function mapSearch(urlParams, search = null) {
    if (search == null) {
        search = $('#map-search-input').val();
    }
    if (search == '') {
        urlParams.delete('search');
        window.history.pushState('obj', '', window.location.protocol + "//" + window.location.host + window.location.pathname);
        $('#map-search-results').addClass('d-none');
        return;
    }
    var latLon = search.split(',');
    if (latLon.length == 2 && (-85 <= latLon[0] && latLon[0] <= 85) && (-180 <= latLon[1] && latLon[1] <= 180)) {
        console.log('searching lat lon');
        window.location = '/spots?latLon=' + latLon;
    }
    if (urlParams.has('search')) {
        urlParams.set('search', search);
    } else {
        urlParams.append('search', search);
    }
    window.history.pushState('obj', '', window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + urlParams);
    searchAddress(search);
    searchSpot(search);
}

function checkInputClear($input, $clear) {
    if ($input.val() !== '') {
        $clear.removeClass('d-none');
    } else {
        $clear.addClass('d-none');
    }
}

function setBoundingBox(latLonArray, map, highlight = false) {
    var boundingBox = [fromLonLat([latLonArray[2], latLonArray[1]])];
    boundingBox.push(fromLonLat([latLonArray[3], latLonArray[0]]));
    map.getView().fit(boundingExtent(boundingBox), {
        padding: [20, 20, 20, 20],
        maxZoom: 20,
    });
    if (highlight) {
        var geoJsonLayer = new VectorLayer({
            source: new VectorSource({
                features: (new GeoJSON()).readFeatures({
                    'type': 'FeatureCollection',
                    'features': [{
                        'type': 'Feature',
                        'geometry': {
                            'type': 'Polygon',
                            'coordinates': [
                                [boundingBox[0], [boundingBox[0][0], boundingBox[1][1]], boundingBox[1], [boundingBox[1][0], boundingBox[0][1]]]
                            ]
                        }
                    }]
                })
            }),
            style: new Style({
                stroke: new Stroke({
                    color: 'black',
                    width: 1
                }),
                fill: new Fill({
                    color: 'rgba(0, 0, 0, 0.1)'
                })
            })
        });
        map.addLayer(geoJsonLayer);
    }
}

function setRating(rating, shape) {
    var $rating = $('#rating-' + shape + '-' + rating),
        selected = 'fa-' + shape,
        unselected = 'fa-' + shape + '-o';
    if (shape === 'star') {
        $('#rating').val(rating);
    } else if (shape === 'circle') {
        $('#difficulty').val(rating);
    }
    // if the selected shape is the highest previously selected, unselect it, otherwise select it
    if ($rating.hasClass(selected) && $('#rating-' + shape + '-' + (rating < 5 ? (rating + 1) : rating)).hasClass(unselected)) {
        $rating.addClass(unselected).removeClass(selected);
    } else {
        $rating.addClass(selected).removeClass(unselected);
    }
    // update the rest of the shapes
    for (var value = 1; value <= 5; value++) {
        if (value === rating) {
            continue;
        }
        var $value = $('#rating-' + shape + '-' + value);
        if (value < rating) {
            $value.addClass(selected).removeClass(unselected);
        } else {
            $value.addClass(unselected).removeClass(selected);
        }
    }
}

$(document).ready(function() {
    lazyload();
    var $window = $(window);
    $window.scroll(function() {
        if ($window.scrollTop() > 100) {
            $('#scroll-arrow').fadeOut();
        }
    });

    $('.require-confirmation').click(function() {
        $(this).hide();
        $(this).siblings('.confirmation-button').removeClass('d-none');
        $(this).siblings('.confirmation-text').removeClass('d-none');
    });

    var popupOptions = {
            positioning: 'bottom-center',
            autoPan: true,
            autoPanMargin: 1,
            autoPanAnimation: {
                duration: 250
            }
        },
        createSpotPopup = document.getElementById('create-spot-popup'),
        createSpotOverlay = new Overlay(
            $.extend(popupOptions,
                {
                    element: createSpotPopup
                }
            )
        ),
        viewSpotPopup = document.getElementById('view-spot-popup'),
        viewSpotOverlay = new Overlay(
            $.extend(popupOptions,
                {
                    element: viewSpotPopup
                }
            )
        ),
        loginRegisterPopup = document.getElementById('login-register-popup'),
        loginRegisterOverlay = new Overlay(
            $.extend(popupOptions,
                {
                    element: loginRegisterPopup
                }
            )
        ),
        urlParams = new URLSearchParams(window.location.search),
        vectorSource = new VectorSource(),
        startingCoords = [-175394.8171068958, 7317942.661464895],
        startingZoom = 10,
        hometownLayer;
    if ($('.select2-movements').length) {
        var $select2 = $('.select2-movements'),
            spot = null;
        if ($select2.attr('id')) {
            spot = $select2.attr('id').split('-')[1];
        }
        $.ajax({
            url: '/movements/getMovements',
            data: {
                spot: spot
            },
            success: function (response) {
                $('.select2-movements').select2({
                    data: response,
                    width: '100%',
                });
                if (urlParams.has('movement')) {
                    $('.select2-movements').val(urlParams.get('movement')).trigger('change');
                }
            },
        });
    }
    if ($('.select2-movement-category').length) {
        $.ajax({
            url: '/movements/getMovementCategories',
            success: function (response) {
                $('.select2-movement-category').select2({
                    data: response,
                    width: '100%',
                });
                if (urlParams.has('category')) {
                    $('.select2-movement-category').val(urlParams.get('category')).trigger('change');
                }
            },
        });
    }

    if (urlParams.has('search')) {
        searchAddress(urlParams.get('search'));
        searchSpot(urlParams.get('search'));
    }

    var startAtHometown = false;
    if (urlParams.has('latLon')) {
        var latLon = urlParams.get('latLon').split(','),
            lonLat = [latLon[1], latLon[0]];
        startingCoords = fromLonLat(lonLat);
        startingZoom = 15;
    } else if (urlParams.has('coords')) {
        startingCoords = urlParams.get('coords').split(',');
        startingZoom = 16;
    } else if (urlParams.has('spot')) {
        startingZoom = 18;
    } else {
        startAtHometown = true;
    }
    var map = new Map({
        layers: [
            new TileLayer({
                source: new OSM(),
            }),
            new VectorLayer({
                source: vectorSource,
                style: new Style({
                    image: new CircleStyle({
                        radius: 7,
                        fill: new Fill({
                            color: 'black'
                        }),
                        stroke: new Stroke({
                            color: 'white',
                            width: 2
                        })
                    })
                })
            })
        ],
        overlays: [createSpotOverlay, viewSpotOverlay, loginRegisterOverlay],
        target: 'map',
        view: new View({
            center: startingCoords,
            zoom: startingZoom,
        }),
        controls: [],
    });

    if (urlParams.has('bounding')) {
        var boundingBoxLatLon = urlParams.get('bounding').split(',');
        setBoundingBox(boundingBoxLatLon, map);
    }

    // set the map bounding box to the user's hometown
    if (startAtHometown) {
        $.ajax({
            url: '/user/fetch_hometown_bounding',
            type: 'GET',
            success: function (response) {
                if (response != false) {
                    setBoundingBox(response, map, true);
                    hometownLayer = map.getLayers().pop();
                }
            }
        });
    }

    // display the spot markers on the map
    $.ajax({
        url: '/spots/fetch',
        type: 'GET',
        success: function(response) {
            for (var spot in response) {
                spot = (response[spot]);
                var feature = new Feature({
                    type: 'spot',
                    geometry: new Point(spot.coordinates.split(',')),
                    id: spot.id,
                    name: spot.name,
                    description: spot.description,
                    private: spot.private,
                    image: spot.image,
                });
                vectorSource.addFeature(feature);
                if (urlParams.get('spot') == spot.id) {
                    showSpot(feature, viewSpotPopup, viewSpotOverlay, urlParams, map);
                }
            }
        },
    });
    // limit the level of zoom that can be achieved
    map.on('movestart', function(e) {
        if (map.getView().getZoom() > 22) {
            map.getView().setZoom(22);
        }
    });
    // show the create or view popup when the user clicks on the map
    map.on('click', function(e) {
        var feature = map.forEachFeatureAtPixel(e.pixel, function(feature) {
            return feature;
        });
        if (feature) {
            // the user clicked on a spot marker so show that spot
            showSpot(feature, viewSpotPopup, viewSpotOverlay, urlParams);
        } else {
            if ($('#create-spot-popup').css('display') == 'block' || $('#view-spot-popup').css('display') == 'block') {
                // the user clicked away from an open popup so close it
                $('.ol-popup').fadeOut('fast');
                urlParams.delete('spot');
                window.history.pushState('obj', '', window.location.protocol + "//" + window.location.host + window.location.pathname);
            } else {
                // the user clicked on an empty space, now check if they're verified and logged in
                $.ajax({
                    url: '/ajax/isVerifiedLoggedIn',
                    type: 'GET',
                    success: function (response) {
                        $('.ol-popup').hide();
                        if (response) {
                            // the user is logged in so show Create Spot popup
                            $(createSpotPopup).show();
                            $('#coordinates').val(e.coordinate[0] + ',' + e.coordinate[1]);
                            var lonLat = toLonLat(e.coordinate);
                            $('#lat-lon').val(lonLat[1] + ',' + lonLat[0]);
                            createSpotOverlay.setPosition(e.coordinate);
                            $(createSpotPopup).css('margin-top', -parseInt($(createSpotPopup).height()) - 10);
                        } else {
                            // the user is not logged in or hasn't verified their email so show Login/Register popup
                            $(loginRegisterPopup).show();
                            loginRegisterOverlay.setPosition(e.coordinate);
                            $(loginRegisterPopup).css('margin-top', -parseInt($(loginRegisterPopup).height()) - 10);
                        }
                    }
                });
            }
        }
    });

    $('.close-popup-button').click(function() {
        $(this).closest('.popup').fadeOut('fast');
        if ($(this).closest('.popup').attr('id') == 'view-spot-popup') {
            window.history.pushState('obj', '', window.location.protocol + "//" + window.location.host + window.location.pathname);
        }
    });

    $('#map-search-button').click(function() {
        mapSearch(urlParams);
    });
    $('#map-search-form').submit(function(e) {
        mapSearch(urlParams);
        e.preventDefault();
    });
    // decide whether to show or hide the clear search button
    var $mapSearchInput = $('#map-search-input'),
        $mapSearchClear = $('#map-search-clear');
    $mapSearchInput.on('input', function(e) {
        checkInputClear($mapSearchInput, $mapSearchClear);
    });
    $mapSearchClear.click(function() {
        mapSearch(urlParams, '');
        $mapSearchInput.val('');
        $mapSearchClear.addClass('d-none');
    });
    checkInputClear($mapSearchInput, $mapSearchClear);

    // search for a city to use as the user's hometown
    $('#hometown-form').submit(function(e) {
        searchHometown($('#hometown').val());
        e.preventDefault();
    });

    // show/hide the description on cards such spots
    $('.content-description-container').hover(function() {
        $(this).children('.spot-description').slideDown('fast');
    }, function() {
        $(this).children('.spot-description').slideUp('fast');
    });

    // have a dropdown for hometown content in the user nav dropdown
    $('#hometown-nav-item').on('click', function(e) {
        var $navItems = $('#hometown-nav-items');
        if ($navItems.css('display') == 'none') {
            $navItems.slideDown('fast');
        } else {
            $navItems.slideUp('fast');
        }
        e.stopPropagation();
    });

    // clear the user's hometown
    $('#remove-hometown-button').click(function() {
        $('#hometown').val('');
        $('#account-form').submit();
    });

    // toggle the card-body
    $('.card-hidden-body').click(function() {
        var $cardBody = $(this).siblings('.card-body');
        if ($cardBody.css('display') === 'none') {
            $cardBody.slideDown();
        } else {
            $cardBody.slideUp();
        }
    });

    // handle descriptions being taller than 50px
    var $descriptionMore = $('#description-more');
    if ($('#description-content').height() > 55) {
        $descriptionMore.show();
    }
    $descriptionMore.click(function() {
        var more = $descriptionMore.text() === 'More';
        $('#description-box').toggleClass('more');
        $descriptionMore.text(more ? 'Less' : 'More');
    });

    // switch challenge image title and video
    $('#switch-title-button').click(function() {
        $('#full-content-title').toggleClass('d-none');
        $('#full-content-video').toggleClass('d-none');
        $(this).children('.fa').toggleClass('fa-film');
        $(this).children('.fa').toggleClass('fa-eye-slash');
    });

    // select a rating
    $('.rating-star.editable').click(function() {
        setRating(parseInt($(this).attr('id').split('-')[2]), 'star');
    });
    setRating(parseInt($('#rating').val()), 'star');
    $('.rating-circle.editable').click(function() {
        setRating(parseInt($(this).attr('id').split('-')[2]), 'circle');
    });
    setRating(parseInt($('#difficulty').val()), 'circle');

    // like a spot comment
    $('.like-spot-comment').click(function() {
        var id = $(this).attr('id').split('-')[3];
        $.ajax({
            url: '/spot_comments/like/' + id,
            type: 'GET',
            success: function(likes) {
                $('#like-spot-comment-' + id).addClass('d-none');
                $('#unlike-spot-comment-' + id).removeClass('d-none');
                var $likes = $('#spot-comment-likes-' + id);
                $likes.html(parseInt(likes) === 1 ? '1 like' : (likes + ' likes'));
            }
        });
    });
    // unlike a spot comment
    $('.unlike-spot-comment').click(function() {
        var id = $(this).attr('id').split('-')[3];
        $.ajax({
            url: '/spot_comments/unlike/' + id,
            type: 'GET',
            success: function(likes) {
                $('#unlike-spot-comment-' + id).addClass('d-none');
                $('#like-spot-comment-' + id).removeClass('d-none');
                var $likes = $('#spot-comment-likes-' + id);
                $likes.html(parseInt(likes) === 1 ? '1 like' : (likes + ' likes'));
            }
        });
    });

    // youtube lazyload
    $.each($('.youtube'), function() {
        var image = $('<img>', {
            src: 'https://img.youtube.com/vi/' + $(this).attr('data-id') + '/maxresdefault.jpg',
        });
        $(this).append(image);
        $(this).click(function() {
            var iframe = $('<iframe>', {
                frameborder: '0',
                allowfullscreen: '',
                src: 'https://www.youtube.com/embed/' + $(this).attr('data-id') + (($(this).attr('data-start') > 0) ? ('?start=' + $(this).attr('data-start') + '&') : '?') + 'rel=0&showinfo=0&autoplay=1'
            });
            $(this).html('');
            $(this).append(iframe);
        });
    });

    // add a spot to hitlist
    $('.add-to-hitlist-button').click(function() {
        var $button = $(this),
            spot = $button.attr('id').split('-')[2];
        $.ajax({
            url: '/spots/add_to_hitlist/' + spot,
            type: 'GET',
            success: function(response) {
                $button.siblings('.tick-off-hitlist-button').removeClass('d-none');
                $button.addClass('d-none');
            }
        })
    });
    // tick a spot off hitlist
    $('.tick-off-hitlist-button').click(function() {
        var $button = $(this),
            spot = $button.attr('id').split('-')[2];
        $.ajax({
            url: '/spots/tick_off_hitlist/' + spot,
            type: 'GET',
            success: function(response) {
                $button.addClass('d-none');
            }
        })
    });

    // follow a user
    $('.follow-user-button').click(function() {
        var $button = $(this),
            spot = $button.attr('id').split('-')[2];
        $.ajax({
            url: '/user/follow/' + spot,
            type: 'GET',
            success: function(response) {
                $button.siblings('.unfollow-user-button').removeClass('d-none');
                $button.addClass('d-none');
            }
        })
    });
    // unfollow a user
    $('.unfollow-user-button').click(function() {
        var $button = $(this),
            spot = $button.attr('id').split('-')[2];
        $.ajax({
            url: '/user/unfollow/' + spot,
            type: 'GET',
            success: function(response) {
                $button.siblings('.follow-user-button').removeClass('d-none');
                $button.addClass('d-none');
            }
        })
    });

    // toggle the hometown boundary layer
    $('#toggle-hometown-button').click(function() {
        if ($(this).hasClass('hidden')) {
            map.addLayer(hometownLayer);
            $(this).removeClass('hidden');
        } else {
            map.getLayers().pop();
            $(this).addClass('hidden');
        }
    });

    // show the show all button if there is more than 1 line of movements
    if ($('#movements-inner-container').height() > 28) {
        $('#all-movements-button').show();
    }

    // show all movements at a spot
    $('#all-movements-button').click(function() {
        if ($('#movements-list').height() <= 28) {
            $('#movements-list').removeClass('movements-list-hidden');
            $(this).text('Hide...');
        } else {
            $('#movements-list').addClass('movements-list-hidden');
            $(this).text('Show All...');
        }
    });

    $('#add-movement-button').click(function() {
        $('#add-movement-container').slideDown();
    })
});
