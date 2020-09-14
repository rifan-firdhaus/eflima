(function($){

  $(function(){
    $("body").on("lazy.loaded", "[data-rid='task-interaction-form-lazy']", function(event, result){
      window.lazy.render(result.item, $(".task-interaction-list-wrapper"), function($content){
        $content.hide();
        $(".task-interaction-list-wrapper").prepend($content);
        $content.slideDown();
      });
    });
  });

})(jQuery);