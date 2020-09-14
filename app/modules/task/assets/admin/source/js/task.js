(function($){

  var Task = function(){
    var self = this;

    this.timer = $.extend({}, admin.panel, {
      showClass: "timer-panel-show",
      hideClass: "timer-panel-hide",
      $element: $("#timer-panel"),
      $badge: $(".timer-count-badge"),
      $link: $("[data-lazy-container=\"#timer-panel\"]"),

      setCount: function(count){
        count = parseInt(count) || 0;

        this.$badge.text(count).toggle(count > 0).data("count", count);
      },

      getCount: function(){
        return parseInt(this.$badge.data("count")) || 0;
      },

      reload: function(){
        this.$element.lazyContainer("load", this.$link.attr("href"));
      }
    });

    var init = function(){
        initTimer();

        return self;
      },
      initTimer = function(){
        self.timer.setCount(self.timer.getCount());

        self.timer.$element.lazyContainer({
          pushState: false,
          scroll: false,
        });

        self.timer.$element.on("lazy.load", function(){
          self.timer.show();
        });

        self.timer.$element.on("click", ".side-panel-close", function(e){
          e.preventDefault();

          self.timer.hide();
        });

        $(document).on("lazy.loaded lazy.failed", function(event, data){
          if (!data || !data.timerCount) {
            return;
          }

          self.timer.setCount(data.timerCount);

          if (self.timer.isShowed()) {
            if (data.timerCount > 0) {
              self.timer.reload();
            } else {
              self.timer.hide();
            }
          }
        });

      };

    return init();
  };

  $(function(){
    if (!window.task) {
      window.task = new Task();
    }
  });

})(jQuery);