(function($){

  var TaskView = function($element, options){
    var self = this;

    this.$taskAssignee = $element.find(".task-assignee-input-container");
    this.$taskAssigneeButton = this.$taskAssignee.find(".btn-task-assignee");
    this.$taskAssigneeInput = this.$taskAssignee.find(".task-assignee-input");

    this.assign = function(staffId){
      var url = admin.updateQueryParam(options.assignUrl, "staff_id", staffId);

      $element.find("[data-rid='task-assignee-list-lazy']").lazyContainer("load", url, "POST", {}, {
        scroll: false
      });
    };

    this.setProgress = function(value){
      return $.ajax({
        url: options.setProgressUrl,
        data: {progress: value},
        type: "POST",
        success: function(data){
          admin.notifies(data.messages);
        }
      });
    };

    var init = function(){
      Prism.highlightAll($element[0]);

      self.$taskAssigneeButton.on("click", function(e){
        e.preventDefault();

        self.$taskAssigneeInput.select2("open");
      });

      self.$taskAssigneeInput.on("change", function(){
        var value = self.$taskAssigneeInput.val();

        self.assign(value);
      });

      return self;
    };

    return init();
  };

  $.fn.taskView = function(first, second, third){
    var taskView = this.data("taskView");

    if (!taskView) {
      if (typeof first === "undefined" || typeof first === "object") {
        taskView = new TaskView(this, first);

        this.data("taskView", taskView);
      }

      return this;
    }

    return taskView[first](second, third);
  };
})(jQuery);
