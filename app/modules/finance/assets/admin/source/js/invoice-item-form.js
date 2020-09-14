(function($){
  var InvoiceItemForm = function($form, options){
    var self = this;
    var $oldOption = null;

    this.$priceInput = $form.find("[name='InvoiceItem[price]']");
    this.$priceInputAlias = this.$priceInput.next();
    this.$amountInput = $form.find("[name='InvoiceItem[amount]']");
    this.$subTotalInput = $form.find("[name='InvoiceItem[sub_total]']");
    this.$subTotalInputAlias = this.$subTotalInput.next();
    this.$grandTotalInput = $form.find("[name='InvoiceItem[grand_total]']");
    this.$grandTotalInputAlias = this.$grandTotalInput.next();
    this.$taxInput = $form.find("[data-rid='invoiceitem-tax_inputs']");
    this.$productChooserInput = $form.find("[name='product_name']");
    this.$nameInput = $form.find("[name='InvoiceItem[name]']");
    this.$productIdInput = $form.find("[name='InvoiceItem[product_id]']");
    this.$typeInput = $form.find("[name='InvoiceItem[type]']");

    this.getGrandTotal = function(){
      var tax = this.$taxInput.taxValue("total");
      var subTotal = this.getSubTotal();

      return subTotal + tax;
    };

    this.getSubTotal = function(){
      var price = parseFloat((parseFloat(this.$priceInput.val()) || 0).toFixed(10));
      var amount = parseFloat((parseFloat(this.$amountInput.val()) || 0).toFixed(10));

      return amount * price;
    };

    var init = function(){
        self.$priceInput.add(self.$amountInput).on("change keyup", setSubtotalInput);
        self.$taxInput.on("taxValue.change", setGrandTotalInput);

        self.$productChooserInput.data("select2").on("results:all", createSelect2Tag);
        self.$productChooserInput.data("select2").on("query", createSelect2Tag);

        self.$productChooserInput.on("change", function(){
          setType();
        });

        setSubtotalInput();

        return self;
      },

      setType = function(){
        var data = self.$productChooserInput.select2("data")[0];

        if (!data) {
          return;
        }

        var type = data.isRaw ? "raw" : "product";
        var productId = !data.isRaw ? data.id : null;

        self.$typeInput.val(type);

        self.$productIdInput.val(productId);
        self.$nameInput.val(data.text);

        if (!data.isRaw) {
          self.$priceInputAlias.val(data.price).trigger("change");
        }
      },

      createSelect2Tag = function(){
        if (!this.results.lastParams.term) {
          if ($oldOption) {
            $oldOption.remove();
            $oldOption = null;
          }

          return;
        }

        var data = {
          text: this.results.lastParams.term,
          disabled: false,
          id: this.results.lastParams.term,
          isRaw: true
        };

        var $newOption = this.results.option(data);

        if ($oldOption && this.$results.find($oldOption).length > 0) {
          $($oldOption).replaceWith($newOption);
        } else {
          this.$results.prepend($newOption);
        }

        $($newOption).addClass("border-bottom");

        this.results.getHighlightedResults().removeClass("select2-results__option--highlighted");
        this.trigger("results:focus", { data: data, element: $($newOption) });

        $oldOption = $newOption;
      },

      setSubtotalInput = function(){
        self.$subTotalInputAlias.val(self.getSubTotal()).trigger("change");

        setGrandTotalInput();
      },

      setGrandTotalInput = function(){
        self.$grandTotalInputAlias.val(self.getGrandTotal()).trigger("change");
      };

    return init();
  };

  $.fn.invoiceItemForm = function(first, second, third){
    var invoiceItemForm = this.data("invoiceItemForm");

    if (!invoiceItemForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        invoiceItemForm = new InvoiceItemForm(this, first);

        this.data("invoiceItemForm", invoiceItemForm);
      }

      return this;
    }

    return invoiceItemForm[first](second, third);
  };

})(jQuery);