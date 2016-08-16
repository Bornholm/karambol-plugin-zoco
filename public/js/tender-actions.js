(function(config) {

  if(!config || !config.pinTenderTemplateUrl) {
    throw new Error('The config object is not correctly initialized !');
  }

  var actionSelectors = {
    pin: '[data-tender-type][data-tender-id][data-tender-action=pin]',
    unpin: '[data-tender-type][data-tender-id][data-tender-action=unpin]'
  };

  $(document.body).on('click', actionSelectors.pin, function(evt) {

    var $el = $(this);
    var tenderType = $el.data('tenderType');
    var tenderId = $el.data('tenderId');
    var actionUrl = config.pinTenderTemplateUrl
      .replace('__tenderId__', tenderId)
      .replace('__tenderType__', tenderType)
    ;

    $.ajax({
        method: 'POST',
        url: actionUrl
      })
      .then(function(res) {
        if(!res || res.result !== 'OK') throw new Error('Invalid AJAX response from server.');
        $el.data('tenderAction', 'unpin')
          .attr('data-tender-action', 'unpin')
          .removeClass('btn-primary', 'btn-default')
          .addClass('btn-warning')
        ;
      })
      .fail(function(err) { throw err; })
    ;

  });

  $(document.body).on('click', actionSelectors.unpin, function(evt) {

    var $el = $(this);
    var tenderType = $el.data('tenderType');
    var tenderId = $el.data('tenderId');
    var actionUrl = config.pinTenderTemplateUrl
      .replace('__tenderId__', tenderId)
      .replace('__tenderType__', tenderType)
    ;

    $.ajax({
        method: 'DELETE',
        url: actionUrl
      })
      .then(function(res) {
        if(!res || res.result !== 'OK') throw new Error('Invalid AJAX response from server.');
        $el.data('tenderAction', 'pin')
          .attr('data-tender-action', 'pin')
          .removeClass('btn-warning')
          .addClass('btn-default')
        ;
      })
      .fail(function(err) { throw err; })
    ;

  });

}(this.__config__ = this.__config__ || {}));
