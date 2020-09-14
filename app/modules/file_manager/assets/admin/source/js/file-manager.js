(function($){

  var FileManager = function($container, options){
    this.$container = $container;
    this.$toolbar = $("<div/>", {
      class: "file-manager--toolbar"
    });

    this.refresh = function(){

    };

    this.option = function(name){
      return options[name];
    };

    var load = function(){
      return $.ajax({
        url: this.option("url")
      });
    };

    var build = function(){
      this.$container.addClass("file-manager--container");
    };

    build();
  };

  $.fn.fileManager = function(){

  };

})(jQuery);
