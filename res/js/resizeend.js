jQuery.fn.extend({
    resizeEnd: function(newHandler){
        var w = jQuery(this).width(),
            h = jQuery(this).height(),
            intvl = false,
            thiz = this;
            $thiz = jQuery(this);
        $thiz.resize(function(){
            if (!intvl) {
                intvl = window.setInterval(function() {
                    if(w === $thiz.width() && h === $thiz.height()) {
                        newHandler.call(thiz);
                        window.clearInterval(intvl);
                        intvl = false;
                    } else {
                        w = $thiz.width();
                        h = $thiz.height();
                    }
                }, 200);
            }
        });
    }
});