(function($){

  var QuickSearch = function(options){
    var self = this;
    var _ajax = null;
    var $body = $("body");

    this.$trigger = $("#quick-search-button");
    this.$container = $(".quick-search-container");
    this.$input = this.$container.find(".quick-search-input");
    this.$form = this.$container.find(".quick-search-form");
    this.$result = this.$container.find(".quick-search-result");
    this.url = this.$container.data("url");

    this.open = function(){
      this.$container.show();
      this.$container.addClass("quick-search-open");
      $body.addClass("quick-search-open");
      this.$input.focus().select();
    };

    this.close = function(){
      this.$container.removeClass("quick-search-open");

      setTimeout(function(){
        self.$container.hide();
        $body.removeClass("quick-search-open");
      }, 300);
    };

    this.isOpen = function(){
      return this.$container.hasClass("quick-search-open");
    };

    this.toggle = function(){
      if (this.isOpen()) {
        this.close();
      } else {
        this.open();
      }
    };

    this.search = function(){
      if (_ajax !== null) {
        _ajax.abort();
      }

      var query = this.$input.val();

      if (query === "") {
        this.$result.empty();

        return;
      }

      var promise = $.ajax({
        url: this.$form.attr("action"),
        data: { q: query },
        dataType: "JSON",
        success: function(data){
          _ajax = null;

          self.render(data.result);
        }
      });

      _ajax = promise;

      return promise;
    };

    this.clear = function(){
      this.$result.empty();
      this.$result.scrollTop(0);
    };

    this.render = function(data){
      this.clear();

      $.each(data, function(index, item){
        self.renderResult(item);
      });

      self.$container.find("a").not("[href^='#'],[data-lazy=0]").lazyLink();
    };

    this.renderResult = function(data){
      if (data.result.length === 0) {
        return;
      }

      var $header = $("<div/>", {
        "class": "quick-search-result-section-header",
        "text": data.label
      });
      var $body = $("<div/>", {
        "class": "quick-search-result-section-body"
      });
      var $container = $("<div/>", {
        "class": "quick-search-result-section quick-search-result-" + data.id + "-section"
      });

      $.each(data.result, function(index, html){
        lazy.render(html, $body, function($content){
          $body.append($content);
        });
      });

      $container.append($header).append($body).appendTo(this.$result);
    };

    var init = function(){
      var timeout = null;

      self.$input.on("keyup", function(){
        clearTimeout(timeout);

        timeout = setTimeout(function(){
          self.$form.trigger("submit");
        }, 500);
      });

      self.$form.on("submit", function(e){
        e.preventDefault();
        e.stopPropagation();

        self.search();
      });

      self.$trigger.on("click", function(e){
        e.preventDefault();

        self.toggle();
      });

      self.$container.on("click", "[data-quick-search-close]", function(){
        self.close();
      });

      var shiftKeyPressed = 0;
      var shiftKeyTimeout = null;

      $body.on("keyup", function(e){
        if ($body.hasClass("quick-search-open")) {
          if (e.keyCode === 27) {
            self.close();
          }
        } else {
          if (e.keyCode === 16 && e.shiftKey === false) {
            shiftKeyPressed++;

            if (shiftKeyPressed === 3) {
              self.open();
            }

            clearTimeout(shiftKeyTimeout);

            shiftKeyTimeout = setTimeout(function(){
              shiftKeyPressed = 0;
            }, 350);
          }
        }
      });

      return self;
    };

    return init();
  };

  $(function(){
    window.quickSearch = new QuickSearch();
  });

})(jQuery);