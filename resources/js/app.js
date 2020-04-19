require('./bootstrap');

var $window = $(window);
$window.scroll(function () {
    if ($window.scrollTop() > 100) {
        $('#scroll-arrow').fadeOut();
    }
});

if (typeof jQuery != 'undefined') {
    console.log(jQuery.fn.jquery);
}
