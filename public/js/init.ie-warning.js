if (navigator.userAgent.match(/Trident/)) {
  let langcode = document.documentElement.lang;
  let translations = {
    fi: "Käytät vanhentunutta selainta – suosittelemme vaihtoa toiseen selaimeen",
    en: "Your web browser is too old -- you should switch to another browser",
    sv: "Gammal webbläsare – använd en annan webbläsare",
  };
  const message = translations[langcode];
  const banner = $("<div/>").addClass("alert alert-danger")
    .append('<i class="fa fa-exclamation-circle mr-3"/>')
    .append($("<span/>").text(message))
    ;

  $("main").prepend(banner);
}
