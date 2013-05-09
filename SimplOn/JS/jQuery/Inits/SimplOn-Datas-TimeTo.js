/*
 * \SimplOn\Datas\TimeTo.js
 * Author: Cristopher Carlos Mendoza Rojas
 * Date: April 6 2013
 */
$(function() {
	$('input.SimplOn.Data.TimeTo.years, input.SimplOn.Data.TimeTo.months, input.SimplOn.Data.TimeTo.weeks, input.SimplOn.Data.TimeTo.days').datepicker({minDate: "1D"});
	$('input.SimplOn.Data.TimeTo.hours, input.SimplOn.Data.TimeTo.minutes, input.SimplOn.Data.TimeTo.seconds').AnyTime_picker({earliest: new Date(), format: "%m/%d/%Y %H:%i"});

});

function timeToTimer(date, format, id){
    var now = new Date();
    var to = new Date(date);
    var milliseconds = to.getTime() - now.getTime();
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
    t=setTimeout(function(){timeToTimer(to, format, id)},500)
}