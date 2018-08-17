(function($) {
  "use strict";

  function toggle_active_language(form, langcode) {
    form = $(form);

    if (langcode) {
      $('[data-app-group="content-language"]', form).addClass('collapsed');
      $('[data-content-language]', form).hide();
      $('[data-content-language="' + langcode + '"]', form).show();
    } else {
      $('[data-content-language]', form).show();
      $('[data-app-group="content-language"]', form).removeClass('collapsed');
    }
  }

  $("[data-app=\"content-language\"]").each(function(i, input) {
    $(input).on("change", () => toggle_active_language(input.form, input.value));

    // Expand translatable fields to allow native error popups to show up.
    $(input.form).find(":input").on("invalid", (event) => {
      toggle_active_language(input.form, null);
    });
  });
}(jQuery));
