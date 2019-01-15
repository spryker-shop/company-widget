import Component from 'ShopUi/models/component';
import FormClear from 'ShopUi/components/molecules/form-clear/form-clear';

export default class CompanyBusinessUnitAddressHandler extends Component {
    /**
     * Collection of the triggers elements.
     */
    triggers: HTMLElement[];
    /**
     * The current form.
     */
    form: HTMLElement;
    /**
     * Collection of the form elements.
     */
    targets: HTMLElement[];
    /**
     * Collection of the targets elements which should be ignored while collection the filters.
     */
    ignoreElements: HTMLElement[];
    /**
     * Collection of the filter elements.
     */
    filterElements: HTMLElement[];
    /**
     * Imported component clears the form.
     */
    formClear: FormClear;
    /**
     * Data object of the address list.
     */
    addressesDataObject: any;
    /**
     * Collection of the address select elements.
     */
    addressesSelects: HTMLSelectElement[];
    /**
     * The selected address.
     */
    currentAddress: String;
    /**
     * The hidden customer id input.
     */
    hiddenCustomerIdInput: HTMLInputElement;
    /**
     * The hidden input with selected address by default.
     */
    hiddenDefaultAddressInput: HTMLInputElement;
    /**
     * The input address element which triggers toggling of the disabled attribute.
     */
    customAddressTriggerInput: HTMLFormElement;

    protected readyCallback(): void {
        const formElements = 'select, input[type="text"], input[type="radio"], input[type="checkbox"]';

        this.form = <HTMLElement>document.querySelector(this.formSelector);
        this.triggers = <HTMLElement[]>Array.from(this.form.querySelectorAll(this.triggerSelector));
        this.addressesSelects = <HTMLSelectElement[]>Array.from(this.form.querySelectorAll(this.dataSelector));
        this.targets = <HTMLElement[]>Array.from(this.form.querySelectorAll(formElements));
        this.ignoreElements = <HTMLElement[]>Array.from(this.form.querySelectorAll(this.ignoreSelector));
        this.filterElements = this.targets.filter((element) => !this.ignoreElements.includes(element));
        this.formClear = <FormClear>this.form.querySelector('.js-form-clear');
        this.hiddenCustomerIdInput = <HTMLInputElement>this.form.querySelector(this.customerAddressIdSelector);
        this.hiddenDefaultAddressInput = <HTMLInputElement>this.form.querySelector(this.defaultAddressSelector);
        this.customAddressTriggerInput = <HTMLFormElement>this.form.querySelector(this.customAddressTrigger);

        this.initAddressesData();
        this.mapEvents();
        this.fillDefaultAddress();
    }

    protected mapEvents(): void {
        this.formClear.addEventListener('form-fields-clear-after', () => {
            this.toggleFormFieldsReadonly(false);
            this.toggleReadonlyForCustomAddressTrigger();
        });
        this.triggers.forEach((triggerElement) => {
            triggerElement.addEventListener('click', () => {
                this.addressesSelects.forEach((selectElement) => {
                    this.setCurrentAddress(selectElement);
                });
                this.onClick(triggerElement);
            });
        });
    }

    protected onClick(triggerElement: HTMLElement): void {
        if (this.currentAddress) {
            this.fillFormWithNewAddress();
            this.toggleFormFieldsReadonly();
            this.toggleReadonlyForCustomAddressTrigger();
        }
    }

    /**
     * Toggles an array of the filter elements.
     * @param isEnabled A boolean value for checking if the element is available for toggling.
     */
    toggleFormFieldsReadonly(isEnabled: boolean = true): void {
        this.filterElements.forEach((formElement: HTMLFormElement) => {
            this.toggleFormFieldReadOnly(formElement, isEnabled);
        });
    }

    /**
     * Toggles the form element.
     * @param formElement HTMLFormElement for toggling.
     * @param isEnabled A boolean value for checking if the element is available for toggling.
     */
    toggleFormFieldReadOnly(formElement: HTMLFormElement, isEnabled: boolean = true): void {
        const isSelect = this.formClear.getTagName(formElement) == 'SELECT';

        if (isSelect) {
            const options = Array.from(formElement.querySelectorAll('option'));

            options.forEach((element) => {
                if (!element.selected) {
                    element.disabled = isEnabled;
                }
            });

            return;
        }

        formElement.readOnly = isEnabled;
    }

    protected setCurrentAddress(selectElement: HTMLSelectElement): void {
        this.currentAddress = selectElement.options[selectElement.selectedIndex].getAttribute('value');
    }

    protected fillFormWithNewAddress(): void {
        const currentAddressList = this.addressesDataObject[this.currentAddress.toString()];
        this.hiddenDefaultAddressInput.value = this.currentAddress.toString();

        this.clearFormFields();
        this.fillFormFields(currentAddressList);
        this.clearFormField(this.customAddressTriggerInput);
    }

    protected fillDefaultAddress(): void {
        const hiddenDefaultAddressInputName = this.hiddenDefaultAddressInput.getAttribute('value');
        if (hiddenDefaultAddressInputName) {
            this.currentAddress = hiddenDefaultAddressInputName;
            this.fillFormWithNewAddress();
            this.toggleFormFieldsReadonly();
        }
        this.toggleReadonlyForCustomAddressTrigger();
    }

    /**
     * Clears the filter elements.
     */
    clearFormFields(): void {
        this.filterElements.forEach((element) => {
            this.clearFormField(<HTMLFormElement>element);
        });
    }

    /**
     * Invokes the clearFormField method of the imported FormClear component on the current element.
     * @param element HTMLFormElement.
     */
    clearFormField(element: HTMLFormElement): void {
        this.formClear.clearFormField(element);
    }

    /**
     * Fills the form element's value with an address value.
     * @param address A data object for filling the fields.
     */
    fillFormFields(address: object): void {
        for (let key in address) {
            const formElement = this.form.querySelector(`[data-key="${key}"]`);

            if (formElement !== null) {
                (<HTMLFormElement>formElement).value = address[key];
            }
        }
    }

    protected toggleReadonlyForCustomAddressTrigger() {
        if (this.customAddressTriggerInput.checked) {
            this.customAddressTriggerInput.disabled = true;
        } else {
            this.customAddressTriggerInput.disabled = false;
        }
    }

    protected initAddressesData(): void {
        const data = this.getAttribute('addresses');
        this.addressesDataObject = JSON.parse(data);
    }

    /**
     * Gets a querySelector name of the form element.
     */
    get formSelector(): string {
        return this.getAttribute('form-selector');
    }

    /**
     * Gets a querySelector name of the trigger elements.
     */
    get triggerSelector(): string {
        return this.getAttribute('trigger-selector');
    }

    /**
     * Gets a querySelector name of the address select elements.
     */
    get dataSelector(): string {
        return this.getAttribute('data-selector');
    }

    /**
     * Gets a querySelector name of the ignore elements.
     */
    get ignoreSelector(): string {
        return this.getAttribute('ignore-selector');
    }

    /**
     * Gets a querySelector name of a hidden customer id input.
     */
    get customerAddressIdSelector(): string {
        return this.getAttribute('customer-address-id-selector');
    }

    /**
     * Gets a querySelector name of a hidden default address input.
     */
    get defaultAddressSelector(): string {
        return this.getAttribute('default-address-selector');
    }

    /**
     * Gets a querySelector name of a custom address trigger input.
     */
    get customAddressTrigger(): string {
        return this.getAttribute('custom-address-trigger');
    }
}
