(function($) {
    window.JBCountDown_2 = function(el, settings) {
        var $el = $(el);
        var glob = settings;

        function deg(deg) {
            return (Math.PI/180)*deg - (Math.PI/180)*90
        }
        
        glob.days    = Math.floor((glob.endDate - glob.now ) / 86400);
        glob.hours   = 24 - Math.floor(((glob.endDate - glob.now) % 86400) / 3600);
        glob.minutes = 60 - Math.floor((((glob.endDate - glob.now) % 86400) % 3600) / 60) ;
        glob.seconds = 60 - (glob.endDate - glob.now) % 60 ;
        
        glob.left    = glob.endDate - glob.now;
        glob.passed  = glob.now - glob.startDate;
        glob.total   = glob.left + glob.passed;

        glob.sec = 1;
        
        if (glob.now >= glob.endDate) {
            return;
        }
        
        var clock = {
            set: {
                seconds: function(){
                    glob.sec++;
                    var cSec = $el.find(".canvas_seconds").get(0);
                    var ctx = cSec.getContext("2d");
                    ctx.clearRect(0, 0, cSec.width, cSec.height);
                    ctx.beginPath();
                    ctx.strokeStyle = glob.secondsColor;
                    
                    ctx.shadowBlur    = 10;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;
                    ctx.shadowColor = glob.secondsGlow;
                    
                    ctx.arc(259,259,245, deg(0), deg((360/glob.total) *(glob.passed + glob.sec)));
                    ctx.lineWidth = 5;
                    ctx.stroke();
                    
                    $el.find(".timer .secs").text(nformat(60 - glob.seconds));
                }
            },
        
            start: function(){
                /* Seconds */
                var cdown = setInterval(function(){
                    if ( glob.seconds > 59 ) {
                        if (60 - glob.minutes == 0 && 24 - glob.hours == 0 && glob.days == 0) {
                            clearInterval(cdown);
                            return;
                        }
                        glob.seconds = 1;
                        
                        if (glob.minutes > 59) {
                            glob.minutes = 1;
                            $el.find(".timer .mins").text(60 - glob.minutes);
                            
                            if (glob.hours > 23) {
                                glob.hours = 1;
                                if (glob.days > 0) {
                                    glob.days--;
                                }
                            } else {
                                glob.hours++;
                            }
                            $el.find(".timer .hrs").text(24 - glob.hours);
                        } else {
                            glob.minutes++;
                        }
                        $el.find(".timer .mins").text(60 - glob.minutes);
                    } else {
                        glob.seconds++;
                    }
                    
                    clock.set.seconds();
                },1000);
            }
        }

        function nformat(n){
            return n > 9 ? "" + n: "0" + n;
        }

        $el.find(".timer .days").text(nformat(glob.days));
        $el.find(".timer .hrs").text(nformat(24 - glob.hours));
        $el.find(".timer .mins").text(nformat(60 - glob.minutes));

        clock.set.seconds();
        clock.start();
    }
})(jQuery);