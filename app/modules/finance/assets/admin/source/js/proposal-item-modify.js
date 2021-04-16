(function($){

  var ProposalItemModify = function($layout, options){
    var self = this;

    this.$container = $layout.closest("[main-container=1]");
    this.$navigation = $layout.find("[data-rid='proposal-item-modify-menu']");

    this.options = function(){
      return options;
    };

    this.container = function(){
      return this.$container.data("lazyContainer");
    };

    var init = function(){
      self.$navigation.find(".nav-link").on("click", function(e){
        e.preventDefault();

        var url = $(this).attr("href");

        var data = {
          "models": JSON.stringify(options.models),
          "model": JSON.stringify(options.model),
          "proposal": JSON.stringify(options.proposal),
        };

        self.container().load(url, "POST", data);
      });

      return self;
    };

    return init();
  };

  $.fn.proposalItemModify = function(first, second, third){
    var proposalItemModify = this.data("proposalItemModify");

    if (!proposalItemModify) {
      if (typeof first === "undefined" || typeof first === "object") {
        proposalItemModify = new ProposalItemModify(this, first);

        this.data("proposalItemModify", proposalItemModify);
      }

      return this;
    }

    return proposalItemModify[first](second, third);
  };

})(jQuery);
