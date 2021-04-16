(function(){

  window.Note = function(options){
    var self = this;
    var $body = $("body");
    var isLoaded = false;

    this.$container = $(options.container);
    this.$trigger = $("#note-button");
    this.$searchQueryInput = self.$container.find(".note-search-query-input");
    this.$searchForm = self.$container.find(".note-search-form");
    this.$closeButton = this.$container.find(".note-container-close");

    this.open = function(){
      this.$container.show();
      this.$container.addClass("note-open");
      $body.addClass("note-open");
    };

    this.close = function(){
      this.$container.removeClass("note-open");

      setTimeout(function(){
        self.$container.hide();
        $body.removeClass("note-open");
      }, 300);
    };

    this.toggle = function(){
      if (this.isOpen()) {
        this.close();
      } else {
        this.open();
      }
    };

    this.isOpen = function(){
      if (!isLoaded) {
        this.$container.noteContainer("load");

        isLoaded = true;
      }

      return this.$container.hasClass("note-open");
    };

    var init = function(){
        self.$trigger.on("click", onTriggerClicked);

        self.$searchForm.on("submit", function(e){
          e.preventDefault();

          self.$container.noteContainer("clear");
          self.$container.noteContainer("load", self.$searchForm.serialize());
        });

        $body.on("keyup", function(e){
          if (self.isOpen() && e.keyCode === 27) {
            self.close();
          }
        });

        self.$closeButton.on("click", function(){
          self.close();
        });
      },

      onTriggerClicked = function(e){
        e.preventDefault();

        self.toggle();
      };

    return init();
  };

  var NoteContainer = function($element, options){
    var self = this;
    var $currentContainer = null;

    this.$addButton = $element.find(".btn-add-note");
    this.$items = $element.find(".note-items");

    this.clear = function(){
      var time = 0;

      this.$items.find(".note-item-container").each(function(){
        var $container = $(this);

        setTimeout(function(){
          self.$items.packery("remove", $container);
        }, time);

        time += 30;
      });
    };

    this.togglePin = function(){

    };

    this.loadItem = function(result, $container){
      if (!$container) {
        $container = createContainer();
      }

      var $noteItem = $container.children(".note-item");

      $noteItem.empty();

      lazy.render(result, $noteItem).done(function(){
        var $updateButton = $container.find(".btn-update-note");
        var $deleteButton = $container.find(".btn-remove-note");
        var $pinButton = $container.find(".btn-pin-note");

        $pinButton.on("click", function(e){
          e.preventDefault();

          var isPinned = $pinButton.attr("data-pin");

          self.togglePin($pinButton.attr("href"), $container);
          $pinButton.attr("data-pin", isPinned == "1" ? "0" : "1");
        });

        $updateButton.on("click", function(e){
          e.preventDefault();

          self.add($updateButton.attr("href"), $container);
        });

        $deleteButton.on("click", function(e){
          e.preventDefault();

          self.delete($deleteButton.attr("href"), $container);
        });
      });

      return $container;
    };

    this.load = function(data){
      return $.ajax({
        url: options.url,
        data: data,
        dataType: "JSON",
        success: function(result){
          var time = 0;
          $.each(result, function(index, html){
            var $container = self.loadItem(html);

            setTimeout(function(){
              self.$items.append($container).packery("appended", $container).packery();
            }, time);

            time += 30;
          });
        }
      });
    };

    this.delete = function(url, $container){
      return $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        success: function(data){
          if (data.success) {
            self.$items.packery("remove", $container).packery();
          }
        }
      });
    };

    this.togglePin = function(url, $container){
      return $.ajax({
        url: url,
        type: "POST",
        dataType: "JSON",
        success: function(data){
          self.$items.packery("remove", $container).packery();
        }
      });
    };

    this.add = function(url, $container){
      if (!$container && $currentContainer) {
        var $contentInput = $currentContainer.find(".note-content-input");
        var contentInputId = $contentInput.attr("id");

        var contentInputTinyMCE = tinyMCE.get(contentInputId);

        contentInputTinyMCE.focus();

        return;
      }

      return $.ajax({
        url: url,
        success: function(form){
          var renderPromise = renderForm(form, $container);

          if (!$container) {
            renderPromise.done(function($content){
              $currentContainer = $content.closest(".note-item-container");

              $currentContainer.find(".note-form").parent().on("lazy.loaded", function(e, data){
                if (data.item) {
                  $currentContainer = null;
                }
              });
            });
          }
        }
      });
    };

    var init = function(){
        self.$addButton.on("click", onAddButtonClicked);

        self.$items.packery({
          itemSelector: ".note-item-container",
          columnWidth: '.note-item-container-sizer',
          percentPosition: true,
        });

        if (options.autoLoad) {
          self.load().done(function(result){
            if (result.length === 0) {
              self.$addButton.click();
            }
          });
        }

        return self;
      },

      setFocus = function(focus, $container){
        $container.toggleClass("focus", focus);
      },

      onAddButtonClicked = function(e){
        e.preventDefault();

        self.add($(this).attr("href"));
      },

      createContainer = function(){
        var $container = $("<div/>", {
          class: "note-item-container"
        });

        var $noteItem = $("<div/>", {
          class: "note-item"
        });

        $container.append($noteItem);

        return $container;
      },

      renderForm = function(form, $container){
        if (typeof $container === "undefined") {
          $container = createContainer();

          self.$items.prepend($container).packery("prepended", $container).packery();
        }

        var $noteItem = $container.children(".note-item");

        return lazy.render(form, $noteItem).done(function(){
          var $form = $container.find(".note-form");
          var form = $form.data("noteForm");

          if (!form) {
            return;
          }

          var $formLazy = $form.parent();

          var contentTinyMCE = form.getContentTinyMCE();

          var $attachmentInput = $container.find(".note-attachment-input");

          $formLazy.on("lazy.loaded", function(e, data){
            if (data.item) {
              contentTinyMCE.destroy(false);
              self.loadItem(data.item, $container);
              self.$items.packery();
            }
          });

          $attachmentInput.on("fileUploader.change", function(){
            self.$items.packery();
          });

          form.$titleInput.on({
            "keydown": function(){
              setTimeout(function(){
                self.$items.packery();
              }, 0);
            },
            "focus": function(){
              setFocus(true, $container);
            },
            "blur": function(){
              setFocus(false, $container);
            }
          });

          contentTinyMCE.on("init", function(e){
            this.focus();
            self.$items.packery();
          });

          contentTinyMCE.on("focus", function(){
            setFocus(true, $container);
          });

          contentTinyMCE.on("blur", function(){
            setFocus(false, $container);
          });

          contentTinyMCE.on("NodeChange SetContent keyup FullscreenStateChanged ResizeContent", function(){
            self.$items.packery();
          });
        });
      };

    return init();
  };

  $.fn.noteContainer = function(first, second, third){
    var noteContainer = this.data("noteContainer");

    if (!noteContainer) {
      if (typeof first === "undefined" || typeof first === "object") {
        noteContainer = new NoteContainer(this, first);

        this.data("noteContainer", noteContainer);
      }

      return this;
    }

    return noteContainer[first](second, third);
  };

})(jQuery);
