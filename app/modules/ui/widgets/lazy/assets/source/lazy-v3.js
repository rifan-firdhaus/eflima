(function($){
  var scriptLoader = {
    links: function($links){
      if (!$links) return;

      var $loadedLinks = $("link[href]");

      $links.each(function(){
        var href = this.href;

        var $css = $loadedLinks.filter(function(){
          return this.href == href;
        });

        if ($css.length > 0) return;

        document.head.appendChild(this);
      });
    },

    scripts: function($scripts, $container){
      var deferred = $.Deferred();

      if (!$scripts) {
        deferred.resolve();
      } else {
        var $loadedScripts = $("script[src]");
        var promise = null;

        $scripts.each(function(){
          var source = this.src;
          var $script = $(this);

          var $isLoaded = $loadedScripts.filter(function(){
            return this.src == source;
          });

          if ($isLoaded.length > 0) return;

          if (promise === null) {
            promise = scriptLoader.script($script, $container);
          } else {
            promise = promise.then(function(){
              return scriptLoader.script($script, $container);
            });
          }
        });

        if (promise) {
          promise.done(function(){
            deferred.resolve();
          });
        } else {
          deferred.resolve();
        }
      }

      return deferred.promise();
    },

    script: function($script, $container){
      var src = $script.attr("src");

      if (src) {
        document.head.appendChild($script.get(0));

        return $.getScript(src);
      }

      $container.append($script);

      var deferred = $.Deferred();

      deferred.resolve();

      return deferred;
    },
  };

  var lazy = function($container, options, renderer){
    var deferred = $.Deferred();

    var send = function(){
        var beforeSend = options.beforeSend;

        options.beforeSend = function(request, settings){
          request.setRequestHeader("X-Lazy", "1");
          request.setRequestHeader("X-Lazy-Container", "#main-container");

          if (beforeSend) beforeSend.call(this, request, settings);
        };

        options.dataType = "JSON";

        return $.ajax(options);
      },

      handleResponse = function(response, statusText, xhr){
        if (response.content !== false) {
          render(response.content, $container).then(function(){
            deferred.resolve(response, statusText, xhr);
          }, function(){
            deferred.reject(xhr);
          });
        } else {
          deferred.reject(xhr);
        }
      },

      handleFailure = function(xhr){
        deferred.reject(xhr);
      },

      render = function(html){
        var $html = $($.parseHTML(html, document, true));
        var $scripts = $html.find("script").addBack("script").remove();
        var $links = $html.find("link[href]").addBack("link[href]").remove();
        var $content = $html.not($scripts).not($links);

        if (renderer) {
          renderer($container, {
            html: $html,
            scripts: $scripts,
            links: $links,
            content: $content
          });
        } else {
          $container.html($content);
        }

        scriptLoader.links($links);

        return scriptLoader.scripts($scripts, $container);
      };

    send().done(handleResponse).fail(handleFailure);

    return deferred.promise();
  };

  var LazyContainer = function($element, options){
    this.load = function(url, options){

    };
  };

  var LazyModal = function(){

  };

  var LazyLink = function(){

  };

  window._lazy = lazy;
})(jQuery);
