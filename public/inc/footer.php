<!-- reproductor de podcast (persistente, esquina inferior derecha) -->
<style>
  .ddp-player { position: fixed; right: 20px; bottom: 20px; z-index: 2000; width: 320px; max-width: calc(100% - 24px);
    background: #fff; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.18); padding: 16px 18px; border: 1px solid #eee; }
  .ddp-player[hidden] { display: none; }
  .ddp-player-close { position: absolute; top: 6px; right: 10px; border: none; background: none; font-size: 22px; line-height: 1; color: #999; cursor: pointer; }
  .ddp-player-title { font-weight: 600; font-size: 15px; color: #212020; padding-right: 20px; margin-bottom: 12px; }
  .ddp-player-controls { display: flex; align-items: center; justify-content: center; gap: 22px; margin-bottom: 12px; }
  .ddp-player-controls button { background: none; border: none; cursor: pointer; color: #e0020d; position: relative; padding: 4px; }
  .ddp-player-controls button[data-action="back15"], .ddp-player-controls button[data-action="fwd30"] { font-size: 22px; }
  .ddp-player-controls button[data-action="back15"] small, .ddp-player-controls button[data-action="fwd30"] small {
    position: absolute; top: 56%; left: 50%; transform: translate(-50%,-50%); font-size: 9px; font-weight: 700; color: #e0020d; }
  #ddp-player-toggle { width: 46px; height: 46px; border-radius: 50%; background: #e0020d; color: #fff; font-size: 16px;
    display: flex; align-items: center; justify-content: center; }
  .ddp-player-controls button[data-action="share"] { font-size: 17px; color: #666; }
  .ddp-player-progress input[type=range] { width: 100%; accent-color: #e0020d; }
  .ddp-player-time { display: flex; justify-content: space-between; font-size: 11px; color: #888; margin-top: 2px; }
  @media (max-width: 575px) { .ddp-player { left: 12px; right: 12px; width: auto; bottom: 12px; } }

  .ddp-podcast-thumb { position: relative; display: block; }
  .ddp-podcast-thumb .ddp-play-overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
  .ddp-podcast-thumb .ddp-play-overlay span { width: 54px; height: 54px; border-radius: 50%; background: rgba(224,2,13,.9); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 20px; }
  .ddp-podcast-thumb.is-playing .ddp-play-overlay span { background: rgba(33,32,32,.85); }

  /* Flechas de los carruseles de Podcast/Especiales: separadas del contenido, sin puntitos. */
  #especiales-carousel, #podcast-carousel { position: relative; }
  #especiales-carousel > .owl-nav, #podcast-carousel > .owl-nav {
    display: flex !important; justify-content: space-between; align-items: center;
    position: absolute; top: 50%; left: 0; right: 0; transform: translateY(-50%);
    margin: 0; pointer-events: none; }
  #especiales-carousel > .owl-nav > *, #podcast-carousel > .owl-nav > * {
    pointer-events: auto; }
  #especiales-carousel > .owl-dots, #podcast-carousel > .owl-dots { display: none !important; }
  @media (min-width: 992px) {
    #especiales-carousel > .owl-nav, #podcast-carousel > .owl-nav { left: -46px; right: -46px; }
  }
</style>
<div id="ddp-player" class="ddp-player" hidden>
    <button type="button" class="ddp-player-close" aria-label="Cerrar">&times;</button>
    <div class="ddp-player-title" id="ddp-player-title"></div>
    <div class="ddp-player-controls">
        <button type="button" data-action="back15" aria-label="Retroceder 15 segundos"><span class="fa fa-undo"></span><small>15</small></button>
        <button type="button" id="ddp-player-toggle" data-action="toggle" aria-label="Reproducir o pausar"><span class="fa fa-play"></span></button>
        <button type="button" data-action="fwd30" aria-label="Avanzar 30 segundos"><span class="fa fa-redo"></span><small>30</small></button>
        <button type="button" data-action="share" aria-label="Compartir"><span class="fa fa-share-alt"></span></button>
    </div>
    <div class="ddp-player-progress">
        <input type="range" id="ddp-player-range" min="0" max="100" value="0" step="0.1">
        <div class="ddp-player-time"><span id="ddp-player-current">0:00</span><span id="ddp-player-duration">0:00</span></div>
    </div>
    <audio id="ddp-player-audio" preload="none"></audio>
</div>

<!-- middle -->
<div class="middle py-5">
    <div class="container py-xl-5 py-lg-3">
        <div class="welcome-left text-center py-md-5 py-3">
            <h3 class="title-big">Síguenos en nuestras Redes Sociales</h3>
            <div class="main-social-footer-29">
            <a target="_blank" rel="noopener" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square fa-2x"></span></a>
            <a target="_blank" rel="noopener" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/site/tiktokg.png" alt="TikTok"></a>
            <a target="_blank" rel="noopener" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram fa-2x"></span></a>
          </div>
        </div>
    </div>
</div>
<!-- //middle -->

<!-- footer block -->
<section class="w3l-footer-29-main py-5" id="footer">
  <div class="footer-29 py-md-3">
    <div class="container">
      <div class="row footer-top-29">
        <div class="col-lg-6 col-md-6 footer-list-29 footer-1">
          <h6 class="footer-title-29">Quiénes Somos</h6>
          <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
          <div class="main-social-footer-29">
            <a target="_blank" rel="noopener" href="https://www.facebook.com/DialogoyDesarrolloPeru" class="facebook"><span class="fa fa-facebook-square"></span></a>
            <a target="_blank" rel="noopener" href="https://www.tiktok.com/@dialogo.y.desarrollo" class="twitter"><img src="assets/site/tiktokp.png" alt="TikTok"></a>
            <a target="_blank" rel="noopener" href="https://www.instagram.com/dialogo.y.desarrollo/" class="instagram"><span class="fa fa-instagram"></span></a>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 footer-list-29 footer-2 mt-md-0 mt-5">
          <ul>
            <h6 class="footer-title-29">Contenido</h6>
            <li><a href="index.php#actualidad">Noticias</a></li>
            <li><a href="index.php#especiales">Videos</a></li>
            <li><a href="index.php#podcast">Podcast</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6 mt-lg-0 mt-5 footer-list-29 footer-3">
          <div class="properties">
            <h6 class="footer-title-29">Contacto</h6>
            <ul>
            <li><a href="mailto:info@dialogoydesarrollo.com.pe">info@dialogoydesarrollo.com.pe</a></li>
          </ul>
          </div>
        </div>
      </div>
      <div class="bottom-copies text-center">
            <p class="copy-footer-29">© <?php echo date('Y'); ?> Diálogo y Desarrollo Perú. All rights reserved | Designed by <a target="_blank" rel="noopener" href="https://www.wsperu.info/">WebSolutions</a></p>
        </div>
    </div>
  </div>
  <button onclick="topFunction()" id="movetop" title="Ir arriba" style="display: none;">
    <span class="fa fa-angle-up"></span>
  </button>
  <script>
    window.onscroll = function () { scrollFunction() };
    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        document.getElementById("movetop").style.display = "block";
      } else {
        document.getElementById("movetop").style.display = "none";
      }
    }
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>
</section>
<!-- //footer block -->

<!-- Template JavaScript -->
<script src="assets/site/jquery-3.3.1.min.js"></script>
<script src="assets/site/theme-change.js"></script>
<script src="assets/site/easyResponsiveTabs.js"></script>
<script src="assets/site/owl.carousel.js"></script>
<script>
  $(document).ready(function () {
    $('.owl-logos').owlCarousel({
      loop: true, margin: 0, nav: false, responsiveClass: true,
      autoplay: true, autoplayTimeout: 5000, autoplaySpeed: 1000, autoplayHoverPause: false,
      responsive: { 0: { items: 2 }, 480: { items: 2 }, 568: { items: 3 }, 1000: { items: 5 } }
    });

    $('.owl-carousel:not(.owl-logos):not(#especiales-carousel):not(#podcast-carousel)').owlCarousel({
      loop: true, margin: 0, responsiveClass: true,
      responsive: {
        0: { items: 1, nav: true },
        400: { items: 2, nav: true, margin: 20 },
        768: { items: 3, nav: true, margin: 20 },
        1000: { items: 4, nav: true, loop: true, margin: 25 }
      }
    });

    // Carrusel de "Especiales": solo repite en bucle si hay mas videos que
    // los que caben en pantalla (evita mostrar el mismo video clonado varias veces).
    var $especiales = $('#especiales-carousel');
    if ($especiales.length) {
      var nVideos = parseInt($especiales.data('count'), 10) || 0;
      $especiales.owlCarousel({
        loop: nVideos > 4, margin: 0, responsiveClass: true, slideBy: 1,
        nav: nVideos > 1, dots: false,
        responsive: {
          0: { items: Math.min(nVideos, 1) || 1, slideBy: 1 },
          400: { items: Math.min(nVideos, 2) || 1, margin: 20, slideBy: 1 },
          768: { items: Math.min(nVideos, 3) || 1, margin: 20, slideBy: 1 },
          1000: { items: Math.min(nVideos, 4) || 1, margin: 25, loop: nVideos > 4, slideBy: 1 }
        }
      });
    }

    // Carrusel de "Podcast": misma logica, una sola fila desplazable.
    var $podcastCarousel = $('#podcast-carousel');
    if ($podcastCarousel.length) {
      var nPodcasts = parseInt($podcastCarousel.data('count'), 10) || 0;
      $podcastCarousel.owlCarousel({
        loop: nPodcasts > 3, margin: 25, responsiveClass: true, slideBy: 1,
        nav: nPodcasts > 1, dots: false,
        responsive: {
          0: { items: Math.min(nPodcasts, 1) || 1, slideBy: 1 },
          576: { items: Math.min(nPodcasts, 2) || 1, margin: 20, slideBy: 1 },
          1000: { items: Math.min(nPodcasts, 3) || 1, margin: 25, loop: nPodcasts > 3, slideBy: 1 }
        }
      });
    }
  });
</script>
<script>
  $(function () {
    $('.navbar-toggler').click(function () { $('body').toggleClass('noscroll'); });
  });
  $(window).on("scroll", function () {
    var scroll = $(window).scrollTop();
    if (scroll >= 80) { $("#site-header").addClass("nav-fixed"); }
    else { $("#site-header").removeClass("nav-fixed"); }
  });
  $(".navbar-toggler").on("click", function () { $("header").toggleClass("active"); });
</script>
<script src="assets/site/jquery.magnific-popup.min.js"></script>
<script>
  $(document).ready(function () {
    $('.popup-with-zoom-anim').magnificPopup({
      type: 'inline', fixedContentPos: false, fixedBgPos: true, overflowY: 'auto',
      closeBtnInside: true, preloader: false, midClick: true, removalDelay: 300, mainClass: 'my-mfp-zoom-in'
    });
  });
</script>
<script src="assets/site/bootstrap.min.js"></script>

<!-- reproductor de podcast -->
<script>
(function () {
    var panel = document.getElementById('ddp-player');
    if (!panel) return;
    var audio = document.getElementById('ddp-player-audio');
    var titleEl = document.getElementById('ddp-player-title');
    var toggleBtn = document.getElementById('ddp-player-toggle');
    var range = document.getElementById('ddp-player-range');
    var curEl = document.getElementById('ddp-player-current');
    var durEl = document.getElementById('ddp-player-duration');
    var closeBtn = panel.querySelector('.ddp-player-close');
    var currentThumb = null;

    function fmt(s) {
        if (!isFinite(s) || s < 0) return '0:00';
        s = Math.floor(s);
        var m = Math.floor(s / 60), r = s % 60;
        return m + ':' + (r < 10 ? '0' : '') + r;
    }
    function setPlayingIcon(playing) {
        toggleBtn.innerHTML = playing ? '<span class="fa fa-pause"></span>' : '<span class="fa fa-play"></span>';
        if (currentThumb) currentThumb.classList.toggle('is-playing', playing);
    }
    function openPlayer(thumb) {
        var src = thumb.getAttribute('data-podcast-src');
        if (!src) return;
        if (currentThumb && currentThumb !== thumb) {
            currentThumb.classList.remove('is-playing');
        }
        currentThumb = thumb;
        if (audio.getAttribute('data-current-src') !== src) {
            audio.src = src;
            audio.setAttribute('data-current-src', src);
        }
        titleEl.textContent = thumb.getAttribute('data-podcast-title') || '';
        panel.hidden = false;
        audio.play().catch(function () {});
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-podcast-src]');
        if (!t) return;
        e.preventDefault();
        if (currentThumb === t && !audio.paused) {
            audio.pause();
            return;
        }
        openPlayer(t);
    });

    toggleBtn.addEventListener('click', function () {
        if (!audio.src) return;
        if (audio.paused) audio.play().catch(function () {}); else audio.pause();
    });
    panel.querySelector('[data-action="back15"]').addEventListener('click', function () {
        audio.currentTime = Math.max(0, audio.currentTime - 15);
    });
    panel.querySelector('[data-action="fwd30"]').addEventListener('click', function () {
        audio.currentTime = Math.min(audio.duration || 1e9, audio.currentTime + 30);
    });
    closeBtn.addEventListener('click', function () {
        audio.pause();
        panel.hidden = true;
        if (currentThumb) currentThumb.classList.remove('is-playing');
    });
    panel.querySelector('[data-action="share"]').addEventListener('click', function () {
        var url = (currentThumb && currentThumb.getAttribute('data-podcast-share')) || window.location.href;
        var shareTitle = titleEl.textContent || document.title;
        if (navigator.share) {
            navigator.share({ title: shareTitle, url: url }).catch(function () {});
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () { alert('Enlace copiado: ' + url); });
        } else {
            window.prompt('Copia el enlace:', url);
        }
    });

    audio.addEventListener('play', function () { setPlayingIcon(true); });
    audio.addEventListener('pause', function () { setPlayingIcon(false); });
    audio.addEventListener('timeupdate', function () {
        curEl.textContent = fmt(audio.currentTime);
        if (audio.duration) range.value = (audio.currentTime / audio.duration) * 100;
    });
    audio.addEventListener('loadedmetadata', function () { durEl.textContent = fmt(audio.duration); });
    range.addEventListener('input', function () {
        if (audio.duration) audio.currentTime = (range.value / 100) * audio.duration;
    });
})();
</script>
</body>
</html>
