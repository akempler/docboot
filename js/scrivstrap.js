
$(document).ready(function(){

  scrivstrapConfig = {htags: $("H1, H2"), validTags: ["H1", "H2"]}

  $('#scriv').css('display','none');
  $('#scrivshow').css('display','none');

  // Generate the navigation and get the default link to click 
  // so content is shown when the page is loaded.
  var defaultLink = scrivNav();
  
  $('#scrivnav > ul > li').click(function() {
    $('#scrivshow').html('');

    var title = $(this).text().replace(" - ", "").trim();
    $('#article-title').html(title);
    
    var elementid = $(this).attr('id').split('-')[1];
    var startElement = $('#'+elementid);
    
    var next = getNextIndex(elementid);

    $(startElement).nextUntil(scrivstrapConfig.htags[next]).clone().appendTo('#scrivshow');
    
    $('#scrivshow').css('display','block');
  });
  
  $(defaultLink).trigger("click");

});


function getNextIndex(id) {
  var next = 0;
  
  scrivstrapConfig.htags.each(function(index) {
    if($(this).attr('id') == id) {
      next = index + 1;
    }
  });
  return next;
}


/**
 * Generate navigation based on headings.
 * 
 * todo keep track of the index.
 * if it is bigger than add another ul li.
 * if less then end them.
 */
 var scrivNav = function() { 
   
   var defaultId = "";
   // heading tags with these ids will be excluded from the nav.
   var exclude = ["page-heading", "article-title"];

   var x = 1;
   scrivstrapConfig.htags.each(function(index) {
     var tagname = $(this).prop("tagName");
     
     // if the tag is in the array of tags.
     if (jQuery.inArray(tagname, scrivstrapConfig.validTags) != -1) {
       
       // if the tag is NOT in the exclude array add it to the nav.
       if (jQuery.inArray($(this).attr('id'), exclude) == -1) {
         
         var listItem = document.createElement('li');
         listItem.setAttribute('id', 'nav-'+$(this).attr('id'));
         
         var indent = "";
         if(tagname == "H2") {
           indent = "&nbsp; - ";
         }
         
         // If the next element is an h tag in the tags array,
         // then don't make a link because there is no content to display.
         var nextElement = $(this).next();
         var nextTag = nextElement.prop("tagName");
         if (jQuery.inArray(nextTag, scrivstrapConfig.validTags) != -1) {
           listItem.innerHTML = indent + $(this).text();
         } else {
           listItem.innerHTML = '<a href="#">' + indent + $(this).text()+'</a>';
         }
         
         $('#scrivnav > ul').append(listItem);
         
         if(x == 1){
           defaultId = '#nav-'+$(this).attr('id');
         }
         x++;
       }
     };
   });
   
   // The element id to use as the default content to display.
   return defaultId;
 }
