(function($){

  var ProposalDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children("button");
    var $bulkSetStatusLink = $dataView.find(".bulk-set-status");
    var $bulkDeleteLink = $dataView.find(".bulk-delete");

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

  $.fn.proposalDataView = function(first, second, third){
    var proposalDataView = this.data("proposalDataView");

    if (!proposalDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        proposalDataView = new ProposalDataView(this, first);

        this.data("proposalDataView", proposalDataView);
      }

      return this;
    }

    return proposalDataView[first](second, third);
  };

})(jQuery);
