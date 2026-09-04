<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index', [
            'sales' => Sale::with(['user', 'customer'])->latest()->paginate(12),
        ]);
    }

    public function create()
    {
        $products = Product::with('primaryBarcode')->where('active', true)->where('stock', '>', 0)->orderBy('name')->get();
        $services = Service::where('active', true)->orderBy('name')->get();

        return view('sales.create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => $products,
            'productOptions' => $products->map(fn ($product) => [
                'id' => $product->id,
                'code' => $product->code,
                'sku' => $product->sku,
                'barcode' => $product->primaryBarcode?->code,
                'name' => $product->name,
                'price' => (float) $product->sale_price,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => $product->stock,
            ])->values(),
            'serviceOptions' => $services->map(fn ($service) => [
                'id' => $service->id,
                'code' => $service->code,
                'name' => $service->name,
                'price' => (float) $service->sale_price,
                'tax_rate' => (float) $service->tax_rate,
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:efectivo,transferencia,tarjeta,mixto,credito'],
            'credit_due_date' => ['nullable', 'date', 'required_if:payment_method,credito'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:product,service'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $discount = (float) ($data['discount'] ?? 0);

        try {
            $sale = DB::transaction(function () use ($data, $discount) {
                $subtotal = 0;
                $taxTotal = 0;
                $items = [];

                foreach ($data['items'] as $item) {
                    $quantity = (int) $item['quantity'];

                    if ($item['item_type'] === 'product') {
                        if (empty($item['product_id'])) {
                            throw new \RuntimeException('Selecciona un producto.');
                        }

                        $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                        if (! $product->active || $product->stock < $quantity) {
                            throw new \RuntimeException("Stock insuficiente para {$product->name}.");
                        }

                        $lineName = $product->name;
                        $unitPrice = (float) $product->sale_price;
                        $taxRate = (float) $product->tax_rate;
                        $productId = $product->id;
                        $serviceId = null;
                    } else {
                        if (empty($item['service_id'])) {
                            throw new \RuntimeException('Selecciona un servicio.');
                        }

                        $service = Service::findOrFail($item['service_id']);

                        if (! $service->active) {
                            throw new \RuntimeException("El servicio {$service->name} no esta activo.");
                        }

                        $lineName = $service->name;
                        $unitPrice = (float) $service->sale_price;
                        $taxRate = (float) $service->tax_rate;
                        $productId = null;
                        $serviceId = $service->id;
                        $product = null;
                    }

                    $lineSubtotal = $unitPrice * $quantity;
                    $lineTax = round($lineSubtotal * ($taxRate / 100), 2);
                    $lineTotal = $lineSubtotal + $lineTax;
                    $subtotal += $lineSubtotal;
                    $taxTotal += $lineTax;
                    $items[] = [
                        'item_type' => $item['item_type'],
                        'product' => $product,
                        'product_id' => $productId,
                        'service_id' => $serviceId,
                        'name' => $lineName,
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'quantity' => $quantity,
                        'subtotal' => $lineSubtotal,
                        'tax_amount' => $lineTax,
                        'total' => $lineTotal,
                    ];
                }

                if ($discount > $subtotal) {
                    throw new \RuntimeException('El descuento no puede ser mayor que el subtotal.');
                }

                $total = ($subtotal - $discount) + $taxTotal;

                $paidAmount = (float) $data['paid_amount'];
                $isCredit = $data['payment_method'] === 'credito';

                if ($isCredit && empty($data['customer_id'])) {
                    throw new \RuntimeException('Para vender a credito debes seleccionar un cliente.');
                }

                if (! $isCredit && $paidAmount < $total) {
                    throw new \RuntimeException('El valor pagado es menor que el total.');
                }

                if ($isCredit && $paidAmount > $total) {
                    throw new \RuntimeException('El abono no puede ser mayor que el total.');
                }

                $balance = $isCredit ? $total - $paidAmount : 0;
                $creditStatus = match (true) {
                    ! $isCredit => 'sin_credito',
                    $balance <= 0 => 'pagado',
                    $paidAmount > 0 => 'abonado',
                    default => 'pendiente',
                };

                $sale = Sale::create([
                    'invoice_number' => 'FV-'.now()->format('YmdHis').'-'.auth()->id(),
                    'user_id' => auth()->id(),
                    'customer_id' => $data['customer_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $taxTotal,
                    'total' => $total,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $isCredit ? 0 : $paidAmount - $total,
                    'balance' => $balance,
                    'credit_due_date' => $isCredit ? $data['credit_due_date'] : null,
                    'credit_status' => $creditStatus,
                    'payment_method' => $data['payment_method'],
                ]);

                foreach ($items as $item) {
                    $sale->items()->create([
                        'product_id' => $item['product_id'],
                        'service_id' => $item['service_id'],
                        'item_type' => $item['item_type'],
                        'product_name' => $item['name'],
                        'unit_price' => $item['unit_price'],
                        'tax_rate' => $item['tax_rate'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                        'tax_amount' => $item['tax_amount'],
                        'total' => $item['total'],
                    ]);

                    if ($item['product']) {
                        $item['product']->decrement('stock', $item['quantity']);
                    }
                }

                return $sale;
            });
        } catch (\RuntimeException $exception) {
            return back()->withInput()->withErrors($exception->getMessage());
        }

        return redirect()->route('sales.receipt', ['sale' => $sale, 'print' => 1])->with('success', 'Venta registrada.');
    }

    public function show(Sale $sale)
    {
        return view('sales.show', ['sale' => $sale->load(['items', 'user', 'customer'])]);
    }

    public function receipt(Sale $sale)
    {
        return view('sales.receipt', ['sale' => $sale->load(['items', 'user', 'customer'])]);
    }

    public function destroy(Sale $sale)
    {
        if ($sale->status === 'anulada') {
            return back()->withErrors('La venta ya esta anulada.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items.product');

            foreach ($sale->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'anulada']);
        });

        return back()->with('success', 'Venta anulada y stock restaurado.');
    }
}
