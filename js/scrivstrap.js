
$(document).ready(function(){

	$('#scriv').css('display','none');
	$('#scrivshow').css('display','none');

	scrivNav();

	$('#scrivnav > ul > li').click(function() {
		// TODO use a jquery animation here to hide it and then show.
		$('#scrivshow').html('');

		var elementid = $(this).attr('id').split('-')[1];
		var startElement = $('#'+elementid);
		$(startElement).clone().appendTo('#scrivshow');

		var content = scrivGetContent(startElement); 

		$('#scrivshow').css('display','block');
	});
	
	$('#samplelink').click(function(){

		var url = $('#samplelink').attr("href");
		var windowName = "Docboot Sample";
		window.open(url, "_blank", "toolbar=no, location=no, directories=no, status=no, menubar=no");
		return false;
	});

});


/**
 * Get the content to display.
 * @param startId string - the element id to start retrieving content at.
 */
 // var scrivGetContent = function(startId) {
 var scrivGetContent = function(element) {

 	// loop through each element starting at startId.
 	var curElement = element.next();

 	if( ! $(curElement).is("h1") ) {
 		$(curElement).clone().appendTo('#scrivshow');
 		scrivGetContent($(curElement));
 	}

 }


/**
 * Generate navigation based on headings.
 */
 var scrivNav = function() { 

 	var nav = $('#scrivnav');
 	
 	$('h1').each(function(index) {

 		var listItem = document.createElement('li');
 		listItem.setAttribute('id', 'nav-'+$(this).attr('id'));
 		listItem.innerHTML = '<a href="#">'+$(this).text()+'</a>';

 		$('#scrivnav > ul').append(listItem);
 	});
 }

/**
 * Create elements
 */
 var scrivElement = function(title) {
 	
 	// link.setAttribute('href', 'mypage.htm');
 	
 }
