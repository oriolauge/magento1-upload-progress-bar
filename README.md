OAG - Magento 1 Ajax Add to Cart with upload progress bar extension
====================================================================================

Technical Details
------------------------------------------------------------------------------------

* Name: Oag_UploadProgressBar
* Version: 1.0.0
* Dependencies: Mage_Checkout
* Tested On: Magento CE 1.9.4.2

Summary
------------------------------------------------------------------------------------

This extension adds a progress bar in product details page when user clicks on "Add to cart" button. This is usefull when the product has some custom options file and the "add to cart" action could needs some time to finish. 

This module is developed with the next logic:

1. reading the normal Magento output `button.btn-cart` for correct add to cart URL's `onclick`
2. prevents default onclick handlers for add to cart buttons
3. executes ajax requests with URL's collected during the first step
4. shows a progress bar meanwhile the add to cart requests is prcessing. When the request finish, the user will be redirect to the next page (product page or checkout cart).


Installation
------------------------------------------------------------------------------------

**With FTP**

1. Clear the store cache under var/cache and all cookies for your store domain. Disable compilation if enabled. This step eliminates almost all potential problems. It’s necessary since Magento uses cache heavily.
2. Backup your store database and web directory.
3. Download and unzip extension contents on your computer and navigate inside the extracted folder.
4. Using your FTP client upload content of "app" & "skin" directories to "app" & "skin" directories inside your store root.

**With modman**

1. Install modman script (https://github.com/colinmollenhour/modman)
2. Execute command modman clone with the repository url


Uninstall
------------------------------------------------------------------------------------

You can safely remove the extension files from your store.


Testing
------------------------------------------------------------------------------------

Open the product details page with some custom option file configured and test "Add To Cart". 