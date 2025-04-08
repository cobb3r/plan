<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ServiceModel;
use App\Models\ServiceProductModel;
use App\Models\ProductModel;
use App\Models\MappingModel;
use Illuminate\Support\Arr;

class SyncServices extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'sync:services';
    protected $description = 'Sync Products With Users';

    public function run(array $params)
    {
        $services = new ServiceModel();
        $products = new ServiceProductModel();
        $productLabels = new ProductModel();
        $mapping = new MappingModel();
        $all = $services->findAll();
        $url = "http://host.docker.internal:8000/api/services?limit=1000";
        $url2 = "http://host.docker.internal:8000/api/service-products?limit=1000";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $ch2 = curl_init($url2);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($ch2);


        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            $data2 = json_decode($response2, true);
            $count = 0;
            foreach ($all as $service) {
                $apiData[$service['id']] = $data['data'][($service['id'] - 1)];
                $spData[$service['id']] = $data2['data'][($service['id'] - 1)];
                $sp = $products->where('service_id', $service['id'])->first();
                $product = $sp ? $productLabels->find($sp['product_id']) : null;

                if ($apiData[$service['id']]['mobile_number'] !== $service['mobile_number']) {
                    echo ("Phone Numbers Not Equal on Record " . $service['id'] . "\n API Data: " . $apiData[$service['id']]['mobile_number'] . "\n Database: " . $service['mobile_number'] . "\n");
                    $toInsert = [
                        'type' => 1,
                        'local_id' => $service['id'],
                        'external_id' => $apiData[$service['id']]['id']
                    ];
                    $alreadyLogged = $mapping->where('external_id', $toInsert['external_id'])->first();
                    if ($alreadyLogged == null) {
                        $mapping->insert($toInsert);
                    }

                    $services->where('id', $service['id'])->update('mobile_number', $apiData[$service['id']]['mobile_number']);
                } elseif ($apiData[$service['id']]['network'] !== $service['network']) {
                    echo ("Networks Not Identical on Record " . $service['id']  . "\n API Data: " . $apiData[$service['id']]['network'] . "\n Database: " . $service['network'] . "\n");
                    $toInsert = [
                        'type' => 1,
                        'local_id' => $service['id'],
                        'external_id' => $apiData[$service['id']]['id']
                    ];
                    $alreadyLogged = $mapping->where('external_id', $toInsert['external_id'])->first();
                    if ($alreadyLogged == null) {
                        $mapping->insert($toInsert);
                    }

                    $services->where('id', $service['id'])->update('network', $apiData[$service['id']]['network']);
                } elseif ($spData[$service['id']]['type'] !== $product['label']) {
                    echo ("Types Not Identical on Record " . $service['id'] . "\n API Data: " . $spData[$service['id']]['type'] . "\n Database: " .  $product['label'] . "\n");
                    $toInsert = [
                        'type' => 2,
                        'local_id' => $product['id'],
                        'external_id' => $spData[$service['id']]['id']
                    ];
                    $alreadyLogged = $mapping->where('external_id', $toInsert['external_id'])->first();
                    if ($alreadyLogged == null) {
                        $mapping->insert($toInsert);
                    }

                    $productLabels->where('id', $service['id'])->update('label', $spData[$service['id']]['type']);
                } elseif ($spData[$service['id']]['price'] !== $sp['amount']) {
                    echo ("Prices Not Identical on Record " . $service['id'] . "\n API Data: " . $spData[$service['id']]['price'] . "\n Database: " . $sp['amount'] . "\n");
                    $toInsert = [
                        'type' => 2,
                        'local_id' => $sp['amount'],
                        'external_id' => $spData[$service['id']]['id']
                    ];
                    $alreadyLogged = $mapping->where('external_id', $toInsert['external_id'])->first();
                    if ($alreadyLogged == null) {
                        $mapping->insert($toInsert);
                    }

                    $products->where('id', $service['id'])->update('mobile_number', $apiData[$service['id']]['mobile_number']);
                }
        }

        curl_close($ch);
    }
    }
}