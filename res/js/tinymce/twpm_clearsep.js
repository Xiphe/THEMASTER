(function() {
	tinymce.create('tinymce.plugins.twpmClearSep', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var disabled = true,
				icon = window.location.href.split('wp-admin')[0]+'wp-content/plugins/_themaster/res/img/tinymce/hrclear.png',
				node;

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('twpmClearSepCMD', function() {
				if ( disabled )
					return;
				ed.dom.add(node, 'hr', {'class' : 'sep clear'});
			});

			// Register example button
			ed.addButton('twpm_clearsep', {
				title : 'Clear - Seperator',
				cmd : 'twpmClearSepCMD',
				image : icon
			});

			ed.onNodeChange.add(function(ed, cm, n, co) {
				node = n;
				disabled = !co;
			});
		},
		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : '!THE MASTER - Clear-Seperator',
				author : 'Xiphe',
				authorurl : 'https://github.com/Xiphe/',
				infourl : '',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('twpm_clearsep', tinymce.plugins.twpmClearSep);
})();