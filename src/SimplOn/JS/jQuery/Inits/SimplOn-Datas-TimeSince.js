/*
 * \SimplOn\Datas\TimeSince.js
 * Author: Cristopher Carlos Mendoza Rojas
 * Date: April 6 2013
 */
$(function() {
	$('input.SimplOn.Data.TimeSince.years, input.SimplOn.Data.TimeSince.months, input.SimplOn.Data.TimeSince.weeks, input.SimplOn.Data.TimeSince.days').datepicker({maxDate: "-1D"});
	$('input.SimplOn.Data.TimeSince.hours, input.SimplOn.Data.TimeSince.minutes, input.SimplOn.Data.TimeSince.seconds').AnyTime_picker({latest: new Date(), format: "%m/%d/%Y %H:%i"});

});

function timeSinceTimer(date, format, id){
    var now = new Date();
    var since = new Date(date);
    var milliseconds = now.getTime() - since.getTime();
    var flags = format.split("");
    var showText = new Array();
    
    for ( var i in flags){
        if(flags[i] == 'y'){
            years = Math.floor(milliseconds/31556926000);
            milliseconds = milliseconds%31556926000;
            showText[i] = years+' Years';
        }else if(flags[i] == 'm'){
            months = Math.floor(milliseconds/2629743830);
            milliseconds = milliseconds%2629743830;
            showText[i] = months+' Months';            
        }else if(flags[i] == 'w'){
            weeks = Math.floor(milliseconds/604800000);
            milliseconds = milliseconds%604800000;
            showText[i] = weeks+' Weeks';            
        }else if(flags[i] == 'd'){
            days = Math.floor(milliseconds/86400000);
            milliseconds = milliseconds%86400000;
            showText[i] = days+' Days'; 
        }else if(flags[i] == 'h'){
            hours = Math.floor(milliseconds/3600000);
            milliseconds = milliseconds%3600000;
            showText[i] = hours+' Hours'; 
        }else if(flags[i] == 'i'){
            minutes = Math.floor(milliseconds/60000);
            milliseconds = milliseconds%60000;
            showText[i] = minutes+' Minutes'; 
        }else if(flags[i] == 's'){
            seconds = Math.floor(milliseconds/1000);
            showText[i] = seconds+' Seconds'; 
        }
    }
    document.getElementById(id).innerHTML=showText.join(', ');
    t=setTimeout(function(){timeSinceTimer(since, format, id)},500)
}