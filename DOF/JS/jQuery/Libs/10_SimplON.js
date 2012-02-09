var SimplOn = new function() {
	this.init = function() {
        this.context = window.document;
		this.initActions();
		this.initForms();
	};
	
	this.initActions = function (context) {
		if(!context) context = window;
		$('.SimplOn.Data a.SimplOn.lightbox', context.document).click(function(e) {
			e.preventDefault();
            $('.SimplOnLightbox').removeClass('SimplOnLightbox');
			var $this = $(this);
            $this.closest('.SimplOn.Data').find('.SimplOn.input').addClass('SimplOnLightbox');
			$this.colorbox({
                iframe: true, 
                innerWidth: "80%", 
                innerHeight: "80%", 
                href: $this.attr('href')
            });
		});
		
		$('.SimplOn.SelectAction', context.document).click(function(e) {
			e.preventDefault();
			var $this = $(this);
			$.ajax({
				dataType: 'json',
				url: $this.attr('href'),
				success: SimplOn.ajaxHandler,
                error: function() {
                    alert('Error en recibir los datos!');
                }
			});
		});
        
        $('.SimplOn.delete', context.document).click(function(e) {
            e.preventDefault();
            SimplOn.commands.changeValue('', $(this).closest('.SimplOn.Data').find('.SimplOn.input'));
            SimplOn.commands.changeHtml('', $(this).closest('.SimplOn.preview'));
        });
	};
	
	this.initForms = function () {
		if(parent !== window) {
			$('form.SimplOn.create, form.SimplOn.update').each(function() {
				$(this).ajaxForm({
					url: $(this).attr('action'),
					dataType: 'json',
					success: SimplOn.ajaxHandler,
                    error: function() {
                        alert('Error en recibir los datos!');
                    }
                        /*
                    function(data) {
						$('[SimplOnReference='+window.location.hash.substring(1)+']', parent.document).val(data.id)
							.siblings('.preview').html(data.preview);
						parent.$.colorbox.close();
					}*/
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
		closeLightbox: function (context) {
			if(!context) context = parent;
            context.$.colorbox.close();
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

$(function(){SimplOn.init();});