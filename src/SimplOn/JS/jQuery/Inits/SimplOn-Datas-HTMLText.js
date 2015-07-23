bkLib.onDomLoaded(function() { 
   $('textarea.SimplOn.Data.HTMLText').each(function(){
       var idt = $(this).attr('id');
       new nicEditor().panelInstance(idt);
   });
});

