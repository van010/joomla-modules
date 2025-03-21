(function($) {
    window.JBCountDown_5 = function(el, settings) {
        var $el = $(el);
        var glob = settings;
    
        function deg(deg) {
            return (Math.PI/180)*deg - (Math.PI/180)*90
        }
        
        glob.total   = Math.floor((glob.endDate - glob.startDate)/86400);
        glob.days    = Math.floor((glob.endDate - glob.now ) / 86400);
        glob.hours   = 24 - Math.floor(((glob.endDate - glob.now) % 86400) / 3600);
        glob.minutes = 60 - Math.floor((((glob.endDate - glob.now) % 86400) % 3600) / 60) ;
        glob.seconds = 60 - (glob.endDate - glob.now) % 60 ;
        
        if (glob.now >= glob.endDate){
            return;
        }
        
        var clock = {
            set: {
                days: function(){
                    var cdays = $el.find(".canvas_days").get(0);
                    var ctx = cdays.getContext("2d");
                    ctx.clearRect(0, 0, cdays.width, cdays.height);
                    ctx.beginPath();
                    ctx.strokeStyle = glob.daysColor;
                    
                    ctx.shadowBlur    = 10;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;
                    ctx.shadowColor = glob.daysGlow;
                    
                    ctx.arc(61,61,50, deg(0), deg((360/glob.total - glob.days)*(glob.total)));
                    ctx.lineWidth = 22;
                    ctx.stroke();
                    $el.find(".clock_days .val").text(glob.days);
                },
                
                hours: function(){
                    var cHr = $el.find(".canvas_hours").get(0);
                    var ctx = cHr.getContext("2d");
                    ctx.clearRect(0, 0, cHr.width, cHr.height);
                    ctx.beginPath();
                    ctx.strokeStyle = glob.hoursColor;
                    
                    ctx.shadowBlur    = 10;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;
                    ctx.shadowColor = glob.hoursGlow;
                    
                    ctx.arc(61,61,50, deg(0), deg(15*glob.hours));
                    ctx.lineWidth = 22;
                    ctx.stroke();
                    $el.find(".clock_hours .val").text(24 - glob.hours);
                },
                
                minutes : function(){
                    var cMin = $el.find(".canvas_minutes").get(0);
                    var ctx = cMin.getContext("2d");
                    ctx.clearRect(0, 0, cMin.width, cMin.height);
                    ctx.beginPath();
                    ctx.strokeStyle = glob.minutesColor;
                    
                    ctx.shadowBlur    = 10;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;
                    ctx.shadowColor = glob.minutesGlow;
                    
                    ctx.arc(61,61,50, deg(0), deg(6*glob.minutes));
                    ctx.lineWidth = 22;
                    ctx.stroke();
                    $el.find(".clock_minutes .val").text(60 - glob.minutes);
                },
                seconds: function(){
                    var cSec = $el.find(".canvas_seconds").get(0);
                    var ctx = cSec.getContext("2d");
                    ctx.clearRect(0, 0, cSec.width, cSec.height);
                    ctx.beginPath();
                    ctx.strokeStyle = glob.secondsColor;
                    
                    ctx.shadowBlur    = 10;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 0;
                    ctx.shadowColor = glob.secondsGlow;
                    
                    ctx.arc(61,61,50, deg(0), deg(6*glob.seconds));
                    ctx.lineWidth = 22;
                    ctx.stroke();
            
                    $el.find(".clock_seconds .val").text(60 - glob.seconds);
                }
            },
        
            start: function(){
                /* Seconds */
                var cdown = setInterval(function(){
                    if ( glob.seconds > 59 ) {
                        if (60 - glob.minutes == 0 && 24 - glob.hours == 0 && glob.days == 0) {
                            clearInterval(cdown);
                            
                            /* Countdown is complete */
                            
                            return;
                        }
                        glob.seconds = 1;
                        if (glob.minutes > 59) {
                            glob.minutes = 1;
                            clock.set.minutes();
                            if (glob.hours > 23) {
                                glob.hours = 1;
                                if (glob.days > 0) {
                                    glob.days--;
                                    clock.set.days();
                                }
                            } else {
                                glob.hours++;
                            }
                            clock.set.hours();
                        } else {
                            glob.minutes++;
                        }
                        clock.set.minutes();
                    } else {
                        glob.seconds++;
                    }
                    clock.set.seconds();
                },1000);
            }
        }
        clock.set.seconds();
        clock.set.minutes();
        clock.set.hours();
        clock.set.days();
        clock.start();
    }
})(jQuery);