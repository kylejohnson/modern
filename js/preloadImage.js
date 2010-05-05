/* jQuery.preloader - v0.95 - K Reeve aka BinaryKitten
 * *
 * * v0.95
 * * 	# Note - keeping below v1 as really not sure that I consider it public usable.
 * * 	# But it saying that it does the job it was intended to do.
 * * 	Added Completion of loading callback.
 * * 	Main Reworking With Thanks to Remy Sharp of jQuery for Designers
 * *
 * *
 * * v0.9
 * * 	Fixed .toString being .toSteing
 * *
 * * v0.8
 * *		Fixed sheet.href being null error (was causing issues in FF3RC1)
 * *
 * * v0.7
 * *		Remade the preLoadImages from jQuery to DOM
 * *
 * * v0.6
 * * 		Fixed IE6 Compatability!
 * *		Moved from jQuery to DOM
 * *
 * * v0.5
 * * 		Shifted the additionalimages loader in the preLoadAllImages so it wasn't called multiple times
 * * 		Created secondary .preLoadImages to handle additionalimages and so it can be called on itself
 * */

(function ($) {
	$.preLoadImages = function(imageList,callback) {
		var pic = [], i, total, loaded = 0;
		if (typeof imageList != 'undefined') {
			if ($.isArray(imageList)) {
				total = imageList.length; // used later
					for (i=0; i < total; i++) {
						pic[i] = document.createElement('img');
						pic[i].onload = function() {
							loaded++; // should never hit a race condition due to JS's non-threaded nature
							if (loaded == total) {
								if ($.isFunction(callback)) {
									callback();
								}
							}
						};
						pic[i].src = imageList[i];
					}
			}
			else {
				pic[0] = document.createElement('img');
				pic[0].onload = function() {
					if ($.isFunction(callback)) {
						callback();
					}
				}
				pic[0].src = imageList;
			}
		}
		pic = undefined;
	};

	$.preLoadCSSImages = function(callback) {
		var pic = [], i, imageList = [], loaded = 0, total, regex = new RegExp("url\((.*)\)",'i'),spl;
		var cssSheets = document.styleSheets, path,myRules,Rule,match,txt,img,sheetIdx,ruleIdx;
		for (sheetIdx=0;sheetIdx < cssSheets.length;sheetIdx++){
			var sheet = cssSheets[sheetIdx];
			if (typeof sheet.href == 'string' && sheet.href.length > 0) {
				spl = sheet.href.split('/');spl.pop();path = spl.join('/')+'/';
			}
			else {
				path = './';
			}
			myRules = sheet.cssRules ? sheet.cssRules : sheet.rules;
			for (ruleIdx=0;ruleIdx < myRules.length;ruleIdx++) {
				Rule = myRules[ruleIdx];
				txt = Rule.cssText ? Rule.cssText : Rule.style.cssText;
				match = regex.exec(txt);
				if (match != null) {
					img = match[1].substring(1,match[1].indexOf(')',1));
					if (img.substring(0,4) == 'http') {
						imageList[imageList.length] = img;
					}
					else if ( match[1].substring(1,2) == '/') {
						var p2 = path.split('/');p2.pop();p2.pop();p2x = p2.join("/");
						imageList[imageList.length] = p2x+img;
					}
					else {
						imageList[imageList.length] = path+img;
					}
				}
			};
		};

		total = imageList.length; // used later
		for (i=0; i < total; i++) {
			pic[i] = document.createElement('img');
			pic[i].onload = function() {
				loaded++; // should never hit a race condition due to JS's non-threaded nature
				if (loaded == total) {
					if ($.isFunction(callback)) {
						callback();
					}
				}
			};
			pic[i].src = imageList[i];
		}

	};
	$.preLoadAllImages = function(imageList,callback) {
		if (typeof imageList != 'undefined') {
			if ($.isFunction(imageList)) {
				callback = imageList;
			}
			else if (!$.isArray(imageList)) {
				imageList = [imageList];
			}
		}
		$.preLoadCSSImages(function(){
			if (imageList.length > 0) {
				$.preLoadImages(imageList,function(){
					callback();
				});
			}
			else {
				callback();
			}
		});
	}
})(jQuery);

