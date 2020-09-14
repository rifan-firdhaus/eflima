(function($){

  var FileUploader = function($element, options){
    var self = this;
    var count = 0;
    var currentInput;
    var items = {};

    this.$element = $element;
    this.inputs = [];
    this.$inputAlias = null;
    this.$browseButtons = null;
    this.$inputAliasContainer = null;
    this.$itemsContainer = null;

    this.options = function(key){
      if (typeof key === "undefined") {
        return options;
      }

      return options[key];
    };

    this.selected = function(){
      return Object.values(items).filter(value => value.item !== null).length;
    };

    this.browse = function(){
      if (!(currentInput instanceof $)) {
        let input = $("<input/>", {
          type: "file",
          name: self.$element.attr("name")
        });

        self.$element.after(input);

        setFileBrowser(input);
        addItem(input);
      }

      currentInput.click();
    };

    this.view = function(id){
      let item = items[id];

      if (item.file.url) {
        window.open(item.file.url);
      }
    };

    this.remove = function(id){
      let item = items[id];

      item.item.remove();

      if (item.input === self.$element) {
        item.input.val("");
        item.item = null;
      } else {
        item.input.remove();

        delete items[id];
      }

      triggerChange();
    };

    var init = function(){
        options = jQuery.extend(true, {}, FileUploader.defaults, options);

        createInputAlias();
        createItemsContainer();
        triggerChange();

        currentInput = self.$element;

        if (self.options("values")) {
          load(self.options("values"));
        }

        setFileBrowser(self.$element);

        if (self.options("multiple") || self.options("values").length === 0) {
          addItem(self.$element);
        }

        return self;
      },

      load = function(values){
        values.forEach(function(value){
          let id = count;

          if (self.options("multiple")) {
            addItem();
          } else {
            addItem(self.$element);
          }

          loadItem(value.src, value, id);
        });
      },

      setFileBrowser = function(input){
        input.on("change", setValue);
        input.addClass("file-uploader-input-field");

        currentInput = input;
      },

      addItem = function(input){
        let isInputAvailable = typeof input !== "undefined";

        items[count] = {
          input: input,
          file: {},
          item: null
        };

        if (isInputAvailable) {
          input.data("fileId", count);
        }

        count++;
      },

      setValue = function(){
        if (self.options("multiple")) {
          currentInput = undefined;
        }

        var $input = $(this);
        var fileReader = new FileReader();
        var file = this.files[0];
        var fileId = $input.data("fileId");

        if (!file) {
          self.remove(fileId);
        } else {
          if (isImage(file)) {
            fileReader.readAsDataURL(file);

            fileReader.addEventListener("load", function(event){
              loadItem(event.target.result, file, fileId);
            });
          } else {
            loadItem(null, file, fileId);
          }
        }
      },

      loadItem = function(src, file, id){
        let template = typeof items[id] !== "undefined" && items[id]["item"] instanceof $ ? items[id]["item"] : self.options("templates").item;

        renderItem(src, file, id, template);

        triggerChange();
      },

      isImage = function(file){
        return file && file.type.split("/")[0] === "image";
      },

      extension = function(file){
        return file.name.split(".").pop();
      },

      triggerChange = function(){
        let totalSelected = self.selected();
        let inputAliasContent = totalSelected > 0 ? self.options("texts").selected : self.options("texts").empty;

        if (self.$inputAlias) {
          self.$inputAlias.html(inputAliasContent.replace("{number}", totalSelected));
        }

        self.$element.trigger("fileUploader.change");
      },

      renderItem = function(src, file, id, template){
        if (!(template instanceof $)) {
          template = $(self.options("templates").item);
        }

        let $removeButtons = template.find("[data-fp-file-remove]");
        let $viewButtons = template.find("[data-fp-file-view]");
        let $fileNames = template.find("[data-fp-file-name]");
        let $fileSizes = template.find("[data-fp-file-size]");
        let $fileThumbnail = template.find("[data-fp-file-thumbnail]");

        $removeButtons.on("click", function(e){
          e.preventDefault();

          self.remove(id);
        });

        if (file.url) {
          $viewButtons.on("click", function(e){
            e.preventDefault();

            self.view(id);
          });
        }

        $fileThumbnail.empty();
        $fileThumbnail.append(createThumbnail(src, file));

        if (file.url) {
          $fileThumbnail.on("click", function(e){
            e.preventDefault();

            self.view(id);
          });
        }

        $fileNames.html(file.name);
        $fileSizes.html(filesize(file.size));

        self.$itemsContainer.append(template);

        items[id]["item"] = template;
        items[id]["file"] = file;

        return template;
      },

      createThumbnail = function(src, file){
        if (isImage(file)) {
          let image = croppedImage(src);

          image.classList.add("picture-uploader-thumbnail-image");

          return image;
        }

        let div = document.createElement("div");

        div.classList.add("picture-uploader-thumbnail-custom");

        div.innerHTML = extension(file);

        return div;
      },

      croppedImage = function(src){
        var canvas = document.createElement("canvas");
        var context = canvas.getContext("2d");
        var image = new Image();

        image.src = src;

        image.onload = function(){
          var imageW = image.width;
          var imageH = image.height;
          var thumbnailSize = options.thumbnailSize;
          var canvasH = 0;
          var canvasW = 0;

          if (imageW >= imageH) {
            canvasH = thumbnailSize;
            canvasW = (imageW / imageH) * canvasH;
          } else {
            canvasW = thumbnailSize;
            canvasH = (imageH / imageW) * canvasW;
          }

          var canvasX = (canvasW - thumbnailSize) / 2;
          var canvasY = (canvasH - thumbnailSize) / 2;

          canvas.width = thumbnailSize;
          canvas.height = thumbnailSize;
          context.drawImage(image, canvasX * -1, canvasY * -1, canvasW, canvasH);
        };

        return canvas;
      },

      createInputAlias = function(){
        self.$inputAliasContainer = $(self.options("templates").input);
        self.$inputAlias = self.$inputAliasContainer.find("[data-fp-input]").addBack("[data-fp-input]");
        self.$browseButtons = self.$inputAliasContainer.find("[data-fp-browse]").addBack("[data-fp-browse]");

        self.$element.after(self.$inputAliasContainer);

        self.$browseButtons.on("click", function(e){
          e.preventDefault();

          self.browse();
        });

        if (self.$inputAlias) {
          self.$inputAlias.on("click", function(e){
            e.preventDefault();

            self.browse();
          });
        }
      },

      createItemsContainer = function(){
        var template = self.options("templates").items;

        if (template instanceof $) {
          self.$itemsContainer = template;
        } else {

          var $template = $(template);
          self.$itemsContainer = $template.find("[data-fp-files-container]").addBack("[data-fp-files-container]");

          self.$inputAliasContainer.after(self.$itemsContainer);
        }
      };

    return init();
  };

  $.fn.fileUploader = function(first, second, third){
    var fileUploader = this.data("fileUploader");

    if (!fileUploader) {
      if (typeof first === "undefined" || typeof first === "object") {
        fileUploader = new FileUploader(this, first);

        this.data("fileUploader", fileUploader);
      }

      return this;
    }

    return fileUploader[first](second, third);
  };

  FileUploader.defaults = {
    files: [],
    multiple: true,
    maxFiles: null,
    minFiles: null,
    values: [],
    thumbnailSize: 50,
    texts: {
      empty: "Select files to upload",
      selected: "{number} files was selected"
    },
    templates: {
      input: `<div class="file-uploader-input-container input-group">
              <div data-fp-input class="file-uploader-input form-control"></div>
              <div class="input-group-append">
                <button type="button" data-fp-browse class="file-uploader-browse btn btn-primary"><i class="icons8-installing-updates mr-1 icons8-size"></i>Browse</button>
              </div>
            </div>`,
      items: `<div class="input-container d-flex flex-column" data-fp-files-container></div>`,
      item: `<div class="file-uploader-item py-2 border-bottom d-flex align-items-center">
            <div class="file-uploader-thumbnail mr-2" data-fp-file-thumbnail></div>  
            <div class="file-uploader-metadata flex-grow-1">
               <div class="file-uploader-name" data-fp-file-name></div>
               <div class="file-uploader-size" data-fp-file-size></div>
            </div> 
            <div class="file-uploader-action">
                <a href="javascript:void(0)" data-lazy="0" data-fp-file-view><i class="icons8-eye icons8 icons8-size"></i></a>
                <a href="javascript:void(0)" data-lazy="0" class="text-danger" data-fp-file-remove><i class="icons8-trash icons8 icons8-size"></i></a>
            </div>
          </div>`
    }
  };
})(jQuery);