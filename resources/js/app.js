require('./bootstrap');

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
