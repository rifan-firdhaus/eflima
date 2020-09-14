(function($){
  $.fn.modal.prototype.constructor.Constructor.prototype.enforceFocus = $.noop;

  let Staff = function(){
    var lastMainScroll = 0;
    var published = {
        $body: $("body"),
        $window: $(window),
        $main: $("#main"),
        $content: $("#content"),
        $header: $("#header"),
        $topbar: $("#topbar"),
        $toolbar: $("#toolbar"),
        $mainContainer: $("#main-container"),
        $sidenav: $("#sidenav"),

        notify: function(className, text){
          var images = {
            "success": "<i class=\"icons8-checked\">",
            "warning": "<i class=\"icons8-error\">",
            "info": "<i class=\"icons8-error\">",
            "danger": "<i class=\"icons8-error\">",
            "dark": "<i class=\"icons8-info\">",
            "light": "<i class=\"icons8-info\">",
            "secondary": "<i class=\"icons8-info\">",
            "primary": "<i class=\"icons8-info\">"
          };

          $.notify({
            text: text,
            image: images[className]
          }, {
            style: "eflima",
            position: "bottom right",
            className: className,
            autoHide: true
          });
        },

        setMainForm: function(selector){
          var $form = $(selector);

          $form.attr("main-form", "1");
        },

        updateQueryParam: function(uri, key, value){
          if (typeof key === "object") {
            $.each(key, function(_key, _value){
              uri = published.updateQueryParam(uri, _key, _value);
            });

            return uri;
          }

          var i = uri.indexOf("#");
          var hash = i === -1 ? "" : uri.substr(i);
          uri = i === -1 ? uri : uri.substr(0, i);

          var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
          var separator = uri.indexOf("?") !== -1 ? "&" : "?";

          if (!value) {
            uri = uri.replace(new RegExp("([?&]?)" + key + "=[^&]*", "i"), "");

            if (uri.slice(-1) === "?") {
              uri = uri.slice(0, -1);
            }

            if (uri.indexOf("?") === -1) uri = uri.replace(/&/, "?");
          } else if (uri.match(re)) {
            uri = uri.replace(re, "$1" + key + "=" + value + "$2");
          } else {
            uri = uri + separator + key + "=" + value;
          }

          return uri + hash;
        },

        notifies: function(messages){
          $.each(messages, function(className, texts){
            $.each(texts, function(index, text){
              published.notify(className, text);
            });
          });
        }
      },

      init = function(){
        published.$body.on("click", ".sidebar-toggler", onToogleButton);

        $(document).on("lazy.loaded lazy.failed", onLazyLoaded);
        $(document).on("lazy.failed", onLazyFailed);
        $(document).on("lazy.redirect", onLazyRedirect);
        $(document).on("lazy.initContent", onLazyInitContent);
        $(document).on("lazy.beforeRender", onLazyBeforeRender);
        $(document).on("lazyModal.beforeClose", onBeforeCloseLazyModal);

        initBootstrapModal();
        initNotify();
        initAjaxLoading();
        initNotification();
        initPage();
        initPopover();
        initSidenav();
        initTinyMce();
        initDropdown();

        published.notification.setCount(published.notification.getCount());

        if (window.messages) {
          published.notifies(window.messages);
        }

        return published;
      },

      initDropdown = function(){
        $(document).on("show.bs.dropdown", function(e){
          var dropdown = $(e.target).find(".dropdown-menu");

          dropdown.appendTo("body");

          $(this).on("hidden.bs.dropdown", function(){
            dropdown.appendTo(e.target);
          });
        });
      },

      initBootstrapModal = function(){
        $(document).on("click", "[data-dismiss=\"extended-modal\"]", function(e){
          e.preventDefault();
          e.stopPropagation();

          $(this).closest(".modal").modal("hide");

          return false;
        });

        $(document).on({
          "show.bs.modal": function(e){
            if (this !== e.target) {
              return;
            }

            var $target = $(e.target);
            var data = $target.data("bs.modal");

            var zIndex = 1040 + (10 * $(".modal:visible").length);
            $target.css("z-index", zIndex);

            setTimeout(function(){
              var $parent = $target.parents(".modal:first");
              var $backdrop = $(data._backdrop);

              if ($parent) {
                $parent.append($backdrop);
              }

              $backdrop.css("z-index", zIndex - 1).addClass("modal-stack");
            }, 0);
          },
          "hidden.bs.modal": function(){
            if ($(".modal:visible").length > 0) {
              // restore the modal-open class to the body element, so that scrolling works
              // properly after de-stacking a modal.
              setTimeout(function(){
                $(document.body).addClass("modal-open");
              }, 0);
            }
          }
        }, ".modal");
      },

      initTinyMce = function(){
        $(document).on("focusin", function(e){
          if ($(e.target).closest(".tox-tinymce").length) {
            e.stopImmediatePropagation();
          }
        });
      },

      initSidenav = function(){
        published.$sidenav.find("> .navbar-nav > .nav-item > .nav-link").tooltip({
          placement: "right",
          trigger: "hover",
          container: "body",
          boundary: "viewport"
        });

        published.sidepanel.$element.lazyContainer({
          pushState: false,
          scroll: false
        });

        published.sidepanel.$element.on("lazy.load", function(){
          published.sidepanel.show();
        });

        published.sidepanel.$element.on("click", ".side-panel-close", function(e){
          e.preventDefault();

          published.sidepanel.hide();
        });
      },

      initNotification = function(){
        published.notification.$element.lazyContainer({
          pushState: false,
          scroll: false
        });

        published.notification.$element.on("lazy.load", function(){
          published.notification.show();
        });

        published.notification.$element.on("click", ".side-panel-close", function(e){
          e.preventDefault();

          published.notification.hide();
        });
      },

      initPopover = function(){
        published.$window.on("inserted.bs.popover", function(e){
          $(e.target).attr("data-popover", true);
        });

        published.$window.on("click", function(e){
          var $target = $(e.target);
          var $clickedPopover = $target.data("popover") ? $target : $target.closest("[data-popover]");

          if (
            $target.closest(".popover.show").length === 0
          ) {
            var $popovers = $(".popover");

            $popovers.each(function(){
              var popover = $(this).data("bs.popover");

              if (popover && ($clickedPopover.length === 0 || $clickedPopover.data("bs.popover").element !== popover.element)) {
                $(popover.element).popover("hide");
              }
            });
          }
        });
      },

      initPage = function(){
        $("[data-toggle=\"popover\"]").popover();
        $("[data-toggle=\"tooltip\"]").tooltip({
          focus: "hover"
        });
      },

      initAjaxLoading = function(){
        $(document).ajaxStart(function(){
          $("#loading").show();
          published.$body.addClass("loading");
        }).ajaxStop(function(){
          $("#loading").hide();
          published.$body.removeClass("loading");
        });
      },

      onLazyInitContent = function(event, content){
        var $content = $(content);
        var $form = $content.find("form[main-form]").addBack("form[main-form]");

        if ($form.length > 0) {
          var $firstInput = $form.find("textarea,select,input").filter(":not(input[type=hidden])").first();

          if ($firstInput.length > 0) {
            setTimeout(function(){
              $firstInput.focus();
              $firstInput.trigger("focus");
            }, 301);
          }
        }
      },

      onLazyLoaded = function(event, data, options){
        if (!data) {
          return;
        }

        // Show up notification
        if (data.messages) {
          published.notifies(data.messages);
        }

        // Update csrf token if it's available in the response data
        if (data.csrf_token && data.csrf_param) {
          yii.setCsrfToken(data.csrf_param, data.csrf_token);
        }

        // Set active on sidebar menu item
        if (data.activeMenu) {
          published.sidebar.setActiveMenu(data.activeMenu);
        }

        // Set notification count
        if (typeof data.notificationCount !== "undefined") {
          published.notification.setCount(data.notificationCount);
        }

        var $target = $(event.target);
        var $main = $target.children(".content");

        if (!options.scroll && $target.attr("id") === "main-container" && $main.length > 0) {
          $main.scrollTop(lastMainScroll);
        }

        if (data.fullHeight && $target.hasClass("modal-body") && $target.closest(".modal").data("lazyModal")) {
          $target.addClass("vh-100");
        } else {
          $target.removeClass("vh-100");
        }

        initPage();
      },
      onLazyBeforeRender = function(event, $content){
        preventMemoryLeak(event.target);

        var $target = $(event.target);
        var $main = $target.children(".content");

        if ($target.attr("id") === "main-container" && $main.length > 0) {
          lastMainScroll = $main.scrollTop();
        }
      },

      preventMemoryLeak = function(element){
        var $container = $(element);

        // Destroy countdown if it exists
        if ($.fn.countdown) {
          $container.find(".is-countdown").each(function(){
            $(this).data("countdown").elem.countdown("destroy");
          });
        }

        // Destroy TinyMCE instance inside the modal
        if (typeof window.tinyMCE !== "undefined") {
          $.each(window.tinyMCE.get(), function(index, instance){
            if ($container.has(instance.targetElm).length) {
              instance.destroy(false);
            }
          });
        }

        // Destroy active tooltips
        if ($.fn.tooltip) {
          published.$body.find(".tooltip").each(function(){
            var tooltip = $(this).data("bs.tooltip");

            if (tooltip) {
              tooltip.hide();
            }
          });
        }

        if ($.fn.flatpickr) {
          $container.find("input").each(function(){
            if (this._flatpickr) {
              this._flatpickr.destroy();
            }
          });
        }
      },

      onLazyFailed = function(event, data, xhr){
        if (xhr.status == 200) {
          return;
        }

        // Show error notification on error (302 is redirection status, it should not count as error)
        if (xhr.status != 302) {
          admin.notify("danger", [xhr.status + ": " + xhr.statusText]);
        }
      },

      onLazyRedirect = function(event, url, data, options){
        var $mainContainer = $(event.target).closest("[main-container]");

        if ($mainContainer.length > 0) {
          $(event.target).lazyContainer("load", url, "GET", {}, options);
        } else {
          published.$mainContainer.lazyContainer("load", url, "GET", {}, options);
        }
      },

      onBeforeCloseLazyModal = function(event){
        if (event.result === false) {
          return false;
        }

        var $modal = $(event.target);
        var $form = $modal.find("form[main-form]");

        if ($form.length > 0 && $form.data("eflimaForm") && $form.eflimaForm("isDirty")) {
          var confirmation = $form.data("confirmation");

          if (!confirmation) {
            confirmation = "Changes you have made might not be saved, are you sure?";
          }

          if (!confirm(confirmation)) {
            return false;
          }
        }

        preventMemoryLeak(event.target);

        return true;
      },

      initNotify = function(){
        $.notify.addStyle("eflima", {
          html:
            "<div>" +
            "<div class=\"alert-icon\" data-notify-html=\"image\"/>" +
            "<div class=\"text-wrapper\">" +
            "<div class=\"alert-heading\" data-notify-html=\"title\"/>" +
            "<div class=\"alert-text\" data-notify-html=\"text\"/>" +
            "</div>" +
            "</div>",
          classes: {
            primary: {},
            secondary: {},
            success: {},
            info: {},
            warning: {},
            danger: {},
            light: {},
            dark: {}
          }
        });
      },

      onToogleButton = function(e){
        e.preventDefault();

        published.sidebar.toggleCollapse();
      };

    published.panel = {
      showClass: "sidebar-show",
      hideClass: "sidebar-hide",
      useBackdrop: true,

      $element: null,
      $backdrop: null,

      show: function(){
        var self = this;

        published.$body.addClass(this.showClass).removeClass(this.hideClass).removeClass(this.collapseClass);

        if (!this.$backdrop && this.useBackdrop) {
          this.$backdrop = $("<div/>", { "class": "side-panel-backdrop" }).appendTo("body").on("click", function(e){
            e.preventDefault();

            self.hide();
          });
        }

        return this;
      },

      hide: function(){
        published.$body.addClass(this.hideClass).removeClass(this.showClass);

        if (this.useBackdrop) {
          this.$backdrop.remove();
          this.$backdrop = null;
        }

        return this;
      },

      toggle: function(){
        if (this.isShowed()) {
          this.hide();
        } else {
          this.show();
        }

        return this;
      },

      isShowed: function(){
        return published.$body.hasClass(this.showClass) && !published.$body.hasClass(this.hideClass);
      },

      isHidden: function(){
        return !this.isShowed();
      }
    };

    published.notification = $.extend({}, published.panel, {
      showClass: "notification-panel-show",
      hideClass: "notification-panel-hide",
      $element: $("#notification-panel"),
      $badge: $(".account-notification-badge"),

      setCount: function(count){
        count = parseInt(count) || 0;

        this.$badge.text(count).toggle(count > 0).data("count", count);
      },

      getCount: function(){
        return parseInt(this.$badge.data("count")) || 0;
      }
    });

    published.sidepanel = $.extend({}, published.panel, {
      showClass: "side-panel-show panel",
      hideClass: "side-panel-hide panel",
      $element: $("#side-panel")
    });

    published.sidebar = $.extend({}, published.panel, {
      showClass: "sidebar-show panel",
      hideClass: "sidebar-hide panel",
      collapseClass: "sidebar-collapse",
      menuItemActiveClass: "active",
      useBackdrop: false,

      $element: $("#sidebar"),

      collapse: function(){
        published.$body.addClass(this.collapseClass).removeClass(this.showClass);

        return this;
      },

      toggleCollapse: function(){
        if (this.isShowed()) {
          this.collapse();
        } else {
          this.show();
        }

        return this;
      },

      isShowed: function(){
        return published.$body.hasClass(this.showClass) || (!published.$body.hasClass(this.hideClass) && !published.$body.hasClass(this.collapseClass));
      },

      isCollapsed: function(){
        return published.$body.hasClass(this.collapseClass);
      },

      setActiveMenu: function(id){
        this.$element.find("#sidebar-nav li > a").removeClass(this.menuItemActiveClass);
        this.$element.find("#sidebar-nav li[data-menu-id='" + id + "'] > a").addClass(this.menuItemActiveClass);
      }
    });

    return init();
  };

  $(function(){
    if (!window.admin) {
      window.admin = Staff();
    }

    $("a[data-lazy-link]").filter(function(){
      return !$(this).data("lazyLink");
    }).lazyLink();
  });
})(jQuery);
