(function(config) {

  if(!config || !config.pinEntryTemplateUrl) {
    throw new Error('The config object is not correctly initialized !');
  }

  var actionSelectors = {
    pin: '[data-entry-type][data-entry-id][data-entry-action=pin]',
    unpin: '[data-entry-type][data-entry-id][data-entry-action=unpin]'
  };

  $(document.body).on('click', actionSelectors.pin, function(evt) {

    var $el = $(this);
    var entryType = $el.data('entryType');
    var entryId = $el.data('entryId');
    var actionUrl = config.pinEntryTemplateUrl
      .replace('__entryId__', entryId)
      .replace('__entryType__', entryType)
    ;

    $.ajax({
        method: 'POST',
        url: actionUrl
      })
      .then(function(res) {
        if(!res || res.result !== 'OK') throw new Error('Invalid AJAX response from server.');
        $el.data('entryAction', 'unpin')
          .attr('data-entry-action', 'unpin')
          .removeClass('btn-primary', 'btn-default')
          .addClass('btn-warning')
        ;
      })
      .fail(function(err) { throw err; })
    ;

  });

  $(document.body).on('click', actionSelectors.unpin, function(evt) {

    var $el = $(this);
    var entryType = $el.data('entryType');
    var entryId = $el.data('entryId');
    var actionUrl = config.pinEntryTemplateUrl
      .replace('__entryId__', entryId)
      .replace('__entryType__', entryType)
    ;

    $.ajax({
        method: 'DELETE',
        url: actionUrl
      })
      .then(function(res) {
        if(!res || res.result !== 'OK') throw new Error('Invalid AJAX response from server.');
        $el.data('entryAction', 'pin')
          .attr('data-entry-action', 'pin')
          .removeClass('btn-warning')
          .addClass('btn-default')
        ;
      })
      .fail(function(err) { throw err; })
    ;

  });

}(this.__config__ = this.__config__ || {}));
