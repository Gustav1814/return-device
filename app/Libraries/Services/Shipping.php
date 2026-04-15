<?php
namespace App\Libraries\Services;

use App\Models\User;
use Shippo;
use Shippo_Address;
use Shippo_Shipment;
use Shippo_Transaction;
use Shippo_Batch;
use Shippo_Track;
class Shipping
{
    public function __construct()
    {
        // Grab this private key from
        // .env and setup the Shippo api
        Shippo::setApiKey(env('SHIPPO_PRIVATE'));
    }

    /**
     * Validate an address through Shippo service
     *
     * @param User $user
     * @return Shippo_Adress
     */
    public function validateAddress($order)
    {
        // echo $order->id;
        // exit();
        // Grab the shipping address from the User model

        // $toAddress = $user->shippingAddress();
        // Pass a validate flag to Shippo
        // $toAddress['validate'] = true;
        // Verify the address data
        $toAddress = [
            'name' => $order->emp_first_name . '' . $order->emp_last_name,
            'company' => $order->receipient_name,
            'street1' => $order->receipient_add_1,
            'city' => $order->receipient_city,
            'state' => 'Texas',
            'zip' => $order->receipient_zip . 'aa',
            'country' => 'US',
            'phone' => $order->receipient_phone,
            'email' => $order->receipient_email,
        ];
        // print_r($toAddress);
        $toAddress['validate'] = false;
        return Shippo_Address::create($toAddress);
    }

    /**
     * Return the shipping data for a user
     *
     * @return array
     */
    public function shippingAddress()
    {
        return [
            'name' => $this->name,
            'company' => $this->company,
            'street1' => $this->street1,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }


    /**
     * Sanitize an address array: replace PHP null / literal "NULL" with empty string,
     * and when city is missing try to extract it from a combined street1 field.
     *
     * Handles patterns like:
     *   "5051 WESTHEIMER RD, STE 1900 FLOOR 19 HOUSTON, TX"
     *   → street1="5051 WESTHEIMER RD, STE 1900 FLOOR 19", city="HOUSTON"
     */
    private function sanitizeAddress(array $addr): array
    {
        $addr = array_map(function ($v) {
            if ($v === null || (is_string($v) && strtoupper(trim($v)) === 'NULL')) {
                return '';
            }
            return $v;
        }, $addr);

        $city = trim($addr['city'] ?? '');
        $street1 = trim($addr['street1'] ?? '');
        $state = strtoupper(trim($addr['state'] ?? ''));

        if ($city === '' && $street1 !== '' && $state !== '') {
            // Strip trailing state abbreviation (e.g. ", TX" or " TX") from street1
            $stripped = preg_replace('/[\s,]+' . preg_quote($state, '/') . '\s*$/i', '', $street1);
            if ($stripped && $stripped !== $street1) {
                // Now find the city: it's the last space-delimited word(s) after the last comma
                $parts = array_map('trim', explode(',', $stripped));
                if (count($parts) >= 2) {
                    $cityCandidate = array_pop($parts);
                    $addr['street1'] = implode(', ', $parts);
                    $addr['city'] = $cityCandidate;
                } else {
                    // No comma — try last word as city (e.g. "123 Main St HOUSTON")
                    if (preg_match('/^(.+?)\s+([A-Z][A-Za-z\s]+)$/', $stripped, $m2)) {
                        $addr['street1'] = trim($m2[1]);
                        $addr['city'] = trim($m2[2]);
                    }
                }
            }
        }

        return $addr;
    }

    public function get_shippo_rates($param)
    {
        $p = array();
        $p['address_from'] = $this->sanitizeAddress($param['from']);
        $p['address_to']   = $this->sanitizeAddress($param['to']);
        $p['parcels'] = $param['parcel'];
        $p['async'] = false;
        isset($param['carrier_accounts']) ? $p['carrier_accounts'] = $param['carrier_accounts'] : '';
        // isset($param['insurance'])?$p['parcels']['extra']['insurance'] = $param['insurance'] :'';
        isset($param['insurance']) ? $p['extra']['insurance'] = $param['insurance'] : '';

        // #region agent log
        try {
            @file_put_contents(base_path('debug-aa8310.log'), json_encode([
                'sessionId'=>'aa8310','hypothesisId'=>'H-P3','location'=>'Shipping.php:get_shippo_rates',
                'message'=>'sanitized address fields sent to Shippo',
                'data'=>['from_address'=>$p['address_from'],'to_address'=>$p['address_to']],
                'timestamp'=>(int)round(microtime(true)*1000),'runId'=>'post-fix-4',
            ])."\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {}
        // #endregion

        $shipment = Shippo_Shipment::create($p);

        // #region agent log
        try {
            $shipStatus = $shipment['status'] ?? null;
            $shipMessages = null;
            if ($shipment instanceof \Shippo_Object) {
                $shipMessages = $shipment['messages'] ?? null;
                if ($shipMessages instanceof \Shippo_Object) {
                    $msgArr = [];
                    foreach ($shipMessages->keys() as $mk) { $msgArr[$mk] = $shipMessages[$mk]; }
                    $shipMessages = $msgArr;
                }
            }
            @file_put_contents(base_path('debug-aa8310.log'), json_encode([
                'sessionId'=>'aa8310','hypothesisId'=>'H-P3','location'=>'Shipping.php:get_shippo_rates',
                'message'=>'shipment create result',
                'data'=>['status'=>$shipStatus,'messages'=>$shipMessages,'address_from_obj'=>$shipment['address_from']['object_id']??null,'address_to_obj'=>$shipment['address_to']['object_id']??null],
                'timestamp'=>(int)round(microtime(true)*1000),'runId'=>'addr-debug',
            ])."\n", FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {}
        // #endregion

        return $shipment;
    }

    public function purchase_shippo_label($param)
    {
        $rateId = $param['oid'];
        $transaction = Shippo_Transaction::create(array(
            'rate' => $rateId,
            'async' => false,
        ));
        return $transaction;
    }


    /**
     *  MODULE: ADDRESS VALIDATE
     *  DESCRIPTION: VALIDATE ADDRESS BY SHIPPO API
     */
    public function get_address_obj($param)
    {
        try {
            $fromAddress = Shippo_Address::create(array(
                "name" => $param['name'],
                "street1" => $param['street1'],
                "city" => $param['city'],
                "state" => $param['state'],
                "zip" => $param['zip'],
                "country" => "US",
                "email" => $param['email'],
                "validate" => true
            ));

            return $fromAddress;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function validate_shippo_address($objectId)
    {

        $response = Shippo_Address::validate($objectId);
        return $response;
    }


    public function get_trackingInfo($objectId, $lblCarrier)
    {
        $status_params = array(
            'id' => 'SHIPPO_TRANSIT',
            'carrier' => 'shippo'
        );
        if (env('WEB_STATUS') == "LIVE") {
            $status_params = array(
                'id' => $objectId,
                'carrier' => $lblCarrier
            );
        }
        $status = Shippo_Track::get_status($status_params);
        return isset($status->tracking_status) ? $status->tracking_status : '';
        //return isset($status)?$status:'';

    }


}
