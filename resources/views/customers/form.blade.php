<div class="form-grid">
    <label>Nombre<input name="name" value="{{ old('name', $customer->name ?? '') }}" required></label>
    <label>Tipo documento<select name="identification_document_code" required><option value="13" @selected(old('identification_document_code', $customer->identification_document_code ?? '13') === '13')>Cédula de ciudadanía (13)</option><option value="31" @selected(old('identification_document_code', $customer->identification_document_code ?? '') === '31')>NIT (31)</option><option value="41" @selected(old('identification_document_code', $customer->identification_document_code ?? '') === '41')>Pasaporte (41)</option><option value="42" @selected(old('identification_document_code', $customer->identification_document_code ?? '') === '42')>Documento extranjero (42)</option></select></label>
    <label>Documento / identificación<input name="document" value="{{ old('document', $customer->document ?? '') }}" required></label>
    <label>Dígito de verificación (NIT)<input name="dv" value="{{ old('dv', $customer->dv ?? '') }}" maxlength="2"></label>
    <label>Tipo de persona<select name="legal_organization_code" required><option value="2" @selected(old('legal_organization_code', $customer->legal_organization_code ?? '2') === '2')>Natural</option><option value="1" @selected(old('legal_organization_code', $customer->legal_organization_code ?? '') === '1')>Jurídica</option></select></label>
    <label>Telefono<input name="phone" value="{{ old('phone', $customer->phone ?? '') }}"></label>
    <label>Correo<input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"></label>
    <label>Nombre comercial<input name="trade_name" value="{{ old('trade_name', $customer->trade_name ?? '') }}"></label>
    <label class="span-2">Direccion<input name="address" value="{{ old('address', $customer->address ?? '') }}"></label>
    <label>Tributo<select name="tribute_code" required><option value="ZZ" @selected(old('tribute_code', $customer->tribute_code ?? 'ZZ') === 'ZZ')>No responsable de IVA (ZZ)</option><option value="01" @selected(old('tribute_code', $customer->tribute_code ?? '') === '01')>IVA (01)</option></select></label>
    <label>Responsabilidades DIAN<input name="responsibilities" value="{{ old('responsibilities', isset($customer) ? implode(', ', $customer->responsibilities ?? []) : 'R-99-PN') }}" placeholder="R-99-PN, O-13"></label>
    <label>País<input name="country_code" value="{{ old('country_code', $customer->country_code ?? 'CO') }}" maxlength="2" required></label>
    @php($municipalityCode = old('municipality_code', $customer->municipality_code ?? ''))
    <label class="municipality-field">Municipio
        <div class="municipality-combobox">
            <input type="search" id="municipalitySearch" autocomplete="off" placeholder="Selecciona o escribe el municipio" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="municipalityResults">
            <input type="hidden" name="municipality_code" id="municipalityCode" value="{{ $municipalityCode }}">
            <select id="municipalitySource" hidden aria-hidden="true" tabindex="-1">
                @foreach($municipalities as $municipality)
                    <option value="{{ $municipality->code }}" @selected($municipalityCode === $municipality->code)>{{ $municipality->name }} — {{ $municipality->department }}</option>
                @endforeach
            </select>
            <div id="municipalityResults" class="municipality-results" role="listbox" hidden></div>
        </div>
        <small class="muted" id="municipalityHelp">Escribe para filtrar y selecciona un municipio de la lista.</small>
    </label>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('customers.index') }}">Cancelar</a></div>
<script>
const municipalitySearch = document.getElementById('municipalitySearch');
const municipalityCode = document.getElementById('municipalityCode');
const municipalityResults = document.getElementById('municipalityResults');
const municipalityHelp = document.getElementById('municipalityHelp');
const municipalityOptions = Array.from(document.getElementById('municipalitySource').options).map((option) => ({
    code: option.value,
    text: option.textContent,
}));

const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
const municipalityLabel = (municipality) => municipality.text;

const hideMunicipalityResults = () => {
    municipalityResults.hidden = true;
    municipalitySearch.setAttribute('aria-expanded', 'false');
};

const selectMunicipality = (municipality) => {
    municipalitySearch.value = municipalityLabel(municipality);
    municipalityCode.value = municipality.code;
    municipalityHelp.textContent = 'Municipio seleccionado.';
    hideMunicipalityResults();
};

const renderMunicipalities = (term = '') => {
    const matches = municipalityOptions
        .filter((municipality) => !term || normalize(municipalityLabel(municipality)).includes(term))
        .slice(0, 60);

    municipalityResults.replaceChildren(...matches.map((municipality) => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'municipality-option';
        option.setAttribute('role', 'option');
        option.textContent = municipalityLabel(municipality);
        option.addEventListener('click', () => selectMunicipality(municipality));
        return option;
    }));

    municipalityResults.hidden = matches.length === 0;
    municipalitySearch.setAttribute('aria-expanded', matches.length ? 'true' : 'false');
    municipalityHelp.textContent = matches.length
        ? 'Selecciona un municipio de la lista.'
        : 'No se encontraron municipios.';
};

const selectedMunicipality = municipalityOptions.find((municipality) => municipality.code === municipalityCode.value);
if (selectedMunicipality) municipalitySearch.value = municipalityLabel(selectedMunicipality);

municipalitySearch.addEventListener('focus', () => {
    municipalitySearch.select();
    renderMunicipalities(normalize(municipalitySearch.value.trim()));
});

municipalitySearch.addEventListener('input', () => {
    municipalityCode.value = '';
    renderMunicipalities(normalize(municipalitySearch.value.trim()));
});

municipalitySearch.addEventListener('blur', () => {
    setTimeout(hideMunicipalityResults, 150);
});
</script>
