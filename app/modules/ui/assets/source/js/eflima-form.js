(function($){

  $.fn.eflimaForm = function(){
    var form = this.data("eflimaForm");

    if (!form) {
      form = EflimaForm(this, arguments[0]);

      return form;
    }

    return form[arguments[0]](arguments[1], arguments[2]);
  };

  let EflimaForm = function($form, options){
    var fields = {},
      validated = false,
      isValid = false,
      validating = false,
      ajaxValidation = undefined,

      published = {
        $form: $form,
        deferreds: [],

        fields: function(id){
          if (id !== undefined) {
            return fields[id];
          }

          return fields;
        },

        field: function(id){
          return this.fields(id);
        },

        options: function(key){
          if (key !== undefined) {
            return options[key];
          }

          return options;
        },

        option: function(key){
          return this.options(key);
        },

        isValid: function(){
          return isValid;
        },

        isDirty: function(){
          let isDirty = false;

          $.each(fields, function(index, field){
            if (field.isDirty()) {
              isDirty = true;

              return false;
            }
          });

          return isDirty;
        },

        addField: function(id, field){
          field = EflimaForm.Field(field, this);

          fields[id] = field;
        },

        validate: function(){
          var needAjaxValidation = false,
            hasError = false,
            deferred = $.Deferred();

          deferred.done(function(_isValid){
            isValid = _isValid;
          });

          deferred.always(function(){
            validated = true;
          });

          $.each(fields, function(index, field){
            if (field.validate) {
              field.validate(false);
            }

            if (field.hasError()) {
              hasError = true;
            }

            if (!hasError && field.options("ajaxValidation")) {
              needAjaxValidation = true;
            }
          });

          if (needAjaxValidation) {
            $.when(this.deferreds).done(function(){
              published.validateAjax(deferred);
            });
          } else {
            deferred.resolve(!hasError);
          }

          return deferred.promise();
        },

        validateAjax: function(deferred){
          if (ajaxValidation !== undefined) {
            ajaxValidation.abort();
          }

          var formData = new FormData($form[0]);

          ajaxValidation = $.ajax({
            url: options.validationUrl,
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false,
            type: $form.attr("method"),
            beforeSend: function(xhr){
              xhr.setRequestHeader("X-Validate", "1");
            },
            success: function(data){
              $.each(fields, function(id, field){
                field.clearError();
              });

              $.each(data, function(index, errors){
                let field = published.field(index);

                if (!field) {
                  return;
                }

                field.addError(errors);
              });

              deferred.resolve($.isEmptyObject(data));
            },
            error: function(){
              deferred.reject();
            }
          });

          return ajaxValidation;
        }
      },

      init = function(){
        published.deferreds.add = function(callback){
          this.push(new $.Deferred(callback));
        };

        options = $.extend({}, EflimaForm.defaultOptions, options);

        $.each(options.fields, function(index, field){
          published.addField(index, field);
        });

        if (options.validationUrl === undefined) {
          options.validationUrl = $form.attr("action");
        }

        $form.on("submit", onSubmit);
        $form.data("eflimaForm", published);

        return published;
      },

      submitted = function(){
        $.each(fields, function(index, field){
          field.submitted();
        });
      },

      onSubmit = function(event){
        if (!validated) {
          event.stopImmediatePropagation();

          if(validating){
            return false;
          }

          let deferred = published.validate();

          validating = true;

          deferred.done(function(isValid){
            if (isValid) {
              $form.submit();
            }
          });

          deferred.fail(function(){
            $form.submit();
          });

          deferred.always(function(){
            validated = false;
            validating = false;
          });

          return false;
        } else {
          submitted();

          // $form.trigger("lazyForm.submit", event);
        }
      };

    return init();
  };

  EflimaForm.Field = function(options, form){
    var validationTimer = undefined,

      typeEvents = ["keyup", "keypress", "keydown"],
      skippedKeycodes = [16, 17, 18, 37, 38, 39, 40],
      oldValue = "",
      currentValue = "",
      initialValue = "",
      $field = form.$form.find(options.field),

      published = {
        $field: $field,
        $input: form.$form.find(options.input),
        $error: $field.find(options.error),

        errors: [],
        name: null,

        options: function(key){
          if (key !== undefined) {
            return options[key];
          }

          return options;
        },

        option: function(key){
          return this.options(key);
        },

        form: function(){
          return form;
        },

        validate: function(withAjax){
          this.updateValue();

          if (!options.validate) {
            return;
          }

          let deferred = $.Deferred(),
            hasError = this.hasError();

          this.clearError();

          options.validate(this.name, currentValue, this.errors, form.deferreds, form.$form);

          if ((withAjax === undefined || withAjax) && options.ajaxValidation && !hasError) {
            $.when(form.deferreds).done(function(){
              form.validateAjax(deferred);
            });
          } else {
            deferred.resolve(hasError);
          }

          this.update();
        },

        hasError: function(){
          return this.errors.length > 0;
        },

        addError: function(message){
          if ($.isArray(message)) {
            $.each(message, function(index, value){
              published.addError(value);
            });
          } else {
            this.errors.push(message);
          }

          this.update();
        },

        clearError: function(){
          this.errors.splice(0, this.errors.length);

          this.update();
        },

        isDirty: function(){
          if ($.isArray(this.getValue())) {
            return !arrayEquals(this.getValue(), initialValue);
          }

          return this.getValue() !== initialValue;
        },

        isChanged: function(){
          if ($.isArray(this.getValue())) {
            return !arrayEquals(this.getValue(), oldValue);
          }

          return this.getValue() !== oldValue;
        },

        submitted: function(){
          initialValue = this.getValue();
        },

        getValue: function(){
          let type = this.$input.attr("type");

          if (type !== "checkbox" && type !== "radio") {
            return this.$input.val();
          }

          let $realInput = this.$input.filter(":checked");

          if (!$realInput.length) {
            $realInput = form.$form.find("input[type=hidden][name=\"" + this.$input.attr("name") + "\"]");
          }

          return $realInput.val();
        },

        updateValue: function(){
          currentValue = this.getValue();
        },

        update: function(){
          let hasError = this.hasError();

          if (hasError) {
            this.$error.text(this.errors[0]);

            this.$input.addClass(options.invalidClass);
            this.$input.removeClass(options.validClass);

            this.$field.addClass(options.invalidFieldClass);
            this.$field.removeClass(options.validFieldClass);
          } else {
            this.$input.removeClass(options.invalidClass);
            this.$input.addClass(options.validClass);

            this.$field.removeClass(options.invalidFieldClass);
            this.$field.addClass(options.validFieldClass);

            this.$error.empty();
          }

          this.$input.attr("aria-invalid", (hasError ? "true" : "false"));
        }
      },

      arrayEquals = function(a, b){
        if (a === b) return true;
        if (a == null || b == null) return false;
        if (a.length != b.length) return false;

        a.sort();
        b.sort();

        for (var i = 0; i < a.length; ++i) {
          if (a[i] !== b[i]) return false;
        }
        return true;
      },

      watch = function(e){
        // Skip! If pressed key is special keys (like: ctrl, shift, alt, etc)
        if ($.inArray(e.type, typeEvents) && $.inArray(e.which, skippedKeycodes) !== -1) {
          return;
        }

        oldValue = currentValue;
        currentValue = published.getValue();

        $field.trigger("eflimaField.change", published);

        // Clear validation queue before create one
        if (validationTimer !== undefined) {
          clearTimeout(validationTimer);
        }

        // Create validation queue
        validationTimer = setTimeout(function(){
          published.validate();
        }, options.validationDelay);
      },

      init = function(){
        options = $.extend({}, EflimaForm.Field.defaultOptions, options);

        published.$input.on(options.watchEvent.join(" "), watch);
        published.name = published.$input.attr("name");

        published.$field.data("eflimaField", published);

        oldValue = initialValue = currentValue = published.getValue();

        return published;
      };

    return init();
  };

  EflimaForm.defaultOptions = {
    fields: []
  };

  EflimaForm.Field.defaultOptions = {
    input: undefined,
    field: undefined,
    error: undefined,
    ajaxValidation: false,
    validate: undefined,
    validClass: "is-valid",
    invalidClass: "is-invalid",
    invalidFieldClass: "has-error",
    validFieldClass: "has-success",
    watchEvent: ["change", "blur", "keyup"],
    validationDelay: 800
  };

})(jQuery);