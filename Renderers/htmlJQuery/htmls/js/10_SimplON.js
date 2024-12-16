
var SimplOn = new function() {
	this.init = function() {
		this.context = window.document;
		this.initActions();
		this.initForms();
		this.paging();
		$('input,button',this.context).first().focus();
	};
	
	this.paging = function(){
		$(".SimplOn_pag").on('click',function(e){
			var link, action;
			e.preventDefault();
			link = $(this).attr('href');
			action = $('form').attr('action');
			$('form').attr('action',action+link);
			$('form').submit();
		});
	}

	this.initActions = function (context) {
		if(!context) context = window;
		/*
		*/
		$('.ElementBox .lightbox', context.document).click(function(e) {
			e.preventDefault();
			$('.active').removeClass('active');
			$(this).colorbox({
                iframe: true, 
                innerWidth: "80%", 
                innerHeight: "80%", 
                href: $(this).attr('href')
            })
			.closest('.ElementBox').addClass('active');
		});
		// $('table.SimplOn a.SimplOn.Action.lightbox', context.document).click(function(e) {
		// 	e.preventDefault();
        //     $('.lightbox').removeClass('lightbox');
		// 	$(this).colorbox({
        //         iframe: true, 
        //         innerWidth: "80%", 
        //         innerHeight: "80%", 
        //         href: $(this).attr('href')
        //     }).closest('.SimplOn.tableRow').addClass('lightbox');
		// });
	
		$('a.Ajax', context.document).click(function(e) {
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

		$('a.SimplOn.ajax', context.document).click(function(e) {
            e.preventDefault();
            $('.lightbox').removeClass('lightbox');
			$.ajax({
				url: $(this).attr('href'),
				dataType: 'json',
				success: SimplOn.ajaxHandler,
				error: function() {
					alert('Error en recibir los datos!');
				}
			});
			$(this).closest('.SimplOn.tableRow').addClass('lightbox');
		});
		
		$('a.SimplOn.SD_SelectAction', context.document).click(function(e) {
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
        
        $('a.SimplOn.SD_delete', context.document).click(function(e) {
            e.preventDefault();
            //SimplOn.commands.changeValue('', $(this).closest('.containedElement').find('.input'));

			//RSL 2022  to show again the element container options
			if($(this).closest('.ElementBox').children('.containedElement').size() == 0){
				SimplOn.commands.show($(this).closest('.ElementBox').find('.options'));
			}
	
			$(this).closest('.containedElement').remove();
            
           //SimplOn.commands.changeHtml('', $(this).closest('.ElementBox .preview'));
        });



	};
	
	this.initForms = function () {
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

			$('.SimplOn.showSearch .SimplOn.SD_selectAction').click(function() {
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
		if(parent !== window) {
            $('button.SimplOn.cancel-form').click(function(e){
               e.preventDefault();
               SimplOn.commands.closeLightbox();
            });
		} else {
            $('button.SimplOn.cancel-form').click(function(e){
               e.preventDefault();
               //window.history.back();
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
		
		
		// Disable form fields with the class 'disabled'
		$('.disabled').prop('disabled', true);
		// Disable form fields within a tag with the class 'disabled'
		$('.disabled').find('input, select, textarea, button').prop('disabled', true);
	


		$('form.SimplOn.create, form.SimplOn.update, form.SimplOn.User.search').validate();
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
		/*/
		//debugger;

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
		//*/
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
				? $element = $('.lightbox', context.document)
				: $($element);
			$element.remove();
		},
		replaceHtml: function (content, $element, context, id) {
			var exits;
			if(!context) context = parent;
			exits = $('.SimplOn.preview', context.document).hasClass(id);
			if(exits){
				if(!$element) $element = $('.SimplOn.preview.'+id, context.document);
				else $element = $($element);
				$element.replaceWith(content);
			} 
			context.SimplOn.initActions(context);
		},
		closeLightbox: function (context) {
			if(!context) context = parent;
			///RSL 2022
			//alert(context.$.colorbox);
			//debugger;
			//$('#t.colorbox').close();
            context.$.colorbox.close();
		},
		changePreview: function (content, $element, context) {

			if(!context) context = parent;
            if(!$element) $element = $('.active .preview', context.document);
            else $element = $($element);      
			$element.html(content);

			$inputName = $('.active .nameREF', context.document);
			$element.find('*[name="placeHolderName"]').attr('name',$inputName.val());

			//alert(.val() );
			//alert($inputName.val());

			$options = $('.active .options', context.document);
			SimplOn.commands.hide($options);
			context.SimplOn.initActions(context);
			
			// debugger;
			// alert('changePreview');
			// var $options;
			// if(!context) context = parent;
            // if(!$element) $element = $('.lightbox', context.document);
            // else $element = $($element);
            // if($element) $options = $('.lightbox', context.document).closest('.SimplOn.SD_ElementContainer').find('.options');
            
			// SimplOn.commands.changeHtml(content, $element, context);
			// if($options) {
			// 	SimplOn.commands.hide($options);
			// } 
		},
		hide: function ($element) {
			$element.hide();
		},
		show: function ($element) {
			$element.show();
		},
		alert: function (text) {
			this.alert(text);
		},
		changeValue: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.active input', context.document);
            else $element = $($element);          
			$element.val(content);
		},
		appendContainedElement: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.active', context.document);
            else $element = $($element);

			$elementBox = $element.closest('.ElementBox');
			$inputName = $elementBox.find('.nameREF', context.document);
			$elementBox.find('.preview').append(content);
			$elementBox.find('*[name="placeHolderName"]').attr('name',$inputName.val()+'[]');

			context.SimplOn.initActions(context);

		},
		prependContainedElement: function (content, $element, context) {
			if(!context) context = parent;
            if(!$element) $element = $('.active', context.document);
            else $element = $($element);
            
			context.SimplOn.initActions(context);
			$element.closest('.ElementBox').find('.preview').prepend(content);
		},
		showValidationMessages: function (field, error){
			var validator = $('form.SimplOn.create, form.SimplOn.update, form.SimplOn.User.search').validate();
			eval('validator.showErrors({"'+field+'": "'+error+'"})');
		},
		showSystemMessage: function (message){
			showMessage(message);
		},
		redirectNextStep: function(url){
			window.location.href= url;
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