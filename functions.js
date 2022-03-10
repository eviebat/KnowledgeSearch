// Automatic json serialization of form data
// Ref: http://jsfiddle.net/sxGtM/3/
$.fn.serializeObject = function()
{
	var o = {};
	var a = this.serializeArray();
	$.each(a, function() {
		if (o[this.name] !== undefined) {
			if (!o[this.name].push) {
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || '');
		} else {
			o[this.name] = this.value || '';
		}
	});
	return o;
};


// Reset submit box
function resetResultPanel() {
	if ( $("#resultPanel").attr('class') != "panel panel-default" ) {				
		$( "#result" ).empty()
		$("#resultPanel").removeClass($("#resultPanel").attr('class'));
		$("#resultPanel").addClass("panel panel-default");
	}
}