(function() {

  var $searchForm = $('form[action="/zoco/search"]');

  $searchForm.on('submit', function(evt) {
    var $fields = $searchForm.find('[name]');
    $fields.each(function(i, el) {
      var $el = $(el);
      if(!$el.val()) $el.attr('disabled', true);
    });
  });

  $('#advanced.collapse.in').each(expandSearchInput);

  $('#advanced').on('show.bs.collapse', expandSearchInput);
  $('#advanced').on('hide.bs.collapse', shrinkSearchInput);


  function expandSearchInput() {
    var $actualSearch = $('input[name=q]');
    var $expandedSearch = $('<textarea style="height:200px;"></textarea>');
    copyAttributes($actualSearch, $expandedSearch);
    $expandedSearch.attr('type', null);
    $expandedSearch.val($actualSearch.val());
    $actualSearch.replaceWith($expandedSearch);
  }

  function shrinkSearchInput() {
    var $expandedSearch = $('textarea[name=q]');
    var $normalSearch = $('<input type="text"></textarea>');
    copyAttributes($expandedSearch, $normalSearch);
    $normalSearch.attr('style', null);
    $normalSearch.val($expandedSearch.val());
    $expandedSearch.replaceWith($normalSearch);
  }

  function copyAttributes($source, $target, blacklist) {
    $.each($source.prop('attributes'), function() {
      $target.attr(this.name, this.value);
    });
  }

}());
