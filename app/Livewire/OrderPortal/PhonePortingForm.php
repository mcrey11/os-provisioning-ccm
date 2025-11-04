<?php

namespace App\Livewire\OrderPortal;

use Livewire\Component;
use Modules\OrderPortal\Contracts\OrderAdaptorInterface;
use Modules\OrderPortal\Entities\WebOrder;

class PhonePortingForm extends Component
{
    public $webOrderModelId = null;

    public $webOrderModelType = null;

    protected $webOrderModel = null;

    protected $webOrderAdaptor = null;

    // Form fields
    public $vorname = '';

    public $nachname = '';

    public $email = '';

    public $telefonnummer = '';

    public $plz = '';

    public $ort = '';

    public $strasse_nr = '';

    public $telefonnummer_portierung = '';

    protected $rules = [
        'vorname' => 'required|string|max:255',
        'nachname' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telefonnummer' => 'required|string|max:255',
        'plz' => 'required|string|max:10',
        'ort' => 'required|string|max:255',
        'strasse_nr' => 'required|string|max:255',
        'telefonnummer_portierung' => 'required|string|max:255',
    ];

    protected $messages = [
        'vorname.required' => 'Vorname ist erforderlich.',
        'nachname.required' => 'Nachname ist erforderlich.',
        'email.required' => 'E-Mail ist erforderlich.',
        'email.email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
        'telefonnummer.required' => 'Telefonnummer ist erforderlich.',
        'plz.required' => 'PLZ ist erforderlich.',
        'ort.required' => 'Ort ist erforderlich.',
        'strasse_nr.required' => 'Strasse & Nr. ist erforderlich.',
        'telefonnummer_portierung.required' => 'Telefonnummer für Portierung ist erforderlich.',
    ];

    public function mount($webOrder = null)
    {
        // Support both WebOrder and Contract (via ContractAdaptor pattern)
        if ($webOrder instanceof \Modules\ProvBase\Entities\Contract) {
            $this->webOrderModelId = $webOrder->id;
            $this->webOrderModelType = 'Contract';
            $this->webOrderModel = $webOrder;
            $this->webOrderAdaptor = new \Modules\OrderPortal\Adaptors\ContractAdaptor($webOrder);
        } elseif ($webOrder instanceof WebOrder) {
            $this->webOrderModelId = $webOrder->id;
            $this->webOrderModelType = 'WebOrder';
            $this->webOrderModel = $webOrder->fresh()->load(['contactPoint.address', 'serviceAddress', 'billingAddress', 'apartment.realty']);
            $this->webOrderAdaptor = new \Modules\OrderPortal\Adaptors\WebOrderAdaptor($this->webOrderModel);
        } elseif ($webOrder instanceof \Modules\OrderPortal\Adaptors\ContractAdaptor || $webOrder instanceof \Modules\OrderPortal\Adaptors\WebOrderAdaptor) {
            $this->webOrderAdaptor = $webOrder;
            $this->webOrderModel = $webOrder->getModel();
            if ($this->webOrderModel instanceof WebOrder) {
                $this->webOrderModelType = 'WebOrder';
                $this->webOrderModelId = $this->webOrderModel->id;
            } elseif ($this->webOrderModel instanceof \Modules\ProvBase\Entities\Contract) {
                $this->webOrderModelType = 'Contract';
                $this->webOrderModelId = $this->webOrderModel->id;
            }
        } else {
            throw new \InvalidArgumentException('Invalid webOrder type. Must be WebOrder, Contract, or Adaptor.');
        }

        // Pre-fill form fields from existing data
        $this->prefillForm();
    }

