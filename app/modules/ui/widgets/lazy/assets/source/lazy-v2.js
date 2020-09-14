(function($){
  window.lazy = new (function(){
    var self = this,
      $loadedScripts;

    this.load = function(url, method, data, ajaxOptions, $container, callback){
      if (typeof ajaxOptions === "undefined") {
        ajaxOptions = {};
      }

      ajaxOptions.url = url;
      ajaxOptions.type = method ? method : "GET";
      ajaxOptions.data = data ? data : {};

      if (typeof ajaxOptions.data === "object" && ajaxOptions.data instanceof FormData) {
        ajaxOptions.processData = false;
        ajaxOptions.contentType = false;
      }

      var beforeSend = ajaxOptions.beforeSend;

      ajaxOptions.beforeSend = function(request, settings){
        request.setRequestHeader("X-Lazy", "1");

        if (beforeSend) {
          beforeSend.call(this, request, settings);
        }
      };

      var deferred = $.Deferred();

      var ajax = $.ajax($.extend({
        dataType: "JSON"
      }, ajaxOptions));

      ajax.done(function(data, statusText, xhr){
        if (data.content !== false) {
          self.render(data.content, $container, callback).done(function(){
            deferred.resolve(data, statusText, xhr);
          });
        } else {
          deferred.reject(xhr);
        }
      }).fail(function(xhr){
        deferred.reject(xhr);
      });

      return deferred.promise();
    };

    this.render = function(content, $container, callback){
      var $html = $($.parseHTML(content, document, true)),
        $scripts = find($html, "script").remove(),
        $linkTags = find($html, "link[href]").remove(),
        $content = $html.not($scripts).not($linkTags);

      if (callback) {
        callback.call(self, $content, $html, $scripts, $linkTags);
      } else {
        $container.html($content);
      }

      var deferred = $.Deferred();

      loadScriptTags($scripts, $container).done(function(){
        deferred.resolve($content);
      }).fail(function(){
        deferred.reject();
      });

      loadLinkTags($linkTags, $container);

      return deferred.promise();
    };

    /***** PRIVATE *****/
    var find = function($element, selector){
        return $element.find(selector).addBack(selector);
      },

      loadLinkTags = function($linkTags){
        if (!$linkTags) return;

        var $loadedLinkTags = $("link[href]");

        $linkTags.each(function(){
          var href = this.href,
            $linkTag = $loadedLinkTags.filter(function(){
              return this.href == href;
            });

          if ($linkTag.length > 0) {
            return;
          }

          document.head.appendChild(this);
        });
      },

      loadScriptTag = function($context, callback){
        var src = this.src;
        var $script = $loadedScripts.filter(function(){
          return this.src === src;
        });

        if ($script.length > 0) {
          callback();

          return;
        }

        if (src) {
          $.getScript(src).always(callback);

          document.head.appendChild(this);
        } else {
          $context.append(this);

          callback();
        }
      },

      loadScriptTags = function($scripts, $context){
        var deferred = $.Deferred();

        if (!$scripts) {
          deferred.resolve();

          return;
        }

        $loadedScripts = $("script[src]");

        var iteration = 0,
          nextIteration = function(){
            if (iteration >= $scripts.length) {
              deferred.resolve();

              return;
            }

            var $script = $scripts[iteration];

            iteration++;

            loadScriptTag.call($script, $context, nextIteration);
          };

        nextIteration();

        return deferred.promise();
      };
  })();

  // TODO: add cache capability
  var LazyContainer = function($element, options){
    var self = this,
      promise = null,
      _url = window.location.href;

    this.uniqueId = null;

    this.option = function(key, value){
      if (typeof value === "undefined") {
        return key ? options[key] : $.extend({}, options);
      }

      options[key] = value;

      return $element;
    };

    this.load = function(url, method, data, options, setHistory){
      options = options ? options : {};
      method = method ? method : "get";
      setHistory = typeof setHistory === "undefined" || setHistory;
      var ajaxOptions = options.ajax ? options.ajax : {};

      ajaxOptions.beforeSend = function(request, settings){
        url = settings.url;

        if (self.option("container")) {
          request.setRequestHeader("X-Lazy-Container", self.option("container"));
        }

        $element.trigger("lazy.beforeSend", [request, settings]);
      };

      $element.trigger("lazy.load", [url, method, data, options]);

      promise = lazy.load(url, method, data, ajaxOptions, $element, render);

      promise.done(function(result, statusText, xhr){
        _url = url;

        self.uniqueId = generateId();

        if (setHistory && url !== window.location.href && self.option("pushState") !== false && method.toLowerCase() === "get") {
          History.pushState({
            type: "lazy",
            container: $element.data("rid"),
            containerId: $element.attr("id"),
            url: url,
            data: data,
            method: method,
            options: options,
            rendered: true,
            uniqueId: self.uniqueId
          }, result.title, url);
        }

        initContent($element.children());

        $element.trigger("lazy.loaded", [result, xhr, options]);

        console.log((typeof self.option("scroll") === "undefined" || self.option("scroll")));
        console.log(options.scroll !== false);

        if ((typeof self.option("scroll") === "undefined" || self.option("scroll")) && (options.scroll !== false)) {
          $(document).scrollTop($element.scrollTop());
        }
      });

      promise.fail(function(xhr){
        var url = xhr && xhr.getResponseHeader("X-Redirect");

        if (url) {
          $element.trigger("lazy.redirect", [url, xhr, options]);
        }

        $element.trigger("lazy.failed", [xhr.responseJSON, xhr]);
      });

      return promise;
    };

    var init = function(){
        options = $.extend({}, LazyContainer.defaults, options);

        if (!options.container) {
          var id = $element.data("rid");

          if (!id) {
            id = generateId();

            $element.attr("data-rid", id);
          }

          options.container = "#" + id;
        }

        if (options.main) {
          $element.attr("main-container", "1");
        }

        initContent($element.children());

        $element.data("lazyContainer", self);

        $element.trigger("lazy.init");

        return self;
      },

      render = function($content){
        $element.trigger("lazy.beforeRender", [$content]);

        $element.html($content);

        $element.trigger("lazy.render", [$content]);
      },

      initContent = function($content){
        if (self.option("linkSelector")) {
          var $linkSelector = $content.find(self.option("linkSelector")).addBack(self.option("linkSelector")).not("[href^='#'],[data-lazy=0]").filter(function(){
            return !$(this).data("lazyLink");
          });

          $linkSelector.lazyLink({
            container: self
          });
        }

        $element.trigger("lazy.initContent", $content);
      },

      generateId = function(){
        return $.now() + Math.round(Math.random() * 20000);
      };

    return init();
  };

  LazyContainer.defaults = {
    pushState: true,
    container: "",
    linkSelector: "a",
    formSelector: "form",
    main: false,
    url: "",
    scroll: true,
    ajax: {
      dataType: "JSON"
    }
  };

  window.LazyLink = function($element, options){
    var self = this,
      container = null,
      confirmationDeferred = null,
      $popover = null;

    this.$popoverConfimation = null;

    this.go = function(){
      var url = $element.attr("href");
      var event = $.Event("lazyLink.beforeGo");

      $element.trigger(event);

      if (event.result === false) {
        return;
      }

      this.confirmation().done(function(){
        var containerObject = self.container();

        if (options.modal) {
          var modalOptions = {
            container: containerObject,
            // id: options.modal,
            size: $element.data("lazy-modal-size")
          };

          var modalElementOptions = $element.data("lazy-modal-options");

          if (modalElementOptions) {
            modalOptions = $.extend({}, modalElementOptions, modalOptions);
          }

          options.scroll = false;

          containerObject = $.lazyModal(modalOptions);
        }

        $element.trigger("lazyLink.go", [containerObject]);

        containerObject.load(url, options.method, options.data, options);
      });
    };

    this.confirmation = function(){
      confirmationDeferred = $.Deferred();

      if (!options.confirmation) {
        confirmationDeferred.resolve();

        return confirmationDeferred.promise();
      }

      createConfirmationPopover();

      confirmationDeferred.always(function(){
        $popover.popover("hide");
      });

      return confirmationDeferred.promise();
    };

    this.container = function(){
      if (!container) {

        if (options.modal) {
          if ($element.data("lazy-container")) {
            options.container = $element.data("lazy-container");
          }

          container = options.container;
        } else {
          if ($element.data("lazy-container")) {
            options.container = $element.data("lazy-container");

            if (options.container === "#main#") {
              options.container = $element.closest("[main-container]").data("lazyContainer");
            }
          }

          if (typeof options.container === "string") {
            container = $(options.container).data("lazyContainer");
          } else {
            container = options.container;
          }
        }
      }

      return container;
    };

    var init = function(){
        options = $.extend({}, LazyLink.defaults, options);

        $element.on("click", function(e){
          if (e.defaultPrevented) {
            return;
          }

          e.preventDefault();

          self.go();
        });

        $element.data("lazyLink", self);

        return self;
      },

      createConfirmationPopover = function(){
        self.$popoverConfimation = $("<div/>");

        var $yes = $("<button/>", { "html": "<i class=\"icon icons8-ok\"></i>Yes", "data-statement": "yes", "class": "btn btn-sm btn-primary" });
        var $no = $("<button/>", { "html": "<i class=\"icon icons8-cancel\"></i>No", "data-statement": "no", "class": "btn btn-sm btn-secondary" });
        var $content = $("<div/>", { html: options.confirmation });
        var $toolbar = $("<div/>", { class: "confirmation-actions btn-group mt-2 w-100" });

        $toolbar.append($yes).append($no);
        self.$popoverConfimation.append($content).append($toolbar);

        $yes.on("click", function(e){
          e.preventDefault();

          confirmationDeferred.resolve();
        });

        $no.on("click", function(e){
          e.preventDefault();

          confirmationDeferred.reject();
        });

        if (!$popover) {
          $popover = $element;

          if ($element.hasClass("dropdown-item")) {
            var $dropdown = $element.closest(".dropdown").first();

            if ($dropdown.length) {
              $popover = $dropdown;
            }
          }
        }

        $popover.popover("dispose");

        $popover.popover({
          title: $element.attr("title"),
          content: self.$popoverConfimation,
          trigger: "manual",
          html: true
        });

        $popover.popover("show");
      };

    return init();
  };

  LazyLink.defaults = {
    container: null,
    data: {},
    method: "GET"
  };

  var LazyForm = function($element, options){
    var self = this,
      selfContain = true;

    this.$form = null;

    this.submit = function(){
      this.$form.trigger("submit");
    };

    var init = function(){
        options = $.extend({}, LazyForm.defaults, options);

        if ($element.get(0).tagName !== "FORM") {
          selfContain = false;
        }

        initContent();

        $element.on("lazy.loaded", function(){
          if (!selfContain) {
            initContent();
          }
        });

        $.extend(self, new LazyContainer($element, options));

        $element.on("lazy.loaded lazy.failed", function(e){
          if (e.target === $element.get(0)) {
            $element.find("[data-lazy-submit-button]").prop("disabled", false);
          }
        }).on("lazy.load", function(e){
          if (e.target === $element.get(0)) {
            $element.find("[data-lazy-submit-button]").prop("disabled", true);
          }
        });

        return self;
      },

      initContent = function(){
        self.$form = selfContain ? $element : $element.children("form").first();

        self.$form.on("submit", function(e){
          e.stopImmediatePropagation();
          e.preventDefault();

          onSubmit();
        });
      },

      onSubmit = function(){
        var action = self.$form.attr("action");
        var method = self.$form.attr("method");
        var data = method.toLowerCase() === "post" ? new FormData(self.$form.get(0)) : self.$form.serialize();

        $element.trigger("lazy:submit", [data]);

        self.load(action, method, data);
      };

    return init();
  };

  LazyForm.defaults = {
    container: null
  };

  // TODO: Add Lazy Modal functionality
  var LazyModal = function(options){
    var self = this,
      beforeOpenHistoryState;

    this.$modal = $("<div class=\"fade modal\" role=\"dialog\"><div class=\"modal-dialog modal-dialog-scrollable modal-dialog-centered\" role=\"document\"><div class=\"modal-content\"><div class=\"modal-header\"><h5 class=\"modal-title\"></h5><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button></div><div class=\"modal-body\"></div></div></div></div>");
    this.$modalDialog = this.$modal.find(".modal-dialog");
    this.$modalHeader = this.$modal.find(".modal-header");
    this.$modalTitle = this.$modalHeader.find(".modal-title");
    this.$modalBody = this.$modal.find(".modal-body");

    this.title = function(title){
      this.$modalTitle.html(title);
    };

    this.close = function(){
      var event = $.Event("lazyModal.beforeClose");

      this.$modal.trigger(event);

      if (event.result === false) {
        return;
      }

      backToBeforeOpen();

      this.$modal.modal("hide");

      this.$modal.trigger("lazyModal.close");
    };

    this.destroy = function(){
      this.$modal.modal("dispose");
      this.$modal.remove();
      this.$modal.trigger("lazyModal.destroy");
    };

    var init = function(){
        options = $.extend({}, LazyModal.defaults, options);

        beforeOpenHistoryState = History.getState();

        if (options.id) {
          self.$modal.attr("data-rid", options.id);
          self.$modalBody.attr("id", options.id + "-body");
        }

        self.$modal.appendTo("body").modal({ show: false, backdrop: "static" });

        if (options.size) {
          self.$modalDialog.addClass(options.size);
        }

        $.extend(self, new LazyContainer(self.$modalBody, options));

        self.$modalBody.on("lazy.loaded", onLoaded);
        self.$modalBody.on("lazy.loaded lazy.failed", onAlways);
        self.$modalBody.on("lazy.initContent", onInitContent);
        self.$modalBody.on("lazy.beforeSend", onBeforeSend);
        self.$modal.on("hidden.bs.modal", onHide);
        self.$modal.on("click", "[data-modal-close]", function(e){
          e.preventDefault();

          self.close();
        });

        self.$modal.data("lazyModal", self);

        self.$modal.trigger("lazyModal.init");

        return self;
      },

      onLoaded = function(e, data){
        if (data.title) {
          self.$modalTitle.html(data.title);
        }

        self.$modal.modal("handleUpdate");
        self.$modal.modal("show");
      },

      onAlways = function(e, data){
        if (typeof data === "object" && data && data.close) {
          self.close();
        }
      },

      onBeforeSend = function(e, request, settings){
        if (e.target === self.$modalBody.get(0)) {
          request.setRequestHeader("X-Lazy-Modal", "#" + options.id);
        }

        request.setRequestHeader("X-Lazy-Inside-Modal", "#" + options.id);
      },

      onHide = function(e){
        if (e.target !== self.$modal.get(0)) {
          return;
        }

        self.destroy();
      },

      onInitContent = function(e, $content){
        if (!($content instanceof jQuery)) {
          $content = $($content);
        }

        var $header = $content.filter(".header");

        if ($header.length > 0) {
          self.$modalHeader.html($header);
        }
      },

      backToBeforeOpen = function(){
        if (self.option("pushState")) {
          if (beforeOpenHistoryState.data.options) {
            beforeOpenHistoryState.data.options.scroll = false;
          }

          gotoState(beforeOpenHistoryState);
        }
      };

    return init();
  };

  LazyModal.defaults = $.extend({}, LazyContainer.defaults, {
    container: null,
    size: "modal-xl",
    main: true,
    scroll: false
  });

  var initLazyContainer = function($element, options){
    var lazyContainer = $element.data("lazyContainer");

    if (!lazyContainer) {
      lazyContainer = new LazyContainer($element, options);
    }

    return lazyContainer;
  };

  $.fn.lazyContainer = function(arg1, arg2, arg3, arg4, arg5){
    var lazyContainer = this.data("lazyContainer");

    if (lazyContainer && typeof arg1 === "string") {
      return lazyContainer[arg1](arg2, arg3, arg4, arg5);
    }

    this.each(function(){
      initLazyContainer($(this), $.extend({}, arg1));
    });

    return this;
  };

  var initLazyForm = function($element, options){
    var lazyForm = $element.data("lazyForm");

    if (!lazyForm) {
      lazyForm = new LazyForm($element, options);

      $element.data("lazyForm", lazyForm);
    }

    return lazyForm;
  };

  $.fn.lazyForm = function(arg1, arg2){
    var lazyForm = this.data("lazyForm");

    if (lazyForm && typeof arg1 === "string") {
      return lazyForm[arg1](arg2);
    }

    this.each(function(){
      initLazyForm($(this), $.extend({}, arg1));
    });

    return this;
  };

  var initLazyLink = function($element, options){
    var lazyLink = $element.data("lazyLink");

    if (!lazyLink) {
      if (!options) {
        options = {};
      }

      var _options = $element.data("lazy-options");

      if (_options) {
        options = $.extend(options, _options);
      }

      if (!options.confirmation) {
        options.confirmation = $element.data("confirmation");
      }

      if (!options.modal) {
        options.modal = $element.data("lazy-modal");
      }

      lazyLink = new LazyLink($element, options);
    }

    return lazyLink;
  };

  $.fn.lazyLink = function(arg1, arg2){
    var lazyLink = this.data("lazyLink");

    if (lazyLink && typeof arg1 === "string") {
      return lazyLink[arg1](arg2);
    }

    this.each(function(){
      initLazyLink($(this), $.extend({}, arg1));
    });

    return this;
  };

  $.lazyModal = function(options){
    if (!options.id) {
      options.id = $.now();
    }

    return new LazyModal(options);
  };

  History.Adapter.bind(window, "statechange", function(){
    var state = History.getState(false);

    if (!state || !state.data || state.data.type !== "lazy") {
      window.location.assign(state.cleanUrl);

      return;
    }

    gotoState(state, true);
  });

  var gotoState = function(state, fromHistory){
    var $container = $("#" + state.data.containerId);

    if ($container.length > 0) {
      var lazyContainer = $container.data("lazyContainer");

      if (lazyContainer.uniqueId !== state.data.uniqueId || fromHistory !== true) {
        lazyContainer.load(state.cleanUrl, state.data.method, state.data.data, state.data.options, fromHistory !== true);
      }
    } else {
      window.location.assign(state.cleanUrl);
    }
  };

})(jQuery);
