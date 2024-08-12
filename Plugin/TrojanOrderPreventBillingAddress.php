<?php
namespace DeployEcommerce\TrojanOrderPrevent\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class TrojanOrderPreventBillingAddress
 *
 * This plugin intercepts the assignment of billing addresses to carts in Magento 2.
 * It checks for specific strings in the request body to prevent trojan orders.
 */
class TrojanOrderPreventBillingAddress
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private \Magento\Framework\Webapi\Rest\Request $request;

    /**
     * @var \Magento\Framework\App\State
     */
    private \Magento\Framework\App\State $state;

    /**
     * @var string[]
     */
    public $strings_to_find = [
        'gettemplate',
        'base64_',
        'afterfiltercall',
        '.filter(',
        'magdemo9816@proton.me',
        '.php',
        'this.getTemp',
        '{{var'
    ];

    /**
     * PreventTrojanOrderBillingAddress constructor.
     *
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Webapi\Rest\Request $request
     */
    public function __construct(\Magento\Framework\App\State $state, \Magento\Framework\Webapi\Rest\Request $request)
    {
        $this->state = $state;
        $this->request = $request;
    }

    /**
     * Before assign billing address to cart
     *
     * This method is executed before the billing address is assigned to the cart.
     * It checks the request body for specific strings that indicate a trojan order.
     * If any of these strings are found, an AccessDeniedHttpException is thrown.
     *
     * @param BillingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     * @throws AccessDeniedHttpException
     */
    public function beforeAssign(
        BillingAddressManagementInterface $subject,
        $cartId,
        AddressInterface $address
    ) {
        if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_WEBAPI_REST) {
            $fields = $this->request->getBodyParams();

            // Make sure we have an address key.
            if (array_key_exists('address', $fields)) {
                // For speed and ease of checking, flatten the array into a string.
                $fields = strtolower(json_encode($fields));

                // Iterate through our banned strings.
                foreach ($this->strings_to_find as $string) {
                    if (strpos($fields, $string) !== false) {
                        throw new AccessDeniedHttpException('This request is not permitted.');
                    }
                }
            }
        }

        return [$cartId, $address];
    }
}
