(function($) {
  "use strict";

  function toggle_active_language(event) {
    var langcode = event.currentTarget.value;

    console.log(langcode);

    if (langcode) {
      $('[data-app-group="content-language"]').addClass('collapsed');
      $('[data-content-language]').hide();
      $('[data-content-language="' + langcode + '"]').show();
    } else {
      $('[data-content-language]').show();
      $('[data-app-group="content-language"]').removeClass('collapsed');
    }
  }

  $("[data-app=\"content-language\"]").each(function(i, input) {
    $(input).on("change", toggle_active_language);
  });
}(jQuery));
