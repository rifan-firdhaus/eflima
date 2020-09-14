(function($){

  window.ProjectMilestoneTaskForm = function($form){
    var self = this;
    var projectId = null;

    this.$modelInput = $form.find("[name='Task[model]']");
    this.$milestoneInput = $form.find("[name='Task[milestone_id]']");
    this.$milestoneField = this.$milestoneInput.closest(".form-group");

    var init = function(){
        setMilestoneValue(true);

        $form.on("change", "[name='Task[model_id]']", setMilestoneValue);
        self.$modelInput.on("change", setMilestoneValue);

        console.log(self.$milestoneInput)

        var milestoneSelect2 = self.$milestoneInput.data("select2");

        var originalData = milestoneSelect2.dataAdapter.ajaxOptions.data;

        milestoneSelect2.dataAdapter.ajaxOptions.data = function(params){
          var result = originalData.call(this, params);

          result.project_id = projectId;

          return result;
        };
      },
      setMilestoneValue = function(initial){
        if (self.$modelInput.val() === "project") {
          self.$milestoneField.show();

          projectId = $form.find("[name='Task[model_id]']").val();
        } else {
          projectId = null;

          self.$milestoneField.hide();
        }

        if (!initial) {
          self.$milestoneInput.val("").trigger("change");
        }
      };

    return init();
  };

})(jQuery);