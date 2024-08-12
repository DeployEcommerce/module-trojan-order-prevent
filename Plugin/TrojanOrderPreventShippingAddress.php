<?php
namespace DeployEcommerce\TrojanOrderPrevent\Plugin;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class TrojanOrderPreventShippingAddress
 *
 * This plugin intercepts the assignment of billing addresses to carts in Magento 2.
 * It checks for specific strings in the request body to prevent trojan orders.
 */
class TrojanOrderPreventShippingAddress
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
     * PreventTrojanOrderAddressSet constructor.
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
     * @param ShippingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     * @throws AccessDeniedHttpException|\Magento\Framework\Exception\LocalizedException
     */
    public function beforeAssign(
        ShippingAddressManagementInterface $subject,
        $cartId,
        AddressInterface $address
    ): array {
        if ($this->state->getAreaCode() === \Magento\Framework\App\Area::AREA_WEBAPI_REST) {
            $fields = $this->request->getBodyParams();

            // For speed and ease of checking, flatten the array into a string.
            $fields = strtolower(json_encode($fields));

            // Iterate through our banned strings.
            foreach ($this->strings_to_find as $string) {
                if (strpos($fields, $string) !== false) {
                    throw new AccessDeniedHttpException('This request is not permitted.');
                }
            }
        }

        return [$cartId, $address];
    }
}
