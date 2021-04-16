(function($){
  var ProjectView = function($element, options){
    var self = this;

    this.$member = $element.find(".project-member-input-container");
    this.$memberButton = this.$member.find(".btn-project-member");

    this.$memberInput = this.$member.find(".project-member-input");

    this.invite = function(staffId){
      var url = admin.updateQueryParam(options.inviteUrl, "staff_id", staffId);

      $element.find("[data-rid='project-member-list-lazy']").lazyContainer("load", url, "POST", {}, {
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

  $.fn.projectView = function(first, second, third){
    var projectView = this.data("projectView");

    if (!projectView) {
      if (typeof first === "undefined" || typeof first === "object") {
        projectView = new ProjectView(this, first);

        this.data("projectView", projectView);
      }

      return this;
    }

    return projectView[first](second, third);
  };
})(jQuery);
