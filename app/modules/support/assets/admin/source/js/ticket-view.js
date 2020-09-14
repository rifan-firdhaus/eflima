(function($){

  var TicketView = function($element, options){
    var self = this;

    this.$replies = $element.find(".ticket-replies");

    var init = function(){
      Prism.highlightAll(self.$replies[0]);

      return self;
    };

    return init();
  };

  $.fn.ticketView = function(first, second, third){
    var ticketView = this.data("ticketView");

    if (!ticketView) {
      if (typeof first === "undefined" || typeof first === "object") {
        ticketView = new TicketView(this, first);

        this.data("ticketView", ticketView);
      }

      return this;
    }

    return ticketView[first](second, third);
  };

})(jQuery);