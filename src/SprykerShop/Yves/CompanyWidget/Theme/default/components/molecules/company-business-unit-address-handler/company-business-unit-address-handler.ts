import Component from 'ShopUi/models/component';
import FormClear from 'ShopUi/components/molecules/form-clear/form-clear';

export default class CompanyBusinessUnitAddressHandler extends Component {
    triggers: HTMLElement[];
    form: HTMLElement;
    targets: HTMLElement[];
    ignoreElements: HTMLElement[];
    filterElements: HTMLElement[];
    formClear: FormClear;
    addressesDataObject: any;
    addressesSelects: HTMLSelectElement[];
    currentAddress: String;
    hiddenCustomerIdInput: HTMLInputElement;
    hiddenDefaultAddressInput: HTMLInputElement;
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
        this.formClear.addEventListener('form-fields-clear-after', () => this.toggleFormFieldsReadonly(false));
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
        }
    }

    toggleFormFieldsReadonly(isEnabled: boolean = true): void {
        this.filterElements.forEach((formElement: HTMLFormElement) => {
            const isSelect = this.formClear.getTagName(formElement) == 'SELECT';

            if(isSelect) {
                const options = Array.from(formElement.querySelectorAll('option'));

                options.forEach((element) => {
                    if(!element.selected) {
                        element.disabled = isEnabled;
                    }
                });

                return;
            }

            formElement.readOnly = isEnabled;
        });
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
    }

    clearFormFields(): void {
        this.filterElements.forEach((element) => {
            this.clearFormField(<HTMLFormElement>element);
        });
    }

    clearFormField(element: HTMLFormElement): void {
        if (element.type == "checkbox") {
            element.checked = false;
            return;
        }

        if (this.formClear.getTagName(element) == "SELECT") {
            element.selectedIndex = 0;
        }

        element.value = '';
    }

    fillFormFields(address: object): void {
        for(let key in address) {
            const formElement = this.form.querySelector(`[data-key="${key}"]`);

            if(formElement !== null) {
                (<HTMLFormElement>formElement).value = address[key];
            }
        }
    }

    protected initAddressesData(): void {
        const data = this.getAttribute('addresses');
        this.addressesDataObject = JSON.parse(data);
    }

    get formSelector(): string {
        return this.getAttribute('form-selector');
    }

    get triggerSelector(): string {
        return this.getAttribute('trigger-selector');
    }

    get dataSelector(): string {
        return this.getAttribute('data-selector');
    }

    get ignoreSelector(): string {
        return this.getAttribute('ignore-selector');
    }

    get customerAddressIdSelector(): string {
        return this.getAttribute('customer-address-id-selector');
    }

    get defaultAddressSelector(): string {
        return this.getAttribute('default-address-selector');
    }

    get customAddressTrigger(): string {
        return this.getAttribute('custom-address-trigger');
    }
}
