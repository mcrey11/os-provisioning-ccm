<?php

namespace App\Livewire\Ccc;

use Auth;
use Livewire\Component;
use Modules\BillingBase\Entities\SepaAccount;
use Modules\Ccc\Entities\Ccc;
use Modules\Ccc\Services\CccApiClient;

class PaymentMethodSelection extends Component
{
    public $selectedMethod = null;

    public $showSepaForm = false;

    public $showCreditCardForm = false;

    public $showAchForm = false;

    public $updateExistingPayment = true; // Toggle: true = view/update existing, false = add new

    public $editExistingPayment = false; // When true, allows editing (no CardToken passed)

    public function updatedUpdateExistingPayment()
    {
        // Reset edit mode when toggling between update/add new
        $this->editExistingPayment = false;
        // When toggle changes, re-initialize payment to update CardToken parameter
        if ($this->showCreditCardForm) {
            $this->initCreditCardPayment();
        } elseif ($this->showAchForm) {
            $this->initAchPayment();
        }
    }

    public function enableEditMode()
    {
        // Enable edit mode - this will reload form without CardToken (editable)
        $this->editExistingPayment = true;
        if ($this->showCreditCardForm) {
            $this->initCreditCardPayment();
        } elseif ($this->showAchForm) {
            $this->initAchPayment();
        }
    }

    // SEPA form fields
    public $iban = '';

    public $bic = '';

    public $holder = '';

    public $institute = '';

    public $mandateReference = '';

    // Rechnung (Invoice) fields
    public $createInvoice = false;

    public $postalInvoiceAgreed = false;

    // Credit Card/ACH PaymentGws data
    public $paymentGwsRef = '';

    public $paymentGwsToken = '';

    public $creditCardCallbackUrl = '';

    public $existingCardToken = null;

    public $contract = null;

    public $cccConfig = null;

    public $enabledMethods = [];

    public $currentPaymentMethod = null;

    public $postalInvoiceProduct = null;

    public $availablePaymentMethods = [];

    protected $rules = [
        'iban' => 'required|iban',
        'bic' => 'nullable|bic',
        'holder' => 'required|string|max:70|regex:/^([^;]*)$/',
        'institute' => 'nullable|string|max:255',
        'mandateReference' => 'required|string|max:255',
        'postalInvoiceAgreed' => 'accepted',
    ];

    protected $messages = [
        'iban.required' => 'IBAN is required.',
        'iban.iban' => 'Please enter a valid IBAN.',
        'bic.bic' => 'Please enter a valid BIC.',
        'holder.required' => 'Account holder name is required.',
        'holder.max' => 'Account holder name must not exceed 70 characters.',
        'holder.regex' => 'Account holder name must not contain semicolons.',
        'mandateReference.required' => 'Mandate reference is required.',
        'postalInvoiceAgreed.accepted' => 'You must agree to the postal invoice product terms.',
    ];

    public function mount()
    {
        $this->cccConfig = Ccc::first();
        $cccUser = Auth::guard('ccc')->user();

        if (! $cccUser || ! $cccUser->contract_id) {
            abort(404, 'Contract not found');
        }

        // Get contract via API
        $apiClient = new CccApiClient;
        $this->contract = $apiClient->get('Contract', $cccUser->contract_id);

        if (! $this->contract) {
            abort(404, 'Contract not found');
        }

        // Get postal invoice product info
        if ($this->cccConfig && $this->cccConfig->postal_invoice_product_id) {
            $this->postalInvoiceProduct = \Modules\BillingBase\Entities\Product::find($this->cccConfig->postal_invoice_product_id);
        }

        // Load current payment method settings
        $this->loadCurrentPaymentMethod();

        // Pre-select current payment method
        if ($this->currentPaymentMethod) {
            $this->selectedMethod = $this->currentPaymentMethod;
            $this->selectMethod($this->currentPaymentMethod);
        }

        // Generate mandate reference if SEPA
        if (! $this->mandateReference) {
            $this->mandateReference = 'CCC-'.$cccUser->contract_id.'-'.date('Ymd');
        }
    }

