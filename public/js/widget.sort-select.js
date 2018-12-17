/**
 * Quick hack to sort contents of <select> boxes.
 *
 * It's fairly expensive to do in Symfony, too, as labels
 * are translated in the Twig template and hence sorting cannot
 * occur before.
 */

document.querySelectorAll("select").forEach((select) => {
  const options = Array.from(select.children).sort((a, b) => {
    return a.text.localeCompare(b.text, 'fi');
  });

  options.forEach((option, i) => {
    // Keep placeholder options at the top.
    if (option.value) {
      select.appendChild(option);
    }
  });
});
