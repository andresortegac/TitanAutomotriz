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
    <label>Buscar municipio<input type="search" id="municipalitySearch" placeholder="Escribe al menos 2 letras"></label>
    <label>Municipio<select name="municipality_code" id="municipalityCode"><option value="{{ old('municipality_code', $customer->municipality_code ?? '') }}">{{ old('municipality_code', $customer->municipality_code ?? '') ? 'Código actual: '.old('municipality_code', $customer->municipality_code ?? '') : 'Busca y selecciona un municipio' }}</option></select></label>
</div><div class="actions" style="margin-top:14px;"><button class="btn">Guardar</button><a class="btn light" href="{{ route('customers.index') }}">Cancelar</a></div>
<script>
const municipalitySearch = document.getElementById('municipalitySearch');
const municipalityCode = document.getElementById('municipalityCode');
let municipalityTimer;
municipalitySearch.addEventListener('input', () => {
    clearTimeout(municipalityTimer);
    const term = municipalitySearch.value.trim();
    if (term.length < 2) return;
    municipalityTimer = setTimeout(async () => {
        municipalityCode.innerHTML = '<option>Buscando municipios...</option>';
        try {
            const response = await fetch(`{{ route('customers.municipalities') }}?search=${encodeURIComponent(term)}`, { headers: { Accept: 'application/json' } });
            const municipalities = await response.json();
            municipalityCode.innerHTML = '<option value="">Selecciona un municipio</option>' + municipalities.map(m => `<option value="${m.code}">${m.name} — ${m.department} (${m.code})</option>`).join('');
        } catch (_) {
            municipalityCode.innerHTML = '<option value="">No fue posible consultar municipios</option>';
        }
    }, 250);
});
</script>
