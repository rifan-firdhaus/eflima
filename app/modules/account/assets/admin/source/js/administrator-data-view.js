(function($){

  window.staffDataView = function(){
    this.$dataView = $("#");
  };

})(jQuery);
tinymce.init({
  "plugins": ["advlist fullscreen autolink autoresize lists link image charmap hr anchor pagebreak", "searchreplace wordcount visualblocks visualchars code fullscreen", "insertdatetime media nonbreaking save table directionality", "emoticons template paste textpattern imagetools codesample toc"],
  "toolbar1": "bold underline italic strikethrough subscript superscript | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
  "toolbar2": "fullscreen code searchreplace | styleselect fontselect fontsizeselect | table image link unlink blockquote codesample",
  "image_advtab": true,
  "autoresize_max_height": 500,
  "autoresize_min_height": 200,
  "autoresize_bottom_margin": 0,
  "link_list": [{ "title": "My page 1", "value": "http://www.tinymce.com" }, { "title": "My page 2", "value": "http://www.ephox.com" }],
  "relative_urls": false,
  "remove_script_host": false,
  "selector": "#task-description-editable",
  "inline": true,
  "setup": function(editor){
    editor.on("change KeyUp", function(){$("#task-description").val(this.getContent());});
    var originalSetup = $.noop;
    if (typeof originalSetup == "function") {originalSetup.apply(this, [editor]);}
  }
});
