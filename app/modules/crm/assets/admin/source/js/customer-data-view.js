(function($){

  var CustomerDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children("button");
    var $bulkDeleteLink = $dataView.find(".bulk-delete");
    var $bulkSetGroupLink = $dataView.find(".bulk-set-group");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        $dataTable.on("dataTable.change", onSelect);

        $bulkDeleteLink.on("lazyLink.go", setRequest);

        $bulkSetGroupLink.on("lazyLink.go", setRequest);

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

  $.fn.customerDataView = function(first, second, third){
    var customerDataView = this.data("customerDataView");

    if (!customerDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        customerDataView = new CustomerDataView(this, first);

        this.data("customerDataView", customerDataView);
      }

      return this;
    }

    return customerDataView[first](second, third);
  };

})(jQuery);
