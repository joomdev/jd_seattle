(function() {
	var a= {
		exec:function(editor){
			if (parent.IeCursorFix)
			{
				parent.IeCursorFix();
			}
			if (parent.SetIgnoreDeselection)
			{
				parent.SetIgnoreDeselection();
			}
			if (parent.FireClick)
			{
				var itemElement = parent.document.getElementById('AcyLienTag');
				parent.FireClick(itemElement);
			}
		}
	},
	b='addtag';
	CKEDITOR.plugins.add(b,{
		init:function(editor){
			editor.addCommand(b,a);
			editor.ui.addButton("addtag",{label:editor.lang.addtag.toolbar,
											icon: this.path + "icon-16-tag.png",
											command:b,
											toolbar: "insert"});
		}
	});
})();

