(function($){
  var TicketReplyForm = function($form, options){
    var self = this;

    this.$lazy = $form.parent();
    this.$replies = $(".ticket-replies");
    this.$predefinedReply = $form.find(".ticket-reply-predefined-reply");
    this.$predefinedReplyInput = this.$predefinedReply.find("select");
    this.$predefinedReplyTrigger = this.$predefinedReply.find(".btn");
    this.$contentEditableInput = $form.find("div[data-rid='ticketreply-content']");
    this.editor = tinyMCE.editors[this.$contentEditableInput.attr("id")];
    this.$bccButton = $form.find(".btn-bcc");
    this.$bccInput = $form.find("[name='TicketReply[blind_carbon_copy]']");
    this.$bccField = this.$bccInput.closest(".form-group");
    this.$ccButton = $form.find(".btn-cc");
    this.$ccInput = $form.find("[name='TicketReply[carbon_copy]']");
    this.$ccField = this.$ccInput.closest(".form-group");

    var init = function(){
        if (!self.$lazy.data("lazy-ticket-reply-form")) {
          self.$lazy.data("lazy-ticket-reply-form", true);

          self.$lazy.on("lazy.loaded", function(event, result){
            window.lazy.render(result.item, self.$replies, function($content){
              $content.hide();
              self.$replies.prepend($content);
              $content.slideDown();

              $content.each(function(){
                Prism.highlightAll(this);
              });
            });
          });
        }

        self.$bccButton.on("click", function(e){
          e.preventDefault();

          self.$bccField.css("display", "flex");
          self.$bccInput.select2("focus");
        });

        self.$ccButton.on("click", function(e){
          e.preventDefault();

          self.$ccField.css("display", "flex");
          self.$ccInput.select2("focus");
        });

        self.editor.on("click", function(e){
          console.log(this.editorContainer);

          $(this.editorContainer).next().appendTo($("body"));
        });

        self.$predefinedReplyTrigger.on("click", function(e){
          e.preventDefault();

          self.$predefinedReplyInput.select2("open");
        });

        self.$predefinedReplyInput.on("change", function(e){
          var value = self.$predefinedReplyInput.val();

          if (value === "") {
            return;
          }

          getPredefinedReplyContent(value).done(function(data){
            if (data.content) {
              self.editor.execCommand("mceInsertContent", false, data.content);
            }
          });

          self.$predefinedReplyInput.val("").trigger("change");
        });

        return self;
      },
      getPredefinedReplyContent = function(id){
        return $.ajax({
          url: options.predefinedReplyUrl,
          dataType: "JSON",
          data: { id: id },
          type: "GET"
        });
      };

    return init();
  };

  $.fn.ticketReplyForm = function(first, second, third){
    var ticketReplyForm = this.data("ticketReplyForm");

    if (!ticketReplyForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        ticketReplyForm = new TicketReplyForm(this, first);

        this.data("ticketReplyForm", ticketReplyForm);
      }

      return this;
    }

    return ticketReplyForm[first](second, third);
  };

})(jQuery);