    protected function prefillForm(): void
    {
        $adaptor = $this->getWebOrderAdaptor();
        if (! $adaptor) {
            return;
        }

        $contactPoint = $adaptor->getContactPoint();

        // Pre-fill from contact point
        if ($contactPoint) {
            $this->vorname = $contactPoint->firstname ?? '';
            $this->nachname = $contactPoint->lastname ?? '';
            $this->email = $contactPoint->email ?? '';
            $this->telefonnummer = $contactPoint->phone ?? '';
        } elseif ($this->webOrderModel instanceof \Modules\ProvBase\Entities\Contract) {
            // For contracts, fallback to contract's direct fields if contactPoint is not available
            $contract = $this->webOrderModel;
            $this->vorname = $contract->firstname ?? '';
            $this->nachname = $contract->lastname ?? '';
            $this->email = $contract->email ?? '';
            $this->telefonnummer = $contract->phone ?? '';
        }

        // Try to get address from multiple sources
        $address = null;

        // First try serviceAddress (direct address)
        $address = $adaptor->getServiceAddress();

        // Then try address through contactPoint
        if (! $address && $contactPoint && $contactPoint->address) {
            $address = $contactPoint->address;
        }

        // For WebOrder, also try via the model's address property
        if (! $address && $this->webOrderModel instanceof WebOrder) {
            $address = $this->webOrderModel->address ?? null;
        }

        // Finally try apartment realty address for WebOrder
        if (! $address && $this->webOrderModel instanceof WebOrder && $this->webOrderModel->apartment && $this->webOrderModel->apartment->realty) {
            $realty = $this->webOrderModel->apartment->realty;
            // Realty has direct address fields, not a relationship
            if ($realty->street || $realty->city || $realty->zip) {
                // Create a temporary object with address-like structure for consistency
                $address = (object) [
                    'street' => $realty->street ?? null,
                    'house_number' => $realty->house_nr ?? null,
                    'city' => $realty->city ?? null,
                    'zip' => $realty->zip ?? null,
                ];
            }
        }

        // Pre-fill from address if found
        if ($address) {
            $this->plz = $address->zip ?? '';
            $this->ort = $address->city ?? '';
            $streetParts = [];
            if ($address->street) {
                $streetParts[] = $address->street;
            }
            if ($address->house_number) {
                $streetParts[] = $address->house_number;
            }
            $this->strasse_nr = implode(' ', $streetParts);
        } elseif ($this->webOrderModel instanceof \Modules\ProvBase\Entities\Contract) {
            // Contracts have direct address fields
            $contract = $this->webOrderModel;
            $this->plz = $contract->zip ?? '';
            $this->ort = $contract->city ?? '';
            $streetParts = [];
            if ($contract->street) {
                $streetParts[] = $contract->street;
            }
            if ($contract->house_number) {
                $streetParts[] = $contract->house_number;
            }
            $this->strasse_nr = implode(' ', $streetParts);
        }

        // Load existing porting data from item custom_data if available (this will override pre-filled values)
        $transferPhoneItem = $this->getTransferPhoneItem();
        if ($transferPhoneItem && $transferPhoneItem->custom_data && isset($transferPhoneItem->custom_data['porting_data'])) {
            $portingData = $transferPhoneItem->custom_data['porting_data'];
            if (is_array($portingData)) {
                $this->vorname = $portingData['vorname'] ?? $this->vorname;
                $this->nachname = $portingData['nachname'] ?? $this->nachname;
                $this->email = $portingData['email'] ?? $this->email;
                $this->telefonnummer = $portingData['telefonnummer'] ?? $this->telefonnummer;
                $this->plz = $portingData['plz'] ?? $this->plz;
                $this->ort = $portingData['ort'] ?? $this->ort;
                $this->strasse_nr = $portingData['strasse_nr'] ?? $this->strasse_nr;
                $this->telefonnummer_portierung = $portingData['telefonnummer_portierung'] ?? '';
            }
        }
    }

