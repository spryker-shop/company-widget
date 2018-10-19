<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyWidget\Address;

interface AddressProviderInterface
{
    /**
     * @return bool
     */
    public function companyBusinessUnitAddressesExists(): bool;

    /**
     * @param string $formType
     *
     * @return string|null
     */
    public function getCombinedAddressesListJson(string $formType): ?string;

    /**
     * @param string $formType
     *
     * @return array
     */
    public function getCombinedComparableAddressesList(string $formType): array;
}