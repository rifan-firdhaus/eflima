(function($){

  var TaskForm = function($form, options){
    var self = this;

    this.$modelInput = $form.find("[name='Task[model]']");
    this.$modelIdInput = $form.find("[name='Task[model_id]']");
    this.$modelIdField = this.$modelIdInput.parent();

    var init = function(){
        self.$modelInput.on("change", onModelChange);
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