/* Glowza — JS principal */
(function ($) {
  'use strict';

  // ─── Catégories pills (navigation rapide) ─────────────────────────────────
  function initCategoryPills() {
    var pills = document.querySelectorAll('.glowza-category-pill');
    pills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        pills.forEach(function (p) { p.classList.remove('active'); });
        this.classList.add('active');
      });
    });
  }

  // ─── Quantité produit : +/- buttons ──────────────────────────────────────
  function initQuantityButtons() {
    $(document).on('click', '.quantity .plus, .quantity .minus', function () {
      var $qty = $(this).siblings('.qty');
      var val  = parseInt($qty.val());
      var max  = parseInt($qty.attr('max'));
      var min  = parseInt($qty.attr('min')) || 1;
      var step = parseInt($qty.attr('step')) || 1;

      if ($(this).hasClass('plus')) {
        if (!max || val < max) $qty.val(val + step).trigger('change');
      } else {
        if (val > min) $qty.val(val - step).trigger('change');
      }
    });
  }

  // ─── Sticky "Ajouter au panier" sur mobile ───────────────────────────────
  function initStickyAddToCart() {
    if (!$('body').hasClass('single-product')) return;

    var $btn     = $('.single_add_to_cart_button');
    var $summary = $('.summary');

    if (!$btn.length || !$summary.length) return;

    var $sticky = $('<div class="glowza-sticky-cart"><button class="button glowza-sticky-cart__btn">Ajouter au panier</button></div>');
    $('body').append($sticky);

    $sticky.find('button').on('click', function () {
      $btn.trigger('click');
    });

    function checkVisibility() {
      var btnBottom = $btn.offset().top + $btn.outerHeight();
      var scrollY   = $(window).scrollTop() + $(window).height();
      $sticky.toggleClass('visible', scrollY < btnBottom + 40);
    }

    $(window).on('scroll', checkVisibility);
    checkVisibility();
  }

  // ─── Mini-cart slide ──────────────────────────────────────────────────────
  $(document.body).on('added_to_cart', function () {
    // Feedback visuel après ajout au panier
    var $feedback = $('<div class="glowza-cart-feedback">✓ Ajouté au panier</div>');
    $('body').append($feedback);
    setTimeout(function () { $feedback.addClass('show'); }, 50);
    setTimeout(function () { $feedback.removeClass('show'); setTimeout(function () { $feedback.remove(); }, 300); }, 2000);
  });

  // ─── Lazy loading images (natif + polyfill léger) ────────────────────────
  function initLazyLoad() {
    if ('loading' in HTMLImageElement.prototype) return; // Natif supporté

    var imgs = document.querySelectorAll('img[loading="lazy"]');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var img = entry.target;
          img.src = img.dataset.src || img.src;
          io.unobserve(img);
        }
      });
    });
    imgs.forEach(function (img) { io.observe(img); });
  }

  // ─── Bandeau livraison animé (header top bar) ────────────────────────────
  function initDeliveryBanner() {
    var messages = [
      '🚚 Livraison en 2–4 semaines · Suivi inclus',
      '🔒 Paiement 100% sécurisé · Stripe & PayPal',
      '↩️ Retour 14 jours garanti (droit FR)',
      '✨ Nouveautés tendance chaque semaine',
    ];
    var idx = 0;
    var $banner = $('.glowza-top-bar__message');
    if (!$banner.length) return;

    setInterval(function () {
      $banner.fadeOut(300, function () {
        idx = (idx + 1) % messages.length;
        $banner.html(messages[idx]).fadeIn(300);
      });
    }, 3500);
  }

  // ─── Checkout : afficher/masquer les champs adresse de livraison ─────────
  function initShippingToggle() {
    var $checkbox = $('#ship-to-different-address-checkbox');
    var $fields   = $('#shipping_address');

    if (!$checkbox.length) return;

    function toggle() {
      $fields.slideToggle($checkbox.is(':checked') ? 'fast' : 'fast');
    }

    $checkbox.on('change', toggle);
    toggle();
  }

  // ─── Init ─────────────────────────────────────────────────────────────────
  $(document).ready(function () {
    initCategoryPills();
    initQuantityButtons();
    initStickyAddToCart();
    initLazyLoad();
    initDeliveryBanner();
    initShippingToggle();
  });

}(jQuery));
