(function($){

  var ExpenseForm = function($form, options){
    var self = this;

    this.$amountInput = $form.find("[name='Expense[amount]']");
    this.$totalInput = $form.find("[name='Expense[total]']");
    this.$totalAliasInput = this.$totalInput.next();
    this.$taxesInput = $form.find("[data-rid='expense-tax_inputs']");
    this.$currencyCodeInput = $form.find("[name='Expense[currency_code]']");
    this.$currencyRateInput = $form.find("[name='Expense[currency_rate]']");
    this.$currencyRateInputAlias = this.$currencyRateInput.next();
    this.$currencyRateField = this.$currencyRateInput.closest('.form-group');

    this.recalculate = function(){
      var amount = parseFloat((parseFloat(this.$amountInput.val()) || 0).toFixed(10));
      var tax = parseFloat((parseFloat(this.$taxesInput.taxValue("total")) || 0).toFixed(10));
      var total = amount + tax;

      this.$totalAliasInput.val(total).trigger("change");
    };

    var init = function(){

        self.$amountInput.on("change keyup", function(){
          self.recalculate();
        });

        self.$taxesInput.on("taxValue.change", function(){
          self.recalculate();
        });

        self.$currencyCodeInput.on("change", function(){
          checkCurrencyRateVisibility();
        });

        checkCurrencyRateVisibility(false);
        self.recalculate();

        return self;
      },

      checkCurrencyRateVisibility = function(focus){
        var value = self.$currencyCodeInput.val();
        var isBaseCurrency = value == options.baseCurrency;

        self.$currencyRateField.toggle(!isBaseCurrency);

        if(!isBaseCurrency && focus !== false){
          self.$currencyRateInputAlias.focus();
        }
      };

    return init();
  };

  $.fn.expenseForm = function(first, second, third){
    var expenseForm = this.data("expenseForm");

    if (!expenseForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        expenseForm = new ExpenseForm(this, first);

        this.data("expenseForm", expenseForm);
      }

      return this;
    }

    return expenseForm[first](second, third);
  };

})(jQuery);