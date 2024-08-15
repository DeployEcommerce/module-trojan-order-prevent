# module-trojan-order-prevent

This is a Magento 2 extension that prevents billing/shipping addresses being 
saved via the API with known trojan order strings. This is *not a fix* for 
CVE-2022-24086 but an additional layer of protection for merchants.

Although patched in most recent Magento versions we still see probes for this 
which look rather unsightly for merchants in the orders screen of Magento.

This module adds two plugins to the `Magento\Quote\Api\BillingAddressManagementInterface` 
and the `Magento\Quote\Model\ShippingAddressManagementInterface` to prevent the 
saving of addresses with the following strings:

```
gettemplate
base64_
afterfiltercall
.filter(
magdemo9816@proton.me
.php
this.getTemp
{{var
```

If these are detected in the payload then an Exception is thrown and the address is not saved.

### Installation
```bash
composer require deployecommerce/module-trojan-order-prevent
bin/magento mo:e DeployEcommerce_TrojanOrderPrevent
```

### Further Reading
- https://sansec.io/research/trojanorder-magento
- https://www.bleepingcomputer.com/news/security/magento-stores-targeted-in-massive-surge-of-trojanorders-attacks/
- https://cyberfraudcentre.com/surge-in-trojanorders-attacks-on-magento-2-e-commerce-sites
- https://magento.stackexchange.com/questions/358839/magento-2-fake-customer-order-came-through-with-weird-code-instead-of-customer
- https://github.com/magento/magento2/issues/36691

### License

This module is licensed under the MIT License. See the [LICENSE](LICENSE.md) file for details.