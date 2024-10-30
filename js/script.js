jQuery(function ($) {
	$(document).ready(function(){
        function set_height_of_table(){
            frame = $('#nnmcs-frame');
            t = {
                contentHeight: $('#nnmcs-content').height(),
                maxHeight: $(window).height() - 96,
                contentPadding: parseInt(frame.css('padding-top')) + parseInt(frame.css('padding-bottom')),
            }
            t.effectiveHeight = t.contentHeight + t.contentPadding; 
            if( t.effectiveHeight >= t.maxHeight ) {
                frame.css('height',t.maxHeight);
                if(!frame.hasClass('overflowing')) {
                    frame.addClass('overflowing')
                }
            } else {
                frame.attr('style','');
                if(frame.hasClass('overflowing')) {
                    frame.removeClass('overflowing')
                }                
            }
        }   
        init = setTimeout(set_height_of_table, 125);
        $(window).resize(set_height_of_table);  

        if(0 !== $('#countdown').length) {
        const timerChange = setInterval(function(){
            let launch = $('#countdown').data('cdate')*1000
            let now = new Date().getTime()
            let elapsed = launch - now;
            let output = {
                y: elapsed / 31536000,
                m: elapsed.getMonth(),
                d: Math.floor(elapsed / (1000 * 60 * 60 * 24)),
                h: Math.floor((elapsed % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
                i: Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60)),
                s: Math.floor((elapsed % (1000 * 60)) / 1000),
            }
            let keys = Object.keys(output)
            for (let i = 0; i < keys.length; i++) {
                $('#countdown .date-part.' + keys[i] + ' .datetime').html(output[keys[i]])
            }
        },1000)

        timerChange              
        }
	});
});
