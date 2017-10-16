<?php

include(__DIR__.'/../../wayforpay.cls.php');

class WayforpayValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        parent::postProcess();

        global $cookie, $link;

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $language = Language::getIsoById((int)$cookie->id_lang);
        $language = (!in_array($language, array('ua', 'en', 'ru'))) ? 'ru' : $language;
        $language = strtoupper($language);

        $currency = new CurrencyCore($cart->id_currency);
        $payCurrency = $currency->iso_code;
        $w4p = new Wayforpay();
        $w4pCls = new WayForPayCls();
        $total = $cart->getOrderTotal();

        $option = array();
        $option['wfp_pay_plg'] = 'PrestaShop '.(defined('_PS_VERSION_')?_PS_VERSION_:'');
        $option['merchantAccount'] = $w4p->getOption('merchant');
        $option['orderDate'] = strtotime($cart->date_add);
        $option['merchantAuthType'] = 'simpleSignature';
        $option['merchantDomainName'] = $_SERVER['HTTP_HOST'];
        $option['merchantTransactionSecureType'] = 'AUTO';
        $option['currency'] = $payCurrency;
        $option['amount'] = $total;
        $option['language'] = $language;
        $option['serviceUrl'] = $link->getModuleLink('wayforpay', 'callback');
        $option['returnUrl'] = $link->getModuleLink('wayforpay', 'result');

        $productNames = array();
        $productPrices = array();
        $productQty = array();

        foreach ($cart->getProducts() as $product) {
        $productNames[] = str_replace(["'", '"', '&#39;'], ['', '', ''], htmlspecialchars_decode($product['name']));
        $productPrices[] = $product['total_wt'];
        $productQty[] = $product['quantity'];
        }

        $option['productName'] = $productNames;
        $option['productPrice'] = $productPrices;
        $option['productCount'] = $productQty;

        $address = new AddressCore($cart->id_address_invoice);
        if ($address) {
            $customer = new CustomerCore($address->id_customer);
            /**
             * Check phone
             */
            $phone = str_replace(array('+', ' ', '(', ')'), array('', '', '', ''), $address->phone_mobile);
            if (strlen($phone) == 10) {
                $phone = '38' . $phone;
            } elseif (strlen($phone) == 11) {
                $phone = '3' . $phone;
            }

            $option['clientFirstName'] = $address->firstname;
            $option['clientLastName'] = $address->lastname;
            $option['clientEmail'] = $customer->email;
            $option['clientPhone'] = $phone;
            $option['clientCity'] = $address->city;
            $option['clientAddress'] = $address->address1 . ' ' . $address->address2;
            $option['clientCountry'] = 'UKR';
        }
        $w4p->validateOrder((int)$cart->id, _PS_OS_PREPARATION_, $total, $w4p->displayName);
        $option['orderReference'] = $w4p->currentOrder . WayForPayCls::ORDER_SEPARATOR . time();
        $option['merchantSignature'] = $w4pCls->getRequestSignature($option);

        $url = WayForPayCls::URL;

        $this->context->smarty->assign(array('fields' => $option, 'url' => $url));
        $this->setTemplate('module:wayforpay/views/templates/front/redirect.tpl');
    }
}
