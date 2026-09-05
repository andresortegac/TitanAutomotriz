<div class="form-grid">
    <label>Nombre<input name="name" value="{{ old('name', $customer->name ?? '') }}" required></label>
    <label>Tipo documento<select name="identification_document_code" required><option value="13" @selected(old('identification_document_code', $customer->identification_document_code ?? '13') === '13')>Cédula de ciudadanía (13)</option><option value="31" @selected(old('identification_document_code', $customer->identification_document_code ?? '') === '31')>NIT (31)</option><option value="41" @selected(old('identification_document_code', $customer->identification_document_code ?? '') === '41')>Pasaporte (41)</option><option value="42" @selected(old('identification_document_code', $customer->identification_document_code ?? '') === '42')>Documento extranjero (42)</option></select></label>
    <label>Documento / identificación<input name="document" value="{{ old('document', $customer->document ?? '') }}" required></label>
    <label>Dígito de verificación (NIT)<input name="dv" value="{{ old('dv', $customer->dv ?? '') }}" maxlength="2"></label>
    <label>Tipo de persona<select name="legal_organization_code" required><option value="2" @selected(old('legal_organization_code', $customer->legal_organization_code ?? '2') === '2')>Natural</option><option value="1" @selected(old('legal_organization_code', $customer->legal_organization_code ?? '') === '1')>Jurídica</option></select></label>
    <label>Telefono<input name="phone" value="{{ old('phone', $customer->phone ?? '') }}"></label>
    <label>Correo<input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"></label>
    <label class="span-2">Direccion<input name="address" value="{{ old('address', $customer->address ?? '') }}"></label>
    <label>Nombre comercial<input name="trade_name" value="{{ old('trade_name', $customer->trade_name ?? '') }}"></label>
    <label>Tributo<select name="tribute_code" required><option value="ZZ" @selected(old('tribute_code', $customer->tribute_code ?? 'ZZ') === 'ZZ')>No responsable de IVA (ZZ)</option><option value="01" @selected(old('tribute_code', $customer->tribute_code ?? '') === '01')>IVA (01)</option></select></label>
    <label>Responsabilidades DIAN<input name="responsibilities" value="{{ old('responsibilities', isset($customer) ? implode(', ', $customer->responsibilities ?? []) : 'R-99-PN') }}" placeholder="R-99-PN, O-13"></label>
    <label>País<input name="country_code" value="{{ old('country_code', $customer->country_code ?? 'CO') }}" maxlength="2" required></label>
    @php($municipalityCode = old('municipality_code', $customer->municipality_code ?? ''))
    <label class="municipality-field">Municipio
        <input type="search" id="municipalitySearch" autocomplete="off" placeholder="Escribe al menos 2 letras para buscar" value="{{ $municipalityCode ? 'Código actual: '.$municipalityCode : '' }}" aria-describedby="municipalityHelp" aria-controls="municipalityResults" aria-expanded="false">
        <input type="hidden" name="municipality_code" id="municipalityCode" value="{{ $municipalityCode }}">
        <small class="muted" id="municipalityHelp">Busca y selecciona un municipio de la lista.</small>
    </label>
    <div id="municipalityResults" class="municipality-results" role="listbox" aria-label="Resultados de municipios" hidden></div>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('customers.index') }}">Cancelar</a></div>
<script>
const municipalitySearch = document.getElementById('municipalitySearch');
const municipalityCode = document.getElementById('municipalityCode');
const municipalityResults = document.getElementById('municipalityResults');
const municipalityHelp = document.getElementById('municipalityHelp');
let municipalityTimer;
let municipalities = [];

const hideMunicipalityResults = () => {
    municipalityResults.hidden = true;
    municipalitySearch.setAttribute('aria-expanded', 'false');
};

const selectMunicipality = (municipality) => {
    municipalitySearch.value = `${municipality.name} — ${municipality.department} (${municipality.code})`;
    municipalityCode.value = municipality.code;
    municipalityHelp.textContent = 'Municipio seleccionado.';
    hideMunicipalityResults();
};

const renderMunicipalities = () => {
    municipalityResults.replaceChildren();

    municipalities.forEach((municipality) => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'municipality-option';
        option.setAttribute('role', 'option');
        option.textContent = `${municipality.name} — ${municipality.department} (${municipality.code})`;
        option.addEventListener('click', () => selectMunicipality(municipality));
        municipalityResults.appendChild(option);
    });

    municipalityResults.hidden = municipalities.length === 0;
    municipalitySearch.setAttribute('aria-expanded', municipalities.length ? 'true' : 'false');
};

municipalitySearch.addEventListener('input', () => {
    clearTimeout(municipalityTimer);
    const term = municipalitySearch.value.trim();
    const selectedMunicipality = municipalities.find((municipality) =>
        `${municipality.name} — ${municipality.department} (${municipality.code})` === municipalitySearch.value
    );

    if (selectedMunicipality) {
        municipalityCode.value = selectedMunicipality.code;
        municipalityHelp.textContent = 'Municipio seleccionado.';
        return;
    }

    municipalityCode.value = '';
    municipalities = [];
    renderMunicipalities();

    if (term.length < 2) {
        municipalityHelp.textContent = 'Escribe al menos 2 letras para buscar.';
        return;
    }

    municipalityTimer = setTimeout(async () => {
        municipalityHelp.textContent = 'Buscando municipios...';

        try {
            const response = await fetch(`{{ route('customers.municipalities') }}?search=${encodeURIComponent(term)}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Municipalities request failed');

            const results = await response.json();
            if (municipalitySearch.value.trim() !== term) return;

            municipalities = results;
            renderMunicipalities();
            municipalityHelp.textContent = municipalities.length
                ? 'Selecciona un municipio de las sugerencias.'
                : 'No se encontraron municipios con ese nombre.';
        } catch (_) {
            hideMunicipalityResults();
            municipalityHelp.textContent = 'No fue posible consultar municipios. Intenta nuevamente.';
        }
    }, 250);
});

municipalitySearch.addEventListener('blur', () => {
    setTimeout(hideMunicipalityResults, 150);
});
</script>
