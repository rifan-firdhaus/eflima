(function($){

  var InvoiceItemModify = function($layout, options){
    var self = this;

    this.$container = $layout.closest("[main-container=1]");
    this.$navigation = $layout.find("[data-rid='invoice-item-modify-menu']");

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
          "invoice": JSON.stringify(options.invoice),
        };

        self.container().load(url, "POST", data);
      });

      return self;
    };

    return init();
  };

  $.fn.invoiceItemModify = function(first, second, third){
    var invoiceItemModify = this.data("invoiceItemModify");

    if (!invoiceItemModify) {
      if (typeof first === "undefined" || typeof first === "object") {
        invoiceItemModify = new InvoiceItemModify(this, first);

        this.data("invoiceItemModify", invoiceItemModify);
      }

      return this;
    }

    return invoiceItemModify[first](second, third);
  };

})(jQuery);