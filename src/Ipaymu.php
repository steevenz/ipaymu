<?php
/**
 * Ipaymu API PHP Class Library
 *
 * MIT License
 *
 * Copyright (c) 2018 Steeve Andrian Salim
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author         Steeve Andrian Salim
 * @copyright      Copyright (c) 2018, Steeve Andrian Salim
 * @filesource
 */

// ------------------------------------------------------------------------

namespace Steevenz;

// ------------------------------------------------------------------------

use O2System\Curl;
use O2System\Kernel\Http\Message\Uri;
use O2System\Spl\Traits\Collectors\ConfigCollectorTrait;
use O2System\Spl\Traits\Collectors\ErrorCollectorTrait;

/**
 * Class Ipaymu
 * @package Steevenz
 */
class Ipaymu
{
    use ErrorCollectorTrait;
    use ConfigCollectorTrait;

    /**
     * Ipaymu::$response
     *
     * Curl Response Object.
     *
     * @var \O2System\Curl\Response
     */
    protected $response;

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::__construct
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if(count($config)) {
            $this->setConfig($config);
        }
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::setKey
     *
     * Sets Ipaymu API key.
     *
     * @param string $apiKey Ipaymu API key.
     *
     * @return static
     */
    public function setApiKey($apiKey)
    {
        $this->setConfig('apiKey', $apiKey);

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::request
     *
     * Curl request API caller.
     *
     * @param string $path
     * @param array  $params
     * @param string $type
     *
     * @access  protected
     * @return  array|bool Returns FALSE if failed.
     */
    protected function request($path, $params = [], $type = 'GET')
    {
        $apiUrl = 'https://my.ipaymu.com';

        $uri = (new Uri($apiUrl))->withPath($path);
        $request = new Curl\Request();

        $params[ 'key' ] = $this->config['apiKey'];
        $params[ 'format' ] = 'json';

        switch ($type) {
            default:
            case 'GET':
                $this->response = $request->setUri($uri)->get($params);
                break;

            case 'POST':
                $request->addHeader('content-type', 'application/x-www-form-urlencoded');
                $this->response = $request->setUri($uri)->post($params);
                break;
        }

        // Try to get curl error
        if (false !== ($error = $this->response->getError())) {
            $this->errors = $error;
        } else {
            $body = $this->response->getBody();

            if (empty($body[ 'Status' ])) {
                return $body->getArrayCopy();
            } else {
                $this->errors[ $body[ 'Status' ] ] = $body[ 'Keterangan' ];
            }
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::getAccount
     *
     * Gets ipaymu account information.
     *
     * @return array|bool Returns FALSE if failed.
     */
    public function getAccount()
    {
        if (false !== ($response = $this->request('api/CekSaldo.php'))) {
            $account = [
                'username' => $response['Username'],
                'balance' => $response['Saldo'],
                'status' => $this->checkAccountStatus($response['Username'])
            ];

            return $account;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::checkAccountBalance
     *
     * Gets latest ipaymu balance.
     *
     * @return int|bool Returns FALSE if failed.
     */
    public function checkAccountBalance()
    {
        if (false !== ($response = $this->request('api/CekSaldo.php'))) {
            return (int) $response[ 'Saldo' ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::checkAccountStatus
     *
     * Gets ipaymu account status.
     *
     * @param string $username Ipaymu account username.
     *
     * @return string|bool Returns FALSE if failed.
     */
    public function checkAccountStatus($username)
    {
        $statusCodes = [
            0 => 'UNVERIFIED',
            1 => 'VERIFIED',
            2 => 'CERTIFIED',
            3 => 'CERTIFIED_PREMIUM',
        ];

        if (false !== ($response = $this->request('api/CekStatus.php', ['user' => $username]))) {
            return $statusCodes[ $response[ 'StatusUser' ] ];
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::checkTransaction
     *
     * Gets ipaymu transaction status.
     *
     * @param string $trxId Transaction ID.
     *
     * @return array|bool Returns FALSE if failed.
     */
    public function checkTransaction($trxId)
    {
        $statusCodes = [
            -1 => 'PROCESSED',
            0  => 'PENDING',
            1  => 'SUCCESS',
            2  => 'CANCELED',
            3  => 'REFUND',
        ];

        if (false !== ($response = $this->request('api/CekTransaksi.php', ['id' => $trxId]))) {

            $response = [
                'id'          => $trxId,
                'status'      => $statusCodes[ $response[ 'Status' ] ],
                'description' => $response[ 'Keterangan' ],
                'sender'      => $response[ 'Pengirim' ],
                'receiver'    => $response[ 'Penerima' ],
                'amount'      => $response[ 'Nominal' ],
                'time'        => date('r', strtotime($response[ 'Waktu' ])),
                'type'        => strtoupper($response[ 'Tipe' ]),
            ];

            return $response;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::addTransaction
     *
     * Add Ipaymu transaction.
     *
     * @example
     * Single Product
     * $ipaymu->addTransaction([
     *      'id' => 'INV-1234567890',
     *      'product' => [
     *          'name' => 'Shoes'
     *          'price' => 10000,
     *          'quantity' => 1,
     *          'description' => 'Amazing Shoes'
     *      ]
     * ]);
     *
     * Multiple Products
     * $ipaymu->addTransaction([
     *      'id' => 'INV-1234567890',
     *      'products' => [
     *          [
     *              'name' => 'Shoes',
     *              'price' => 10000,
     *              'quantity' => 1,
     *              'description' => 'Amazing Shoes'
     *          ],
     *          [
     *              'name' => 'Bag',
     *              'price' => 5000,
     *              'quantity' => 2,
     *              'description' => 'Amazing Bag'
     *          ]
     *      ]
     * ]);
     *
     * Single Product with PayPal
     * $ipaymu->addTransaction([
     *      'id' => 'INV-1234567890',
     *      'product' => [
     *          'name' => 'Shoes'
     *          'price' => 10000,
     *          'price_usd' => 1, // Required for payment using PayPal
     *          'quantity' => 1,
     *          'description' => 'Amazing Shoes'
     *      ]
     * ], 'paypalemail@yourdomain.com');
     *
     * Multiple Products
     * $ipaymu->addTransaction([
     *      'id' => 'INV-1234567890',
     *      'products' => [
     *          [
     *              'name' => 'Shoes',
     *              'price' => 10000,
     *              'price_usd' => 1, // Required for payment using PayPal
     *              'quantity' => 1,
     *              'description' => 'Amazing Shoes'
     *          ],
     *          [
     *              'name' => 'Bag',
     *              'price' => 5000,
     *              'price_usd' => 1, // Required for payment using PayPal
     *              'quantity' => 2,
     *              'description' => 'Amazing Bag'
     *          ]
     *      ]
     * ], 'paypalemail@yourdomain.com');
     *
     * @param array         $transaction    Transaction parameters, please see the example.
     * @param string|null   $paypalAccount  Your PayPal email account.
     *
     * @return array|bool
     */
    public function addTransaction( array $transaction, $paypalAccount = null)
    {
        $params = [
            'action'         => 'payment',
            'invoice_number' => $transaction[ 'id' ],
        ];

        if (isset($transaction[ 'product' ])) {
            $params[ 'product' ] = $transaction[ 'product' ][ 'name' ];
            $params[ 'price' ] = $transaction[ 'product' ][ 'price' ];
            $params[ 'quantity' ] = $transaction[ 'product' ][ 'quantity' ];

            if (isset($transaction[ 'product' ][ 'description' ])) {
                $params[ 'comments' ] = $transaction[ 'product' ][ 'description' ];
            }

            if(isset($paypalAccount)) {
                $params['paypal_price'] = $transaction['product']['price_usd'];
            }
        } elseif (isset($transaction[ 'products' ])) {
            $index = 0;
            foreach ($transaction[ 'products' ] as $product) {
                $params[ 'product' ][ $index ] = $product[ 'name' ];
                $params[ 'price' ][ $index ] = $product[ 'price' ];
                $params[ 'quantity' ][ $index ] = $product[ 'quantity' ];
                $params[ 'comments' ][ $index ] = empty($product[ 'description' ]) ? null : $product[ 'description' ];

                if(isset($options['paypal'])) {
                    $params['paypal_price'][$index] = $product['price_usd'];
                }

                $index++;
            }
        }

        if(isset($this->config['url']['return'])) {
            $params['ureturn'] = $this->config['url']['return'];
        }

        if(isset($this->config['url']['notify'])) {
            $params['unotify'] = $this->config['url']['notify'];
        }

        if(isset($this->config['url']['cancel'])) {
            $params['ucancel'] = $this->config['url']['cancel'];
        }

        if(isset($paypalAccount)) {
            $params['paypal_email'] = $paypalAccount;
        }

        if (false !== ($response = $this->request('payment.htm', $params))) {
            return $response;
        }

        return false;
    }

    // ------------------------------------------------------------------------

    /**
     * Ipaymu::getResponse
     *
     * Get original response object.
     *
     * @param   string $offset Response Offset Object
     *
     * @access  public
     * @return  \O2System\Curl\Response|bool Returns FALSE if failed.
     */
    public function getResponse()
    {
        return $this->response;
    }
}