    protected function getWebOrderAdaptor(): ?OrderAdaptorInterface
    {
        if ($this->webOrderAdaptor) {
            return $this->webOrderAdaptor;
        }

        // Recreate adaptor if needed
        if ($this->webOrderModelId && $this->webOrderModelType) {
            if ($this->webOrderModelType === 'WebOrder') {
                $model = WebOrder::find($this->webOrderModelId);
                if ($model) {
                    $this->webOrderModel = $model->load(['contactPoint.address', 'serviceAddress', 'billingAddress', 'apartment.realty']);
                    $this->webOrderAdaptor = new \Modules\OrderPortal\Adaptors\WebOrderAdaptor($this->webOrderModel);
                }
            } elseif ($this->webOrderModelType === 'Contract') {
                $model = \Modules\ProvBase\Entities\Contract::find($this->webOrderModelId);
                if ($model) {
                    $this->webOrderModel = $model;
                    $this->webOrderAdaptor = new \Modules\OrderPortal\Adaptors\ContractAdaptor($model);
                }
            }
        }

        return $this->webOrderAdaptor;
    }

    protected function getModel()
    {
        if ($this->webOrderModel) {
            return $this->webOrderModel;
        }

        $adaptor = $this->getWebOrderAdaptor();
        if ($adaptor instanceof \Modules\OrderPortal\Adaptors\WebOrderAdaptor) {
            return $adaptor->getModel();
        } elseif ($adaptor instanceof \Modules\OrderPortal\Adaptors\ContractAdaptor) {
            return $adaptor->getModel();
        }

        return null;
    }

    protected function getTransferPhoneProductId(): ?int
    {
        $adaptor = $this->getWebOrderAdaptor();
        if (! $adaptor) {
            return null;
        }

        // For contracts (CCC flow), check CCC config; for WebOrder, check OrderPortal config
        $isContract = $adaptor instanceof \Modules\OrderPortal\Adaptors\ContractAdaptor;

        if ($isContract) {
            $config = cache()->remember('CccConfig', 3600, function () {
                return \Modules\Ccc\Entities\Ccc::first();
            });
        } else {
            $config = cache()->remember('OrderPortalConfig', 3600, function () {
                return \Modules\OrderPortal\Entities\OrderPortal::first();
            });
        }

        return $config ? (int) $config->transfer_phone_product_id : null;
    }

    protected function getTransferPhoneItem(): ?\Modules\OrderPortal\Entities\WebOrderItem
    {
        $adaptor = $this->getWebOrderAdaptor();
        if (! $adaptor) {
            return null;
        }

        $transferPhoneProductId = $this->getTransferPhoneProductId();
        if (! $transferPhoneProductId) {
            return null;
        }

        // Get items and find the transfer phone product item
        if ($adaptor instanceof \Modules\OrderPortal\Adaptors\ContractAdaptor) {
            $items = \Modules\OrderPortal\Entities\WebOrderItem::where('contract_id', $adaptor->getId())->
                where('confirmed', false)-> // Only check unconfirmed items (during checkout)
                with('product')->
                get();
        } else {
            $items = $adaptor->getItems()->with('product')->get();
        }

        return $items->first(function ($item) use ($transferPhoneProductId) {
            return (int) $item->product_id === $transferPhoneProductId;
        });
    }

    public function save()
    {
        $this->validate();

        $transferPhoneItem = $this->getTransferPhoneItem();
        if (! $transferPhoneItem) {
            $this->addError('general', 'Unable to find transfer phone product item. Please refresh the page.');

            return;
        }

        // Save porting data to item's custom_data
        $portingData = [
            'vorname' => $this->vorname,
            'nachname' => $this->nachname,
            'email' => $this->email,
            'telefonnummer' => $this->telefonnummer,
            'plz' => $this->plz,
            'ort' => $this->ort,
            'strasse_nr' => $this->strasse_nr,
            'telefonnummer_portierung' => $this->telefonnummer_portierung,
        ];

        // Update item's custom_data, preserving any existing custom_data
        $customData = $transferPhoneItem->custom_data ?? [];
        $customData['porting_data'] = $portingData;
        $transferPhoneItem->update(['custom_data' => $customData]);

        // Dispatch event to parent component that porting data is saved
        $this->dispatch('portingDataSaved', $portingData);
    }

    public function render()
    {
        return view('livewire.order-portal.phone-porting-form');
    }
}
