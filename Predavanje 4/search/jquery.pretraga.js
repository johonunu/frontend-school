(function($) {

$.fn.pretraga = function(opcije) {

	var podesavanja = $.extend({
		bojaPozadine: "yellow",
		bojaFonta: "black",
		tag: 'p'
	},opcije);

	this.after('<button>Search</button>');

	var plugin = this
	

	this.next().click(function(){

		var kljucnaRec = plugin.val();
		var reg = new RegExp(kljucnaRec,"gi");

		$(podesavanja.tag+' span').removeAttr('style');

		$(podesavanja.tag).each(function(){
			var tekst = $(this).html();
			tekst = tekst.replace(reg,'<span style="color:'+podesavanja.bojaFonta+';background:'+podesavanja.bojaPozadine+'">'+kljucnaRec+"</span>");
			$(this).html(tekst);
		});

	});

	

	return this;
   
};

}(jQuery));
