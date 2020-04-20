require('./bootstrap');

var $window = $(window);
$window.scroll(function () {
    if ($window.scrollTop() > 100) {
        $('#scroll-arrow').fadeOut();
    }
});
