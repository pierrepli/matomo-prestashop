<?php
/**
 * Vanilla Matomo Tracking
 *
 * Intégration Matomo pour PrestaShop, en JavaScript 100% natif (aucune
 * dépendance jQuery), suivi sans cookie et tracking e-commerce complet.
 * Beaucoup de modules Matomo tiers injectent un script inline qui suppose
 * jQuery déjà chargé au moment de son exécution — ce qui casse sur les
 * thèmes qui chargent jQuery tardivement (ex. Hummingbird v2, erreur
 * "$ is not defined"). Ce module n'a aucune dépendance JS externe.
 *
 * @author    Pierre Plissonneau
 * @copyright 2026 Pierre Plissonneau
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Vanillamatomo extends Module
{
    public function __construct()
    {
        $this->name = 'vanillamatomo';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Pierre Plissonneau';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Vanilla Matomo Tracking');
        $this->description = $this->l('Suivi Matomo sans cookie et tracking e-commerce, en JavaScript natif (sans jQuery).');
        $this->confirmUninstall = $this->l('Voulez-vous vraiment désinstaller ce module ?');
    }

    public function install()
    {
        Configuration::updateValue('VANILLAMATOMO_URL', '');
        Configuration::updateValue('VANILLAMATOMO_SITE_ID', '');
        Configuration::updateValue('VANILLAMATOMO_DISABLE_COOKIES', 1);
        Configuration::updateValue('VANILLAMATOMO_ECOMMERCE', 1);

        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall()
    {
        Configuration::deleteByName('VANILLAMATOMO_URL');
        Configuration::deleteByName('VANILLAMATOMO_SITE_ID');
        Configuration::deleteByName('VANILLAMATOMO_DISABLE_COOKIES');
        Configuration::deleteByName('VANILLAMATOMO_ECOMMERCE');

        return parent::uninstall();
    }

    /**
     * Page de configuration du module (back-office).
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitVanillamatomo')) {
            $matomoUrl = trim((string) Tools::getValue('VANILLAMATOMO_URL'));
            $siteId = trim((string) Tools::getValue('VANILLAMATOMO_SITE_ID'));
            $disableCookies = (int) Tools::getValue('VANILLAMATOMO_DISABLE_COOKIES');
            $ecommerce = (int) Tools::getValue('VANILLAMATOMO_ECOMMERCE');

            if ($matomoUrl === '' || $siteId === '') {
                $output .= $this->displayError(
                    $this->l('L\'URL Matomo et l\'identifiant du site sont obligatoires.')
                );
            } else {
                Configuration::updateValue('VANILLAMATOMO_URL', $matomoUrl);
                Configuration::updateValue('VANILLAMATOMO_SITE_ID', $siteId);
                Configuration::updateValue('VANILLAMATOMO_DISABLE_COOKIES', $disableCookies);
                Configuration::updateValue('VANILLAMATOMO_ECOMMERCE', $ecommerce);
                $output .= $this->displayConfirmation($this->l('Réglages enregistrés.'));
            }
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Réglages Matomo'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('URL de Matomo'),
                        'name' => 'VANILLAMATOMO_URL',
                        'desc' => $this->l('Ex : //stats.mon-domaine.fr/ (avec le / final)'),
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Identifiant du site (idSite)'),
                        'name' => 'VANILLAMATOMO_SITE_ID',
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Suivi sans cookie'),
                        'name' => 'VANILLAMATOMO_DISABLE_COOKIES',
                        'desc' => $this->l('Appelle disableCookies avant le premier pageview.'),
                        'values' => [
                            ['id' => 'vanillamatomo_cookies_on', 'value' => 1, 'label' => $this->l('Activé')],
                            ['id' => 'vanillamatomo_cookies_off', 'value' => 0, 'label' => $this->l('Désactivé')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Suivi e-commerce'),
                        'name' => 'VANILLAMATOMO_ECOMMERCE',
                        'desc' => $this->l('Vue produit, ajout panier, commande validée.'),
                        'values' => [
                            ['id' => 'vanillamatomo_ecommerce_on', 'value' => 1, 'label' => $this->l('Activé')],
                            ['id' => 'vanillamatomo_ecommerce_off', 'value' => 0, 'label' => $this->l('Désactivé')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Enregistrer'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitVanillamatomo';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['VANILLAMATOMO_URL'] = Configuration::get('VANILLAMATOMO_URL');
        $helper->fields_value['VANILLAMATOMO_SITE_ID'] = Configuration::get('VANILLAMATOMO_SITE_ID');
        $helper->fields_value['VANILLAMATOMO_DISABLE_COOKIES'] = Configuration::get('VANILLAMATOMO_DISABLE_COOKIES');
        $helper->fields_value['VANILLAMATOMO_ECOMMERCE'] = Configuration::get('VANILLAMATOMO_ECOMMERCE');

        return $helper->generateForm([$fieldsForm]);
    }

    /**
     * Injecte le tag Matomo de base (+ setEcommerceView sur une fiche produit).
     */
    public function hookDisplayHeader($params)
    {
        // Ne pas tracer les employés connectés (prévisualisations back-office).
        if (isset($this->context->employee) && Validate::isLoadedObject($this->context->employee)) {
            return '';
        }

        $matomoUrl = Configuration::get('VANILLAMATOMO_URL');
        $siteId = Configuration::get('VANILLAMATOMO_SITE_ID');

        if (!$matomoUrl || !$siteId) {
            return '';
        }

        $ecommerceView = '';
        if (Configuration::get('VANILLAMATOMO_ECOMMERCE')
            && isset($this->context->controller)
            && $this->context->controller instanceof ProductController
            && method_exists($this->context->controller, 'getProduct')
        ) {
            $product = $this->context->controller->getProduct();

            if (Validate::isLoadedObject($product)) {
                $categoryName = '';
                $category = new Category((int) $product->id_category_default, (int) $this->context->language->id);
                if (Validate::isLoadedObject($category)) {
                    $categoryName = $category->name;
                }

                $sku = $product->reference ? $product->reference : ('idv0_' . (int) $product->id);

                $ecommerceView = sprintf(
                    "_paq.push(['setEcommerceView', %s, %s, %s, %s]);\n      ",
                    json_encode($sku),
                    json_encode($product->name),
                    json_encode($categoryName),
                    json_encode((float) $product->getPrice())
                );
            }
        }

        $this->context->smarty->assign([
            'vanillamatomo_url' => $matomoUrl,
            'vanillamatomo_site_id' => (int) $siteId,
            'vanillamatomo_disable_cookies' => (bool) Configuration::get('VANILLAMATOMO_DISABLE_COOKIES'),
            'vanillamatomo_ecommerce_view' => $ecommerceView,
        ]);

        return $this->fetch('module:vanillamatomo/views/templates/hook/header.tpl');
    }

    /**
     * Écoute l'événement JS prestashop.on('updateCart') pour tracer les ajouts panier
     * (vanilla JS, aucune dépendance jQuery — c'est le bug qu'on corrige).
     */
    public function hookDisplayFooter($params)
    {
        if (!Configuration::get('VANILLAMATOMO_ECOMMERCE')) {
            return '';
        }

        if (isset($this->context->employee) && Validate::isLoadedObject($this->context->employee)) {
            return '';
        }

        return $this->fetch('module:vanillamatomo/views/templates/hook/footer.tpl');
    }

    /**
     * Trace la commande validée (trackEcommerceOrder) sur la page de confirmation.
     */
    public function hookDisplayOrderConfirmation($params)
    {
        if (!Configuration::get('VANILLAMATOMO_ECOMMERCE')) {
            return '';
        }

        $order = isset($params['order']) ? $params['order'] : null;

        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $items = [];
        foreach ($order->getProducts() as $product) {
            $items[] = [
                'sku' => $product['product_reference'] ? $product['product_reference'] : ('idv0_' . (int) $product['product_id']),
                'name' => $product['product_name'],
                'category' => '',
                'price' => (float) $product['product_price'],
                'quantity' => (int) $product['product_quantity'],
            ];
        }

        $this->context->smarty->assign([
            'vanillamatomo_order_reference' => $order->reference,
            'vanillamatomo_order_items' => $items,
            'vanillamatomo_order_total' => (float) $order->total_paid,
            'vanillamatomo_order_subtotal' => (float) $order->total_products,
            'vanillamatomo_order_tax' => (float) ($order->total_paid_tax_incl - $order->total_paid_tax_excl),
            'vanillamatomo_order_shipping' => (float) $order->total_shipping,
            'vanillamatomo_order_discount' => (float) $order->total_discounts,
        ]);

        return $this->fetch('module:vanillamatomo/views/templates/hook/order_confirmation.tpl');
    }
}
