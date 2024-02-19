(function ($) {
  "use strict";

  Drupal.behaviors.tabChange = {
    attach: function (context) {
      $('.tab', context).on('click', function() {
        var tab = this.getAttribute('id').replace("tab-","");
        sessionStorage.setItem('tab', tab);
        $(this).addClass('active');
        $('#block-' + tab).attr('style', 'display: block;');
        $('.tab[id!="tab-' + tab + '"]').removeClass('active');
        $('.tabbed-filter[id!="block-' + tab + '"]').attr('style', 'display: none;');
      });
      $(window).on('load', function() {
        if (!sessionStorage.tab){
          $('.tab-bar').children(":first").addClass('active');
          $('.view-filter-fields').children(":first").attr('style', 'display: block;');
        } else{
          $('#tab-' + sessionStorage.tab).addClass('active');
          $('#block-' + sessionStorage.tab).attr('style', 'display: block;');
          $('.tab[id!="tab-' + sessionStorage.tab + '"]').removeClass('active');
          $('.tabbed-filter[id!="block-' + sessionStorage.tab + '"]').attr('style', 'display: none;');  
        }
      });
      $(document).on('ajaxSuccess', function() {
        if (!sessionStorage.tab){
          $('.tab-bar').children(":first").addClass('active');
          $('.view-filter-fields').children(":first").attr('style', 'display: block;');
        } else{
          $('#tab-' + sessionStorage.tab).addClass('active');
          $('#block-' + sessionStorage.tab).attr('style', 'display: block;');
          $('.tab[id!="tab-' + sessionStorage.tab + '"]').removeClass('active');
          $('.tabbed-filter[id!="block-' + sessionStorage.tab + '"]').attr('style', 'display: none;');  
        }
      });
    }
  };

  Drupal.behaviors.cardFlip = {
    attach: function (context, settings) {
      $('.usa-card__flip').on('mouseenter', function() {
        if ($(this).children('.usa-card__rollover').length > 0) {
          $(this).children('.usa-card__default').hide();
          $(this).children('.usa-card__rollover').show();
        }
      });

      $('.usa-card__flip').on('mouseleave', function() {
        if ($(this).children('.usa-card__rollover').length > 0) {
          $(this).children('.usa-card__default').show();
          $(this).children('.usa-card__rollover').hide();
        }
      });
    }
  };

  Drupal.behaviors.moreLessToggle = {
    attach: function (context, settings) {
      if ($('.toggle-section').length > 0) {
        // Toggle visibility of second field in group
        if ($('.toggle-section').children('.toggle.double').length) {
          $('.toggle-section').children('.toggle.double').children('.toggle-more').on('click', function() {
            $(this)
              .parent('.toggle.double')
              .parent('.toggle-section')
              .children('.children')
              .children('.field:nth-child(2)')
              .show();
            $(this).hide();
            $(this)
              .parent('.toggle.double')
              .children('.toggle-less')
              .show();
          });

          $('.toggle-section').children('.toggle.double').children('.toggle-less').on('click', function() {
            $(this)
              .parent('.toggle.double')
              .parent('.toggle-section')
              .children('.children')
              .children('.field:nth-child(2)')
              .hide();
            $(this).hide();
            $(this)
              .parent('.toggle.double')
              .children('.toggle-more')
              .show();
          });
        // Toggle visibility of extra paragraphs in single field
        } else if ($('.toggle-section').children('.has-toggle.needs-trim').length) {
          $('.toggle-section').children('.has-toggle.needs-trim').each( function(tsIdx) {
            var field_contents = $(this).children(".field").contents();
            $(field_contents).not(":first-child").css('display', 'none');

            $(this).parent('aside').children('.toggle.single').children('.toggle-more').on('click', function() {
              $(field_contents).not(":first-child").css('display', '');
              $(this).hide();
              $(this)
                .parent('.toggle.single')
                .children('.toggle-less')
                .show();
            });

            $(this).parent('aside').children('.toggle.single').children('.toggle-less').on('click', function() {
              $(field_contents).not(":first-child").css('display', 'none');
              $(this).hide();
              $(this)
                .parent('.toggle.single')
                .children('.toggle-more')
                .show();
            });
          });
        }
      }
    }
  };

  Drupal.behaviors.desktopHeaderSearch = {
    attach: function (context, settings) {
      if ($('#desktop-header-search-container').length > 0) {
        $('#desktop-header-search-icon > button').on('click', function() {
          $(this).parent('#desktop-header-search-icon').hide();
          $('#desktop-header-search-block').show();
        });

        $('#desktop-header-search-container').on('blur', function() {
          $('#desktop-header-search-icon').show();
          $('#desktop-header-search-block').hide();
        });

        $('#desktop-header-search-closer').on('click', function() {
          $('#desktop-header-search-icon').show();
          $('#desktop-header-search-block').hide();
        });
      }
    }
  };

  Drupal.behaviors.footerMenuNavigation = {
    attach: function (context, settings) {
      $('.usa-footer__mobile-toggle').on('click', function() {
        var this_submenu = $(this).parent('div').parent('section').children('ul');
        var this_id = $(this).attr('aria-controls');

        // Expand or collapse the target menu.
        if ($(this).hasClass('expanded')) {
          $(this)
            .removeClass('expanded')
            .addClass('collapsed')
            .attr('aria-expanded', 'false');
          $(this_submenu).hide();
        } else {
          $(this)
            .removeClass('collapsed')
            .addClass('expanded')
            .attr('aria-expanded', 'true');
          $(this_submenu).show();
        }

        // Close all other menus to mimic accordion behavior.
        $('.usa-footer__mobile-toggle[aria-controls!="' + this_id + '"]')
          .removeClass('expanded')
          .addClass('collapsed')
          .attr('aria-expanded', 'false')
          .parent('div')
          .parent('section')
          .children('ul')
          .hide();
      });
    }
  };

  Drupal.behaviors.primaryMenuNavigation = {
    attach: function (context, settings) {
      var timeouts = {};
      const breakpoints = {
        'desktop': 1024,
        'tablet-lg': 800,
        'tablet': 640,
        'mobile-lg': 480,
        'mobile': 320
      };
      const menu_timeout = 3000;

      function _nbw_uswds_hide_submenu(id) {
        $('#' + id).attr('hidden', 'hidden');
      }

      function _nbw_uswds_show_submenu(id) {
        $('#' + id).removeAttr('hidden');

        _nbw_uswds_set_timer(id);
      }

      function _nbw_uswds_set_timer(id) {
        timeouts[id] = setTimeout(
          _nbw_uswds_hide_submenu,
          menu_timeout,
          id
        );
      }

      function _nbw_uswds_clear_timer(id) {
        clearTimeout(timeouts[id]);
      }

      function _nbw_uswds_set_navigation_top(val) {
        $('#header')
          .children('.usa-nav-container')
          .children('nav.usa-nav')
          .css('top', val);
      }

      function _nbw_uswds_set_navigation() {
        var headerHeight = $('#header').outerHeight();
        var headerOffset = $('#header').offset(); 
        var headerOffsetTop = headerOffset.top;
        var headerNavTop = headerHeight + headerOffsetTop;
        var windowWidth = $(document).width();
        var id;
        var controlId;

        if (windowWidth >= breakpoints.desktop) {
          _nbw_uswds_set_navigation_top('');

          $('ul.usa-nav__submenu')
            .off()
            .on('mouseenter', function() {       
              id = $(this).attr('id');

              _nbw_uswds_clear_timer(id);
              $('ul.usa-nav__submenu[id!="' + id + '"]').attr('hidden', 'hidden');
            })
            .on('mouseleave', function() {
              //$(this)
              $('ul.usa-nav__submenu')
                .css('display', '')
                .attr('hidden', 'hidden');
              $(this)
                .parent('li.usa-nav__primary-item')
                .trigger('blur');
            }
          );

          // Hide all submenus.
          $('ul.usa-nav__primary')
            .children('li.usa-nav__primary-item')
            .children('a.usa-nav__link')
            .off()
            .on('mouseenter', function() {
              controlId = $(this).attr('aria-controls');

              $('.usa-nav__submenu[id!="' + controlId + '"]').attr('hidden', 'hidden');

              // Un-hide target submenu.
              if ($(this).hasClass('has-submenu')) {
                _nbw_uswds_show_submenu(controlId);
              }
            }
          );

        } else {
          $('nav.usa-nav')
            .off()
            .css('width', '100%');

            _nbw_uswds_set_navigation_top(headerNavTop + 'px');

          $('ul.usa-nav__primary')
            .children('.usa-nav__primary-item')
            .children('.mobile-nav-expander')
            .off()
            .on('click', function() {
              $('.usa-nav__submenu.menu-level--1').attr('hidden', 'hidden');

              controlId = $(this).attr('aria-controls');

              if ($(this).attr('aria-expanded') == 'false') {
                $(this).attr('aria-expanded', 'true');
                $('#' + controlId).removeAttr('hidden');
              } else {
                $(this).attr('aria-expanded', 'false');
                $('#' + controlId).attr('hidden', 'hidden');
              }
            }
          );

          $('ul.usa-nav__primary a')
            .off()
            .on('click', function() {
              $(this).addClass('active');
            }
          );

          $('button.usa-menu-btn')
            .off()
            .on('click', function() {
              if ($('nav.usa-nav').length > 0) {
                if ($('nav.usa-nav').hasClass('is-visible')) {
                  $('nav.usa-nav').removeClass('is-visible');
                } else {
                  $('nav.usa-nav').addClass('is-visible');
                }
              }
            }
          );

          $('button.usa-nav__close')
            .off()
            .on('click', function() {
              $('nav.usa-nav').removeClass('is-visible');
            }
          );
        }
      }

      _nbw_uswds_set_navigation();

      $(window).resize( function() {
        _nbw_uswds_set_navigation();
      });
    }
  };

  // Block quicktab tabs when child chart is blank.
  Drupal.behaviors.chartTabHider = {
    attach: function (context, settings) {
      var labelElement;
      var labelText = '';
      var chartBlockId = '';
      var firstChartBlockId = '';
      var chartId;
      var hasChart = false;

      // Iterate through each tab pane.
      if ($('.quicktabs-main').length > 0) {
        $('.quicktabs-main').children('.quicktabs-tabpage').each( function(qtIdx) {
          hasChart = false;
          chartBlockId = $(this).attr('id');

          // Check each chart in this pane.
          $(this).children('div').children('.views-element-container').each( function(veIdx) {
            // Get id of this chart.
            chartId = $(this).attr('id');

            labelElement = $(this)
              .find('.charts-highchart')
              .children('.highcharts-container')
              .children('svg')
              .children('g.highcharts-xaxis-labels')
              .children('text:first-child');

            labelText = $(labelElement)
              .text()
              .replace(/^\s+|\s+$/gm, '')
              .trim();

            if (labelText.length) {
              // There is at least one chart in this pane.
              hasChart = true;
            } else {
              // Hide this chart within the pane.
              $('#' + chartId).remove();
            }
          });

          // Hide tab and pane if no valid charts in this pane.
          if (hasChart === false) {
            // Hide tab.
            $('ul.quicktabs-tabs')
              .children('li[aria-controls="' + chartBlockId + '"]')
              .hide()
              .removeClass('active')
              .attr('aria-selected', 'false');

            // Hide pane.
            $('#' + chartBlockId)
              .addClass('quicktabs-hide');
          }

          if (hasChart && firstChartBlockId.length === 0) {
            firstChartBlockId = chartBlockId;
          }
        });
      }

      if (firstChartBlockId.length > 0) {
        // Activate tab.
        $('ul.quicktabs-tabs')
          .children('li[aria-controls="' + firstChartBlockId + '"]')
          .trigger('click')
          .addClass('active')
          .attr('aria-selected', 'true');

        // Activate pane.
        $('#' + firstChartBlockId)
          .removeClass('quicktabs-hide');
      }
    }
  };

  // Resize Power BI charts on partner profiles.
  Drupal.behaviors.powerChartSizer = {
    attach: function (context, settings) {
      function _nbw_uswds_set_power_chart_size() {
        jQuery('iframe[src*="app.powerbi.com"]').each( function(idx) {
          const ar = 1.1;
          var p = jQuery(this).parent('p');
          var w = p.width();
          var h = Math.ceil(w / ar);
          jQuery(this).width(w);
          jQuery(this).height(h);
        });
      }

      _nbw_uswds_set_power_chart_size();

      $(window).resize( function() {
        _nbw_uswds_set_power_chart_size();
      });
    }
  };

  // Resize videos on SWAP pages.
  Drupal.behaviors.videoSizer = {
    attach: function (context, settings) {
      function _nbw_uswds_set_video_size() {
        jQuery('iframe[src*="youtube.com/embed"]').each( function(idx) {
          var aspectRatio = 1.7777;
          var embedWidth = jQuery(this).attr('width');
          var embedHeight = jQuery(this).attr('height');

          if (typeof embedWidth !== 'undefined' && typeof embedHeight !== 'undefined') {
            aspectRatio = embedWidth / embedHeight;
          }

          var parent = jQuery(this).parent();
          var parentWidth = parent.width();
          var parentHeight = Math.ceil(parentWidth / aspectRatio);

          if (parentWidth > 0 && parentHeight > 0) {          
            jQuery(this).width(parentWidth);
            jQuery(this).height(parentHeight);
          }
        });
      }

      _nbw_uswds_set_video_size();

      $(window).resize( function() {
        _nbw_uswds_set_video_size();
      });
    }
  };

  // Load more toggle for solutions block
  Drupal.behaviors.solutionsBlockLoader = {
    attach: function (context, settings) {
      if ($('.field.field--name-field-solution-block').length > 0) {
        var hasToggle = false;
        var nidx = 0;
        var nid;
        var nids = [];

        $('.field.field--name-field-solution-block').find('.item').each( function(i) {
          nid = $(this).attr('nid');

          if (nids.indexOf(nid) >= 0) {
            $(this).remove();
          } else {
            nids.push(nid);
            nidx++;
          }

          if (nidx >3) {
            hasToggle = true;
            $(this).hide();
          }
        });

        if (!hasToggle) {
          $('#solutions-block-footer').hide();
        }

        $('.field.field--name-field-solution-block').find('.pill:contains("Other Resources")').hide();
      }

      $('#solutions-block-loader').on('click', function(e) {
        $('.field.field--name-field-solution-block').find('.item').show();
        $('#solutions-block-footer').hide();
      });
    }
  };

  Drupal.behaviors.mobilePartnerSlides = {
    attach: function (context, settings) {

      if ($('.slider-slides').length) {
        const breakpoints = {
          'desktop': 1024
        };

        function _nbw_uswds_destroy_partner_slider() {
          try {  
            var existingSlick = $('.slider-slides');

            if (typeof existingSlick.slick !== 'undefined') {
              $('.slider-slides').slick('unslick');  
            }
          } catch (e) {

          }
        }

        function _nbw_uswds_set_partner_slider() {
          var windowWidth = $(document).width();
          var slideCount = 0;
          var thisHeight = 0;
          var slickHeight = 0;

          try {      

            if (windowWidth < breakpoints.desktop) {
              $('.slider-slides .mobile-sidebar-slider-slide').each( function(idx) {
                slideCount++;

                thisHeight = $(this).height();

                if (thisHeight > slickHeight) {
                  slickHeight = thisHeight;
                }
              });

              if (slideCount) {
                $('.slider-slides').slick({
                  slide: '.mobile-sidebar-slider-slide',
                  slidesToShow: 1,
                  slidesToScroll: 1,
                  arrows: true,
                  dots: true,
                  prevArrow: '<button class="slider-cta slide-prev"></button>',
                  nextArrow: '<button class="slider-cta slide-next"></button>'
                });

                $('.slick-list').height(slickHeight + 32);
              }
            }
          } catch (e) {

          }
        }

        _nbw_uswds_set_partner_slider();

        $(window).resize( function() {
          _nbw_uswds_destroy_partner_slider();

          _nbw_uswds_set_partner_slider();
        });
      }
    }
  };

  Drupal.behaviors.swapGalleryMover = {
    attach: function (context, settings) {
      var existingElement = '#block-nbw-uswds-views-block-swap-block-swap-photo-gallery';
      var newElement = '#swap-photo-gallery';

      if ($(existingElement).length) {
        if ($(newElement).length) {
          var galleryHtml = $(existingElement).html();

          $(existingElement).remove();
          $(newElement).html(galleryHtml);
        }
      }
    }
  };

  Drupal.behaviors.searchApiFormMover = {
    attach: function (context, settings) {
      var searchView = '.view.view--search';

      if ($(searchView).length) {
        var searchFormId = '#views-exposed-form-search-page-1';
        var searchInput = $(searchView).find('.form-item.form-item-search-api-views-fulltext');
        var searchSort = $(searchView).find('.form-item.form-item-sort-bef-combine');
        var searchHeader = $(searchView).find('header');
        var searchFacetsId = $('#block-facetsummary');
        var searchFacets = $(searchFacetsId);
        var clearFilters = $(searchView).find('#edit-actions').find('#edit-reset');
        var clearFiltersTop = $(searchView).find('#edit-reset');

        if (typeof searchHeader !== 'undefined' && typeof searchSort !== 'undefined') {
          var searchHeaderHtml = searchHeader.prop('outerHTML');

          if (typeof searchHeaderHtml !== 'undefined') {
            $(searchHeader).remove();
            $(searchSort).after(searchHeaderHtml);
          }
        }

        if (typeof clearFilters !== 'undefined') {
          var clearFiltersHtml = clearFilters.clone(true);  

          if (typeof clearFiltersHtml !== 'undefined') {
            clearFiltersHtml.hide();
            $(searchInput).after(clearFiltersHtml);
            $(clearFilters).remove();
          }
        }

        if (typeof searchFacets !== 'undefined' && typeof searchSort !== 'undefined') {
          var searchFacetsHtml = searchFacets.prop('outerHTML');

          if (typeof searchFacetsHtml !== 'undefined') {
            $(searchFacets).remove();
            $(searchSort).before(searchFacetsHtml);
            $(clearFiltersTop).show();
          }
        }
      }
    }
  };

})(jQuery);