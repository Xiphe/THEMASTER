jQuery.fn.extend({scrollEnd:function(e){var a=jQuery(this),c=a.scrollTop(),b=!1;a.scroll(function(){b||(b=window.setInterval(function(){c===a.scrollTop()?(e.call(a[0]),window.clearInterval(b),b=!1):c=a.scrollTop()},200))})}});