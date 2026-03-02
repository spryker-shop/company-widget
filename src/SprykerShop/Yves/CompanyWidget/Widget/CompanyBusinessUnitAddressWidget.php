<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyWidget\Widget;

use Generated\Shared\Transfer\AddressTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;
use SprykerShop\Yves\CompanyWidget\Address\AddressProviderInterface;

/**
 * @method \SprykerShop\Yves\CompanyWidget\CompanyWidgetFactory getFactory()
 */
class CompanyBusinessUnitAddressWidget extends AbstractWidget
{
    /**
     * @var string
     */
    protected const PARAMETER_CURRENT_COMPANY_BUSINESS_UNIT_ADDRESS = 'currentCompanyBusinessUnitAddress';

    public function __construct(string $formType, AddressTransfer $formAddressTransfer)
    {
        $addressProvider = $this->getFactory()
            ->createAddressProvider();

        $this->addFormTypeParameter($formType);
        $this->addFormAddressTransferParameter($formAddressTransfer);
        $this->addIsApplicableParameter($addressProvider);

        if (!$addressProvider->companyBusinessUnitAddressesExists()) {
            return;
        }

        $customerAddresses = $addressProvider->getIndexedCustomerAddressList();
        $companyBusinessUnitAddresses = $addressProvider->getIndexedCompanyBusinessUnitAddressList();

        $this->addAddressesParameter($customerAddresses, $companyBusinessUnitAddresses);
        $this->addCustomerAddressesParameter($customerAddresses);
        $this->addCompanyBusinessUnitAddressesParameter($companyBusinessUnitAddresses);
        $this->addCurrentCompanyBusinessUnitAddressParameter(
            $addressProvider->findCurrentCompanyBusinessUnitAddress($formAddressTransfer, $companyBusinessUnitAddresses),
        );
        $this->addIsCurrentAddressEmptyParameter($formAddressTransfer);
    }

    protected function addIsCurrentAddressEmptyParameter(AddressTransfer $addressTransfer): void
    {
        $this->addParameter('isCurrentAddressEmpty', $this->isAddressEmpty($addressTransfer));
    }

    protected function addFormTypeParameter(string $formType): void
    {
        $this->addParameter('formType', $formType);
    }

    protected function addFormAddressTransferParameter(AddressTransfer $formAddressTransfer): void
    {
        $this->addParameter('formAddressTransfer', $formAddressTransfer);
    }

    protected function addIsApplicableParameter(AddressProviderInterface $addressProvider): void
    {
        $this->addParameter('isApplicable', $addressProvider->companyBusinessUnitAddressesExists());
    }

    /**
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $customerAddresses
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $companyBusinessUnitAddresses
     *
     * @return void
     */
    protected function addAddressesParameter(array $customerAddresses, array $companyBusinessUnitAddresses): void
    {
        $addressJson = $this->encodeAddressesToJson(
            array_merge(
                $customerAddresses,
                $companyBusinessUnitAddresses,
            ),
        );

        $this->addParameter('addresses', $addressJson);
    }

    /**
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $customerAddresses
     *
     * @return void
     */
    protected function addCustomerAddressesParameter(array $customerAddresses): void
    {
        $this->addParameter('customerAddresses', $customerAddresses);
    }

    /**
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $companyBusinessUnitAddresses
     *
     * @return void
     */
    protected function addCompanyBusinessUnitAddressesParameter(array $companyBusinessUnitAddresses): void
    {
        $this->addParameter('companyBusinessUnitAddresses', $companyBusinessUnitAddresses);
    }

    protected function addCurrentCompanyBusinessUnitAddressParameter(?AddressTransfer $currentCompanyBusinessUnitAddressTransfer): void
    {
        $this->addParameter(static::PARAMETER_CURRENT_COMPANY_BUSINESS_UNIT_ADDRESS, $currentCompanyBusinessUnitAddressTransfer);
    }

    public static function getName(): string
    {
        return 'CompanyBusinessUnitAddressWidget';
    }

    public static function getTemplate(): string
    {
        return '@CompanyWidget/views/company-business-unit-address/company-business-unit-address.twig';
    }

    /**
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $addressTransferList
     *
     * @return string
     */
    protected function encodeAddressesToJson(array $addressTransferList): string
    {
        $addressesList = [];
        foreach ($addressTransferList as $key => $addressTransfer) {
            $addressesList[$addressTransfer->getKey()] = $addressTransfer->toArray();
        }
        $jsonEncodedAddresses = json_encode($addressesList, JSON_PRETTY_PRINT);

        return ($jsonEncodedAddresses !== false)
            ? $jsonEncodedAddresses
            : '[]';
    }

    protected function isAddressEmpty(AddressTransfer $addressTransfer): bool
    {
        return ($addressTransfer->getFirstName() === null || $addressTransfer->getFirstName() === ''
            || $addressTransfer->getLastName() === null || $addressTransfer->getLastName() === '');
    }
}
