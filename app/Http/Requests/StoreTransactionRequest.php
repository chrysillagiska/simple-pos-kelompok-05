<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $item) {
                if (isset($item['product_id']) && isset($item['qty'])) {
                    $product = Product::find($item['product_id']);

                    if ($product && $item['qty'] > $product->stock) {
                        // Tambah error ke field 'items'
                        $validator->errors()->add(
                            'items',
                            "Transaksi ditolak: Kuantitas {$product->name} melebihi stok (sisa stok: {$product->stock})."
                        );
                    }
                }
            }
        });
    }
}