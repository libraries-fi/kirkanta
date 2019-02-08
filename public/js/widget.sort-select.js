/**
 * Quick hack to sort contents of <select> boxes.
 *
 * It's fairly expensive to do in Symfony, too, as labels
 * are translated in the Twig template and hence sorting cannot
 * occur before.
 */

document.querySelectorAll("select").forEach((select) => {
  if (select.dataset.noSort === undefined) {
    const options = Array.from(select.children);
    const separator = select.querySelector('[disabled]');
    const preferredChoicesUntil = options.indexOf(separator);
    const sortableChildren = options.slice(preferredChoicesUntil + 1);

    sortableChildren.sort((a, b) => {
      if (a.tagName == 'OPTGROUP') {
        return -1
      } else if (b.tagName == 'OPTGROUP') {
        return 1;
      }
      return a.text.localeCompare(b.text, 'fi', { numeric: true });
    });

    sortableChildren.forEach((option, i) => {
      // Keep placeholder options at the top.
      if (option.value) {
        select.appendChild(option);
      }
    });
  }
});
