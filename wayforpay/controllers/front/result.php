<?php

require_once(dirname(__FILE__) . '../../../wayforpay.php');
require_once(dirname(__FILE__) . '../../../wayforpay.cls.php');
require_once(dirname(__FILE__) . '../../../../classes/order/Order.php');
require_once(dirname(__FILE__) . '../../../../classes/order/OrderHistory.php');

class WayforpayResultModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $data = $_POST;

        $order_id = !empty($data['orderReference']) ? $data['orderReference'] : null;
        $order = new OrderCore(intval($order_id));
        if (!Validate::isLoadedObject($order)) {
            die('Заказ не найден');
        }

        $wayForPayCls = new WayForPayCls();

        $isPaymentValid = $wayForPayCls->isPaymentValid($data);
        if ($isPaymentValid !== true) {
            $this->errors[] = Tools::displayError($isPaymentValid);
        }

        $customer = new CustomerCore($order->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (empty($this->errors)) {

            list($orderId,) = explode(WayForPayCls::ORDER_SEPARATOR, $data['orderReference']);
            $history = new OrderHistoryCore();
            $history->id_order = $orderId;
            $history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $orderId);
            $history->addWithemail(true, array(
                'order_name' => $orderId
            ));

            return Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $order->id_cart . '&id_module=' . $this->module->id . '&id_order=' . $this->module->currentOrder);
        }

    }
}