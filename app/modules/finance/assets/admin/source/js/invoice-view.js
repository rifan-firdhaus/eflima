(function($){

  var InvoiceView = function($element, options){
    var self = this;

    this.$addItemButton = $element.find(".add-invoice-item-button");
    this.$table = $element.find(".invoice-item-table");
    this.$tableBody = this.$table.find("tbody");
    this.$tableFooter = this.$table.find("tfoot");

    this.setFooter = function(footer){
      lazy.render(footer, this.$tableFooter, function(lazy, $footer){
        self.$tableFooter.find("tr:not(:first)").remove();
        self.$tableFooter.append($footer);
      });
    };

    this.update = function(newRow, $oldRow){
      lazy.render(newRow, this.$tableBody, function(lazy, $newRow){
        if ($oldRow) {
          $oldRow.replaceWith($newRow);
        } else {
          self.$tableBody.append($newRow);
        }

        $newRow.find(".delete-invoice-item-button").lazyLink({
          container: $element.closest("[data-rid='invoice-view-wrapper-lazy']").data("lazyContainer")
        });
      });
    };

    this.sort = function(order){
        return $.ajax({
          url: options.sortUrl,
          dataType: "JSON",
          type: "POST",
          data: {sort: order},
          success: function(data){
            if (data.messages) {
              admin.notifies(data.messages);
            }
          }
        })
    };

    this.open = function(url, $row){
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
            self.update(row, $row);
          });
        }

        if (data.footer) {
          self.setFooter(data.footer);
        }
      });

      modal.load(url, "GET", {}, {
        scroll: false
      });
    };

    this.remove = function(url){
      $element.closest("[data-rid='invoice-view-wrapper-lazy']").lazyContainer("load", url, "POST", {}, {
        scroll: false
      });
    };

    var init = function(){
      self.$addItemButton.on("click", function(e){
        e.preventDefault();

        self.open($(this).attr("href"));
      });

      self.$tableBody.on("click", ".update-invoice-item-button", function(e){
        e.preventDefault();

        self.open($(this).attr("href"), $(this).closest("tr"));
      });

      new Sortable(self.$tableBody.get(0), {
        animation: 300,
        handle: ".handle",
        onEnd: function(){
          self.sort(this.toArray());
        }
      });
    };

    return init();
  };

  $.fn.invoiceView = function(first, second, third){
    var invoiceView = this.data("invoiceView");

    if (!invoiceView) {
      if (typeof first === "undefined" || typeof first === "object") {
        invoiceView = new InvoiceView(this, first);

        this.data("invoiceView", invoiceView);
      }

      return this;
    }

    return invoiceView[first](second, third);
  };

})(jQuery);
