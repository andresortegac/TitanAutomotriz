<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\BarcodeService;
use App\Services\ProductBarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ProductImportController extends Controller
{
    public function create()
    {
        return view('products.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,csv,txt'],
        ]);

        $rows = $this->rowsFromFile($request->file('file')->getRealPath(), $request->file('file')->getClientOriginalExtension());
        $products = $this->prepareProducts($rows);

        DB::transaction(function () use ($products): void {
            $categories = [];
            foreach ($products as $attributes) {
                $categoryName = $attributes['category_name'];
                if (! isset($categories[Str::lower($categoryName)])) {
                    $categories[Str::lower($categoryName)] = Category::whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])->first()
                        ?? Category::create(['name' => $categoryName, 'active' => true]);
                }
                $attributes['category_id'] = $categories[Str::lower($categoryName)]->id;
                unset($attributes['category_name']);
                $product = Product::create($attributes);
                $product->update(['sku' => app(BarcodeService::class)->generateSku($product)]);
                app(ProductBarcodeService::class)->syncPrimaryBarcode($product, null, 'CODE128', auth()->id());
            }
        });

        return redirect()->route('products.index')->with('success', count($products).' productos cargados y sus códigos de barras fueron generados.');
    }

    public function template()
    {
        $content = "Item;Código;Descripción;Unidad;Cantidad;Valor Unitario;Precio de Venta;% Dscto;% IVA;Valor IVA;Total;Categoría;Stock Mínimo\r\n1;REF-001;Producto de ejemplo;und;10;25000;35000;0%;19%;47500;250000;MOTO;5\r\n";

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla-productos.csv"',
        ]);
    }

    private function prepareProducts(array $rows): array
    {
        if (count($rows) < 2) {
            throw ValidationException::withMessages(['file' => 'El archivo no contiene productos para importar.']);
        }

        $headers = array_map(fn ($header) => $this->header($header), array_shift($rows));
        $columns = array_flip($headers);
        foreach (['codigo', 'descripcion', 'cantidad', 'valorunitario', 'categoria', 'stockminimo'] as $required) {
            if (! array_key_exists($required, $columns)) {
                throw ValidationException::withMessages(['file' => "Falta la columna requerida: {$required}."]);
            }
        }

        $products = [];
        $codes = [];
        foreach ($rows as $index => $row) {
            if (! array_filter($row, fn ($value) => filled($value))) {
                continue;
            }

            $line = $index + 2;
            $code = trim((string) ($row[$columns['codigo']] ?? ''));
            $name = trim((string) ($row[$columns['descripcion']] ?? ''));
            $stock = $this->number($row[$columns['cantidad']] ?? null);
            $cost = $this->number($row[$columns['valorunitario']] ?? null);
            $salePrice = array_key_exists('precioventa', $columns) ? $this->number($row[$columns['precioventa']] ?? null) : null;
            $tax = array_key_exists('iva', $columns) ? $this->number($row[$columns['iva']] ?? 0) : 0;
            $category = trim((string) ($row[$columns['categoria']] ?? ''));
            $minStock = $this->number($row[$columns['stockminimo']] ?? null);

            if ($code === '' || $name === '' || $category === '' || $stock === null || $stock < 0 || floor($stock) !== $stock || $minStock === null || $minStock < 0 || floor($minStock) !== $minStock || $cost === null || $cost < 0 || $salePrice !== null && $salePrice < 0 || $tax === null || $tax < 0 || $tax > 100) {
                throw ValidationException::withMessages(['file' => "La fila {$line} tiene datos incompletos o inválidos. Código, descripción, cantidad y valor unitario son obligatorios."]);
            }
            if (isset($codes[Str::lower($code)]) || Product::where('code', $code)->exists()) {
                throw ValidationException::withMessages(['file' => "El código '{$code}' de la fila {$line} ya existe o está repetido en el archivo."]);
            }

            $codes[Str::lower($code)] = true;
            $products[] = [
                'category_name' => $category,
                'supplier_id' => null,
                'code' => $code,
                'name' => $name,
                'description' => $name,
                'purchase_price' => $cost,
                'sale_price' => $salePrice,
                'tax_rate' => $tax,
                'stock' => (int) $stock,
                'min_stock' => (int) $minStock,
                'active' => true,
            ];
        }

        if ($products === []) {
            throw ValidationException::withMessages(['file' => 'El archivo no contiene filas válidas.']);
        }

        return $products;
    }

    private function rowsFromFile(string $path, string $extension): array
    {
        if (strtolower($extension) !== 'xlsx') {
            $handle = fopen($path, 'r');
            $rows = [];
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) === 1) {
                    $row = str_getcsv($row[0], ',');
                }
                $rows[] = $row;
            }
            fclose($handle);

            return $rows;
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => 'No se pudo abrir el archivo Excel.']);
        }
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if (! $sheetXml) {
            throw ValidationException::withMessages(['file' => 'El archivo Excel no contiene una primera hoja válida.']);
        }

        $shared = [];
        if ($sharedXml) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $item) {
                $shared[] = trim(implode('', $item->xpath('.//t')));
            }
        }
        $xml = simplexml_load_string($sheetXml);
        $xml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = [];
        foreach ($xml->xpath('//x:sheetData/x:row') as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                preg_match('/[A-Z]+/', (string) $cell['r'], $match);
                $column = $this->columnNumber($match[0] ?? 'A');
                $value = (string) $cell->v;
                $type = (string) $cell['t'];
                if ($type === 's') {
                    $value = $shared[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }
                $values[$column] = $value;
            }
            $rows[] = $values === [] ? [] : array_values(array_replace(array_fill(0, max(array_keys($values)) + 1, ''), $values));
        }

        return $rows;
    }

    private function header(mixed $value): string
    {
        $value = Str::ascii(Str::lower(trim((string) $value)));
        $value = preg_replace('/[^a-z0-9]+/', '', $value);

        return match ($value) {
            'codigo', 'cod' => 'codigo',
            'descripcion', 'nombre', 'producto' => 'descripcion',
            'cantidad', 'stock' => 'cantidad',
            'valorunitario', 'precio', 'preciounitario', 'valor' => 'valorunitario',
            'precioventa', 'preciodeventa', 'venta' => 'precioventa',
            'iva', 'porcentajeiva' => 'iva',
            'categoria', 'category' => 'categoria',
            'stockminimo', 'minstock', 'existenciaminima' => 'stockminimo',
            default => $value,
        };
    }

    private function number(mixed $value): ?float
    {
        $value = trim(str_replace(['$', '%', ' ', "\xc2\xa0"], '', (string) $value));
        if ($value === '') {
            return null;
        }
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $parts = explode(',', $value);
            $value = strlen(end($parts)) === 2 ? str_replace(',', '.', $value) : str_replace(',', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function columnNumber(string $letters): int
    {
        $number = 0;
        foreach (str_split($letters) as $letter) {
            $number = $number * 26 + (ord($letter) - 64);
        }

        return $number - 1;
    }
}
