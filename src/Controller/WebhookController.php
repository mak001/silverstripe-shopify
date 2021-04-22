<?php

namespace Dynamic\Shopify\Controller;

use Dynamic\Shopify\Client\ShopifyClient;
use Dynamic\Shopify\Page\ShopifyProduct;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

/**
 * Class WebhookController
 * @package Dynamic\Shopify\Controller
 */
class WebhookController extends Controller
{
    private static $allowed_actions = [
        'createProduct',
        'updateProduct',
        'deleteProduct',
    ];

    /**
     * @inerhitDoc
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function init()
    {
        parent::init();

        $request = $this->getRequest();
        if ($request->getHeader('X-Shopify-Shop-Domain') !== ShopifyClient::config()->get('shopify_domain')) {
            return $this->httpError(403, 'mis-matched shopify domain');
        }

        $secret = ShopifyClient::config()->get('shared_secret');
        $calculated_hmac = base64_encode(hash_hmac('sha256', $request->getBody(), $secret, true));
        if (hash_equals($request->getHeader('X-Shopify-Hmac-Sha256'), $calculated_hmac)) {
            return $this->httpError(403, 'payload did not verify correctly');
        }
    }

    /**
     * @param HTTPRequest $request
     * @throws \SilverStripe\Control\HTTPResponse_Exception
     */
    public function deleteProduct($request)
    {
        if ($request === null) {
            $request = $this->getRequest();
        }

        $body = json_decode($request->getBody(), true);
        /** @var ShopifyProduct|null $product */
        $product = ShopifyProduct::get()->find('ShopifyID', $body['id']);
        if (!$product) {
            return $this->httpError(404, 'product with id ' . $body['id'] . ' not found');
        }
        $product->doUnpublish();
    }

    /**
     * @param HTTPRequest $request
     */
    public function createProduct($request)
    {
        return 'All good';
    }
}
