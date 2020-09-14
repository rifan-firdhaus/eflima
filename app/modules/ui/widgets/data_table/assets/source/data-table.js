(function($){

  var DataTable = function($element, options){
    var self = this,
      selected = [],
      selectionColumn = {};

    this.$table = $element.find("table:first");
    this.$tableHead = this.$table.find("thead");
    this.$tableBody = this.$table.find("tbody");
    this.$tableFooter = this.$table.find("tfooter");

    this.getSelected = function(){
      return selected;
    };

    this.select = function(id){
      if ($.isArray(id)) {
        for (var singleId of id) {
          this.select(singleId);
        }

        return this;
      }

      if (this.isSelected(id)) {
        return this;
      }

      selected.push(id);

      if (selectionColumn.selectedClass) {
        var $row = this.getRow(id);

        $row.addClass(selectionColumn.selectedClass);
        $row.find(selectionColumn.selector + " [type='checkbox']").prop("checked", true);

        checkHeaderCheckbox();
      }

      $element.trigger("dataTable.select", id, selected);
      $element.trigger("dataTable.change", selected);

      return this;
    };

    this.setSelectionColumn = function(options){
      selectionColumn = options;

      this.$tableBody.on("change", selectionColumn.selector + " input[type=checkbox]", onCheckboxChange);
      this.$tableHead.on("change", selectionColumn.selector + " input[type=checkbox]", onCheckboxAllChange);
    };

    this.unselect = function(id){
      if ($.isArray(id)) {
        for (var singleId of id) {
          this.unselect(singleId);
        }

        return this;
      }

      if (!this.isSelected(id)) {
        return this;
      }

      selected.splice(selected.indexOf(id), 1);

      if (selectionColumn.selectedClass) {
        var $row = this.getRow(id);

        $row.removeClass(selectionColumn.selectedClass);
        $row.find(selectionColumn.selector + " [type='checkbox']").prop("checked", false);

        checkHeaderCheckbox();
      }

      $element.trigger("dataTable.unselect", id, selected);
      $element.trigger("dataTable.change", selected);

      return this;
    };

    this.selectAll = function(){
      this.getRows().each(function(){
        var id = $(this).data("id");

        self.select(id);
      });
    };

    this.clearSelection = function(){
      this.getRows().each(function(){
        var id = $(this).data("id");

        self.unselect(id);
      });
    };

    this.isSelected = function(id){
      return selected.indexOf(id) > -1;
    };

    this.getRows = function(){
      return this.$tableBody.find("tr");
    };

    this.getRow = function(id){
      return this.getRows().filter("[data-id='" + id + "']");
    };

    var init = function(){
        return self;
      },
      findRowByCheckbox = function(value){
        return self.$tableBody.find(selectionColumn.selector + " input[type=checkbox][value='" + value + "']").closest("tr");
      },
      checkHeaderCheckbox = function(){
        var $headCheckbox = self.$tableHead.find(selectionColumn.selector + " [type='checkbox']");

        if (self.getRows().length === selected.length) {
          $headCheckbox.prop("indeterminate", false);
          $headCheckbox.prop("checked", true);
        } else {
          if (selected.length !== 0) {
            $headCheckbox.prop("indeterminate", true);
          } else {
            $headCheckbox.prop("indeterminate", false);
          }

          $headCheckbox.prop("checked", false);
        }
      },
      onCheckboxAllChange = function(){
        var isChecked = $(this).is(":checked");

        if (isChecked) {
          self.selectAll();
        } else {
          self.clearSelection();
        }
      },
      onCheckboxChange = function(){
        var isChecked = $(this).is(":checked");
        var value = $(this).val();
        var $row = findRowByCheckbox(value);
        var id = $row.data("id");

        if (isChecked) {
          self.select(id);
        } else {
          self.unselect(id);
        }
      };

    return init();
  };

  var initDataTable = function($element, options){
    var dataTable = $element.data("lazyForm");

    if (!dataTable) {
      dataTable = new DataTable($element, options);

      $element.data("dataTable", dataTable);
    }

    return dataTable;
  };

  $.fn.dataTable = function(arg1, arg2, arg3){
    var dataTable = this.data("dataTable");

    if (dataTable && typeof arg1 === "string") {
      if (typeof dataTable[arg1] !== "function") {
        throw new Error(arg1 + " is not a function");
      }

      return dataTable[arg1](arg2, arg3);
    }

    this.each(function(){
      initDataTable($(this), arg1);
    });

    return this;
  };

})(jQuery);