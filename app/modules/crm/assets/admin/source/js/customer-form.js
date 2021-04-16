(function($){

  var CustomerForm = function($form){
    var self = this;

    this.$companyDetailCard = $form.find("[data-rid='card-field-customer-company_detail_section']");
    this.$passwordContainerField = $form.find("[data-rid='container-field-password-container']");
    this.$typeInput = $form.find("[data-rid='customer-type'] input[type=radio]");
    this.$hasCustomerAreaAccess = $form.find("input[type=checkbox][name='CustomerContact[has_customer_area_access]']");

    var init = function(){
        self.$typeInput.on("change", checkCompanyVisibility);
        self.$hasCustomerAreaAccess.on("change", checkPasswordAvailability);

        checkCompanyVisibility();
        checkPasswordAvailability();
      },

      checkPasswordAvailability = function(){
        var value = self.$hasCustomerAreaAccess.is(":checked");

        if (value) {
          self.$passwordContainerField.stop().slideDown();
        } else {
          self.$passwordContainerField.stop().slideUp();
        }
      },

      checkCompanyVisibility = function(){
        var type = self.$typeInput.filter(":checked").val();

        if (type === "C") {
          self.$companyDetailCard.stop().slideDown();
        } else {
          self.$companyDetailCard.stop().slideUp();
        }

      };

    return init();
  };

  $.fn.customerForm = function(first, second, third){
    var customerForm = this.data("customerForm");

    if (!customerForm) {
      if (typeof first === "undefined" || typeof first === "object") {
        customerForm = new CustomerForm(this, first);

        this.data("customerForm", customerForm);
      }

      return this;
    }

    return customerForm[first](second, third);
  };

})(jQuery);
