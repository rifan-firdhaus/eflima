(function($){

  var LeadDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children("button");
    var $bulkDeleteLink = $dataView.find(".bulk-delete");
    var $bulkSetStatusLink = $dataView.find(".bulk-set-status");
    var $bulkReassignLink = $dataView.find(".bulk-reassign");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        $dataTable.on("dataTable.change", onSelect);

        $bulkDeleteLink.on("lazyLink.go", setRequest);
        $bulkSetStatusLink.on("lazyLink.go", setRequest);
        $bulkReassignLink.on("lazyLink.go", setRequest);

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

  $.fn.leadDataView = function(first, second, third){
    var leadDataView = this.data("leadDataView");

    if (!leadDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        leadDataView = new LeadDataView(this, first);

        this.data("leadDataView", leadDataView);
      }

      return this;
    }

    return leadDataView[first](second, third);
  };

})(jQuery);
