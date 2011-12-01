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
		
		$('.DOF.SelectAction').click(function(e) {
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
			$('form.DOF.create, form.DOF.update').each(function() {
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

			$('.DOF.showSearch .DOF.selectAction').click(function() {
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

		$('.DOF.showSearch form.DOF.search').each(function() {
			var $list = $(this).siblings('.DOF.list');
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
				func: 'insert',
				args: ['hola']
			},{
				func: 'close',
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
	
	this.insert = function (content, element, selector) {
		//alert(Array.prototype.slice.call(arguments).join(', '));
		var id = window.location.hash;
		$(id, parent.document).val(content);
	};
	this.close = function () {
		parent.$.colorbox.close();
	};
};

$(function(){SimplOn.init();});