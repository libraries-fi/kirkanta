class TimeoutLock {
  constructor() {
    this.last = null;
  }

  wait(ms) {
    return new Promise((resolve, reject) => {
      let tid = this.last = setTimeout(() => {
        if (this.last == tid) {
          this.last = null;
          resolve();
        }
      }, ms);
    });
  }
}

$.fn.sluggable = function() {
  return this.each((i, input) => {
    let target = input.name.replace(/\bslug\b/, input.dataset.slugSource);
    let langcode = input.dataset.slugLangcode;
    let handler = input.dataset.slugUrl;
    let lock = new TimeoutLock;

    console.log(input.dataset);

    $(`input[name="${target}"]`).on("input", (event) => {
      lock.wait(300).then(() => {
        $.get(handler, {name: event.target.value, langcode}).then((result) => {
          input.value = result;
        });
      });
    })
  });
};

$('input[type="text"][data-sluggable]').sluggable();