    public function getAvailablePaymentMethodsProperty()
    {
        $methods = [];

        if (! $this->cccConfig) {
            return [];
        }

        // SEPA
        if ($this->cccConfig->payment_method_sepa) {
            $methods['sepa'] = [
                'label' => __('view.payment.sepa.label'),
                'description' => __('view.payment.sepa.description'),
                'icon' => 'fas fa-university',
            ];
        }

        // Rechnung (Invoice)
        if ($this->cccConfig->payment_method_rechnung) {
            $priceInfo = '';
            if ($this->postalInvoiceProduct && $this->postalInvoiceProduct->price) {
                $formattedPriceWithCurrency = \Modules\BillingBase\Providers\BillingConf::formatPriceWithCurrency($this->postalInvoiceProduct->price);
                $billingCycle = $this->getBillingCycleLabel($this->postalInvoiceProduct->billing_cycle ?? 'monthly');
                $priceInfo = " ({$formattedPriceWithCurrency} / {$billingCycle})";
            }

            $methods['rechnung'] = [
                'label' => __('view.payment.rechnung.label'),
                'description' => __('view.payment.rechnung.description').$priceInfo,
                'icon' => 'fas fa-file-invoice',
            ];
        }

        // Credit Card
        if ($this->cccConfig->payment_method_credit_card && \Module::collections()->has('PaymentGws')) {
            $methods['credit_card'] = [
                'label' => __('view.payment.card.label'),
                'description' => __('view.payment.card.description'),
                'icon' => 'fas fa-credit-card',
            ];
        }

        // ACH
        if ($this->cccConfig->payment_method_acs && \Module::collections()->has('PaymentGws')) {
            $methods['ach'] = [
                'label' => __('view.payment.acs.label'),
                'description' => __('view.payment.acs.description'),
                'icon' => 'fas fa-money-check-alt',
            ];
        }

        return $methods;
    }

    public function getBillingCycleLabel($cycle)
    {
        return match ($cycle) {
            'monthly' => __('view.billing_cycle.monthly'),
            'quarterly' => __('view.billing_cycle.quarterly'),
            'yearly', 'annually' => __('view.billing_cycle.yearly'),
            'once', 'one-time', 'one_time' => __('view.billing_cycle.once'),
            default => __('view.billing_cycle.monthly'),
        };
    }

    public function formatProductPrice($product)
    {
        if (! $product || ! $product->price) {
            return null;
        }

        $formattedPriceWithCurrency = \Modules\BillingBase\Providers\BillingConf::formatPriceWithCurrency($product->price);
        $billingCycle = $this->getBillingCycleLabel($product->billing_cycle ?? 'monthly');

        return [
            'formatted' => $formattedPriceWithCurrency,
            'cycle' => $billingCycle,
            'full' => "{$formattedPriceWithCurrency} / {$billingCycle}",
        ];
    }

    // Determine current payment method from contract
    protected function loadCurrentPaymentMethod()
    {
        if (! $this->contract) {
            return;
        }

        $apiClient = new CccApiClient;
        $contractId = $this->contract['id'];

        // Check for existing SEPA mandate
        $sepaMandates = $apiClient->getAll('SepaMandate', ['contract_id' => $contractId]);
        if (! empty($sepaMandates)) {
            // Find the valid mandate (not disabled, valid now)
            foreach ($sepaMandates as $mandate) {
                if (empty($mandate['disable']) || ! $mandate['disable']) {
                    $validTo = $mandate['valid_to'] ?? null;
                    $validFrom = $mandate['valid_from'] ?? $mandate['created_at'] ?? null;

                    // Check if mandate is currently valid
                    $isValid = true;
                    if ($validFrom && strtotime($validFrom) > time()) {
                        $isValid = false;
                    }
                    if ($validTo && strtotime($validTo) < time()) {
                        $isValid = false;
                    }

                    if ($isValid) {
                        $this->currentPaymentMethod = 'sepa';
                        $this->iban = $mandate['iban'] ?? '';
                        $this->bic = $mandate['bic'] ?? '';
                        $this->holder = $mandate['holder'] ?? '';
                        $this->institute = $mandate['institute'] ?? '';
                        $this->mandateReference = $mandate['reference'] ?? $this->mandateReference;
                        break;
                    }
                }
            }
        }

        // Check for Credit Card/ACH payment (stored in contract.number2)
        if (! $this->currentPaymentMethod) {
            if (! empty($this->contract['number2'])) {
                // Contract has payment token stored - default to credit_card
                // Note: We can't distinguish between CC and ACH from number2 alone,
                // so we default to credit_card. User can switch if needed.
                $this->currentPaymentMethod = 'credit_card';
            }
        }

        // Check for postal invoice product in contract items
        if (! $this->currentPaymentMethod) {
            if (! $this->postalInvoiceProduct) {
                return;
            }

            $items = $apiClient->getAll('Item', ['contract_id' => $contractId]);

            foreach ($items as $item) {
                if (isset($item['product_id']) && $item['product_id'] == $this->postalInvoiceProduct->id) {
                    // Check if item is still valid
                    $validTo = $item['valid_to'] ?? null;
                    $validFrom = $item['valid_from'] ?? $item['created_at'] ?? null;

                    $isValid = true;
                    if ($validFrom && strtotime($validFrom) > time()) {
                        $isValid = false;
                    }
                    if ($validTo && strtotime($validTo) < time()) {
                        $isValid = false;
                    }

                    if (! $isValid) {
                        continue;
                    }

                    $this->currentPaymentMethod = 'rechnung';
                    $this->createInvoice = true;
                    $this->postalInvoiceAgreed = true;

                    break;
                }
            }
        }
    }

