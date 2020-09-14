(function($){
  var LeadView = function($element, options){
    var self = this;

    this.$assignee = $element.find(".lead-assignee-input-container");
    this.$assigneeButton = this.$assignee.find(".btn-lead-assignee");

    this.$assigneeInput = this.$assignee.find(".lead-assignee-input");

    this.invite = function(staffId){
      var url = admin.updateQueryParam(options.inviteUrl, "staff_id", staffId);

      $element.find("[data-rid='lead-assignee-list-lazy']").lazyContainer("load", url, "POST", {}, {
        scroll: false
      });
    };

    var init = function(){

      self.$assigneeButton.on("click", function(e){
        e.preventDefault();

        self.$assigneeInput.select2("open");
      });

      self.$assigneeInput.on("change", function(){
        var value = self.$assigneeInput.val();

        self.invite(value);
      });
    };

    init();

    return this;
  };

  $.fn.leadView = function(first, second, third){
    var leadView = this.data("leadView");

    if (!leadView) {
      if (typeof first === "undefined" || typeof first === "object") {
        leadView = new LeadView(this, first);

        this.data("leadView", leadView);
      }

      return this;
    }

    return leadView[first](second, third);
  };
})(jQuery);
