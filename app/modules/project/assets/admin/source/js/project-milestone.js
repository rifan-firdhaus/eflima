(function($){

  var ProjectMilestone = function($element, options){
    var self = this;
    var milestonesSortable = null;
    var tasksSortable = [];
    this.taskPagination = {};

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

    this.loadTask = function(milestoneId, reload){
      var page = this.taskPagination[milestoneId] ? this.taskPagination[milestoneId].page + 1 : 1;

      if (reload === true) {
        page = this.taskPagination[milestoneId].page;
      }

      var url = admin.updateQueryParam(options.loadTaskUrl, {
        id: milestoneId,
        page: page
      });

      $element.find("[data-rid=\"project-milestone-items-" + milestoneId + "\"]").lazyContainer("load", url, "GET", {}, {
        renderer: function($content){
          var scrollTop = $(this).scrollTop();

          $content.insertBefore($(this).find('.btn-load-more'));

          $(this).scrollTop(scrollTop);
        }
      }, false);
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
        var $tasksWrapper = $(this);
        var milestoneId = $tasksWrapper.closest(".project-milestone-item").data("id");
        var $tasks = $tasksWrapper.find("[data-rid=\"project-milestone-items-" + milestoneId + "\"]");
        var $loadMoreButton = $tasksWrapper.find(".btn-load-more");

        $loadMoreButton.on("click", function(e){
          e.preventDefault();

          self.loadTask(milestoneId);
        });

        $tasks.on("lazy.loaded", function(e, data){
          if (!data.page) {
            self.loadTask(milestoneId, true);
          }

          self.taskPagination[milestoneId] = {
            hasMorePage: data.has_more_page,
            page: data.page
          };

          if(!data.has_more_page){
            $tasksWrapper.find(".btn-load-more").hide();
          }
        });

        self.loadTask(milestoneId);

        tasksSortable[milestoneId] = new Sortable($tasks.get(0), {
          group: "task-container",
          draggable: ".project-milestone-item-task-container",
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
