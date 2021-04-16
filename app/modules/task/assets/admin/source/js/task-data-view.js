(function($){

  var TaskDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children('button');
    var $bulkDeleteLink = $dataView.find(".bulk-delete");
    var $bulkSetStatusLink = $dataView.find(".bulk-set-status");
    var $bulkSetPriorityLink = $dataView.find(".bulk-set-priority");
    var $bulkReassignLink = $dataView.find(".bulk-reassign");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        $dataTable.on("dataTable.change", onSelect);

        $bulkDeleteLink.on('lazyLink.go',setRequest);
        $bulkSetStatusLink.on('lazyLink.go',setRequest);
        $bulkSetPriorityLink.on('lazyLink.go',setRequest);
        $bulkReassignLink.on('lazyLink.go',setRequest);

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

  $.fn.taskDataView = function(first, second, third){
    var taskDataView = this.data("taskDataView");

    if (!taskDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        taskDataView = new TaskDataView(this, first);

        this.data("taskDataView", taskDataView);
      }

      return this;
    }

    return taskDataView[first](second, third);
  };

})(jQuery);
