/**
 * Class RefreshCache
 *
 * Refresh the XMl files on an AJAX loop
 */
var RefreshCache =
{
	/**
	 * Start the AJAX process
	 * @param string
	 * @return boolean
	 */
	startRefresh: function(el, offset, startTxt, endTxt)
	{
		if(offset==0)
		{
			document.id('refresh-cache').set('html','<div id="tl_header">'+startTxt+'</div><div id="message"></div>');
			this.spinner = new Spinner(document.id('tl_header'),{containerPosition: {position:'centerBottom', offset:{x:-12}}});
			this.spinner.show();
		}
		var request = new Request.Contao(
		{
			url: window.location.href,
			data: 'isAjax=1&action=startCache&offset=' + offset,
			onComplete: function(obj, txt)
			{
				if(obj)
				{
					if (obj.offset != 'finished')
					{
						RefreshCache.startRefresh(el,obj.offset);
						document.id('message').set('html',obj.message);
					}
					else
					{
						document.id('refresh-cache').set('html','<div id="tl_header">'+obj.message+'</div>');
						this.spinner.destroy();
					}
				}
				else
				{
					RefreshCache.getError(txt);
				}
			}.bind(this)
		}).send();
	},

	getError: function(error)
	{
		if(error)
		{
			document.id('refresh-cache').set('html','<div class="tl_error">'+ error +'</div>');
		} else
		{
			alert("If you are seeing this message, something went really wrong during the import, like a database connection failure, or a page refresh or something. It shouldn't happen, but if it does, you will want to start over.");
			document.id('refresh-cache').set('html','<div class="tl_error">An Error Occurred</div>');
		}
	}

}
