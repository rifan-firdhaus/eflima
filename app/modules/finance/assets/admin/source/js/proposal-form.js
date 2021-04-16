(function($){

  var ProposalForm = function($form, options){
    var self = this;
    var count = 1;

    this.$modelInput = $form.find("[name='Proposal[model]']");
    this.$modelIdInput = $form.find("[name='Proposal[model_id]']");
    this.$modelIdField = this.$modelIdInput.parent();

    this.$currencyCodeInput = $form.find("[data-rid='proposal-currency_code']");
    this.$currencyRateInput = $form.find("[data-rid='proposal-currency_rate']");
    this.$currencyRateField = this.$currencyRateInput.closest(".form-group");

    this.$addItemButton = $form.find(".add-proposal-item-button");
    this.$table = $form.find(".proposal-item-table");
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
        "proposal": JSON.stringify({
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

    this.sort = function(order){
      $.each(order, function(order, id){
        if (self.models[id]) {
          self.models[id]["order"] = order;
        }
      });
    };

    this.open = function(url, count){
      var modal = $.lazyModal({
        "size": "sm",
        "id": "proposal-item-form-modal",
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
        "proposal": JSON.stringify({
          "currency_code": this.$currencyCodeInput.val(),
          "currency_rate": this.$currencyRateInput.val()
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

        new Sortable(self.$tableBody.get(0), {
          animation: 300,
          handle: ".handle",
          onEnd: function(){
            self.sort(this.toArray());
          }
        });

        self.$modelInput.on("change", onModelChange);
        self.$currencyCodeInput.on("change", checkCurrencyRateVisibility);

        self.$addItemButton.on("click", function(e){
          e.preventDefault();

          var url = $(this).attr("href");

          self.open(url, count);
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

        $row.find(".update-proposal-item-button").on("click", function(e){
          e.preventDefault();

          var url = $(this).attr("href");

          self.open(url, count);
        });

        $row.find(".delete-proposal-item-button").on("click", function(e){
          e.preventDefault();

          if (confirm("You are about to delete this item, are you sure?")) {
            self.remove(count);
          }
        });
      },
      checkCurrencyRateVisibility = function(){
        var value = self.$currencyCodeInput.val();

        self.$currencyRateField.toggle(value !== options.baseCurrency);
      },
      onModelChange = function(e){
        var model = $(this).val();

        return $.ajax({
          url: options.modelInputUrl,
          dataType: "JSON",
          type: "GET",
          data: {
            model: model
          },
          success: function(data){
            if (!data.input) {
              return;
            }

            self.$modelIdField.empty();

            lazy.render(data.input, self.$modelIdField).done(function(){
              var $modelIdInput = $form.find("[name='Proposal[model_id]']");

              if ($modelIdInput.data("select2")) {
                $modelIdInput.select2("open");
              } else {
                $modelIdInput.focus();
              }
            });
          }
        });
      };

    return init();
  };

  $.fn.proposalForm = function(first, second, third){
    var proposalForm = this.data("proposalForm");

    if (!proposalForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        proposalForm = new ProposalForm(this, first);

        this.data("proposalForm", proposalForm);
      }

      return this;
    }

    return proposalForm[first](second, third);
  };
})(jQuery);
