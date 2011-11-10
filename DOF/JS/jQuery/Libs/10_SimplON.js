$(function() {
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
	
	$('a.lightbox').click(function(e) {
		e.preventDefault();
		var $this = $(this);
		var href = $this.attr('href') + '#' + $this.siblings('.input').attr('id');
		$this.colorbox({iframe: true, innerWidth: "80%", innerHeight: "80%", href: href});
	});
	
});