    public function selectMethod($method)
    {
        $this->selectedMethod = $method;
        $this->showSepaForm = ($method === 'sepa');
        $this->showCreditCardForm = false;
        $this->showAchForm = false;

        // Reset edit mode when switching payment methods
        $this->editExistingPayment = false;

        // Initialize Credit Card or ACH payment
        if ($method === 'credit_card') {
            $this->initCreditCardPayment();
        } elseif ($method === 'ach') {
            $this->initAchPayment();
        }

        // Load existing data if method is already selected
        if ($this->currentPaymentMethod === $method) {
            $this->loadCurrentPaymentMethod();
        }
    }

    public function updatedIban()
    {
        // Auto-fill BIC when IBAN changes
        if ($this->iban) {
            $this->bic = SepaAccount::get_bic($this->iban);

            // Auto-fill holder from contract if available
            if ($this->contract && ! $this->holder) {
                $name = trim(($this->contract['firstname'] ?? '').' '.($this->contract['lastname'] ?? ''));
                if ($name) {
                    $this->holder = $name;
                }
            }
        }

        $this->validate(['iban' => 'required|iban']);
    }

    public function saveSepaPayment()
    {
        $this->validate([
            'iban' => 'required|iban',
            'bic' => 'nullable|bic',
            'holder' => 'required|string|max:70|regex:/^([^;]*)$/',
            'institute' => 'nullable|string|max:255',
            'mandateReference' => 'required|string|max:255',
        ]);

        $cccUser = Auth::guard('ccc')->user();
        $apiClient = new CccApiClient;

        // Prepare SEPA data
        $iban = strtoupper(str_replace(' ', '', $this->iban));
        $bic = $this->bic ? strtoupper(str_replace(' ', '', $this->bic)) : null;

        // Update contract with SEPA data
        $contractData = [
            'sepa_iban' => $iban,
            'sepa_bic' => $bic,
            'sepa_holder' => $this->holder,
            'sepa_institute' => $this->institute,
            'create_invoice' => false, // SEPA overrides invoice
        ];

        $updated = $apiClient->update('Contract', $this->contract['id'], $contractData);

        if (! $updated) {
            session()->flash('error', __('messages.update_failed'));

            return;
        }

        // Deactivate postal invoice product item if it exists
        if ($this->postalInvoiceProduct) {
            $items = $apiClient->getAll('Item', ['contract_id' => $this->contract['id']]);
            if ($items) {
                foreach ($items as $item) {
                    if (isset($item['product_id']) && $item['product_id'] == $this->postalInvoiceProduct->id) {
                        // Set valid_to appropriately (must be after valid_from per validation rules)
                        $validFrom = $item['valid_from'] ?? date('Y-m-d');
                        $validFromTimestamp = strtotime($validFrom);
                        $todayTimestamp = strtotime('today');
                        $yesterdayTimestamp = strtotime('-1 day');

                        if ($validFromTimestamp > $todayTimestamp) {
                            // valid_from is in the future, set valid_to to one day before valid_from
                            $itemData = [
                                'valid_to' => date('Y-m-d', strtotime($validFrom.' -1 day')),
                                'valid_to_fixed' => true,
                            ];
                        } elseif ($validFromTimestamp == $todayTimestamp) {
                            // valid_from is today, set both valid_from and valid_to to yesterday to deactivate
                            // This ensures valid_to > valid_from (both are yesterday, but set valid_to to end of day logic)
                            // Actually, validation requires valid_to > valid_from, so set valid_from to 2 days ago and valid_to to yesterday
                            $itemData = [
                                'valid_from' => date('Y-m-d', strtotime('-2 days')),
                                'valid_from_fixed' => true,
                                'valid_to' => date('Y-m-d', strtotime('-1 day')),
                                'valid_to_fixed' => true,
                            ];
                        } else {
                            // valid_from is in the past, set valid_to to yesterday
                            // Ensure valid_to is after valid_from
                            $deactivateDate = date('Y-m-d', strtotime('-1 day'));
                            if (strtotime($deactivateDate) <= $validFromTimestamp) {
                                // If valid_from is yesterday or later, use today instead
                                $deactivateDate = date('Y-m-d');
                            }
                            $itemData = [
                                'valid_to' => $deactivateDate,
                                'valid_to_fixed' => true,
                            ];
                        }

                        $apiClient->update('Item', $item['id'], $itemData);
                        break;
                    }
                }
            }
        }

        // Check if a SEPA mandate with this IBAN already exists for this contract
        $existingMandates = $apiClient->getAll('SepaMandate', ['contract_id' => $this->contract['id']]);
        $existingMandate = null;

        if ($existingMandates) {
            foreach ($existingMandates as $mandate) {
                // Check if mandate has the same IBAN
                if (isset($mandate['iban']) && strtoupper(str_replace(' ', '', $mandate['iban'])) === $iban) {
                    $existingMandate = $mandate;
                    break;
                }
            }
        }

        // Create new mandate or reactivate/update existing one
        if ($existingMandate) {
            // Reactivate existing mandate by clearing valid_to if it was deactivated
            $validTo = $existingMandate['valid_to'] ?? null;
            if ($validTo && strtotime($validTo) < strtotime('today')) {
                // Mandate was deactivated, reactivate it
                $mandateData = [
                    'contract_id' => $this->contract['id'], // Ensure contract_id is included
                    'valid_to' => null,
                    'valid_from' => date('Y-m-d'),
                    'holder' => $this->holder,
                    'bic' => $bic,
                    'institute' => $this->institute,
                    'reference' => $this->mandateReference,
                    'signature_date' => date('Y-m-d'),
                ];
                $apiClient->update('SepaMandate', $existingMandate['id'], $mandateData);
            } else {
                // Update existing active mandate
                $mandateData = [
                    'contract_id' => $this->contract['id'], // Ensure contract_id is included
                    'holder' => $this->holder,
                    'bic' => $bic,
                    'institute' => $this->institute,
                    'reference' => $this->mandateReference,
                ];
                $apiClient->update('SepaMandate', $existingMandate['id'], $mandateData);
            }
        } else {
            // Create new SEPA mandate
            $mandateData = [
                'contract_id' => $this->contract['id'],
                'reference' => $this->mandateReference,
                'signature_date' => date('Y-m-d'),
                'holder' => $this->holder,
                'iban' => $iban,
                'bic' => $bic,
                'institute' => $this->institute,
                'valid_from' => date('Y-m-d'),
                'state' => 'FRST', // First mandate
            ];

            try {
                $apiClient->create('SepaMandate', $mandateData);
            } catch (\Exception $e) {
                \Log::warning('Failed to create SEPA mandate via API', ['error' => $e->getMessage()]);
            }
        }

        $this->currentPaymentMethod = 'sepa';
        $this->contract = $apiClient->get('Contract', $this->contract['id']); // Refresh

        session()->flash('success', __('view.payment.sepa.saved_successfully'));
        $this->dispatch('paymentMethodUpdated');
    }

