$("<link/>", {
   rel: "stylesheet",
   type: "text/css",
   href: "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css"
}).appendTo("head");

$.getScript( "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js", function( data, textStatus, jqxhr ) {
  console.log( "Load was performed." );
});

$('.field-dropdown').data("show-subtext",true);
$('.field-dropdown').data("live-search",true);

$('.field-dropdown').selectpicker({
  style: 'btn-info'
});


