{**
 * Vanilla Matomo Tracking — trackEcommerceOrder sur la page de remerciement.
 * Garde-fou sessionStorage pour éviter un double comptage en cas de F5.
 *}
<!-- Matomo ecommerce: commande validée (vanillamatomo) -->
<script>
  (function () {
    if (typeof window._paq === 'undefined') {
      return;
    }

    var orderRef = {$vanillamatomo_order_reference|json_encode nofilter};
    var storageKey = 'vanillamatomo_order_' + orderRef;

    try {
      if (window.sessionStorage && window.sessionStorage.getItem(storageKey)) {
        return;
      }
    } catch (e) {}

    {foreach $vanillamatomo_order_items as $item}
    _paq.push(['addEcommerceItem',
      {$item.sku|json_encode nofilter},
      {$item.name|json_encode nofilter},
      {$item.category|json_encode nofilter},
      {$item.price|json_encode nofilter},
      {$item.quantity|json_encode nofilter}
    ]);
    {/foreach}

    _paq.push(['trackEcommerceOrder',
      orderRef,
      {$vanillamatomo_order_total|json_encode nofilter},
      {$vanillamatomo_order_subtotal|json_encode nofilter},
      {$vanillamatomo_order_tax|json_encode nofilter},
      {$vanillamatomo_order_shipping|json_encode nofilter},
      {$vanillamatomo_order_discount|json_encode nofilter}
    ]);

    try {
      if (window.sessionStorage) {
        window.sessionStorage.setItem(storageKey, '1');
      }
    } catch (e) {}
  })();
</script>
<!-- End Matomo ecommerce order -->
