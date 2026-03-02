<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyWidget;

use Spryker\Yves\Kernel\AbstractFactory;
use SprykerShop\Yves\CompanyWidget\Address\AddressProvider;
use SprykerShop\Yves\CompanyWidget\Address\AddressProviderInterface;
use SprykerShop\Yves\CompanyWidget\Dependency\Client\CompanyWidgetToCustomerClientInterface;

class CompanyWidgetFactory extends AbstractFactory
{
    public function createAddressProvider(): AddressProviderInterface
    {
        return new AddressProvider(
            $this->getCustomerClient(),
        );
    }

    public function getCustomerClient(): CompanyWidgetToCustomerClientInterface
    {
        return $this->getProvidedDependency(CompanyWidgetDependencyProvider::CLIENT_CUSTOMER);
    }
}
