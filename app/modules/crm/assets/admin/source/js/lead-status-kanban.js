(function($){

  var LeadStatusKanban = function($element, options){
    var self = this;
    var leadStatusSortable;
    var leadsSortable = {};

    this.$leads = $element.find(".lead-status-kanban-item-content");
    this.leadPaginations = {};

    this.sendSort = function(){
      var data = leadStatusSortable.toArray();

      return $.ajax({
        url: options.sortUrl,
        type: "POST",
        dataType: "JSON",
        data: { sort: data },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.sendSortLead = function(statusId, sort){
      return $.ajax({
        url: admin.updateQueryParam(options.sortLeadUrl, { id: statusId }),
        type: "POST",
        dataType: "JSON",
        data: {
          sort: sort
        },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.sendMoveLead = function(leadId, fromStatusId, toStatusId, sort){
      return $.ajax({
        url: admin.updateQueryParam(options.moveLeadUrl, { id: fromStatusId }),
        type: "POST",
        dataType: "JSON",
        data: {
          sort: sort,
          status_id: toStatusId,
          lead_id: leadId
        },
        success: function(data){
          if (data.messages) {
            admin.notifies(data.messages);
          }
        }
      });
    };

    this.loadLeads = function(statusId, reload){
      var page = this.leadPaginations[statusId] ? this.leadPaginations[statusId].page + 1 : 1;

      if (reload === true) {
        page = this.leadPaginations[statusId].page;
      }

      var url = admin.updateQueryParam(options.loadLeadUrl, {
        id: statusId,
        page: page
      });

      $element.find("[data-rid=\"lead-status-kanban-items-" + statusId + "\"]").lazyContainer("load", url, "GET", {}, {
        renderer: function($content){
          var scrollTop = $(this).scrollTop();

          $content.insertBefore($(this).find('.btn-load-more'));

          $(this).scrollTop(scrollTop);
        }
      }, false);
    };

    var init = function(){
      self.$leads.each(function(){
        var $leadsWrapper = $(this);
        var statusId = $leadsWrapper.closest(".lead-status-kanban-item").data("id");
        var $leads = $leadsWrapper.find("[data-rid=\"lead-status-kanban-items-" + statusId + "\"]");
        var $loadMoreButton = $leadsWrapper.find(".btn-load-more");

        $loadMoreButton.on("click", function(e){
          e.preventDefault();

          self.loadLeads(statusId);
        });

        $leads.on("lazy.loaded", function(e, data){
          if (!data.page) {
            self.loadLeads(statusId, true);
          }

          self.leadPaginations[statusId] = {
            hasMorePage: data.has_more_page,
            page: data.page
          };

          if (!data.has_more_page) {
            $leadsWrapper.find(".btn-load-more").hide();
          }
        });

        self.loadLeads(statusId);

        leadsSortable[statusId] = new Sortable($leads.get(0), {
          group: "lead-container",
          draggable: '.lead-status-kanban-item-lead-container',
          filter: '.btn-load-more',
          animation: 200,
          scroll: true,
          scrollSensitivity: 85,
          onEnd: function(e){
            var isStatusChanged = e.from !== e.to;
            var sort;

            if (!isStatusChanged) {
              sort = this.toArray();

              self.sendSortLead(statusId, sort);
            } else {
              var leadId = $(e.item).data("id");
              var toStatusId = $(e.to).closest(".lead-status-kanban-item").data("id");
              sort = leadsSortable[toStatusId].toArray();

              self.sendMoveLead(leadId, statusId, toStatusId, sort);
            }
          }
        });
      });

      leadStatusSortable = new Sortable($element.get(0), {
        animation: 200,
        scroll: $element.get(0),
        scrollSensitivity: 85,
        handle: ".lead-status-kanban-item-header",
        onEnd: function(e){
          self.sendSort();
        }
      });
    };

    init();
  };

  $.fn.leadStatusKanban = function(first, second, third){
    var leadStatusKanban = this.data("leadStatusKanban");

    if (!leadStatusKanban) {
      if (typeof first === "undefined" || typeof first === "object") {
        leadStatusKanban = new LeadStatusKanban(this, first);

        this.data("leadStatusKanban", leadStatusKanban);
      }

      return this;
    }

    return leadStatusKanban[first](second, third);
  };

})(jQuery);
