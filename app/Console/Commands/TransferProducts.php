<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Account;

require_once('vendor/autoload.php');

class TransferProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $Cont = 0;
        $Origen= "1992fdf6d21c483808b1ac3cd4a6eb01";
        $Destino= "4e10165688432e7a41e4feee221058f1";
        
        $Productos = $this->GetList($Origen);

        foreach ($Productos   as $product) {
            if ($Cont < 3) {
                Log::info($product['id']);
                $IDOrigen = $product['id'];
                $ProductoOrigen = $this->ExtractProduct($IDOrigen,$Origen);
                $this->SendProduct($ProductoOrigen,$Destino);
            }
            $Cont++;
        }


        
    }
    
    public function GetList($APIKey) {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://api.holded.com/api/invoicing/v1/products', [
        'headers' => [
            'accept' => 'application/json',
            'key' => $APIKey,
        ],
        ]);

        $response = $response->getBody();
        return json_decode($response,true);
    }

    public function ExtractProduct($ID,$APIKey) 
    {

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', "https://api.holded.com/api/invoicing/v1/products/$ID", [
        'headers' => [
            'accept' => 'application/json',
            'key' => $APIKey,
        ],
        ]);

        $body = $response->getBody();
        Log::info($body);
        return json_decode($body, true); // Aquí hacemos la decodificación

    }

    public function SendProduct($Producto,$APIKey) {

        $Asociativo = [
          "s_iva_21" => 21,  
        ];

        $TaxesDefault = $Producto['taxes'];
        $Tax = $Asociativo[$TaxesDefault[0]];

        $account = Account::where('IDOrigen', $Producto['expAccountId'])->first();
        $account2 = Account::where('IDOrigen', $Producto['salesChannelId'])->first();
        $ExpensesAccount = $account->IDDestino;
        $salesChannel = $account2->IDDestino;

        $body = [
            "kind" => $Producto['kind'], // Tipo de producto (simple, variable, etc.)
            "name" => $Producto['name'], // Nombre del producto
            "desc" => $Producto['desc'], // Descripción del producto
            "typeId" => $Producto['typeId'], // ID del tipo de producto
            "contactId" => $Producto['contactId'], // ID del contacto asociado
            "contactName" => $Producto['contactName'], // Nombre del contacto asociado
            "price" => $Producto['price'], // Precio del producto
            "taxes" => $Tax, // Array de impuestos aplicables
            "total" => round($Producto['price'] + ($Producto['price'] * 0.21), 2), // Calcular el total, ajusta el porcentaje si es diferente
            "hasStock" => $Producto['stock'] > 0 ? 1 : 0, // Determina si hay stock o no
            "stock" => $Producto['stock'], // Cantidad en stock
            "barcode" => $Producto['barcode'], // Código de barras del producto
            "sku" => $Producto['sku'], // SKU del producto
            "cost" => $Producto['cost'], // Costo del producto
            "purchasePrice" => $Producto['purchasePrice'], // Precio de compra
            "weight" => $Producto['weight'], // Peso del producto
            "tags" => $Producto['tags'], // Array de etiquetas relacionadas
            "categoryId" => $Producto['categoryId'], // ID de la categoría
            "factoryCode" => $Producto['factoryCode'], // Código de fábrica
            "forSale" => $Producto['forSale'], // Indicador de si está a la venta
            "forPurchase" => $Producto['forPurchase'], // Indicador de si está disponible para compra
            "salesChannelId" => $salesChannel, // ID del canal de ventas
            "expAccountId" => $ExpensesAccount, // ID de la cuenta de gastos
            "warehouseId" => $Producto['warehouseId'], // ID del almacén, si aplica
            "type" => [
                [
                    "id" => $Producto['type'][0]['id'], // ID del tipo
                    "name" => $Producto['type'][0]['name'], // Nombre del tipo
                    "desc" => $Producto['type'][0]['desc'] // Descripción vacía por ahora
                ]
            ],
            "attributes" => [
                [
                    "id" => $Producto['attributes'][0]['id'], // ID del atributo
                    "value" => $Producto['attributes'][0]['value'], // Valor del atributo (tipo de producto)
                    "name" => $Producto['attributes'][0]['name'] // Nombre del atributo
                ]
            ]
        ];

        
        $Body = json_encode($body);
        Log::info($Body);
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.holded.com/api/invoicing/v1/products', [
        'body' => $Body,
        'headers' => [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'key' => $APIKey,
        ],
        ]);

        echo $response->getBody();   
    }

    public function ExtractLists($APIKey) {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://api.holded.com/api/invoicing/v1/saleschannels', [
        'headers' => [
            'accept' => 'application/json',
            'key' => $APIKey,
        ],
        ]);

        $response = $response->getBody();
        Log::info($response);
        return json_decode($response,true);
    }

    
    public function ExtractAccounts($APIKey) {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET','https://api.holded.com/api/invoicing/v1/expensesaccounts', [
        'headers' => [
            'accept' => 'application/json',
            'key' => $APIKey,
        ],
        ]);

        $response = $response->getBody();
        Log::info($response);
        return json_decode($response,true);
    }
}
