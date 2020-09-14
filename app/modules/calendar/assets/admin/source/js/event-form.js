(function($){

  var EventForm = function($form, options){
    var self = this;

    this.$modelInput = $form.find("[name='Event[model]']");
    this.$modelIdInput = $form.find("[name='Event[model_id]']");
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
              var $modelIdInput = $form.find("[name='Event[model_id]']");

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

  $.fn.eventForm = function(first, second, third){
    var eventForm = this.data("eventForm");

    if (!eventForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        eventForm = new EventForm(this, first);

        this.data("eventForm", eventForm);
      }

      return this;
    }

    return eventForm[first](second, third);
  };

})(jQuery);