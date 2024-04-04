<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function get_all_invoices()
    {
        $invoices = Invoice::with('customer')->orderBy('id','desc')->get();

        return response()->json([
            "invoices"=> $invoices
        ], 200);
    }

    public function search_invoices(Request $request)
    {
        $search = $request->get("search");  
        if(!empty($search)) {
            $invoices = Invoice::with('customer')
                        ->where('id','LIKE','%'.$search.'%')
                        ->get();

            return response()->json([ 
                'invoices' => $invoices
             ], 200);        
        }

        return $this->get_all_invoices();
    }

    public function create_invoice(Request $request)    
    {
        $counter = Counter::where('key', 'invoice')->first();
        $invoice = Invoice::orderBy('id', 'DESC')->first();

        // Get serial invoice_no 
        if($invoice) {
            $invoice = $invoice->id+1;
            $counters = $counter->value + $invoice;
        } else {
            $counters = $counter->value;
        }
        $formData = [
            'invoice_number'=> $counter->prefix.$counters,
            'customer_id' => null,
            'customer' => null,
            'date' => date('Y-m-d'),
            'due_date' => null,
            'reference' => null,
            'discount' => 0,
            'terms_and_conditions' => 'Default Terms and conditions',
            'items' => [
                'product_id' => null,
                'product' => null,
                'unit_price' => null,
                'quantity' => 1
            ]
        ];

        return response()->json($formData);
    }

    public function add_invoice(Request $request)
    {
        $invoiceItem = $request->input('invoice_item');

        $invoiceData['sub_total'] = $request->input('sub_total');
        $invoiceData['total'] = $request->input('total');
        $invoiceData['customer_id'] = $request->input('customer_id');
        $invoiceData['invoice_number'] = $request->input('invoice_number');
        $invoiceData['date'] = $request->input('date');
        $invoiceData['due_date'] = $request->input('due_date');
        $invoiceData['discount'] = $request->input('discount');
        $invoiceData['reference'] = $request->input('reference');
        $invoiceData['terms_and_conditions'] = $request->input('terms_and_conditions');

        $invoice = Invoice::create($invoiceData);

        foreach(json_decode($invoiceItem) as $item) {
            $itemData['product_id'] = $item->id;
            $itemData['invoice_id'] = $invoice->id;
            $itemData['quantity'] = $item->quantity;
            $itemData['unit_price'] = $item->unit_price;

            InvoiceItem::create($itemData);
        }

        return response()->json([
            "success" => "new invoice added..!"
        ], 200);
    }

    public function show_invoice($id)
    {
        $invoice = Invoice::with(['customer', 'invoice_items.product'])->findOrFail($id);

        return response()->json([
            'invoice' => $invoice
        ], 200);
    }
    public function edit_invoice($id)
    {
        $invoice = Invoice::with(['customer', 'invoice_items.product'])->findOrFail($id);

        return response()->json([
            'invoice' => $invoice
        ], 200);
    }

    public function update_invoice($id, Request $request)
    {

        $invoice = Invoice::where('id', $id)->first();
        $invoicedata = $request->except(["invoice_item"]);
        $invoice->update($invoicedata);  

        $invoiceItems = $request->input("invoice_item");
        $invoice->invoice_items()->delete();

        foreach(json_decode($invoiceItems) as $item) {
            $itemData['product_id'] = $item->product_id;
            $itemData['invoice_id'] = $invoice->id;
            $itemData['quantity'] = $item->quantity;
            $itemData['unit_price'] = $item->unit_price;

            InvoiceItem::create($itemData);
        }

        return response()->json(["success" => "Invoice updated..!"], 200);
    }
    public  function delete_invoice_items($id)
    {
        $invoiceItems = InvoiceItem::findOrFail( $id ); 
        $invoiceItems->delete();

        return response()->json(["success"=> "Invoice item deleted"], 200);
    }

    public function delete_invoice($id)
    {
        $invoice = Invoice::findOrFail( $id );
        $invoice->invoice_items()->delete();    
        $invoice->delete();

        return response()->json(["success"=> "Invoice deleted..!"],200);
    }
}
