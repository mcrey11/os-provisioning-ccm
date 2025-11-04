<div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-phone me-2"></i>
                {{ __('view.porting.title') }}
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">{{ __('view.porting.description') }}</p>

            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vorname" class="form-label">
                            {{ __('view.porting.vorname') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('vorname') is-invalid @enderror" 
                               id="vorname" 
                               wire:model="vorname" 
                               required>
                        @error('vorname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nachname" class="form-label">
                            {{ __('view.porting.nachname') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nachname') is-invalid @enderror" 
                               id="nachname" 
                               wire:model="nachname" 
                               required>
                        @error('nachname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            {{ __('view.porting.email') }} <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               wire:model="email" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="telefonnummer" class="form-label">
                            {{ __('view.porting.telefonnummer') }} <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control @error('telefonnummer') is-invalid @enderror" 
                               id="telefonnummer" 
                               wire:model="telefonnummer" 
                               required>
                        @error('telefonnummer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="plz" class="form-label">
                            {{ __('view.porting.plz') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('plz') is-invalid @enderror" 
                               id="plz" 
                               wire:model="plz" 
                               required>
                        @error('plz')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="ort" class="form-label">
                            {{ __('view.porting.ort') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('ort') is-invalid @enderror" 
                               id="ort" 
                               wire:model="ort" 
                               required>
                        @error('ort')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="strasse_nr" class="form-label">
                            {{ __('view.porting.strasse_nr') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('strasse_nr') is-invalid @enderror" 
                               id="strasse_nr" 
                               wire:model="strasse_nr" 
                               required>
                        @error('strasse_nr')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="telefonnummer_portierung" class="form-label">
                            {{ __('view.porting.telefonnummer_portierung') }} <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control @error('telefonnummer_portierung') is-invalid @enderror" 
                               id="telefonnummer_portierung" 
                               wire:model="telefonnummer_portierung" 
                               placeholder="{{ __('view.porting.telefonnummer_portierung_placeholder') }}"
                               required>
                        @error('telefonnummer_portierung')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">{{ __('view.porting.telefonnummer_portierung_help') }}</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        {{ __('view.porting.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
