<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyWidget\Address;

use ArrayObject;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use SprykerShop\Yves\CompanyWidget\Dependency\Client\CompanyWidgetToCustomerClientInterface;

class AddressProvider implements AddressProviderInterface
{
    /**
     * @var string
     */
    protected const COMPANY_BUSINESS_UNIT_ADDRESS_KEY_PATTERN = 'company_business_unit_address_%s';

    /**
     * @var string
     */
    protected const CUSTOMER_ADDRESS_KEY_PATTERN = 'customer_address_%s';

    /**
     * @var string
     */
    protected const KEY_IS_DEFAULT_SHIPPING = 'is_default_shipping';

    /**
     * @var string
     */
    protected const KEY_IS_DEFAULT_BILLING = 'is_default_billing';

    /**
     * @var string
     */
    protected const KEY_ID_CUSTOMER_ADDRESS = 'id_customer_address';

    /**
     * @var \SprykerShop\Yves\CompanyWidget\Dependency\Client\CompanyWidgetToCustomerClientInterface
     */
    protected $customerClient;

    public function __construct(CompanyWidgetToCustomerClientInterface $customerClient)
    {
        $this->customerClient = $customerClient;
    }

    public function companyBusinessUnitAddressesExists(): bool
    {
        $customerTransfer = $this->customerClient->getCustomer();

        if ($customerTransfer === null) {
            return false;
        }

        return $customerTransfer->getCompanyUserTransfer() && ($this->getCompanyBusinessUnitAddressCollection($customerTransfer)->count() > 0);
    }

    /**
     * @return array<\Generated\Shared\Transfer\AddressTransfer>
     */
    public function getIndexedCustomerAddressList(): array
    {
        $customerAddressTransferList = $this->getCustomerAddressList();
        foreach ($customerAddressTransferList as $addressTransfer) {
            $addressTransfer->setKey($this->getCustomerAddressKey($addressTransfer->getIdCustomerAddress()));
        }

        return $customerAddressTransferList->getArrayCopy();
    }

