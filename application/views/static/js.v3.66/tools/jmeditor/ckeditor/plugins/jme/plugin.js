CKEDITOR.plugins.add( 'jme', {
    icons: 'jme',
    init: function( editor ) {
        // Plugin logic goes here...
        editor.addCommand( 'jmeDialog', new CKEDITOR.dialogCommand( 'jmeDialog' ) );
        editor.ui.addButton( 'jme', {
				    label: '公式编辑器',
				    command: 'jmeDialog',
				    toolbar: 'insert'
				});
				CKEDITOR.dialog.add( 'jmeDialog', this.path + 'dialogs/jme.js' );
				
    }
});
