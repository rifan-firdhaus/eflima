(function($){

  var ProjectMilestone = function($element, options){
    var self = this;
    var milestonesSortable = null;
    var tasksSortable = [];

    this.$tasks = $element.find(".project-milestone-item-content");

    this.sendSort = function(){
      var data = milestonesSortable.toArray();

      return $.ajax({
        url: options.sortUrl,
        type: "POST",
        dataType: "JSON",
        data: { sort: data },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.sendSortTask = function(milestoneId, sort){
      return $.ajax({
        url: admin.updateQueryParam(options.sortTaskUrl, { id: milestoneId }),
        type: "POST",
        dataType: "JSON",
        data: {
          sort: sort
        },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.sendMoveTask = function(taskId, fromMilestoneId, toMilestoneId, sort){
      return $.ajax({
        url: admin.updateQueryParam(options.moveTaskUrl, { id: fromMilestoneId }),
        type: "POST",
        dataType: "JSON",
        data: {
          sort: sort,
          milestone_id: toMilestoneId,
          task_id: taskId
        },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    var init = function(){
      milestonesSortable = new Sortable($element.get(0), {
        animation: 200,
        scroll: $element.get(0),
        scrollSensitivity: 85,
        handle: ".project-milestone-item-header",
        onEnd: function(e){
          self.sendSort();
        }
      });

      self.$tasks.each(function(){
        var milestoneId = $(this).closest(".project-milestone-item").data("id");

        tasksSortable[milestoneId] = new Sortable(this, {
          group: "task-container",
          animation: 200,
          scroll: this,
          scrollSensitivity: 85,
          onEnd: function(e){
            var isMilestoneChanged = e.from !== e.to;
            var sort;

            if (!isMilestoneChanged) {
              sort = this.toArray();

              self.sendSortTask(milestoneId, sort);
            } else {
              var taskId = $(e.item).data("id");
              var toMilestoneId = $(e.to).closest(".project-milestone-item").data("id");
              sort = tasksSortable[toMilestoneId].toArray();

              self.sendMoveTask(taskId, milestoneId, toMilestoneId, sort);
            }
          }
        });
      });
    };

    return init();
  };

  $.fn.projectMilestone = function(first, second, third){
    var projectMilestone = this.data("projectMilestone");

    if (!projectMilestone) {
      if (typeof first === "undefined" || typeof first === "object") {
        projectMilestone = new ProjectMilestone(this, first);

        this.data("projectMilestone", projectMilestone);
      }

      return this;
    }

    return projectMilestone[first](second, third);
  };

})(jQuery);