    public function saveInvoicePayment()
    {
        if (! $this->postalInvoiceAgreed && $this->postalInvoiceProduct) {
            $this->addError('postalInvoiceAgreed', __('view.payment.rechnung.agreement_required'));

            return;
        }

        $apiClient = new CccApiClient;

        $contractData = [
            'create_invoice' => true,
            // Clear SEPA data when switching to invoice
            'sepa_iban' => null,
            'sepa_bic' => null,
            'sepa_holder' => null,
            'sepa_institute' => null,
        ];

        $updated = $apiClient->update('Contract', $this->contract['id'], $contractData);

        if (! $updated) {
            session()->flash('error', __('messages.update_failed'));

            return;
        }

        // Deactivate existing SEPA mandates by setting valid_to appropriately
        $sepaMandates = $apiClient->getAll('SepaMandate', ['contract_id' => $this->contract['id']]);
        if ($sepaMandates) {
            foreach ($sepaMandates as $mandate) {
                // Only deactivate if it's currently valid (no valid_to or valid_to in the future)
                $validTo = $mandate['valid_to'] ?? null;
                $isCurrentlyValid = ! $validTo || strtotime($validTo) >= strtotime('today');

                if ($isCurrentlyValid && (empty($mandate['disable']) || ! $mandate['disable'])) {
                    $validFrom = $mandate['valid_from'] ?? $mandate['created_at'] ?? date('Y-m-d');
                    $deactivateDate = date('Y-m-d');

                    // If valid_from is in the future or today, use today; otherwise use yesterday if valid_from is in the past
                    if (strtotime($validFrom) > strtotime('today')) {
                        // valid_from is in the future, set valid_to to one day before valid_from
                        $deactivateDate = date('Y-m-d', strtotime($validFrom.' -1 day'));
                    } elseif (strtotime($validFrom) == strtotime('today')) {
                        // valid_from is today, set valid_to to today (same day is allowed)
                        $deactivateDate = date('Y-m-d');
                    } else {
                        // valid_from is in the past, set valid_to to yesterday
                        $deactivateDate = date('Y-m-d', strtotime('-1 day'));
                    }

                    $mandateData = [
                        'contract_id' => $this->contract['id'], // Ensure contract_id is included
                        'valid_to' => $deactivateDate,
                    ];
                    $apiClient->update('SepaMandate', $mandate['id'], $mandateData);
                }
            }
        }

        // Check if postal invoice product item already exists
        $items = $apiClient->getAll('Item', ['contract_id' => $this->contract['id']]);
        $existingItem = null;
        if ($items && $this->postalInvoiceProduct) {
            foreach ($items as $item) {
                if (isset($item['product_id']) && $item['product_id'] == $this->postalInvoiceProduct->id) {
                    $existingItem = $item;
                    break;
                }
            }
        }

        // Add or reactivate postal invoice product item
        if ($this->postalInvoiceProduct && $this->postalInvoiceAgreed) {
            if ($existingItem) {
                // Reactivate existing item by clearing valid_to if it was deactivated
                $validTo = $existingItem['valid_to'] ?? null;
                if ($validTo && strtotime($validTo) < strtotime('today')) {
                    // Item was deactivated, reactivate it
                    $itemData = [
                        'valid_to' => null,
                        'valid_to_fixed' => false,
                        'valid_from' => date('Y-m-d'),
                        'valid_from_fixed' => true,
                    ];
                    $apiClient->update('Item', $existingItem['id'], $itemData);
                }
            } else {
                // Create new item for postal invoice product
                $itemData = [
                    'contract_id' => $this->contract['id'],
                    'product_id' => $this->postalInvoiceProduct->id,
                    'count' => 1,
                    'valid_from' => date('Y-m-d'),
                    'valid_from_fixed' => true,
                    'valid_to' => null,
                    'valid_to_fixed' => false,
                ];

                $createdItem = $apiClient->create('Item', $itemData);
                if (! $createdItem) {
                    session()->flash('error', __('messages.update_failed'));

                    return;
                }
            }
        } else {
            // Deactivate postal invoice product item by setting valid_to appropriately
            if ($existingItem) {
                $validTo = $existingItem['valid_to'] ?? null;
                $isCurrentlyValid = ! $validTo || strtotime($validTo) >= strtotime('today');

                if ($isCurrentlyValid) {
                    $validFrom = $existingItem['valid_from'] ?? date('Y-m-d');
                    $validFromTimestamp = strtotime($validFrom);
                    $todayTimestamp = strtotime('today');

                    if ($validFromTimestamp > $todayTimestamp) {
                        // valid_from is in the future, set valid_to to one day before valid_from
                        $itemData = [
                            'valid_to' => date('Y-m-d', strtotime($validFrom.' -1 day')),
                            'valid_to_fixed' => true,
                        ];
                    } elseif ($validFromTimestamp == $todayTimestamp) {
                        // valid_from is today, set both valid_from and valid_to to deactivate
                        // Set valid_from to 2 days ago and valid_to to yesterday to ensure valid_to > valid_from
                        $itemData = [
                            'valid_from' => date('Y-m-d', strtotime('-2 days')),
                            'valid_from_fixed' => true,
                            'valid_to' => date('Y-m-d', strtotime('-1 day')),
                            'valid_to_fixed' => true,
                        ];
                    } else {
                        // valid_from is in the past, set valid_to to yesterday
                        $deactivateDate = date('Y-m-d', strtotime('-1 day'));
                        if (strtotime($deactivateDate) <= $validFromTimestamp) {
                            // If valid_from is yesterday or later, use today instead
                            $deactivateDate = date('Y-m-d');
                        }
                        $itemData = [
                            'valid_to' => $deactivateDate,
                            'valid_to_fixed' => true,
                        ];
                    }

                    $apiClient->update('Item', $existingItem['id'], $itemData);
                }
            }
        }

        $this->currentPaymentMethod = 'rechnung';
        $this->createInvoice = true;
        $this->contract = $apiClient->get('Contract', $this->contract['id']); // Refresh

        session()->flash('success', __('view.payment.rechnung.saved_successfully'));
        $this->dispatch('paymentMethodUpdated');
    }

