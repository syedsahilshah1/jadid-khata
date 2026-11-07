<?php

namespace Modules\WhatsApp\Services;

use App\Utils\Util;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Builder;
use Modules\WhatsApp\Entities\WhatsAppAccounts;

class WhatsAppServices extends Util
{
	protected string $templateRepairNotification;
	protected string $note;
	public const TEMPLATE_REPAIR = '01c2868e-4ac5-4edb-9bee-2b82e00d3f9d';

	/**
	 * requestData function is for store logic while all data stored and send to whatsapp gateway
	 * $isTransfer is optional
	 * $note is optional
	 * @param [string] $type
	 * @param [array] $data
	 * @param [string] $isTransfer
	 * @param [string] $note
	 * @param [int] $phone
	 * @return void
	 * @throws GuzzleException
	 */
	public function requestData($type, $data, $isTransfer = null, $note = null): void
	{
		$accounts = WhatsAppAccounts::where('is_default', 1)->first();
		$this->templateRepairNotification = self::TEMPLATE_REPAIR;
		switch (true) {
			case ($type == 'repair' and $isTransfer == 'yes'):
				$sessionData = WhatsAppAccounts::customerDataRepairTransfers($data->id);
				$notes = 'Your device is transfered to branch ' . $sessionData->location_transfer . ' and repair status is ' . $sessionData->status . '. ' . $note;
				$body = $this->templateMessage($accounts, $sessionData, $notes);
				$url = $accounts->wa_server;
				$this->sendRequest($url, $body);
				break;
			case ($type == 'repair' and $isTransfer == 'none'):
				$sessionData = WhatsAppAccounts::customerDataRepair($data->id);
				$note = $note ?? '-';
				$body = $this->templateMessage($accounts, $sessionData, $note);
				$url = $accounts->wa_server;
				$this->sendRequest($url, $body);
				break;
			case ($type == 'sale'):
				$sessionData = $data;
				$body = $this->notificationMessage($accounts, $sessionData);
				$url = $accounts->wa_server;
				$this->sendRequest($url, $body);
				break;
			case ($type == 'notification'):
				$sessionData = WhatsAppAccounts::customerDataRepairTransfers($data->id);
				$body = $this->notificationMessage($accounts, $sessionData, $isTransfer, $note);
				$url = $accounts->wa_server;
				$this->sendRequest($url, $body);
				break;
			default:
				# code...
				break;
		}
	}

	/**
	 * @throws GuzzleException
	 */
	public function requestDataBot($type, $data, $isTransfer = null, $note = null, $phone = null): void
	{
		$accounts = WhatsAppAccounts::where ( 'is_default', 1 )->first ();
		$this->templateRepairNotification = self::TEMPLATE_REPAIR;
		switch ( true ) {
			case ($type == 'repair' and $isTransfer == 'yes'):
				$sessionData = WhatsAppAccounts::customerDataRepairTransfers ( $data->id );
				$notes = 'Your device is transfered to branch ' . $sessionData->location_transfer . ' and repair status is ' . $sessionData->status . '. ' . $note;
				$body = $this->templateMessage ( $accounts, $sessionData, $notes );
				$url = $accounts->wa_server;
				$this->sendRequest ( $url, $body );
				break;
			case ($type == 'repair' and $isTransfer == 'none'):
				$sessionData = WhatsAppAccounts::customerDataRepair ( $data->id );
				$note = $note ?? '-';
				$body = $this->templateMessageBot ( $accounts, $sessionData, $note, $phone );
				$url = $accounts->wa_server;
				$this->sendRequest ( $url, $body );
				break;
			case ($type == 'new_sale'):

				break;
			case ($type == 'notification'):
				$sessionData = WhatsAppAccounts::customerDataRepairTransfers ( $data->id );
				$body = $this->notificationMessage ( $accounts, $sessionData, $isTransfer, $note );
				$url = $accounts->wa_server;
				$this->sendRequest ( $url, $body );
				break;
			default:
				# code...
				break;
		}
	}

	/**
	 * template message for sending data
	 *
	 * @param $accounts
	 * @param $data
	 * @param null $note
	 * @return array
	 */
	public function templateMessage($accounts, $data, $note = null): array
	{
		return [
			'appkey'      => $accounts->app_key,
			'authkey'     => $accounts->auth_key,
			'to'          => country_code( $data->mobile ),
			'sanbox'      => false,
			'template_id' => $this->templateRepairNotification,
			'variables'   => [
				'{1}' => $data->job_id,
				'{2}' => whatsapp_date( $data->date ),
				'{3}' => whatsapp_date_human( $data->date ),
				'{4}' => $data->location,
				'{5}' => $data->device,
				'{6}' => $data->status,
				'{7}' => $note,
				'{8}' => trim( $data->name )
			]
		];
	}

	public function templateMessageBot($accounts, $data, $note = null, $phone): array
	{
		return [
			'appkey'      => $accounts->app_key,
			'authkey'     => $accounts->auth_key,
			'sender'      => $accounts->sender,
			'number'      => $phone,
			'to'          => $phone,
			'sanbox'      => false,
			'template_id' => $this->templateRepairNotification,
			'variables' => [
				'{1}' => $data->job_id,
				'{2}' => whatsapp_date ( $data->date ),
				'{3}' => whatsapp_date_human ( $data->date ),
				'{4}' => $data->location,
				'{5}' => $data->device,
				'{6}' => $data->status,
				'{7}' => $note,
				'{8}' => trim ( $data->name )
			]
		];
	}

	public function templateFiles($accounts, $data): array
	{
		return [
			'appkey'      => $accounts->app_key,
			'authkey'     => $accounts->auth_key,
			'to'          => country_code( $data->phone ),
			'sanbox'      => false,
			'template_id' => $this->templateRepairNotification,
			'file'        => $data->file,
			'message'     => $data->message
		];
	}

	public function notificationMessage($accounts, $data): array
	{
		return [
			'appkey'    => $accounts->app_key,
			'sender'    => $accounts->sender,
			'api_key'   => $accounts->app_key,
			'authkey'   => $accounts->auth_key,
			'to'        => $data['mobile_number'],
			'number'    => $data['mobile_number'],
			'message'   => $data['whatsapp_text'],
			'sandbox'   => false
		];
	}

	/**
	 * @throws GuzzleException
	 */
	public function sendRequest($url, $body): void
	{
		$client = new Client(['verify' => false]);
		$client->post($url, [
			'debug'      =>false,
			'http_errors'=>false,
			'form_params'=>$body,
			'headers'    =>['Content-Type'=>'application/x-www-form-urlencoded']
		] );
	}

	public function loadAccounts(): Builder
	{
		return WhatsAppAccounts::query();
	}

	public function saveWhatsAppAccounts($input)
	{
		return WhatsAppAccounts::create($input);
	}

	public function deleteWhatsAppAccounts($id): ?bool
	{
		return WhatsAppAccounts::where('id', $id)->delete();
	}

	public function showWhatsAppAccounts($id)
	{
		return WhatsAppAccounts::find($id);
	}

	public function updateWhatsAppAccounts($request, $id): void
	{
		$accounts = WhatsAppAccounts::findOrFail($id);
		$accounts->update($request);
		$accounts->save();
	}

	public function checkAccountsDefaultGateway(): bool
	{
		return WhatsAppAccounts::where('is_default', 1)->exists();
	}
}