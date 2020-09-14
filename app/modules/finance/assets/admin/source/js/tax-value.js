(function($){

  var TaxValue = function($element, options){
    var self = this;
    var addedTax = [];
    var count = 0;

    this.$element = $element;
    this.$fields = $("<div/>");
    this.$addButton = null;

    this.add = function(tax){
      var $field = this.build(tax);

      this.$fields.append($field);

      $element.trigger("taxValue.change");
    };

    this.total = function(){
      var totalTax = 0;

      this.$fields.find(" > *").each(function(){
        totalTax += parseFloat((parseFloat($(this).find("[data-input-name=value]").val()) || 0).toFixed(10));
      });

      return totalTax;
    };

    this.remove = function($formGroup){
      var tax = $formGroup.data("field").tax;

      var index = addedTax.indexOf(tax.id);

      addedTax.splice(index, 1);

      $formGroup.remove();

      $element.trigger("taxValue.change");
    };

    this.getTaxById = function(id){
      for (var i = 0; i < options.taxes.length; i++) {
        if (id == options.taxes[i].id) {
          return options.taxes[i];
        }
      }
    };

    this.readonly = function(isReadonly){
      $element.trigger('taxValue.readonly');
      options.readonly = isReadonly;
      this.$addButton.toggle(!isReadonly);
    };

    this.load = function(models){
      $.each(models, function(index, model){
        var tax = self.getTaxById(model.tax_id);

        var $field = self.build(tax, model);

        self.$fields.append($field);
      });

      self.$element.trigger("taxValue.change");
    };

    this.build = function(tax, model){
      var $formGroup = $("<div/>", {
        "class": "form-group form-row",
        "data-index": count
      });
      var $valueAliasInput = $("<input/>", {
        "class": "form-control",
        "data-input-name": "value-alias",
        "readonly": true
      });
      var $valueInput = $("<input/>", {
        "type": "hidden",
        "name": options.inputName + "[" + count + "]" + "[value]",
        "data-input-name": "value"
      });
      var $idInput = $("<input/>", {
        "type": "hidden",
        "name": options.inputName + "[" + count + "]" + "[id]",
        "data-input-name": "id"
      });
      var $taxIdInput = $("<input/>", {
        "type": "hidden",
        "name": options.inputName + "[" + count + "]" + "[tax_id]",
        "data-input-name": "tax_id"
      });
      var $inputContainer = $("<div/>", {
        class: "col-sm-9"
      });
      var $label = $("<label/>", {
        class: "col-sm-3 d-flex col-form-label",
        html: tax.name + " <strong class=\"ml-1\">" + tax.rate + "%</strong>"
      });
      var $removeButton = $("<a/>", {
        "class": "btn-remove-tax ml-auto text-danger",
        "html": "<i class=\"icon icons8-size icons8-trash\"></i>",
        "href": "#"
      });

      $removeButton.appendTo($label);

      $removeButton.on("click", function(e){
        e.preventDefault();

        self.remove($formGroup);
      });

      $removeButton.toggle(!options.readonly);

      $element.on('taxValue.readonly',function(){
          $removeButton.toggle(!options.readonly);
      });

      $valueAliasInput.inputmask(options.decimalInputMask);

      $valueAliasInput.on("change", function(e){
        $valueInput.val($valueAliasInput.inputmask("unmaskedvalue"));
      });

      $taxIdInput.val(tax.id);

      if (model) {
        $idInput.val(model.id);
      }

      $inputContainer.append($idInput).append($taxIdInput).append($valueInput).append($valueAliasInput);
      $formGroup.append($label).append($inputContainer).data("field", {
        tax: tax,
        model: model
      });

      addedTax.push(tax.id);

      count++;

      return $formGroup;
    };

    var init = function(){
        options = $.extend({}, TaxValue.defaultOptions, options);

        self.$addButton = createButton();

        self.$element.append(self.$fields).append(self.$addButton);

        self.$element.on("taxValue.change", function(e){
          self.$addButton.toggle(addedTax.length !== options.taxes.length);

          recalculateTax();
        });

        if (options.beforeTaxInput) {
          $(options.beforeTaxInput).on("change keyup", recalculateTax);
        }

        if (options.models) {
          self.load(options.models);
        }

        if (options) {
          self.readonly(typeof options.readonly === "boolean" ? options.readonly : false);
        }
      },

      recalculateTax = function(){
        var beforeTax = parseFloat((parseFloat($(options.beforeTaxInput).val()) || 0).toFixed(10));

        self.$fields.find(" > *").each(function(){
          var $valueInput = $(this).find("[data-input-name=value-alias]");
          var field = $(this).data("field");
          var tax = parseFloat((beforeTax * (field.tax.rate / 100)).toFixed(10));

          $valueInput.val(tax).trigger("change");
        });
      },

      createButton = function(){
        var $button = $("<button/>", {
          "class": "btn btn-outline-primary btn-block text-uppercase dropdown-toggle",
          "data-toggle": "dropdown",
          "html": "Add Tax"
        });
        var $dropdown = $("<div/>", {
          class: "dropdown offset-md-3"
        });
        var $menu = $("<div/>", {
          class: "dropdown-menu"
        });

        $element.on('taxValue.readonly',function(){
            $button.toggle(!options.readonly);
        });

        $.each(options.taxes, function(index, tax){
          var $item = $("<a/>", {
            class: "dropdown-item",
            href: "#",
            html: tax.name + " <strong>" + tax.rate + "%</strong>"
          });

          $item.on("click", function(e){
            e.preventDefault();

            self.add(tax);
          });

          $item.data("tax", tax).appendTo($menu);

          self.$element.on("taxValue.change", function(e){
            $item.toggle(addedTax.indexOf(tax.id) === -1);
          });
        });

        $dropdown.append($button).append($menu);

        return $dropdown;
      };

    return init();
  };

  TaxValue.defaultOptions = {
    decimalInputMask: {
      "alias": "decimal",
      "autoGroup": true,
      "rightAlign": true,
      "radixPoint": ".",
      "groupSeparator": ","
    }
  };

  $.fn.taxValue = function(first, second, third){
    var taxValue = this.data("taxValue");

    if (!taxValue) {
      if (typeof first === "undefined" || typeof first === "object") {
        taxValue = new TaxValue(this, first);

        this.data("taxValue", taxValue);
      }

      return this;
    }

    return taxValue[first](second, third);
  };

})(jQuery);