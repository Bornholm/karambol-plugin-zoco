(function() {

  var $searchForm = $('form[action="/zoco/search"]');

  $searchForm.on('submit', function(evt) {
    var $fields = $searchForm.find('[name]');
    $fields.each(function(i, el) {
      var $el = $(el);
      if(!$el.val()) $el.attr('disabled', true);
    });
  });

}());
