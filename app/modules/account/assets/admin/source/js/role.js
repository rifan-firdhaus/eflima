(function($){

  window.Role = function($element, data, options){
    var self = this;

    this.options = options;
    this.$element = $element;

    this.parseData = function(data){
      var items = [];

      Object.values(data).forEach(function(role){
        let item = {
          title: role.role.description,
          key: role.role.name,
          folder: false,
          expanded: true,
          children: []
        };

        if (Object.keys(role.children).length > 0) {
          item.children = this.parseData(role.children);
        }

        items.push(item);
      }, this);

      return items;
    };

    this.move = function(node, parentNode){
      console.log(node, parentNode);
    };

    this.remove = function(node){
      if (!confirm("Are you sure?")) {
        return false;
      }

      return $.ajax({
        url: admin.updateQueryParam(this.options.deleteUrl, { name: node.key }),
        type: "post",
        dataType: "json",
        success: function(result){
          if (result.success) {
            node.remove();
          }

          admin.notifies(result.messages);
        }
      });
    };

    this.update = function(node, isNew){
      var parent = node.tree.getRootNode().key == node.parent.key ? null : node.parent.key;

      return $.ajax({
        url: this.options.updateUrl,
        data: { name: node.key, description: node.title, parent_name: parent, is_new: isNew ? 1 : 0 },
        type: "post",
        dataType: "json",
        success: function(result){
          if (result.success && result.model) {
            node.key = result.model.name;
            node.render();
          }

          admin.notifies(result.messages);
        }
      });
    };

    var init = function(){
      self.$element.fancytree({
        extensions: ["glyph", "dnd5", "edit", "table"],
        source: self.parseData(data),
        glyph: {
          preset: "awesome5",
          map: {
            doc: "icons8-access icons8-size",
            docOpen: "icons8-access icons8-size",
            expanderClosed: "",
            expanderOpen: ""
          }
        },
        table: {
          indentation: 20,
          nodeColumnIdx: 0
        },
        dnd5: {
          autoExpandMS: 400,
          focusOnClick: true,
          preventVoidMoves: true,
          preventRecursiveMoves: true,
          dragStart: function(){
            return true;
          },
          dragEnter: function(node, data){
            if (node.parent.key == node.tree.getRootNode().key) {
              return ["before", "after", "over"];
            }

            if (node.key == data.otherNode.parent.key) {
              return ["before", "after"];
            }

            return ["over"];
          },
          dragDrop: function(node, data){
            data.otherNode.moveTo(node, data.hitMode);

            if (data.hitMode === "over") {
              node.setExpanded(true);
            }

            self.move(data.otherNode, data.otherNode.parent);
          }
        },
        edit: {
          triggerStart: ["clickActive", "dblclick", "f2", "mac+enter", "shift+click"],
          adjustWidthOfs: 75,
          beforeEdit: function(event, data){
            data.node.setActive(true);

            return true;
          },
          save: function(event, data){
            setTimeout(function(){
              self.update(data.node, data.isNew);
            }, 1);

            return true;
          }
        },
        renderColumns: function(event, data){
          var node = data.node;
          var $cells = $(node.tr).find(" > td");
          var $actionCell = $cells.eq(2);

          var $deleteButton = $("<a/>", { html: "<i class=\"icons8-trash text-danger icons8-size\"></i>", href: "#" });
          var $permissionButton = $("<a/>", { html: "<i class=\"icons8-key mr-2 icons8-size\"></i>", href: "#" });
          var $addButton = $("<a/>", { html: "<i class=\"icons8-plus mr-2 icons8-size\"></i>", href: "#" });
          var $updateButton = $("<a/>", { html: "<i class=\"icons8-edit mr-2 icons8-size\"></i>", href: "#" });

          $addButton.on("click", function(e){
            e.preventDefault();

            node.editCreateNode("child", {
              folder: false,
              expanded: true,
              title: ""
            });
          });

          $updateButton.on("click", function(e){
            e.preventDefault();

            node.setActive(true);
            node.editStart();
          });

          $deleteButton.on("click", function(e){
            e.preventDefault();

            self.remove(node);
          });

          $actionCell.empty().append($updateButton).append($addButton).append($permissionButton).append($deleteButton);
        }
      });
    };

    return init();
  };

})(jQuery);