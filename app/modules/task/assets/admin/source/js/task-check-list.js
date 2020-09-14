(function($){
  var TaskCheckList = function($element, options){
    var self = this;

    this.data = options.data;
    this.modelClass = "TaskChecklist";
    this.template = "<div class=\"task-check-list-item\"><div class=\"handle\"></div><input type=\"hidden\" class=\"task-check-list-order-input\"/><div class=\"checkbox-wrapper\"><div class=\"custom-control custom-checkbox no-label\"><input class=\"task-check-list-checked-hidden-input\" type=\"hidden\"/><input type=\"checkbox\" value=\"1\" class=\"custom-control-input task-check-list-checked-input\"><label class=\"custom-control-label\"></label></div></div><div class=\"text-wrapper\"><input class=\"task-check-list-content-input\" type=\"text\"/></div><a href=\"#\" class=\"task-check-list-remove-item text-danger\"><i class=\"icons8-trash icons8-size\"></i></a></div>";
    this.$wrapper = $("<div/>");
    this.$addButton = $("<a/>", { href: "#", class: "btn btn-primary btn-sm btn-add-check-list btn-block", text: "Add Check List" });
    this.items = {};

    this.url = options.url;

    this.addItem = function(item, focus){
      var $template = $(this.template);
      var $checkbox = $template.find("input[type=checkbox].task-check-list-checked-input");
      var $hidden = $template.find("input[type=hidden].task-check-list-checked-hidden-input");
      var $checkboxLabel = $checkbox.next("label");
      var $input = $template.find("input[type=text].task-check-list-content-input");
      var $orderInput = $template.find("input[type=hidden].task-check-list-order-input");
      var $removeButton = $template.find(".task-check-list-remove-item");

      if (typeof item.id === "undefined") {
        item.id = generateUniqueId();
        item.isNew = true;
      } else {
        item.isNew = false;
      }

      $checkbox.attr("id", this.getAttributeId(item, "is_checked"));
      $checkbox.attr("name", this.getAttributeName(item, "is_checked"));
      $hidden.attr("name", this.getAttributeName(item, "is_checked"));
      $checkboxLabel.attr("for", this.getAttributeId(item, "is_checked"));
      $checkbox.prop("checked", parseInt(item.is_checked));

      $input.attr("id", this.getAttributeId(item, "label"));
      $input.attr("name", this.getAttributeName(item, "label"));
      $input.val(item.label);

      $orderInput.attr("id", this.getAttributeId(item, "order"));
      $orderInput.attr("name", this.getAttributeName(item, "order"));
      $orderInput.val(item.order);

      $template.attr("data-id", item.id);

      this.$wrapper.append($template);
      this.items[item.id] = item;

      if (focus !== false) {
        $input.focus();
      }

      $input.on("blur change", function(){
        if ($input.val() == "") {
          self.removeItem(item.id);
        }
      });

      $input.on("change", function(){
        item.label = $input.val();
      });

      $checkbox.on("change", function(){
        item.is_checked = $checkbox.is(":checked") ? 1 : 0;
      });

      if (this.url) {
        $input.add($checkbox).on("change", function(){
          self.save(item);
        });
      }

      $input.on("keydown", function(e){
        if (e.keyCode === 13) {
          e.preventDefault();
          e.stopPropagation();

          self.addItem({});

          return false;
        }
      });

      $removeButton.on("click", function(e){
        e.preventDefault();

        self.removeItem(item.id);
      });
    };

    this.sendSort = function(sort){
      return $.ajax({
        url: options.sortUrl,
        dataType: "JSON",
        type: "POST",
        data: { sort: sort },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.save = function(item){
      return $.ajax({
        type: "POST",
        data: item,
        url: this.url,
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }

          if (data.id) {
            $element.find("[data-id=" + item.id + "]").attr("data-id", data.id);

            item.id = data.id;
            item.isNew = false;
          }
        }
      });
    };

    this.getAttributeName = function(item, attribute){
      return this.modelClass + "[" + item.id + "][" + attribute + "]";
    };

    this.getAttributeId = function(item, attribute){
      return this.modelClass + "-" + item.id + "-" + attribute + "-";
    };

    this.getIndexById = function(id){
      var result = null;

      $.each(this.items, function(index, value){
        if (value.id == id) {
          result = index;
        }
      });

      return result;
    };

    this.applySort = function(sorts){
      $.each(sorts,function(sort,id){
        var index = self.getIndexById(id);

        $element.find("[data-id=" + id + "] .task-check-list-order-input").val(sort);

        self.items[index].order = sort;
      });
    };

    this.removeItem = function(id){
      var index = this.getIndexById(id);

      if (this.url && !this.items[index].isNew) {
        this.items[index]["label"] = "";
        this.save(this.items[index]);
      }

      $element.find("[data-id=" + id + "]").remove();

      delete this.items[index];
    };

    this.render = function(){
      for (var item of this.data) {
        this.addItem(item, false);
      }

      this.$wrapper.appendTo($element);
      $element.append(this.$addButton);

      this.$addButton.on("click", function(e){
        e.preventDefault();

        self.addItem({});
      });

      new Sortable(this.$wrapper.get(0), {
        animation: 300,
        handle: ".handle",
        onEnd: function(){
          self.applySort(this.toArray());

          if(self.url){
            self.sendSort(this.toArray())
          }
        }
      });
    };

    var init = function(){
        $element.addClass("task-check-list");

        self.modelClass = options.modelClass;

        self.render();
      },

      generateUniqueId = function(){
        return "__" + $.now() + Math.round(Math.random() * 1000000);
      };

    return init();
  };

  $.fn.taskCheckList = function(firstArgument, secondArcument){
    var $element = $(this);

    var taskCheckList = $element.data("TaskCheckList");

    if (!taskCheckList) {
      $element.each(function(){
        var options = $.extend({}, firstArgument);

        if (!options.data) {
          options.data = [];
        }

        taskCheckList = new TaskCheckList($element, options);

        $element.data("TaskCheckList", taskCheckList);
      });

      return $element;
    } else if (typeof firstArgument === "string") {
      return taskCheckList[firstArgument](secondArcument);
    }

    return taskCheckList;
  };

})(jQuery);