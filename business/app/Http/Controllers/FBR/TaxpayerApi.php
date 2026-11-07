<?php

namespace App\Http\Controllers\FBR;

class TaxpayerApi {

    public function payTax($posId, $posToken, $data){
     
    $data1=     [
        "InvoiceNumber" => '',
        "POSID" => $posId,
        "USIN" => 'USIN0',
        "DateTime" => $data['DateTime'],
        "BuyerNTN" => "1234567-8",
        "BuyerCNIC" => "12345-1234567-8",
        "BuyerName" => $data['BuyerName'],
        "BuyerPhoneNumber" => $data['BuyerPhoneNumber'],
        "items" => $data['items'],
        "TotalBillAmount" => $data['TotalBillAmount'],
        "TotalQuantity" => $data['TotalQuantity'],
        "TotalSaleValue" => $data['TotalSaleValue'],
        "TotalTaxCharged" => $data['TotalTaxCharged'],
        "PaymentMode" => 2,
        "InvoiceType" => 1
    ];


    $jsonData1 = json_encode($data1);
   
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://gw.fbr.gov.pk/imsp/v1/api/Live/PostData',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>$jsonData1,
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $posToken",
        "Content-Type: application/json",
    ],
    ));
    
    $response = curl_exec($curl);
  
    curl_close($curl);
  

        $staticJsonResponse['response'] = $response;
        return $staticJsonResponse;
  }   
}