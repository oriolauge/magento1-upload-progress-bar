<?php
/**
 * Oag UploadProgressBar Block
 *
 * @category   Oag
 * @package    Oag_UploadProgressBar
 * @author     Oag Team
 */
class Oag_UploadProgressBar_Block_UploadProgressBar extends Mage_Core_Block_Template
{
    protected function getUploadProgressBarAddToCartUrl()
    {
        return $this->getUrl('uploadprogressbar/checkout_cart/add');
    }
}