    public function initCreditCardPayment()
    {
        if (! \Module::collections()->has('PaymentGws')) {
            session()->flash('error', 'Payment gateway not available');

            return;
        }

        $cccUser = Auth::guard('ccc')->user();
        $ref = $cccUser->contract_id.'-'.random_int(0, 99999999);

        $callbackUrl = route('setPaymentMethodResponse');
        if (config('app.adminPort')) {
            $callbackUrl = str_replace('/admin/', ':'.config('app.adminPort').'/admin/', $callbackUrl);
        }

        $conf = config('paymentgws.gw');
        $key = $conf['securityKey'];
        $token = sha1($ref.$key);

        // Save ref and token in DB (for PaymentGws callback)
        $cccUser->payment_update_ref = $ref;
        $cccUser->payment_update_token = $token;
        $cccUser->saveQuietly();

        // Save ref and token for form
        // CardToken will be included/excluded in render() based on updateExistingPayment toggle
        $this->paymentGwsRef = $ref;
        $this->paymentGwsToken = $token;
        $this->creditCardCallbackUrl = $callbackUrl;

        $this->showCreditCardForm = true;
    }

    public function initAchPayment()
    {
        // Similar to credit card but with PaymentType = ACH
        $this->initCreditCardPayment();
        $this->showAchForm = true;
        $this->showCreditCardForm = false;
    }