    /**
     * @return array<\Generated\Shared\Transfer\AddressTransfer>
     */
    public function getIndexedCompanyBusinessUnitAddressList(): array
    {
        $customerTransfer = $this->customerClient->getCustomer();
        $companyBusinessUnitAddressTransferList = $this->getCompanyBusinessUnitAddressCollection($customerTransfer);
        $addressTransferList = [];
        foreach ($companyBusinessUnitAddressTransferList as $addressTransfer) {
            $addressTransferList[] = $this->mapCompanyBusinessUnitAddressToAddress($addressTransfer, $customerTransfer);
        }

        return $addressTransferList;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $formAddressTransfer
     * @param array<\Generated\Shared\Transfer\AddressTransfer> $companyBusinessUnitAddresses
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|null
     */
    public function findCurrentCompanyBusinessUnitAddress(AddressTransfer $formAddressTransfer, array $companyBusinessUnitAddresses): ?AddressTransfer
    {
        $formAddressData = $formAddressTransfer->modifiedToArray();
        if ($this->isAddressFormDataEmpty($formAddressData)) {
            return null;
        }

        $formAddressData = $this->cleanAddressDefaultFields($formAddressData);

        foreach ($companyBusinessUnitAddresses as $companyBusinessUnitAddressTransfer) {
            if ($this->isSameCompanyUnitAddress($formAddressData, $companyBusinessUnitAddressTransfer)) {
                return $companyBusinessUnitAddressTransfer;
            }
        }

        return null;
    }

    protected function isSameCompanyUnitAddress(array $formAddressData, AddressTransfer $companyBusinessUnitAddressTransfer): bool
    {
        $companyBusinessUnitAddressData = $companyBusinessUnitAddressTransfer->toArray();

        foreach ($formAddressData as $formAddressKey => $formAddressValue) {
            if (
                !isset($companyBusinessUnitAddressData[$formAddressKey])
                || $companyBusinessUnitAddressData[$formAddressKey] !== $formAddressValue
            ) {
                return false;
            }
        }

        return true;
    }

    protected function cleanAddressDefaultFields(array $formAddressData): array
    {
        unset(
            $formAddressData[static::KEY_IS_DEFAULT_SHIPPING],
            $formAddressData[static::KEY_IS_DEFAULT_BILLING],
            $formAddressData[static::KEY_ID_CUSTOMER_ADDRESS],
        );

        return $formAddressData;
    }

    protected function isAddressFormDataEmpty(array $formAddressData): bool
    {
        return !array_filter($formAddressData);
    }

    protected function mapCompanyBusinessUnitAddressToAddress(
        CompanyUnitAddressTransfer $companyUnitAddressTransfer,
        CustomerTransfer $customerTransfer
    ): AddressTransfer {
        $addressTransfer = (new AddressTransfer())
            ->fromArray($companyUnitAddressTransfer->modifiedToArray(), true);

        $addressTransfer = $this->setAddressCustomerAttributes($addressTransfer, $customerTransfer);
        $addressTransfer->setKey($this->getBusinessUnitAddressKey($companyUnitAddressTransfer->getIdCompanyUnitAddress()));
        $addressTransfer = $this->hydrateCompanyNameToAddressTransfer($addressTransfer, $customerTransfer);

        return $addressTransfer;
    }

    /**
     * @return \ArrayObject<int, \Generated\Shared\Transfer\AddressTransfer>
     */
    protected function getCustomerAddressList(): ArrayObject
    {
        $customerTransfer = $this->customerClient->getCustomer();
        $addressesTransfer = $customerTransfer->getAddresses();

        if ($addressesTransfer === null) {
            return new ArrayObject();
        }

        return $customerTransfer->getAddresses()
            ->getAddresses();
    }

    protected function findCompanyBusinessUnit(CustomerTransfer $customerTransfer): ?CompanyBusinessUnitTransfer
    {
        $companyUserTransfer = $customerTransfer->getCompanyUserTransfer();
        if ($companyUserTransfer === null) {
            return null;
        }

        return $companyUserTransfer->getCompanyBusinessUnit();
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CompanyUnitAddressTransfer>
     */
    protected function getCompanyBusinessUnitAddressCollection(CustomerTransfer $customerTransfer): ArrayObject
    {
        $companyBusinessUnitTransfer = $this->findCompanyBusinessUnit($customerTransfer);
        if ($companyBusinessUnitTransfer === null) {
            return new ArrayObject();
        }

        $companyBusinessUnitAddressCollection = $companyBusinessUnitTransfer->getAddressCollection();
        if ($companyBusinessUnitAddressCollection === null) {
            return new ArrayObject();
        }

        $companyUnitAddressTransfers = $companyBusinessUnitAddressCollection->getCompanyUnitAddresses();

        $idCompanyUnitAddress = $companyBusinessUnitTransfer->getDefaultBillingAddress();
        if (!$idCompanyUnitAddress) {
            return $companyUnitAddressTransfers;
        }

        return $this->markDefaultBillingCompanyBusinessUnitAddress($idCompanyUnitAddress, $companyUnitAddressTransfers);
    }

    /**
     * @param int $idCompanyUnitAddress
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CompanyUnitAddressTransfer> $companyUnitAddressTransfers
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CompanyUnitAddressTransfer>
     */
    protected function markDefaultBillingCompanyBusinessUnitAddress(
        int $idCompanyUnitAddress,
        ArrayObject $companyUnitAddressTransfers
    ): ArrayObject {
        foreach ($companyUnitAddressTransfers as $companyUnitAddressTransfer) {
            if ($companyUnitAddressTransfer->getIdCompanyUnitAddress() === $idCompanyUnitAddress) {
                $companyUnitAddressTransfer->setIsDefaultBilling(true);

                return $companyUnitAddressTransfers;
            }
        }

        return $companyUnitAddressTransfers;
    }

    protected function getCustomerAddressKey(int $idCustomerAddress): string
    {
        return sprintf(static::CUSTOMER_ADDRESS_KEY_PATTERN, $idCustomerAddress);
    }

    protected function getBusinessUnitAddressKey(int $idCompanyUnitAddress): string
    {
        return sprintf(static::COMPANY_BUSINESS_UNIT_ADDRESS_KEY_PATTERN, $idCompanyUnitAddress);
    }

    protected function setAddressCustomerAttributes(
        AddressTransfer $addressTransfer,
        CustomerTransfer $customerTransfer
    ): AddressTransfer {
        return $addressTransfer
            ->setLastName($customerTransfer->getLastName())
            ->setFirstName($customerTransfer->getFirstName())
            ->setSalutation($customerTransfer->getSalutation());
    }

    protected function hydrateCompanyNameToAddressTransfer(
        AddressTransfer $addressTransfer,
        CustomerTransfer $customerTransfer
    ): AddressTransfer {
        $companyBusinessUnitTransfer = $this->findCompanyBusinessUnit($customerTransfer);
        if ($companyBusinessUnitTransfer === null) {
            return $addressTransfer;
        }

        $companyTransfer = $companyBusinessUnitTransfer->getCompany();
        if ($companyTransfer === null) {
            return $addressTransfer;
        }

        $addressTransfer->setCompany($companyTransfer->getName());

        return $addressTransfer;
    }
}
