{**
 * Vanilla Matomo Tracking — tag de base, injecté sur displayHeader.
 * Aucune dépendance jQuery : script autonome et asynchrone.
 *}
<!-- Matomo (vanillamatomo) -->
<script>
  var _paq = window._paq = window._paq || [];
  {if $vanillamatomo_disable_cookies}
  _paq.push(['disableCookies']);
  {/if}
  _paq.push(['enableLinkTracking']);
  {if $vanillamatomo_ecommerce_view}
  {$vanillamatomo_ecommerce_view nofilter}
  {/if}
  _paq.push(['trackPageView']);
  (function () {
    var u = "{$vanillamatomo_url|escape:'javascript':'UTF-8'}";
    _paq.push(['setTrackerUrl', u + 'matomo.php']);
    _paq.push(['setSiteId', '{$vanillamatomo_site_id|intval}']);
    var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
    g.type = 'text/javascript';
    g.async = true;
    g.src = u + 'matomo.js';
    s.parentNode.insertBefore(g, s);
  })();
</script>
<!-- End Matomo -->
