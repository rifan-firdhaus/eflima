(function($){

  var ProjectDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children("button");
    var $bulkDeleteLink = $dataView.find(".bulk-delete");
    var $bulkSetStatusLink = $dataView.find(".bulk-set-status");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        $dataTable.on("dataTable.change", onSelect);

        $bulkDeleteLink.on("lazyLink.go", setRequest);
        $bulkSetStatusLink.on("lazyLink.go", setRequest);

        onSelect();
      },

      setRequest = function(event, container, options){
        options.data["id"] = dataTable.getSelected();
      },

      onSelect = function(){
        var selected = dataTable.getSelected();

        $bulkActionsButton.prop("disabled", selected.length === 0);
      };

    return init();
  };

  $.fn.projectDataView = function(first, second, third){
    var projectDataView = this.data("projectDataView");

    if (!projectDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        projectDataView = new ProjectDataView(this, first);

        this.data("projectDataView", projectDataView);
      }

      return this;
    }

    return projectDataView[first](second, third);
  };

})(jQuery);
