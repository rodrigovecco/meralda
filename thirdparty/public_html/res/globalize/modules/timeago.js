jQuery.extend(Globalize, {

    timeago:function(date){
        var distance = (new Date().getTime() - date.getTime()),
            seconds = Math.abs(distance) / 1000,
            minutes = seconds / 60,
            hours = minutes / 60;
		if( seconds < 45){
			return Globalize.localize('%lbl.time.justnow');	
		}
		var formatter = Globalize.mwMessageFormater("mw.datetime.relative.past" );
		if(!formatter){
			return 	this.format(date,'d') + " " + this.format(date, 't');
		}
		if( seconds < 90){
			return formatter.format([ 1, Globalize.localize('%lbl.datetime.minute')]);
		}
		if( minutes < 45){
			return formatter.format([Math.round(minutes), Globalize.localize('%lbl.datetime.minutes')]);
		}
		if( minutes < 90){
			return formatter.format([1, Globalize.localize('%lbl.datetime.hour')]);
		}
		if( hours < 24){
			return formatter.format([Math.round(hours), Globalize.localize('%lbl.datetime.hours')]);
		}
		if( hours < 42){
			return Globalize.localize('%lbl.datetime.yesterday') + " " + this.format(date, 't');
		}
		return 	this.format(date,'d') + " " + this.format(date, 't');
    }

});
jQuery.extend(Globalize, {
    timeagoRequeriesFurtherUpdate:function(date){
        var distance = (new Date().getTime() - date.getTime()),
            seconds = Math.abs(distance) / 1000,
            minutes = seconds / 60,
            hours = minutes / 60;
			
		if(hours>42){
			return false;	
		}
		return true;
	},
    setTimeAgoUpdateIntervalAndUpdate:function(freq){
		this.updateTimeAgoElems();
		return this.setTimeAgoUpdateInterval(freq);
	},

    setTimeAgoUpdateInterval:function(freq){
		if(!freq){
			freq=10000;	
		}
		Globalize.cancelTimeAgoUpdateInterval();
		Globalize.timeAgoUpdateInterval=setInterval(function(){Globalize.updateTimeAgoElems()},freq);
		return true;
    },
    updateTimeAgoElem:function(elm){
		if(!elm){
			return false;	
		}
		var date = elm.attr('data-time');
		var updated_times = elm.attr('updated_times');
		updated_times=mw_getInt(updated_times);
		
		if(!date){
			return false;	
		}
		var current = elm.html();
		var realDate=new Date();
		var t=mw_getInt(date);
		if(!t){
			return false;	
		}
		realDate.setTime(t);
		elm.attr('updated_times',(updated_times+1));
		if(updated_times<1){
			elm.attr('title',this.format(realDate,'D') + " " + this.format(realDate, 't'));	
		}
		//
		var timeago=Globalize.timeago( realDate);
		if(current != timeago){
			elm.html(timeago);
		}
		if(!Globalize.timeagoRequeriesFurtherUpdate( realDate)){
			elm.removeClass("timeago");
		}
	},

    updateTimeAgoElems:function(){
		var elms = $('.timeago');
		elms.each(function(){
			Globalize.updateTimeAgoElem($(this));
		});	
	},

    cancelTimeAgoUpdateInterval:function(){
		if(Globalize.timeAgoUpdateInterval){
			clearInterval(Globalize.timeAgoUpdateInterval);	
			Globalize.timeAgoUpdateInterval=false;
			return true;
		}
		return false;
    }


});
