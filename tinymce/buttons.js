(function() {
  tinymce.PluginManager.add( 'ubiliz_gifts', function( editor, url ) {
    editor.addButton('ubiliz_gifts', {
      title: 'Ubiliz : Cadeaux',
      cmd: 'ubiliz_gifts',
      image: url + '/logo-ubiliz-black.svg',
    });
    editor.addCommand('ubiliz_gifts', function() {
      editor.execCommand('mceInsertContent', false, '[UBILIZ]');
    });
  });
})();