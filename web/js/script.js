$(function(){

    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    var windowIsThin = window.matchMedia("(max-width:768px)").matches;

    if (isMobile || windowIsThin) {
        //carousel disabled
        $('.carousel').carousel({
            interval: false
        });
    }; 

});