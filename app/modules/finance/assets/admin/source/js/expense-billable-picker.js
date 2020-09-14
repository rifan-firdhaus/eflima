(function($){
  var ExpenseBillablePicker = function($picker, options){
    var self = this;
    var isUpdate = false;

    this.$submitButton = $picker.find(".btn-submit-billable-picker");
    this.$dataTable = $picker.find("[data-rid='expense-data-table']");
    this.$invoiceItemModify = $picker.closest("[data-rid='invoice-item-modify-container']");

    this.submit = function(){
      var expenses = this.$dataTable.dataTable("getSelected");
      var invoiceItemModify = this.$invoiceItemModify.data("invoiceItemModify");
      var invoiceItemModifyOptions = invoiceItemModify.options();

      var data = {
        models: JSON.stringify(invoiceItemModifyOptions.models),
        model: JSON.stringify(invoiceItemModifyOptions.model),
        invoice: JSON.stringify(invoiceItemModifyOptions.invoice),
        expenses: expenses
      };

      invoiceItemModify.container().load(options.url, "POST", data);
    };

    var init = function(){
      self.$submitButton.on("click", function(e){
        e.preventDefault();

        self.submit();
      });
    };

    return init();
  };

  $.fn.expenseBillablePicker = function(first, second, third){
    var expenseBillablePicker = this.data("expenseBillablePicker");

    if (!expenseBillablePicker) {
      if (typeof first === "undefined" || typeof first === "object") {
        expenseBillablePicker = new ExpenseBillablePicker(this, first);

        this.data("expenseBillablePicker", expenseBillablePicker);
      }

      return this;
    }

    return expenseBillablePicker[first](second, third);
  };
})(jQuery);