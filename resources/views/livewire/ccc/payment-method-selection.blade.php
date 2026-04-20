<div>
    <div class="panel-heading flex flex-row justify-content-between">
        <h3>{{ __('view.payment.title') }}</h3>
    </div>

    <div class="panel-body fader flex flex-column h-full overflow-y-auto">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (empty($availablePaymentMethods))
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle me-2"></i>
                {{ __('view.payment.no_methods_available') }}
            </div>
        @else
            <!-- Payment Method Selection Boxes -->
            <div class="row">
                @foreach($availablePaymentMethods as $method => $config)
                    <div class="col-md-6 mb-3 d-flex">
                        <div class="card payment-method-card w-100 {{ $selectedMethod === $method ? 'border-primary' : 'border-light' }}
                                    {{ $selectedMethod === $method ? 'bg-light' : '' }}"
                             style="cursor: pointer; transition: all 0.3s ease;"
                             wire:click="selectMethod('{{ $method }}')"
                             wire:key="payment-method-{{ $method }}">
                            <div class="card-body text-center py-3 d-flex flex-column" style="min-height: 140px;">
                                <div class="mb-2">
                                    <i class="{{ $config['icon'] }} fa-2x"></i>
                                </div>
                                <h6 class="card-title mb-1">
                                    {{ $config['label'] }}
                                </h6>
                                <p class="card-text small mb-2 flex-grow-1">
                                    {{ $config['description'] }}
                                </p>
                                @if($selectedMethod === $method)
                                    <div>
                                        <i class="fa fa-check-circle" style="color: var(--primary-color, #007bff);"></i>
                                        <small class="ms-1" style="color: var(--primary-color, #007bff);">{{ __('view.payment.method_selected') }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <style>
            .payment-method-card:hover {
                border-color: var(--primary-color, #007bff) !important;
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            }

            .payment-method-card.border-primary {
                box-shadow: 0 0.125rem 0.25rem rgba(0, 123, 255, 0.25);
            }
            </style>

            <!-- SEPA Payment Form -->
            @if ($showSepaForm)
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-university me-2"></i>
                            {{ __('view.payment.sepa.title') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ __('view.payment.sepa.info') }}
                        </div>

                        <form wire:submit.prevent="saveSepaPayment">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="iban" class="form-label">
                                        <i class="fa fa-university me-2"></i>
                                        {{ __('view.payment.sepa.iban') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('iban') is-invalid @enderror"
                                           id="iban"
                                           wire:model.live="iban"
                                           placeholder="{{ __('view.payment.sepa.iban_placeholder') }}"
                                           maxlength="34">
                                    @error('iban')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="bic" class="form-label">
                                        <i class="fa fa-building me-2"></i>
                                        {{ __('view.payment.sepa.bic') }}
                                        <small class="text-muted">({{ __('view.payment.sepa.auto_filled') }})</small>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('bic') is-invalid @enderror"
                                           id="bic"
                                           wire:model.live="bic"
                                           placeholder="{{ __('view.payment.sepa.bic_placeholder') }}"
                                           maxlength="11">
                                    @error('bic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="holder" class="form-label">
                                        <i class="fa fa-user me-2"></i>
                                        {{ __('view.payment.sepa.holder') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('holder') is-invalid @enderror"
                                           id="holder"
                                           wire:model.live="holder"
                                           placeholder="{{ __('view.payment.sepa.holder_placeholder') }}"
                                           maxlength="70">
                                    @error('holder')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="institute" class="form-label">
                                        <i class="fa fa-building me-2"></i>
                                        {{ __('view.payment.sepa.institute') }}
                                    </label>
                                    <input type="text"
                                           class="form-control @error('institute') is-invalid @enderror"
                                           id="institute"
                                           wire:model.live="institute"
                                           placeholder="{{ __('view.payment.sepa.institute_placeholder') }}"
                                           maxlength="255">
                                    @error('institute')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="mandateReference" class="form-label">
                                        <i class="fa fa-file-contract me-2"></i>
                                        {{ __('view.payment.sepa.mandate_reference') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('mandateReference') is-invalid @enderror"
                                           id="mandateReference"
                                           wire:model.live="mandateReference"
                                           placeholder="{{ __('view.payment.sepa.mandate_reference_placeholder') }}"
                                           maxlength="255">
                                    @error('mandateReference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ __('view.payment.sepa.mandate_reference_help') }}</div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                {{ __('view.payment.sepa.mandate_warning') }}
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-2"></i>
                                    {{ __('messages.Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Rechnung (Invoice) Payment Form -->
            @if ($selectedMethod === 'rechnung')
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-file-invoice me-2"></i>
                            {{ __('view.payment.rechnung.title') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light mb-4">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ __('view.payment.rechnung.info') }}
                        </div>

                        @if ($postalInvoiceProduct)
                            <div class="mb-4">
                                <h6 class="mb-3">
                                    <i class="fa fa-file-alt me-2"></i>
                                    {{ __('view.payment.rechnung.postal_invoice_product') }}
                                </h6>
                                @php
                                    $priceInfo = $this->formatProductPrice($postalInvoiceProduct);
                                @endphp
                                <div class="bg-light p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <p class="mb-1 fw-bold">{{ $postalInvoiceProduct->name }}</p>
                                            @if ($postalInvoiceProduct->description)
                                                <p class="mb-0 text-muted small">{{ $postalInvoiceProduct->description }}</p>
                                            @endif
                                        </div>
                                        @if ($priceInfo)
                                            <div class="text-end ms-3">
                                                <div class="fw-bold">
                                                    {{ $priceInfo['formatted'] }}
                                                </div>
                                                <small class="text-muted">
                                                    / {{ $priceInfo['cycle'] }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check d-flex align-items-start">
                                        <input
                                            class="form-check-input mt-1 @error('postalInvoiceAgreed') is-invalid @enderror"
                                            type="checkbox"
                                            id="postalInvoiceAgreed"
                                            wire:model.live="postalInvoiceAgreed"
                                            style="flex-shrink: 0;">
                                        <label class="form-check-label ms-4" for="postalInvoiceAgreed">
                                            {{ __('view.payment.rechnung.agreement_text', ['product' => $postalInvoiceProduct->name]) }}
                                            <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                    @error('postalInvoiceAgreed')
                                        <div class="invalid-feedback d-block ms-4">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-primary" wire:click="saveInvoicePayment">
                                <i class="fa fa-save me-2"></i>
                                {{ __('view.payment.rechnung.save') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Credit Card Payment Form -->
            @if ($showCreditCardForm && $paymentGwsData)
                <div class="card mb-4"
                     x-data="{ submitted: false }"
                     x-init="
                         $watch('$wire.showCreditCardForm', value => {
                             if (value && !submitted) {
                                 setTimeout(() => {
                                     const form = document.getElementById('ippayAuth');
                                     if (form) {
                                         form.submit();
                                         submitted = true;
                                     }
                                 }, 200);
                             }
                         });
                        // Submit on initial load or when updateExistingPayment or editExistingPayment changes
                        $watch('$wire.updateExistingPayment', () => {
                            submitted = false;
                            setTimeout(() => {
                                const form = document.getElementById('ippayAuth');
                                if (form) {
                                    form.submit();
                                    submitted = true;
                                }
                            }, 200);
                        });
                        $watch('$wire.editExistingPayment', () => {
                            submitted = false;
                            setTimeout(() => {
                                const form = document.getElementById('ippayAuth');
                                if (form) {
                                    form.submit();
                                    submitted = true;
                                }
                            }, 200);
                        });
                         // Submit on initial load
                         setTimeout(() => {
                             const form = document.getElementById('ippayAuth');
                             if (form && !submitted) {
                                 form.submit();
                                 submitted = true;
                             }
                         }, 200);
                     ">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-credit-card me-2"></i>
                            {{ __('view.payment.credit_card.title') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($paymentGwsData['hasExistingPayment'] ?? false)
                            @if($editExistingPayment)
                                <div class="alert alert-warning mb-3">
                                    <i class="fa fa-edit me-2"></i>
                                    {{ __('view.payment.editing_payment_info') }}
                                </div>
                            @elseif($updateExistingPayment && !empty($paymentGwsData['existingCardToken']))
                                <div class="alert alert-info mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-info-circle me-2"></i>
                                        {{ __('view.payment.viewing_existing_info') }}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" wire:click="enableEditMode">
                                        <i class="fa fa-edit me-1"></i>
                                        {{ __('view.payment.edit_payment') }}
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-info mb-3">
                                    <i class="fa fa-info-circle me-2"></i>
                                    {{ __('view.payment.add_new_info') }}
                                </div>
                            @endif
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="updateExistingCard"
                                           wire:model.live="updateExistingPayment"
                                           wire:change="$set('editExistingPayment', false)">
                                    <label class="form-check-label ms-5" for="updateExistingCard">
                                        @if($updateExistingPayment)
                                            {{ __('view.payment.update_existing') }}
                                        @else
                                            {{ __('view.payment.add_new') }}
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endif
                        <div class="alert alert-light">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ __('view.payment.credit_card.info') }}
                        </div>

                        <form id='ippayAuth' target="ippay" action="{{ config('paymentgws.gw.url', 'https://hpp-test.ippay.com') }}" method="post">
                            <input type="hidden" name="TransactionType" value="TOKENIZE" />
                            <input type="hidden" name="TerminalID" value="{{ $paymentGwsData['terminalId'] }}" />
                            <input type="hidden" name="MID" value="{{ $paymentGwsData['merchantId'] }}" />
                            <input type="hidden" name="CustomerId" value="{{ $paymentGwsData['customerId'] }}" />
                            <input type="hidden" name="ReferenceId" value="{{ $paymentGwsData['ref'] }}" />
                            <input type="hidden" name="Token" value="{{ $paymentGwsData['token'] }}" />
                            <input type="hidden" name="Amount" value="0.00" />
                            <input type="hidden" name="PaymentType" value="CC">
                            <input type="hidden" name="SaveForFuture" value="true">
                            <input type="hidden" name="CallbackURL" value="{{ $paymentGwsData['callbackUrl'] }}" />
                            @if(!empty($paymentGwsData['existingCardToken']) && $updateExistingPayment)
                                <input type="hidden" name="CardToken" value="{{ $paymentGwsData['existingCardToken'] }}" />
                            @endif
                        </form>

                        <iframe name='ippay' class="w-full" style="height: 600px; border: 1px solid #ddd;" src=""></iframe>
                    </div>
                </div>
            @endif

            <!-- ACH Payment Form -->
            @if ($showAchForm && $paymentGwsData)
                <div class="card mb-4"
                     x-data="{ submitted: false }"
                     x-init="
                         $watch('$wire.showAchForm', value => {
                             if (value && !submitted) {
                                 setTimeout(() => {
                                     const form = document.getElementById('ippayAuthAch');
                                     if (form) {
                                         form.submit();
                                         submitted = true;
                                     }
                                 }, 200);
                             }
                        });
                        // Submit on initial load or when updateExistingPayment or editExistingPayment changes
                        $watch('$wire.updateExistingPayment', () => {
                            submitted = false;
                            setTimeout(() => {
                                const form = document.getElementById('ippayAuthAch');
                                if (form) {
                                    form.submit();
                                    submitted = true;
                                }
                            }, 200);
                        });
                        $watch('$wire.editExistingPayment', () => {
                            submitted = false;
                            setTimeout(() => {
                                const form = document.getElementById('ippayAuthAch');
                                if (form) {
                                    form.submit();
                                    submitted = true;
                                }
                            }, 200);
                        });
                        // Submit on initial load
                         setTimeout(() => {
                             const form = document.getElementById('ippayAuthAch');
                             if (form && !submitted) {
                                 form.submit();
                                 submitted = true;
                             }
                         }, 200);
                     ">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-building me-2"></i>
                            {{ __('view.payment.acs.title') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($paymentGwsData['hasExistingPayment'] ?? false)
                            @if($editExistingPayment)
                                <div class="alert alert-warning mb-3">
                                    <i class="fa fa-edit me-2"></i>
                                    {{ __('view.payment.editing_payment_info') }}
                                </div>
                            @elseif($updateExistingPayment && !empty($paymentGwsData['existingCardToken']))
                                <div class="alert alert-info mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fa fa-info-circle me-2"></i>
                                        {{ __('view.payment.viewing_existing_info') }}
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary" wire:click="enableEditMode">
                                        <i class="fa fa-edit me-1"></i>
                                        {{ __('view.payment.edit_payment') }}
                                    </button>
                                </div>
                            @else
                                <div class="alert alert-info mb-3">
                                    <i class="fa fa-info-circle me-2"></i>
                                    {{ __('view.payment.add_new_info') }}
                                </div>
                            @endif
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="updateExistingAch"
                                           wire:model.live="updateExistingPayment"
                                           wire:change="$set('editExistingPayment', false)">
                                    <label class="form-check-label ms-5" for="updateExistingAch">
                                        @if($updateExistingPayment)
                                            {{ __('view.payment.update_existing') }}
                                        @else
                                            {{ __('view.payment.add_new') }}
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endif
                        <div class="alert alert-light">
                            <i class="fa fa-info-circle me-2"></i>
                            {{ __('view.payment.acs.info') }}
                        </div>

                        <form id='ippayAuthAch' target="ippayAch" action="{{ config('paymentgws.gw.url', 'https://hpp-test.ippay.com') }}" method="post">
                            <input type="hidden" name="TransactionType" value="TOKENIZE" />
                            <input type="hidden" name="TerminalID" value="{{ $paymentGwsData['terminalId'] }}" />
                            <input type="hidden" name="MID" value="{{ $paymentGwsData['merchantId'] }}" />
                            <input type="hidden" name="CustomerId" value="{{ $paymentGwsData['customerId'] }}" />
                            <input type="hidden" name="ReferenceId" value="{{ $paymentGwsData['ref'] }}" />
                            <input type="hidden" name="Token" value="{{ $paymentGwsData['token'] }}" />
                            <input type="hidden" name="Amount" value="0.00" />
                            <input type="hidden" name="PaymentType" value="ACH">
                            <input type="hidden" name="SaveForFuture" value="true">
                            <input type="hidden" name="CallbackURL" value="{{ $paymentGwsData['callbackUrl'] }}" />
                            @if(!empty($paymentGwsData['existingCardToken']))
                                <input type="hidden" name="CardToken" value="{{ $paymentGwsData['existingCardToken'] }}" />
                            @endif
                        </form>

                        <iframe name='ippayAch' class="w-full" style="height: 600px; border: 1px solid #ddd;" src=""></iframe>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
