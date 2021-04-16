(function($){

  var EventDataView = function($dataView){
    var self = this;

    var $dataTable = $dataView.find(".data-table");
    var $bulkActions = $dataView.find(".bulk-actions");
    var $bulkActionsButton = $bulkActions.children("button");
    var $bulkDeleteLink = $dataView.find(".bulk-delete");

    var dataTable = $dataTable.data("dataTable");

    var init = function(){
        if ($dataTable.length > 0) {
          $dataTable.on("dataTable.change", onSelect);

          $bulkDeleteLink.on("lazyLink.go", setRequest);

          onSelect();
        }else{
          $bulkActions.hide();
        }
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

  $.fn.eventDataView = function(first, second, third){
    var eventDataView = this.data("eventDataView");

    if (!eventDataView) {
      if (typeof first === "undefined" || typeof first === "object") {
        eventDataView = new EventDataView(this, first);

        this.data("eventDataView", eventDataView);
      }

      return this;
    }

    return eventDataView[first](second, third);
  };

})(jQuery);
