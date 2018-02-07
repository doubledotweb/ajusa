$(document).ready(function()
{
	$("#decline").on("click",function(e)
	{
		e.preventDefault();
		$("#message").children().remove();
		$("#box_options").children().remove();
		$("#popup").hide();

	})
});