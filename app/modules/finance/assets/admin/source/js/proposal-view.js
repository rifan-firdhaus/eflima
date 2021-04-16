(function($){

  var ProposalView = function($element, options){
    var self = this;
    var saveContentAjax;

    this.$addItemButton = $element.find(".add-proposal-item-button");
    this.$table = $element.find(".proposal-item-table");
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

        $newRow.find(".delete-proposal-item-button").lazyLink({
          container: $element.closest("[data-rid='proposal-view-wrapper-lazy']").data("lazyContainer")
        });
      });
    };

    this.sort = function(order){
      return $.ajax({
        url: options.sortUrl,
        dataType: "JSON",
        type: "POST",
        data: { sort: order },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.open = function(url, $row){
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
      $element.closest("[data-rid='proposal-view-wrapper-lazy']").lazyContainer("load", url, "POST", {}, {
        scroll: false
      });
    };

    this.saveContent = function(content){
      if (saveContentAjax) {
        saveContentAjax.abort();
      }

      saveContentAjax = $.ajax({
        url: options.saveContentUrl,
        method: "POST",
        data: {
          content: content
        }
      });

      return saveContentAjax;
    };

    var init = function(){
      self.$addItemButton.on("click", function(e){
        e.preventDefault();

        self.open($(this).attr("href"));
      });

      var proposalContentId = $element.find("[name=proposal_view]").prev().attr("id");
      
      console.log($element.find("[name=proposal_view]"));

      tinyMCE.editors[proposalContentId].on("change", function(e, instance){
        self.saveContent(this.getContent());
      });

      self.$tableBody.on("click", ".update-proposal-item-button", function(e){
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

  $.fn.proposalView = function(first, second, third){
    var proposalView = this.data("proposalView");

    if (!proposalView) {
      if (typeof first === "undefined" || typeof first === "object") {
        proposalView = new ProposalView(this, first);

        this.data("proposalView", proposalView);
      }

      return this;
    }

    return proposalView[first](second, third);
  };

})(jQuery);
