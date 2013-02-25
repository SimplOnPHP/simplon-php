var SimplOn = new function() {
	this.init = function() {
		this.context = window.document;
		this.initActions();
		this.initForms();
		
		$('input,button',this.context).first().focus();
	};
	
	this.initActions = function (context) {
		if(!context) context = window;
		$('.SimplOn.Data a.SimplOn.lightbox', context.document).click(function(e) {
			e.preventDefault();
            $('.SimplOnLightbox').removeClass('SimplOnLightbox');
			$(this).colorbox({
                iframe: true, 
                innerWidth: "80%", 
                innerHeight: "80%", 
                href: $(this).attr('href')
            }).closest('.SimplOn.Data').find('.SimplOn.input,.SimplOn.Container').addClass('SimplOnLightbox');
		});
		
		$('table.SimplOn a.SimplOn.Action.lightbox', context.document).click(function(e) {
			e.preventDefault();
            $('.SimplOnLightbox').removeClass('SimplOnLightbox');
			$(this).colorbox({
                iframe: true, 
                innerWidth: "80%", 
                innerHeight: "80%", 
                href: $(this).attr('href')
            }).closest('.SimplOn.tableRow').addClass('SimplOnLightbox');
		});
		
		$('a.SimplOn.ajax', context.document).click(function(e) {
            e.preventDefault();
            $('.SimplOnLightbox').removeClass('SimplOnLightbox');
			$.ajax({
				url: $(this).attr('href'),
				dataType: 'json',
				success: SimplOn.ajaxHandler,
				error: function() {
					alert('Error en recibir los datos!');
				}
			});
			$(this).closest('.SimplOn.tableRow').addClass('SimplOnLightbox');
		});
		
		$('a.SimplOn.SelectAction', context.document).click(function(e) {
			e.preventDefault();
			$.ajax({
				dataType: 'json',
				url: $(this).attr('href'),
				success: SimplOn.ajaxHandler,
                error: function() {
                    alert('Error en recibir los datos!');
                }
			});
		});
        
        $('a.SimplOn.delete', context.document).click(function(e) {
            e.preventDefault();
            SimplOn.commands.changeValue('', $(this).closest('.SimplOn.Data').find('.SimplOn.input'));
            SimplOn.commands.changeHtml('', $(this).closest('.SimplOn.preview'));
        });
	};
	
	this.initForms = function () {
		if(parent !== window) {
			$('form.SimplOn.create, form.SimplOn.update, form.SimplOn.delete').each(function() {
				$(this).ajaxForm({
					url: $(this).attr('action'),
					dataType: 'json',
					success: SimplOn.ajaxHandler,
                    error: function() {
                        alert('Error en recibir los datos!');
                    }
				});
			});

			$('.SimplOn.showSearch .SimplOn.selectAction').click(function() {
				$(this).ajaxForm({
					url: $(this).attr('action'),
					dataType: 'json',
					success:  SimplOn.ajaxHandler,
                    error: function() {
                        alert('Error en recibir los datos!');
                    }
                        /*function(data) {
						$('[SimplOnReference='+window.location.hash.substring(1)+']', parent.document).val(data.id)
							.siblings('.preview').html(data.preview);
						parent.$.colorbox.close();
					}*/
				});
			});
            
            $('button.SimplOn.cancel-form').click(function(e){
               e.preventDefault();
               SimplOn.commands.closeLightbox();
            });
		} else {
            $('button.SimplOn.cancel-form').click(function(e){
               e.preventDefault();
               window.history.back();
            });
        }

		$('.SimplOn.showSearch form.SimplOn.search').each(function() {
			var $list = $(this).siblings('.SimplOn.list');
			$(this).ajaxForm({
				url: $(this).attr('action'),
				dataType: 'html',
				context: $list,
				success: function(data) {
					$(this).html(data);
				},
                error: function() {
                    alert('Error en recibir los datos!');
                }
			});
		});
		
                $('form.SimplOn.create, form.SimplOn.update').validate();
	};
	
	this.ajaxHandler = function(r) {
		/* example *
		r = {
			status: true,
			type: 'commands',
			data: [{
				func: 'changeValue',
				args: ['hola']
			},{
				func: 'changePreview',
				args: ['<h1>hola '+Math.floor(Math.random()*100)+'</h1>']
			},{
				func: 'closeLightbox',
				args: []
			}]
		};
		/* */
		if(r.status) {
			switch(r.type) {
				case 'commands':
					for(var i=0; i<r.data.length; i++) {
						if(SimplOn.commands.hasOwnProperty(r.data[i].func)) {
							SimplOn.commands[r.data[i].func].apply(window, r.data[i].args ? r.data[i].args : []);
                        }
					}
					break;
			}
		}
		if(r.message) {
			alert(r.message);
		}
	}
	
	this.commands = {
		changeHtml: function (content, $element, context) {
			if(!context) context = window;
			$($element).html(content);
            context.SimplOn.initActions(context);
		},
		removeHtml: function ($element, context) {
			if(!context) context = parent;
            $element = !$element
				? $element = $('.SimplOnLightbox', context.document)
				: $($element);
			$element.remove();
		},
		closeLightbox: function (context) {
			if(!context) context = parent;
            context.$.colorbox.close();
		},
		
		changePreview: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.SimplOnLightbox', context.document).closest('.SimplOn.ElementContainer').find('.preview');
            else $element = $($element);
            
			SimplOn.commands.changeHtml(content, $element, context);
		},
		changeValue: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.SimplOnLightbox', context.document);
            else $element = $($element);
            
			$element.val(content);
		},
		appendContainedElement: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.SimplOnLightbox', context.document);
            else $element = $($element);
            
			$element.append(content);
		},
		prependContainedElement: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.SimplOnLightbox', context.document);
            else $element = $($element);
            
			$element.prepend(content);
		}
	};
	
	this.utils = {
		scheduler: function () {
			return {
				running: false,
				events: [],
				add: function(context, func, args) {
					this.events.push({context: context, func: func, args: args});
					if(!this.running)
						this.run();
				},
				run: function() {
					var ret = null;

					this.running = true;
					if(this.events.length) {
						var event = this.events.shift();
						ret = event.func.apply(event.context, event.args);
					}
					this.running = false;

					return ret;
				}
			};
		}
	}
};

$(function(){
	SimplOn.init();
});
