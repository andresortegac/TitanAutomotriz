# FASE 2 - Arquitectura del modulo de codigos de barras

## Decision principal

El modulo se implementara con una tabla independiente `product_barcodes`.

Esta opcion es mejor que guardar solo `barcode` en `products`, porque permite:

- Un producto con varios codigos.
- Un codigo principal por producto.
- Codigos internos y codigos del fabricante.
- Preparar presentaciones futuras sin romper ventas existentes.
- Mantener historial y auditoria de cambios.

## Modelo de datos recomendado

### products

Se mantendra la tabla actual como base del inventario y del catalogo.

Campos actuales importantes:

- `id`
- `category_id`
- `supplier_id`
- `code`
- `name`
- `purchase_price`
- `sale_price`
- `stock`
- `min_stock`
- `active`

Cambios recomendados:

- Agregar `sku` unico.
- Mantener `code` por compatibilidad inicial, pero usar `sku` como codigo interno formal.
- No agregar `barcode` directamente aqui como fuente principal.

### product_barcodes

Tabla nueva para administrar todos los codigos asociados a productos.

Campos recomendados:

- `id`
- `product_id`
- `code`
- `type`
- `is_primary`
- `source`
- `active`
- `created_by`
- `updated_by`
- timestamps

Valores recomendados:

- `type`: `CODE128`, `EAN13`, `EAN8`, `UPCA`
- `source`: `internal`, `manufacturer`, `presentation`, `manual`

Restricciones:

- `code` debe ser unico global.
- Solo debe existir un codigo principal activo por producto.
- `product_id` debe tener llave foranea hacia `products`.
- `created_by` y `updated_by` deben apuntar a `users` cuando exista usuario autenticado.

### barcode_audits

Tabla futura para registrar historial y auditoria.

Campos recomendados:

- `id`
- `product_id`
- `product_barcode_id`
- `user_id`
- `action`
- `old_code`
- `new_code`
- `old_type`
- `new_type`
- `reason`
- `metadata`
- timestamps

Acciones recomendadas:

- `generated`
- `created_manual`
- `updated`
- `deactivated`
- `duplicate_rejected`
- `primary_changed`
- `label_printed`

### product_presentations

No se implementara de inmediato en la FASE 3, pero la arquitectura queda preparada.

Campos futuros:

- `id`
- `product_id`
- `name`
- `unit`
- `conversion_factor`
- `price`
- `active`
- timestamps

Luego se podra relacionar `product_barcodes` con una presentacion si se necesita.

## Relaciones Eloquent

### Product

Relaciones nuevas:

- `hasMany(ProductBarcode::class)`
- `hasOne(ProductBarcode::class)->where('is_primary', true)`

### ProductBarcode

Relaciones:

- `belongsTo(Product::class)`
- `belongsTo(User::class, 'created_by')`
- `belongsTo(User::class, 'updated_by')`

### User

Relaciones opcionales:

- `hasMany(ProductBarcode::class, 'created_by')`
- `hasMany(BarcodeAudit::class)`

## Servicios

### BarcodeService

Responsabilidades:

- Generar codigos internos tipo `CODE128`.
- Validar formatos.
- Validar EAN-13, EAN-8 y UPC-A.
- Calcular digitos de control cuando aplique.
- Buscar codigos duplicados.
- Normalizar codigos antes de guardar.

Formato recomendado para codigos internos:

- `FER00000001`
- `FER00000002`
- `FER00000003`

El prefijo `FER` se mantiene para codigos internos de la ferreteria.

### ProductBarcodeService

Responsabilidades:

- Crear codigo principal al crear producto.
- Registrar codigos manuales.
- Cambiar codigo principal.
- Inactivar codigos.
- Mantener la regla de un solo principal por producto.
- Registrar auditoria de cambios.

## Flujo de creacion de producto

1. Administrador abre `/products/create`.
2. Ingresa datos del producto.
3. Puede generar SKU y codigo automaticamente.
4. El backend valida datos.
5. Se crea el producto.
6. Se crea el codigo principal en `product_barcodes`.
7. Se registra auditoria.

## Flujo de codigo manual

1. Administrador escribe el codigo.
2. El backend normaliza el codigo.
3. Se valida el tipo.
4. Se verifica unicidad global.
5. Si existe, se devuelve mensaje indicando el producto asignado.
6. Si no existe, se guarda como codigo principal o secundario.

## Flujo POS con lector USB

1. En `/sales/create` habra un campo fijo: `Escanear o escribir codigo`.
2. El lector USB escribe el codigo y envia ENTER.
3. JavaScript llama a un endpoint interno.
4. El backend busca en `product_barcodes.code`.
5. Solo devuelve productos activos y codigos activos.
6. El frontend agrega el producto al carrito.
7. Si ya existe, incrementa cantidad.
8. Se valida stock visualmente en frontend y definitivamente en backend al guardar.

## Endpoint recomendado

Ruta web protegida por `auth`:

- `GET /products/lookup?code=FER00000001`

Respuesta exitosa:

```json
{
    "success": true,
    "product": {
        "id": 1,
        "name": "Martillo 16 oz",
        "sku": "FER-000001",
        "barcode": "FER00000001",
        "barcode_type": "CODE128",
        "price": 28000,
        "stock": 20
    }
}
```

Respuesta cuando no existe:

```json
{
    "success": false,
    "message": "Producto no encontrado"
}
```

## Seguridad

Reglas iniciales:

- Todo usuario autenticado puede buscar por codigo en POS.
- Solo `admin` puede crear, cambiar, regenerar o inactivar codigos.
- El backend nunca debe confiar solo en JavaScript.
- La venta final debe seguir validando stock con transaccion y `lockForUpdate()`.

## Interfaz

### Productos

En crear/editar producto se agregaran:

- SKU
- Codigo de barras principal
- Tipo de codigo
- Boton generar codigo
- Boton imprimir etiqueta

### POS

En nueva venta se agregara:

- Campo fijo para escaneo.
- Busqueda por codigo/SKU/nombre.
- Mensajes de producto no encontrado o sin stock.

### Etiquetas

Se creara luego:

- `/barcode-labels`
- Vista de configuracion.
- Vista imprimible.

## Orden de implementacion recomendado

1. Crear migracion de `product_barcodes`.
2. Agregar `sku` a `products`.
3. Crear modelo `ProductBarcode`.
4. Agregar relaciones a `Product`.
5. Crear `BarcodeService`.
6. Crear `ProductBarcodeService`.
7. Actualizar formularios de productos.
8. Crear endpoint de busqueda.
9. Integrar campo de escaneo en POS.
10. Crear etiquetas e impresion.
11. Agregar auditoria.
12. Crear pruebas.

## Archivos que se tocaran en FASE 3 y FASE 4

- `database/migrations/*_add_sku_to_products_table.php`
- `database/migrations/*_create_product_barcodes_table.php`
- `app/Models/Product.php`
- `app/Models/ProductBarcode.php`
- `app/Services/BarcodeService.php`
- `app/Services/ProductBarcodeService.php`
- `app/Http/Controllers/ProductController.php`
- `app/Http/Controllers/ProductLookupController.php`
- `routes/web.php`
- `resources/views/products/form.blade.php`
- `resources/views/products/index.blade.php`
- `resources/views/sales/create.blade.php`

