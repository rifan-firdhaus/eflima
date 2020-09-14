(function($){

  var NoteForm = function($form, options){
    var self = this;

    this.$titleInput = $form.find(".note-title-input");
    this.$contentInput = $form.find(".note-content-input");
    this.$isPrivateInput = $form.find(".note-is-private-input");
    this.$isPrivateToggle = $("<button/>", {
      "class": "btn btn-link btn-lg btn-icon"
    });

    this.getContentTinyMCE = function(){
      var id = this.$contentInput.attr("id");

      return tinyMCE.get(id);
    };

    this.setPrivate = function(){
      this.$isPrivateInput.val(1);

      this.$isPrivateToggle.html("<i class=\"icon icons8-size icons8-lock\"></i>");
    };

    this.setPublic = function(){
      this.$isPrivateInput.val(0);

      this.$isPrivateToggle.html("<i class=\"icon icons8-size icons8-globe\"></i>");
    };

    this.isPrivate = function(){
      var value = parseInt(this.$isPrivateInput.val());

      return !isNaN(value) && value !== 0;
    };

    this.togglePrivate = function(){
      if (this.isPrivate()) {
        this.setPublic();
      } else {
        this.setPrivate();
      }
    };

    var init = function(){
        self.$isPrivateToggle.insertAfter(self.$isPrivateInput);

        if (self.isPrivate()) {
          self.setPrivate();
        } else {
          self.setPublic();
        }

        setTitleSize();

        self.$titleInput.on({
          "keydown": function(e){
            if (e.keyCode === 13) {
              e.preventDefault();
              e.stopPropagation();

              self.getContentTinyMCE().focus();

              return false;
            }

            setTimeout(function(){
              setTitleSize();
            }, 0);
          }
        });

        self.$isPrivateToggle.on("click", function(e){
          e.preventDefault();

          self.togglePrivate();
        });
      },

      setTitleSize = function(){
        self.$titleInput.css({
          height: "auto"
        });
        self.$titleInput.css({
          height: self.$titleInput.get(0).scrollHeight
        });
      };

    return init();
  };

  $.fn.noteForm = function(first, second, third){
    var noteForm = this.data("noteForm");

    if (!noteForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        noteForm = new NoteForm(this, first);

        this.data("noteForm", noteForm);
      }

      return this;
    }

    return expenseForm[first](second, third);
  };

})(jQuery);