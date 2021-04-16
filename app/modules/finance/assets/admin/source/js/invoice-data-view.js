(function($){

  var InvoiceDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children("button");
    var $bulkDeleteLink = $dataView.find(".bulk-delete");
    var $bulkDownloadLink = $dataView.find(".bulk-download");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        $dataTable.on("dataTable.change", onSelect);

        $bulkDeleteLink.on("lazyLink.go", setRequest);
        $bulkDownloadLink.on("click", function(e){
          e.preventDefault();

          window.location.href = admin.updateQueryParam($(this).attr('href'),{
            'id': dataTable.getSelected()
          });
        });

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

  $.fn.invoiceDataView = function(first, second, third){
    var invoiceDataView = this.data("invoiceDataView");

    if (!invoiceDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        invoiceDataView = new InvoiceDataView(this, first);

        this.data("invoiceDataView", invoiceDataView);
      }

      return this;
    }

    return invoiceDataView[first](second, third);
  };

})(jQuery);
