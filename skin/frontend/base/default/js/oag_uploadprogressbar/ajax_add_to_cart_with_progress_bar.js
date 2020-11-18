var OagUploadProgressBar = {
    oagUploadProgressBarCheckoutUrl: null,
    // hijack the onclick handler for add to cart buttons
    init: function(oagUploadProgressBarCheckoutUrl) {
        //Change productAddToCartForm.submit(this) to our class
        $j('form#product_addtocart_form button.btn-cart').each(function(event) {
            $j(this).attr('onclick','OagUploadProgressBar.submit(this)');
        });

        //We detect that some actions (like change configurable selections revert our onclick change, this function
        //will prevent this change and addds our class again
        $j('body').on('DOMSubtreeModified', 'form#product_addtocart_form button.btn-cart', function(){
            $j(this).attr('onclick','OagUploadProgressBar.submit(this)');
        });

        this.oagUploadProgressBarCheckoutUrl = oagUploadProgressBarCheckoutUrl;
    },

    submit: function(el) {
        event.preventDefault();
        if (!productAddToCartForm.validator.validate()) {
            return false;
        }

        //Remove error messages if we try to upload a file in the past
        const oagUploadProgressBarErrorDiv = $j('#oag-uploadprogressbar-error');
        if (oagUploadProgressBarErrorDiv.length >= 1) {
            oagUploadProgressBarErrorDiv.remove();
        }

        const myForm = document.getElementById('product_addtocart_form');
        const data = new FormData(myForm);

        const oagUploadProgressBar = $j('#oag-uploadprogressbar')
        const parentdiv = $j(el).parent();
        if (oagUploadProgressBar.length < 1) {
            parentdiv.append('<div id="oag-uploadprogressbar"><div id="oag-progressbar"><span id="oag-percentcomplete"></span></div></div>')
        }
        
        $j.ajax({
            url: this.oagUploadProgressBarCheckoutUrl,
            type: "POST",
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(event) {
                    if (event.lengthComputable) {
                        let percentComplete = event.loaded / event.total;
                        percentComplete = parseInt(percentComplete * 100);
                        $j('#oag-progressbar').css('width', percentComplete  + '%');
                        $j('#oag-percentcomplete').text(percentComplete + '%');
                        if (percentComplete == 100) {
                            const uploadMessage = Translator.translate('This action may take some MINUTES. Please be patient still the file is placed in the cart');
                            $j('#oag-percentcomplete').text(uploadMessage);
                        }
                    }
                }, false);
                return xhr;
            }
        }).done(function(result) {
            const resultJson = $j.parseJSON(result);
            if (resultJson.status == 'error' && !resultJson.redirect_url) {
                OagUploadProgressBar.showErrorMessage(resultJson.message, parentdiv);
            } else if (resultJson.redirect_url) {
                window.location.href = resultJson.redirect_url;
            }
        }).fail(function(result) {
            let errorMessage = '';
            if (result.status == 413) {
                errorMessage = Translator.translate('The file is too large to be processed by the web server. Please, try to resize it and try again.');
            } else {
                errorMessage = result.statusText;
            }
            OagUploadProgressBar.showErrorMessage(errorMessage, parentdiv);
        });        
        return false;
    },

    showErrorMessage: function(message, parentElement) {
        $j('#oag-uploadprogressbar').hide('slow', function(){ $j(this).remove(); });
        parentElement.append('<div id="oag-uploadprogressbar-error">' + message + '</div>')
    }
};
