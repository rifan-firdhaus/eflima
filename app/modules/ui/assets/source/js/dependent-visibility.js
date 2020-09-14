(function($){

  var Visibility = function(options, $element){
    var _self = this;
    var _options = {};
    var _defaults = {
      "form": null,
      "visibility": []
    };
    var _$dependents = {};

    this.$element = $element;
    this.events = {};

    this.option = function(key, value){
      if (typeof value === "undefined") {
        return key ? _options[key] : $.extend({}, _options);
      }

      _options[key] = value;
    };

    this.operate = function(condition){
      var $element = _$dependents[condition.selector];
      var value = "";
      $element.each(function(){
        var $el = $(this);
        var type = $el.attr("type");
        var _value;
        var name = $el.attr("name");

        if (type === "radio" && $el.is("checked")) {
          _value = $el.val();
        } else if (type === "checkbox") {
          if ($el.is(":checked")) {
            _value = $el.val();
          }
        } else {
          _value = $el.val();
        }

        if (typeof _value !== "undefined") {
          if (name.substr(-1) === "[]" && !($el.get(0).tagName === "SELECT" && $el.attr("multiple"))) {
            if (typeof name === "string") {
              value = [];
            }

            value.push(_value);
          } else {
            value = _value;
          }
        }
      });

      switch (condition.operator) {
        case "==":
          return value == condition.value;
        case "===":
          return value === condition.value;
        case "!=":
          return value != condition.value;
        case "!==":
          return value !== condition.value;
        case "<":
          return value < condition.value;
        case ">":
          return value > condition.value;
        case "<=":
          return value <= condition.value;
        case ">=":
          return value >= condition.value;
        case "IN":
          return $.inArray(value, $.inArray(condition.value) ? condition.value : [condition.value]) !== -1;
        case "FIND_IN":
          return $.inArray(condition.value, $.inArray(value) ? value : [value]) !== -1;
      }
    };

    this.isVisible = function(visibilities){
      var isVisible = null;
      var conditionOperator = visibilities[0];

      for (var index = 1; index < visibilities.length; index++) {
        var value;
        var _isVisible;

        if (!$.isArray(visibilities[index])) {
          value = this.operate(visibilities[index]);
        } else {
          value = this.isVisible(visibilities[index]);
        }

        if (isVisible === null) {
          isVisible = value;
        } else {
          switch (conditionOperator) {
            case "OR":
              _isVisible = isVisible || value;

              if (_isVisible === true) return true;
              else isVisible = _isVisible;

              break;
            case "AND":
              _isVisible = isVisible && value;

              if (_isVisible === false) return false;
              else isVisible = _isVisible;

              break;
          }
        }
      }

      return isVisible;
    };

    this.normalizeEvents = function(events){
      var normalized = [];

      for (var index = 0; index < events.length; index++) {
        var _events = events[index].split(" ");

        for (var _index = 0; _index < _events.length; _index++) {
          if ($.inArray(_events[_index], normalized) === -1) {
            normalized.push(_events[_index].trim());
          }
        }
      }

      return normalized.join(" ");
    };

    this.render = function(){
      if (this.isVisible(this.option("visibility"))) {
        this.$element.stop().fadeIn();
      } else {
        this.$element.stop().fadeOut();
      }
    };

    this.delegateEvents = function(){
      var index;

      for (index in this.events) {
        var events = this.normalizeEvents(this.events[index]);
        var $element = _$dependents[index];

        $element.on(events, function(){
          _self.render();
        });
      }
    };

    this.normalize = function(visibilities){
      if (!$.isArray(visibilities)) {
        var selector = getSelectorFromCondition(visibilities);

        visibilities.selector = selector[0];
        visibilities.value = selector[1];

        if (typeof visibilities.operator === "undefined") {
          if ($.isArray(visibilities.value)) {
            visibilities.operator = "IN";
          } else {
            visibilities.operator = "==";
          }
        }

        if (typeof visibilities.typeCast === "undefined") {
          visibilities.typeCast = "raw";
        }

        if (typeof visibilities.on === "undefined") {
          visibilities.on = "change";
        }

        _$dependents[visibilities.selector] = $(visibilities.selector);

        if (typeof this.events[visibilities.selector] === "undefined") {
          this.events[visibilities.selector] = [];
        }

        this.events[visibilities.selector].push(visibilities.on);
      } else {
        for (var index = 1; index < visibilities.length; index++) {
          this.normalize(visibilities[index]);
        }
      }
    };

    var getSelectorFromCondition = function(condition){
      for (var k in condition) {
        return [k, condition[k]];
      }
    };

    _options = $.extend({}, _defaults, options);

    this.normalize(_options.visibility);

    console.log(_options.visibility);
    this.delegateEvents();
    this.render();
  };

  $.fn.visibility = function(firstArgument, secondArcument){
    var $element = $(this);

    var visibility = $element.data("Visibility");

    if (!visibility) {
      visibility = new Visibility(firstArgument, $element);

      $element.data("Visibility", visibility);

      return $element;
    } else if (typeof firstArgument === "string") {
      return visibility[firstArgument](secondArcument);
    }

    return visibility;
  };

})(jQuery);

// $("#kyjxpbkr-vllewmxp").visibility({
//   visibility: [
//     "AND",
//     { "[name='CustomerContact[hasAccount]']": "1", "operator": "==" },
//     { "[name='Customer[name]']": "1235", "operator": "==" },
//     [
//       "AND",
//       { "[name='Customer[name]']": "wewew", "operator": "IN" },
//       { "[name='Customer[name]']": "wewewewe", "operator": ">=" }]
//   ]
// });