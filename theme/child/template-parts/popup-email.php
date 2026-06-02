<?php
// Ne pas afficher si l'utilisateur a déjà accepté ou si admin
if (isset($_COOKIE['glowza_popup_closed'])) return;
?>
<div class="glowza-popup-overlay" id="glowzaPopup" aria-hidden="true" role="dialog" aria-label="Offre bienvenue Glowza">
  <div class="glowza-popup">
    <button class="glowza-popup__close" id="glowzaPopupClose" aria-label="Fermer">×</button>

    <div class="glowza-popup__deco">✨</div>

    <h2 class="glowza-popup__title">
      -10% sur ta<br>première commande
    </h2>
    <p class="glowza-popup__sub">
      Inscris-toi et reçois ton code promo instantanément.<br>
      <small>Nouveautés, tendances & offres exclusives chaque semaine.</small>
    </p>

    <form class="glowza-popup__form" id="glowzaEmailForm">
      <input
        type="email"
        name="email"
        placeholder="ton@email.fr"
        required
        autocomplete="email"
        class="glowza-popup__input"
      >
      <button type="submit" class="btn glowza-popup__btn">
        Je veux mon -10% →
      </button>
      <p class="glowza-popup__gdpr">
        Sans spam. Désinscription en 1 clic. <a href="/politique-confidentialite/">RGPD</a>
      </p>
    </form>

    <div class="glowza-popup__success" id="glowzaPopupSuccess" style="display:none;">
      <span class="glowza-popup__check">✅</span>
      <p>C'est dans ta boîte ! Utilise le code <strong>BIENVENUE10</strong> au checkout.</p>
    </div>
  </div>
</div>

<style>
.glowza-popup-overlay {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.55);
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
  opacity: 0; pointer-events: none;
  transition: opacity .3s ease;
}
.glowza-popup-overlay.active { opacity: 1; pointer-events: all; }

.glowza-popup {
  background: #fff;
  border-radius: 20px;
  padding: 40px 32px;
  max-width: 420px; width: 100%;
  text-align: center;
  position: relative;
  box-shadow: 0 20px 60px rgba(0,0,0,.18);
  animation: popupIn .4s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes popupIn {
  from { transform: scale(.85) translateY(20px); opacity: 0; }
  to   { transform: scale(1) translateY(0); opacity: 1; }
}

.glowza-popup__close {
  position: absolute; top: 14px; right: 18px;
  background: none; border: none;
  font-size: 22px; color: #999; cursor: pointer; line-height: 1;
}
.glowza-popup__close:hover { color: #333; }

.glowza-popup__deco { font-size: 2.4rem; margin-bottom: 8px; }

.glowza-popup__title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.8rem; color: #1E1E1E;
  margin-bottom: 12px; line-height: 1.2;
}

.glowza-popup__sub {
  color: #6B6B6B; font-size: .9rem; line-height: 1.6; margin-bottom: 24px;
}
.glowza-popup__sub small { font-size: .8rem; color: #999; }

.glowza-popup__input {
  width: 100%; padding: 13px 16px;
  border: 1.5px solid #EEE0E5; border-radius: 999px;
  font-size: 1rem; margin-bottom: 10px;
  transition: border-color .2s;
}
.glowza-popup__input:focus { border-color: #E8849A; outline: none; }

.glowza-popup__btn { width: 100%; padding: 14px; }

.glowza-popup__gdpr { font-size: .75rem; color: #BBB; margin-top: 10px; }
.glowza-popup__gdpr a { color: #BBB; text-decoration: underline; }

.glowza-popup__check { font-size: 2rem; display: block; margin-bottom: 12px; }
.glowza-popup__success p { color: #444; line-height: 1.7; }
.glowza-popup__success strong { color: #E8849A; font-size: 1.1rem; }
</style>

<script>
(function () {
  var popup    = document.getElementById('glowzaPopup');
  var closeBtn = document.getElementById('glowzaPopupClose');
  var form     = document.getElementById('glowzaEmailForm');
  var success  = document.getElementById('glowzaPopupSuccess');
  var triggers = document.querySelectorAll('.glowza-popup-trigger');

  if (!popup) return;

  function openPopup()  { popup.classList.add('active'); popup.setAttribute('aria-hidden', 'false'); }
  function closePopup() {
    popup.classList.remove('active'); popup.setAttribute('aria-hidden', 'true');
    document.cookie = 'glowza_popup_closed=1; path=/; max-age=2592000'; // 30j
  }

  // Auto-ouverture après 8 secondes (première visite seulement)
  setTimeout(openPopup, 8000);

  // Triggers manuels (boutons CTA homepage)
  triggers.forEach(function(t) { t.addEventListener('click', function(e){ e.preventDefault(); openPopup(); }); });

  // Fermeture
  closeBtn.addEventListener('click', closePopup);
  popup.addEventListener('click', function(e){ if (e.target === popup) closePopup(); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closePopup(); });

  // Soumission formulaire
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var email = form.querySelector('input[name="email"]').value;

    // Envoyer via AJAX WordPress (peut se brancher à Mailchimp/Brevo via le plugin)
    fetch('<?= admin_url('admin-ajax.php') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=glowza_subscribe&email=' + encodeURIComponent(email) + '&nonce=<?= wp_create_nonce('glowza_subscribe') ?>',
    })
    .then(function(r){ return r.json(); })
    .catch(function(){})
    .finally(function() {
      form.style.display = 'none';
      success.style.display = 'block';
      setTimeout(closePopup, 4000);
    });
  });
})();
</script>
