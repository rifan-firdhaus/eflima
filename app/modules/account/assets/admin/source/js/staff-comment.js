(function($){

  var StaffCommentForm = function($form, options){
    var self = this;

    this.$lazyContainer = $form.parent();

    var init = function(){
      self.$lazyContainer.on("lazy.loaded", function(event, result){
        window.lazy.render(result.item, $(".task-interaction-list-wrapper"), function($content){
          $content.hide();
          $(".task-interaction-list-wrapper").prepend($content);
          $content.slideDown();
        });
      });
    };

    return init();
  };

  var StaffComment = function($container, options){
    var self = this;

    this.$form = $(options.form);
    this.$lazyForm = this.$form.parent();

    var init = function(){
      self.$lazyForm.on("lazy.loaded", function(event, result){
        window.lazy.render(result.item, $container, function($content){
          $content.hide();
          $container.prepend($content);
          $content.slideDown();
        });
      });
    };

    return init();
  };

  $.fn.staffComment = function(first, second, third){
    var staffComment = this.data("staffComment");

    if (!staffComment) {
      if (typeof first === "undefined" || typeof first === "object") {
        staffComment = new StaffComment(this, first);

        this.data("staffComment", staffComment);
      }

      return this;
    }

    return expenseForm[first](second, third);
  };

})(jQuery);