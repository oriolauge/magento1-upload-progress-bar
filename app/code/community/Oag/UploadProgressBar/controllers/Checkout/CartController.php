<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Oag
 * @package     Oag_UploadProgressBar
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Oag UploadProgressBar checkout cart controller
 *
 * Copies addAction from Mage_Checkout_CartController but, we change the redirection and errors messages to
 * json response to catch with ajax.
 */
require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';
class Oag_UploadProgressBar_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_setAjaxResponseBody('error', null, $this->__('Incorrect request data.'));
            return;
        }
        $cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_setAjaxResponseBody('error', null, $this->__('Product not exists.'));
                return;
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }

                $url = $this->_goBackUrl();
                $this->_setAjaxResponseBody('success', $url);
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if (!$url) {
                $url = Mage::helper('checkout/cart')->getCartUrl();
            }
            $this->_setAjaxResponseBody('error', $url);

        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);

            $url = $this->_goBackUrl();
            $this->_setAjaxResponseBody('error', $url);
        }
    }

    /**
     * Set back redirect url to response
     *
     * @return string
     * @throws Mage_Exception
     */
    protected function _goBackUrl()
    {
        $url = $this->getRequest()->getParam('return_url');
        if ($url) {

            if (!$this->_isUrlInternal($url)) {
                throw new Mage_Exception('External urls redirect to "' . $url . '" denied!');
            }

            $this->_getSession()->getMessages(true);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            return $backUrl;
        } else {
            if (
                (strtolower($this->getRequest()->getActionName()) == 'add')
                && !$this->getRequest()->getParam('in_cart')
            ) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $url = Mage::helper('checkout/cart')->getCartUrl();
        }
        return $url;
    }

    /**
     * Set JSON with parameters return body
     * @param string $status
     * @param array  $data
     * @param void
     */
    protected function _setAjaxResponseBody($status, $redirectUrl = null, $message = null)
    {
        $this->getResponse()->setBody(
            Zend_Json::encode(array('status' => $status, 'redirect_url' => $redirectUrl, 'message' => $message))
        );
    }
}
