{**
 * Vanilla Matomo Tracking — suivi des ajouts panier.
 * Écoute prestashop.on('updateCart') en JS vanilla, chargé après le DOM.
 * C'est ce point précis qui plantait dans le module tiers (jQuery non
 * encore chargé au moment de l'exécution du $(document).ready inline).
 *}
<!-- Matomo ecommerce: suivi panier (vanillamatomo) -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof prestashop === 'undefined' || typeof prestashop.on !== 'function') {
      return;
    }

    prestashop.on('updateCart', function (event) {
      if (!window._paq || !event || !event.reason || event.reason.linkAction !== 'add-to-cart') {
        return;
      }

      if (!event.resp || event.resp.hasError || !event.reason.cart) {
        return;
      }

      var products = event.reason.cart.products || [];

      products.forEach(function (p) {
        var rawPrice = String(p.price || '0').replace(',', '.').replace(/[^0-9.\-]/g, '');
        var price = parseFloat(rawPrice);

        window._paq.push(['addEcommerceItem',
          'idv' + (p.id_product_attribute || 0) + '_' + p.id_product,
          p.name || '',
          p.category || '',
          isNaN(price) ? 0 : price,
          p.quantity || 1
        ]);
      });

      var total = 0;
      if (event.reason.cart.totals && event.reason.cart.totals.total) {
        var rawTotal = String(event.reason.cart.totals.total.amount || '0').replace(',', '.');
        var parsedTotal = parseFloat(rawTotal);
        total = isNaN(parsedTotal) ? 0 : parsedTotal;
      }

      window._paq.push(['trackEcommerceCartUpdate', total]);
    });
  });
</script>
<!-- End Matomo ecommerce cart -->
