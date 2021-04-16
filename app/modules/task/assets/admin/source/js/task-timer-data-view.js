(function($){

  var TaskTimerDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children('button');
    var $bulkDeleteLink = $dataView.find(".bulk-delete");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        $dataTable.on("dataTable.change", onSelect);

        $bulkDeleteLink.on('lazyLink.go',setRequest);

        onSelect();
      },

      setRequest = function(event,container,options){
        options.data['id'] = dataTable.getSelected();
      },

      onSelect = function(){
        var selected = dataTable.getSelected();

        $bulkActionsButton.prop('disabled',selected.length === 0);
      };

    return init();
  };

  $.fn.taskTimerDataView = function(first, second, third){
    var taskTimerDataView = this.data("taskTimerDataView");

    if (!taskTimerDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        taskTimerDataView = new TaskTimerDataView(this, first);

        this.data("taskDataView", taskTimerDataView);
      }

      return this;
    }

    return taskTimerDataView[first](second, third);
  };

})(jQuery);
