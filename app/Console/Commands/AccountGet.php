<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Account; // Asegúrate de importar el modelo Account.


class AccountGet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:account-get';

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
        $Origen= "1992fdf6d21c483808b1ac3cd4a6eb01";
        $Destino= "4e10165688432e7a41e4feee221058f1";

        $Datos = $this->ExtractAccounts($Origen);
        $this->SaveAccounts($Datos, 'IDOrigen');

        $Datos = $this->ExtractLists($Origen);
        $this->SaveAccounts($Datos, 'IDOrigen');

        $Datos = $this->ExtractAccounts($Destino);
        $this->SaveAccounts($Datos, 'IDDestino');

        $Datos = $this->ExtractLists($Destino);
        $this->SaveAccounts($Datos, 'IDDestino');

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

    public function SaveAccounts($Datos, $campoDestino)
    {
        foreach ($Datos as $Data) {
            // Verificamos si la cuenta ya existe antes de crearla
            Account::updateOrCreate(
                [
                    'name' => $Data['name']  // Almacena el nombre de la cuenta
                ],
                [
                    'name' => $Data['name'],  // Almacena el nombre de la cuenta
                    $campoDestino => $Data['id'] // Dependiendo si es origen o destino
                ]
            );
        }
    }
}
