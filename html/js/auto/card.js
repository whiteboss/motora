$(function() {
    //$('.tabs').tabs();

    if ( $("#gallery").is('*') ) {
        $("#gallery").ulslide({
            statusbar: true,
            //width: 600,
            //height: 600,
            bnext: '#slide_next',
            bprev: '#slide_prev',
            axis: 'x',
            mousewheel: false,
            duration: 200,
            autoslide: 0
        });
    }
});