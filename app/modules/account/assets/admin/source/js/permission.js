(function($){

  window.Permission = function($element, options){
    var self = this;

    this.options = options;
    this.$element = $element;
    this.role = null;

    this.setSource = function(role, source){
      source = Permission.parseData(source);
      this.role = role;

      var tree = this.$element.fancytree("getTree");

      return tree.reload(source);
    };

    this.setAccess = function(node, access){
      node.data.has_access = access;

      if (!access) {
        node.data.has_children_access = false;
      }

      node.render(true);

      Permission.renderChildrenNode(node);
      Permission.renderParentNode(node.parent);

      return $.ajax({
        url: admin.updateQueryParam(this.options.accessUrl, {
          name: node.key,
          role: this.role,
          access: access ? "1" : "0"
        }),
        type: "post",
        dataType: "json",
        success: function(result){
          admin.notifies(result.messages);
        }
      });
    };

    var init = function(){
      self.$element.fancytree({
        extensions: ["glyph", "table"],
        source: [],
        glyph: {
          preset: "awesome5",
          map: {
            doc: "",
            docOpen: "",
            expanderClosed: "icons8-forward icons8-size",
            expanderOpen: "icons8-expand-arrow  icons8-size"
          }
        },
        table: {
          indentation: 25,
          nodeColumnIdx: 0
        },
        renderColumns: function(event, data){
          var node = data.node;
          var $cells = $(node.tr).find(" > td");
          var $actionCell = $cells.eq(1);

          var checkboxId = "permission." + node.key;
          var $checkbox = $("<input/>", { type: "checkbox", id: checkboxId, class: "custom-control-input", style: "left:0" });
          var $label = $("<label/>", { class: "custom-control-label", for: checkboxId });
          var $checkboxContainer = $("<div/>", { class: "custom-control custom-checkbox pl-0" });

          $checkbox.prop("checked", node.data.has_access);
          $checkbox.prop("indeterminate", !node.data.has_access && node.data.has_children_access && (node.children && node.children.length > 0));

          $checkbox.on("change", function(){
            self.setAccess(node, $(this).prop("checked"));
          });

          $checkboxContainer.append($checkbox).append($label);

          $actionCell.append($checkboxContainer);
        }
      });

      return self;
    };

    return init();
  };

  Permission.renderChildrenNode = function(node){
    if (!node.children) return;

    $.each(node.children, function(index, child){
      child.data.has_access = node.data.has_access;

      if (!node.data.has_access) {
        node.data.has_children_access = false;
      }

      child.render(true);

      if (child.children) {
        Permission.renderChildrenNode(child);
      }
    });
  };

  Permission.renderParentNode = function(node){
    if (!node) return;

    node.data.has_access = Permission.isAllChildChecked(node);
    node.data.has_children_access = Permission.hasChildrenAccess(node);

    node.render(true);

    if (node.parent) {
      Permission.renderParentNode(node.parent);
    }
  };

  Permission.isAllChildChecked = function(node){
    var result = true;

    $.each(node.children, function(index, child){
      if (!result) return;

      if (!child.data.has_access) {
        result = false;
      }
    });

    return result;
  };

  Permission.hasChildrenAccess = function(permission){
    var result = false;

    if (!permission.children) {
      return false;
    }

    $.each(permission.children, function(index, child){
      if (result) return;

      if (child.data.has_access) {
        result = true;
      }

      if (child.children && Object.keys(child.children).length > 0) {
        if (Permission.hasChildrenAccess(child)) {
          result = true;
        }
      }
    });

    return result;
  };

  Permission.parseData = function(data){
    var items = [];

    Object.values(data).forEach(function(permission){
      var item = {
        title: permission.permission.description,
        key: permission.permission.name,
        folder: false,
        expanded: false,
        children: [],
        data: {
          has_access: permission.has_access,
          has_children_access: false
        }
      };

      if (Object.keys(permission.children).length > 0) {
        item.children = Permission.parseData(permission.children);
      }

      item.data.has_children_access = Permission.hasChildrenAccess(item);

      items.push(item);
    }, this);

    return items;
  };

})(jQuery);
