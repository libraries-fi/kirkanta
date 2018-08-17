import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

document.querySelectorAll("textarea.richtext").forEach((input) => {
  let editor = ClassicEditor.create(input, {
    toolbar: ["heading", "|", "bold", "italic", "blockQuote", "|", "link", "|", "numberedList", "bulletedList"]
  });

  editor.then(editor => {

    console.log(Array.from( editor.ui.componentFactory.names() ));

  });

















  // ckeditor.replace(input, {
  //   customConfig: false,
  //   defaultLanguage: "fi",
  //   language: document.documentElement.lang,
  //   toolbarGroups: [
  // 		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
  // 		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
  // 		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
  // 		{ name: 'links', groups: [ 'links' ] },
  // 		{ name: 'insert', groups: [ 'insert' ] },
  // 		{ name: 'forms', groups: [ 'forms' ] },
  // 		{ name: 'tools', groups: [ 'tools' ] },
  // 		{ name: 'others', groups: [ 'others' ] },
  // 		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
  // 		{ name: 'colors', groups: [ 'colors' ] },
  // 	],
  //   removeButtons: 'Underline,Subscript,Superscript,Cut,Undo,Scayt,HorizontalRule,Maximize,Copy,Paste,PasteText,PasteFromWord,Redo',
  //
  //   // Disable umlaut encoding.
  //   entities: false,
  // });
});
