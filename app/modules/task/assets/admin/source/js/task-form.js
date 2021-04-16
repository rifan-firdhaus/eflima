(function($){

  var TaskForm = function($form, options){
    var self = this;

    this.$modelInput = $form.find("[name='Task[model]']");
    this.$modelIdInput = $form.find("[name='Task[model_id]']");
    this.$modelIdField = this.$modelIdInput.parent();

    this.$enableTimerInput = $form.find("input[type=checkbox][name='Task[is_timer_enabled]']");
    this.$timerGroup = $form.find(".timer-group");

    this.$visibleToCustomerInput = $form.find("input[type=checkbox][name='Task[is_visible_to_customer]']");
    this.$allowCustomerToCommentField = $form.find("[data-rid='active-field-task-is_customer_allowed_to_comment']");

    this.$visibilityInput = $form.find("input[type=radio][name='Task[visibility]']");
    this.$assigneeField = $form.find("[data-rid='active-field-task-assignee_ids']");

    var init = function(){
        self.$modelInput.on("change", onModelChange);
        self.$enableTimerInput.on("change", onTimerInputChange);
        self.$visibleToCustomerInput.on("change", onVisibleToCustomerInputChange);
        self.$visibilityInput.on("change", onVisibilityInputChange);

        onVisibleToCustomerInputChange();
        onTimerInputChange();
        onVisibilityInputChange();
      },

      onVisibilityInputChange = function(){
        var value = self.$visibilityInput.filter(":checked").val();

        if (value === "P") {
          self.$assigneeField.slideUp();
        } else {
          self.$assigneeField.slideDown();
        }
      },

      onVisibleToCustomerInputChange = function(){
        var value = self.$visibleToCustomerInput.is(":checked");

        if (value) {
          self.$allowCustomerToCommentField.slideDown();
        } else {
          self.$allowCustomerToCommentField.slideUp();
        }
      },

      onTimerInputChange = function(){
        var value = self.$enableTimerInput.is(":checked");

        if (value) {
          self.$timerGroup.slideDown();
        } else {
          self.$timerGroup.slideUp();
        }
      },

      onModelChange = function(e){
        var model = $(this).val();

        return $.ajax({
          url: options.modelInputUrl,
          dataType: "JSON",
          type: "GET",
          data: {
            model: model
          },
          success: function(data){
            if (!data.input) {
              return;
            }

            self.$modelIdField.empty();

            lazy.render(data.input, self.$modelIdField).done(function(){
              var $modelIdInput = $form.find("[name='Task[model_id]']");

              if ($modelIdInput.data("select2")) {
                $modelIdInput.select2("open");
              } else {
                $modelIdInput.focus();
              }
            });
          }
        });
      };

    return init();
  };

  $.fn.taskForm = function(first, second, third){
    var taskForm = this.data("taskForm");

    if (!taskForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        taskForm = new TaskForm(this, first);

        this.data("taskForm", taskForm);
      }

      return this;
    }

    return taskForm[first](second, third);
  };

})(jQuery);
