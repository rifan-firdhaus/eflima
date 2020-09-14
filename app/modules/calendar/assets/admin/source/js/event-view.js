(function($){
  var EventView = function($element, options){
    var self = this;

    this.$member = $element.find(".event-member-input-container");
    this.$memberButton = this.$member.find(".btn-event-member");

    this.$memberInput = this.$member.find(".event-member-input");

    this.invite = function(staffId){
      var url = admin.updateQueryParam(options.inviteUrl, "staff_id", staffId);

      $element.find("[data-rid='event-member-list-lazy']").lazyContainer("load", url, "POST", {}, {
        scroll: false
      });
    };

    var init = function(){

      self.$memberButton.on("click", function(e){
        e.preventDefault();

        self.$memberInput.select2("open");
      });

      self.$memberInput.on("change", function(){
        var value = self.$memberInput.val();

        self.invite(value);
      });
    };

    init();

    return this;
  };

  $.fn.eventView = function(first, second, third){
    var eventView = this.data("eventView");

    if (!eventView) {
      if (typeof first === "undefined" || typeof first === "object") {
        eventView = new EventView(this, first);

        this.data("eventView", eventView);
      }

      return this;
    }

    return eventView[first](second, third);
  };
})(jQuery);
