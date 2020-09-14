(function($){
  var InvoiceForm = function($form, options){
    var self = this;
    var count = 1;

    this.$currencyCodeInput = $form.find("[data-rid='invoice-currency_code']");
    this.$customerInput = $form.find("[data-rid='invoice-customer_id']");
    this.$currencyRateInput = $form.find("[data-rid='invoice-currency_rate']");
    this.$currencyRateField = this.$currencyRateInput.closest(".form-group");
    this.$addItemButton = $form.find(".add-invoice-item-button");
    this.$table = $form.find(".invoice-item-table");
    this.$tableBody = this.$table.find("tbody");
    this.$tableFooter = this.$table.find("tfoot");

    this.models = {};

    this.add = function(model, row){
      self.models[count] = model;

      lazy.render(row, this.$tableBody, function(lazy, $row){
        renderRow($row, model, count);

        self.$tableBody.append($row);
      });

      count++;
    };

    this.remove = function(count){
      delete self.models[count];

      self.$tableBody.find("tr[data-id=" + count + "]").remove();

      self.reevaluate();
    };

    this.update = function(count, model, row){
      self.models[count] = model;

      lazy.render(row, this.$tableBody, function(lazy, $row){
        renderRow($row, model, count);

        self.$tableBody.find("tr[data-id=" + count + "]").replaceWith($row);
      });
    };

    this.reevaluate = function(){
      var data = {
        "invoice": JSON.stringify({
          "currency_code": this.$currencyCodeInput.val(),
          "currency_rate": this.$currencyRateInput.val()
        }),
        "models": JSON.stringify(this.models)
      };

      return $.ajax({
        "url": options.reevaluateUrl,
        "data": data,
        "type": "POST",
        "dataType": "JSON",
        "success": function(result){
          self.$tableBody.empty();
          self.models = {};

          $.each(result.rows, function(index, row){
            self.add(row["model"], row["row"]);
          });

          self.setFooter(result.footer);
        }
      });
    };

    this.setFooter = function(footer){
      lazy.render(footer, this.$tableFooter, function(lazy, $footer){
        self.$tableFooter.find("tr:not(:first)").remove();
        self.$tableFooter.append($footer);
      });
    };

    this.open = function(url, count){
      var modal = $.lazyModal({
        "size": "sm",
        "id": "invoice-item-form-modal",
        "container": "#main-container",
        "scroll": false,
        "pushState": false
      });

      modal.$modal.on("lazy.loaded", function(e, data){
        if (data.rows) {
          $.each(data.rows, function(index, row){
            if (row.row && row.model) {
              if (self.models[index]) {
                self.update(index, row.model, row.row);
              } else {
                self.add(row.model, row.row);
              }
            }
          });
        }

        if (data.footer) {
          self.setFooter(data.footer);
        }
      });

      var params = {
        "invoice": JSON.stringify({
          "currency_code": this.$currencyCodeInput.val(),
          "currency_rate": this.$currencyRateInput.val(),
          "customer_id": this.$customerInput.val()
        }),
        "models": JSON.stringify(this.models)
      };

      if (this.models[count]) {
        params.model = JSON.stringify(this.models[count]);
      }

      modal.load(admin.updateQueryParam(url, "temp", count), "POST", params, {
        scroll: false
      });
    };

    var init = function(){
        if (options.rows) {
          $.each(options.rows, function(index, row){
            self.add(row["model"], row["row"]);
          });
        }

        self.$currencyCodeInput.on("change", checkCurrencyRateVisibility);

        self.$addItemButton.on("click", function(e){
          e.preventDefault();

          var url = $(this).attr("href");

          self.open(url,count);
        });

        $form.parent().on("lazy.beforeSend", function(e, request, settings){
          if (settings.data instanceof FormData) {
            settings.data.append("items", JSON.stringify(self.models));
          }
        });

        self.$currencyCodeInput.add(self.$currencyRateInput).on("change", function(){
          self.reevaluate();
        });

        checkCurrencyRateVisibility();
      },

      renderRow = function($row, model, count){
        $row.attr("data-id", count);

        $row.find(".update-invoice-item-button").on("click", function(e){
          e.preventDefault();

          var url = $(this).attr("href");

          self.open(url, count);
        });

        $row.find(".delete-invoice-item-button").on("click", function(e){
          e.preventDefault();

          if (confirm("You are about to delete this item, are you sure?")) {
            self.remove(count);
          }
        });
      },

      checkCurrencyRateVisibility = function(){
        var value = self.$currencyCodeInput.val();

        self.$currencyRateField.toggle(value !== options.baseCurrency);
      };

    return init();
  };

  $.fn.invoiceForm = function(first, second, third){
    var invoiceForm = this.data("invoiceForm");

    if (!invoiceForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        invoiceForm = new InvoiceForm(this, first);

        this.data("invoiceForm", invoiceForm);
      }

      return this;
    }

    return invoiceForm[first](second, third);
  };
})(jQuery);