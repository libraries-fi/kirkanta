import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

document.querySelectorAll("textarea.richtext").forEach((input) => {
  let editor = ClassicEditor.create(input, {
    toolbar: ["heading", "|", "bold", "italic", "blockQuote", "|", "link", "|", "numberedList", "bulletedList"]
  });

  // editor.then(editor => {
  //   console.log(Array.from( editor.ui.componentFactory.names() ));
  // });
});