    public function render()
    {
        $this->availablePaymentMethods = $this->getAvailablePaymentMethodsProperty();

        $paymentGwsData = null;
        if ($this->showCreditCardForm || $this->showAchForm) {
            $conf = config('paymentgws.gw');

            // Check if there's an existing payment method
            $hasExistingPayment = $this->contract && ! empty($this->contract['number2']);

            // Pass CardToken when viewing existing AND not in edit mode
            // This shows existing payment info (read-only view)
            // When edit mode is enabled, don't pass CardToken (makes form editable)
            $existingCardToken = null;
            if ($this->updateExistingPayment && $hasExistingPayment && ! $this->editExistingPayment) {
                $existingCardToken = $this->contract['number2'];
            }

            $paymentGwsData = [
                'terminalId' => $conf['terminalId'] ?? '',
                'merchantId' => $conf['merchantId'] ?? '',
                'customerId' => $conf['customerId'] ?? '',
                'ref' => $this->paymentGwsRef,
                'token' => $this->paymentGwsToken,
                'callbackUrl' => $this->creditCardCallbackUrl,
                'existingCardToken' => $existingCardToken,
                'hasExistingPayment' => $hasExistingPayment,
            ];
        }

        return view('livewire.ccc.payment-method-selection', [
            'paymentGwsData' => $paymentGwsData,
        ]);
    }
}
