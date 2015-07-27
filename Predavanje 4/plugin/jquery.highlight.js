(function($){
	
	$.fn.highlight = function(options){

		var settings = $.extend({
			keyword:'',
			class:'highlight'
		},options);

		var reg = new RegExp(settings.keyword, 'g');

		$.each(this, function() {
			var text = $(this).html();
			text = text.replace(reg,'<span class="'+settings.class+'">'+settings.keyword+'</span>');
			$(this).html(text);
		});

		return this;

	};

}(jQuery));