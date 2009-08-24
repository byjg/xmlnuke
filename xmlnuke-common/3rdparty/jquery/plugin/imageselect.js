/** 
 * ImageSelect jQuery plugin
 * 
 * @author Stefan Zollinger
 * 
 * options:
 *   containerClass: 'image-select-container'
 *   imageClass: 'image-select'
 *   thumbnailWidth: '60'
 *   imageSrc: 'text' or 'value'
 *   emptyText: 'No image'
 *   notFoundImage: ''
 * 
 */

(function($) {
	
	$.fn.imageSelect = function(options){
		var opts = $.extend({}, $.fn.imageSelect.defaults, options);
		
		return this.each(function() {
			var $this = $(this);
			if(this.tagName == 'SELECT'){
				
				$this.wrap('<div class="' + opts.containerClass + '">' );
				var html = '';				
				$this.children('option').each(function(){
					
					if(this.selected == true){
						selectClass = 'selected';
					}else{
						selectClass = '';
					}
					var src;
					if(opts.imageSrc == 'text'){
						src = $(this).text(); 
					}else{
						src = this.value;
					}
					
					if (this.value == '' || this.value == undefined) {
						html += '<a class="' +
							selectClass +
							' ' +
							opts.imageClass +
							'" href="#select_' +
							this.value +
							'"><div style="background-color: #ccc; width: ' +
							opts.thumbnailWidth + 'px; height: ' + opts.thumbnailWidth +
							'px" >'+opts.emptyText+'</div></a>';
					} else {
						html += '<a class="' +
							selectClass +
							' ' +
							opts.imageClass +
							'" href="#select_' +
							this.value +
							'"><img  src="' +
							src +
							'" style="width: ' +
							opts.thumbnailWidth +
							'px; border: 0px;" ' +
							( opts.notFoundImage != "" ? 'onerror="this.onerror=null; this.src=\'' + opts.notFoundImage + '\'"' : '' ) +
							'/></a>';
					}
				});
				
				$(this).after(html);
				
				$('a.image-select').click($.fn.imageSelect.selectImage);
				
				$this.css('display', 'none');
			}
			
  		});
	}
	
	$.fn.imageSelect.selectImage = function(e){
		var $selectBox = $(this).prevAll('select:first');
		
		if($selectBox.attr('multiple') == true){
			var $option = $selectBox.children('option[value='+this.href.split('_')[1]+']');
			
			if($option.attr('selected') == true){
				$option.attr('selected', false);
				$(this).removeClass('selected');
			}else{
				$option.attr('selected', true);
				$(this).addClass('selected');
			}
			
		}else{
			$selectBox.val(this.href.split('_')[1]);
			$(this).parent().children('a').removeClass('selected');
			$(this).addClass('selected');
		}
		
		
		
		return false;
	}
	
	$.fn.imageSelect.defaults = {
		containerClass: 'image-select-container',
		imageClass: 'image-select',
		imageSrc: 'text',
		thumbnailWidth: '60',
		emptyText: 'No image',
		notFoundImage: ''
	};
  
	function debug(msg) {
		if (window.console && window.console.log) 
			window.console.log('imageselect: ' + msg);
	};
  

})(jQuery)

