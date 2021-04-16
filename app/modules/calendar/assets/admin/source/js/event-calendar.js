(function($){

  var EventCalendar = function($dataView, options){
    var self = this;

    this.$calendar = $dataView.find("[data-rid='event-calendar']");
    this.$nextButton = $dataView.find(".btn-calendar-next");
    this.$prevButton = $dataView.find(".btn-calendar-prev");
    this.$todayButton = $dataView.find(".btn-calendar-today");
    this.$header = $dataView.find(".calendar-header");
    this.$calendarChooser = $dataView.find(".calendar-type-chooser");
    this.$calendarChooserLink = this.$calendarChooser.find(".dropdown-item");
    this.calendar = null;

    this.fetchEvent = function(start, end){
      return $.ajax({
        url: options.fetchEventUrl,
        data: {
          date_start: start.valueOf() / 1000,
          date_end: end.valueOf() / 1000
        }
      });
    };

    this.addEvent = function(start, end){
      var lazy = $.lazyModal({
        container: "#main-container",
        id: "event-form-modal",
        size: "modal-lg",
        pushState: false
      });

      lazy.load(options.addEventUrl, "GET", {
        start_date: start.valueOf() / 1000,
        end_date: end.valueOf() / 1000
      });

      lazy.$modal.on("lazyModal.close", function(){
        self.calendar.refetchEvents();
      });

      return lazy;
    };

    this.view = function(eventId){
      var lazy = $.lazyModal({
        container: "#main-container",
        id: "event-form-modal",
        size: "modal-lg",
        pushState: false
      });

      lazy.load(admin.updateQueryParam(options.viewEventUrl, "id", eventId));

      return lazy;
    };

    this.updateEventDate = function(id, start, end){
      return $.ajax({
        url: admin.updateQueryParam(options.updateEventDateUrl, "id", id),
        type: "POST",
        data: {
          "Event[start_date]": start.valueOf() / 1000,
          "Event[end_date]": end.valueOf() / 1000
        },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    var init = function(){
      self.calendar = new FullCalendar.Calendar(self.$calendar.get(0), {
        themeSystem: "bootstrap",
        height: "100%",
        headerToolbar: false,
        editable: true,
        selectable: true,
        events: function(info, onSuccess, onFailed){
          self.fetchEvent(info.start, info.end).done(function(data){
            onSuccess(data);
          });
        },
        select: function(info){
          self.addEvent(info.start, info.end);
        },
        datesSet: function(dateInfo){
          self.$header.html(dateInfo.view.title);
        },
        eventClick: function(info){
          self.view(info.event.id);
        },
        eventDrop: function(info){
          if (!confirm("You are about to change the date of '" + info.event.title + "', are you sure?")) {
            info.revert();
          } else {
            self.updateEventDate(info.event.id, info.event.start, info.event.end).fail(function(){
              info.revert();
            }).done(function(data){
              if (!data.success) {
                info.revert();
              }
            });
          }
        }
      });

      self.$nextButton.on("click", function(e){
        e.preventDefault();

        self.calendar.next();
      });

      self.$todayButton.on("click", function(e){
        e.preventDefault();

        self.calendar.today();
      });

      self.$prevButton.on("click", function(e){
        e.preventDefault();

        self.calendar.prev();
      });

      self.calendar.render();

      $dataView.parent().on("lazy.initContent", function(){
        var search = yii.getQueryParams(location.href);

        switch (search.type) {
          case "weekly":
            self.calendar.changeView("timeGridWeek");
            break;
          case "daily":
            self.calendar.changeView("timeGridDay");
            break;
          default:
            self.calendar.changeView("dayGridMonth");
        }
      });

      self.$calendarChooserLink.on("click", function(e){
        e.preventDefault();

        var params = yii.getQueryParams($(this).attr("href"));

        switch (params.type) {
          case "weekly":
            self.calendar.changeView("timeGridWeek");
            break;

          case "daily":
            self.calendar.changeView("timeGridDay");
            break;

          default:
            self.calendar.changeView("dayGridMonth");
        }
      });

      return self;
    };

    return init();
  };

  $.fn.eventCalendar = function(first, second, third){
    var eventCalendar = this.data("eventCalendar");

    if (!eventCalendar) {
      if (typeof first === "undefined" || typeof first === "object") {
        eventCalendar = new EventCalendar(this, first);

        this.data("eventCalendar", eventCalendar);
      }

      return this;
    }

    return eventCalendar[first](second, third);
  };

})(jQuery);
