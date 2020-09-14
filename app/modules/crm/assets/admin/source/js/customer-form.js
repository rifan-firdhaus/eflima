(function($){

  var CustomerForm = function($form){
    var self = this;

    this.$companyDetailCard = $form.find("#card-field-customer-company_detail_section");
    this.$passwordContainerField = $form.find('#container-field-password-container');
    this.$typeInput = $form.find("#customer-type input[type=radio]");
    this.$hasCustomerAreaAccess = $form.find('#customercontact-has_customer_area_access');

    var init = function(){
        self.$typeInput.on("change", checkCompanyVisibility);
        self.$hasCustomerAreaAccess.on("change", checkPasswordAvailability);

        checkCompanyVisibility();
      },

      checkPasswordAvailability = function(){
          var value = self.$hasCustomerAreaAccess.is(':checked');

          self.$passwordContainerField.toggle(value);
      },
      
      checkCompanyVisibility = function(){
        var type = self.$typeInput.filter(":checked").val();

        self.$companyDetailCard.toggle(type === "C");
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