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
    <label class="span-2">País<input name="country_code" value="{{ old('country_code', $customer->country_code ?? 'CO') }}" maxlength="2" required></label>
    @php($municipalityCode = old('municipality_code', $customer->municipality_code ?? ''))
    <label class="municipality-field">Municipio
        <input type="search" id="municipalityFilter" autocomplete="off" placeholder="Escribe para filtrar municipios" aria-controls="municipalityCode">
        <select name="municipality_code" id="municipalityCode" required>
            <option value="">Selecciona el municipio</option>
            @foreach($municipalities as $municipality)
                <option value="{{ $municipality->code }}" @selected($municipalityCode === $municipality->code)>{{ $municipality->name }} — {{ $municipality->department }}</option>
            @endforeach
        </select>
        <small class="muted" id="municipalityHelp">Escribe el nombre del municipio y selecciónalo de la lista filtrada.</small>
    </label>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('customers.index') }}">Cancelar</a></div>
<script>
const municipalityFilter = document.getElementById('municipalityFilter');
const municipalitySelect = document.getElementById('municipalityCode');
const municipalityOptions = Array.from(municipalitySelect.options).map((option) => ({
    value: option.value,
    text: option.textContent,
    selected: option.selected,
}));

const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

municipalityFilter.addEventListener('input', () => {
    const term = normalize(municipalityFilter.value.trim());
    const selectedValue = municipalitySelect.value;
    const matches = municipalityOptions.filter((option) => !term || !option.value || normalize(option.text).includes(term));

    municipalitySelect.replaceChildren(...matches.map((option) => {
        const element = new Option(option.text, option.value, false, option.value === selectedValue || (!selectedValue && option.selected));
        return element;
    }));
});
</script>
