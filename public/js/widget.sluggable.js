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
  this.each((i, input) => {
    let target = input.name.replace(/\bslug\b/, input.dataset.slugSource);
    let langcode = input.dataset.slugLangcode;
    let handler = input.dataset.slugUrl;
    let lock = new TimeoutLock;

    $(`input[name="${target}"]`).on("input", (event) => {
      lock.wait(300).then(() => {
        $.post(handler, {name: event.target.value, langcode}).then((result) => {
          input.value = result;
        });
      });
    })
  });

  this.on("input", (event) => {
    console.log("INPUT", event.target.value);
  });
};

$('input[type="text"][data-sluggable]').sluggable();
