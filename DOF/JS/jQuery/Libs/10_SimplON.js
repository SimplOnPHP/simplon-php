var SimplOn = new function() {
	this.init = function() {
		this.initActions();
		this.initForms();
	};
	
	this.initActions = function () {
		$('a.lightbox').click(function(e) {
			e.preventDefault();
			var $this = $(this);
			var href = $this.attr('href') + '#' + $this.siblings('.input').attr('id');
			$this.colorbox({iframe: true, innerWidth: "80%", innerHeight: "80%", href: href});
		});
		
		$('.SimplOn.SelectAction').click(function(e) {
			e.preventDefault();
			var $this = $(this);
			$.ajax({
				url: $this.attr('href'),
				success: SimplOn.ajaxHandler
			});
		});
	};
	
	this.initForms = function () {
		if(parent !== window) {
			$('form.SimplOn.create, form.SimplOn.update').each(function() {
				$(this).ajaxForm({
					url: $(this).attr('action')+'/"json"',
					dataType: 'json',
					success: function(data) {
						$(window.location.hash, parent.document).val(data.id)
							.siblings('.preview').html(data.preview);
						parent.$.colorbox.close();
					}
				});
			});

			$('.SimplOn.showSearch .SimplOn.selectAction').click(function() {
				$(this).ajaxForm({
					url: $(this).attr('action')+'/"json"',
					dataType: 'json',
					success: function(data) {
						$(window.location.hash, parent.document).val(data.id)
							.siblings('.preview').html(data.preview);
						parent.$.colorbox.close();
					}
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
				}
			});
		});
	};
	
	this.ajaxHandler = function(r) {
		/* example */
		r = {
			status: true,
			type: 'functions call',
			data: [{
				func: 'changeValue',
				args: ['hola']
			},{
				func: 'changePreview',
				args: ['<h1>hola '+Math.floor(Math.random()*100)+'</h1>','home']
			},{
				func: 'closeLightbox',
				args: []
			}]
		};
		/* */
		if(r.status) {
			switch(r.type) {
				case 'functions call':
					for(var i=0; i<r.data.length; i++) {
						if(SimplOn.hasOwnProperty(r.data[i].func))
							SimplOn[r.data[i].func].apply(window, r.data[i].args);
					}
					break;
			}
		}
		if(r.message) {
			alert(r.message);
		}
	}
	
	this.changePreview = function (content, element, selector) {
		var id = window.location.hash;
		$(id, parent.document).closest('.SimplOn.'+element).find('.preview').html(content);
	};
	
	this.changeValue = function (content, element, selector) {
		var id = window.location.hash;
		$(id, parent.document).val(content);
	};
	
	this.closeLightbox = function () {
		parent.$.colorbox.close();
	};
};

$(function(){SimplOn.init();});