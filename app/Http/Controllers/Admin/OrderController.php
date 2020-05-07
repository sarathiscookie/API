<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CurlTrait;
use App\Http\Traits\OrderStatusTrait;
use App\Http\Traits\ShopTrait;
use App\Order;
use App\Shop;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OrderController extends Controller
{
    use CompanyTrait, ShopTrait, CurlTrait, OrderStatusTrait;
    /**
     * Show the order view page. Passing all order statuses and matching companies into the orders view page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Company trait to fetch the companies matching with shops.
        $companies = $this->fetchCompanyMatchingWithShop();

        // Order status traits to fetch the order statuses.
        $orderStatuses = $this->orderStatuses();

        return view('admin.order', ['companies' => $companies, 'orderStatuses' => $orderStatuses]);
    }

    /**
     * Show the orders data into the view page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        try {
            // Getting all the http request.
            $params        = $request->all();

            $data          = [];
            $totalData     = 0;
            $pages         = 0;
            $per_page      = '';
            $totalFiltered = 0;
            $search        = '';
            $status        = '';

            if (!empty($request->orderListDateRange) && !empty($request->orderCompany)) {

                // Seperate from and to date.
                $dateRange = explode("-", $request->orderListDateRange);

                // If the request has a search value (order number), this query will execute and fetch the results.
                if (!empty($request->input('search.value'))) {
                    $search = "&search=" . urlencode($request->input('search.value')) . "&search_field=order_no";
                }

                // If the table has footer column value (order number), this query will execute and fetch the results based on order numbers.
                if (!empty($params['columns'][1]['search']['value'])) {
                    $search = "&search=" . urlencode($params['columns'][1]['search']['value']) . "&search_field=order_no";
                }

                // If the table has footer column value (order status), this query will execute and fetch the results based on order status.
                if (!empty($params['columns'][2]['search']['value'])) {
                    $status = '&status=' . $params['columns'][2]['search']['value'];
                }

                // Get api key from shops
                // 1 = Rakuten: Other shops like Amazone and eBay send invoices automatically. For Rakuten, we need to send invoices.
                // The invoice sends functionality is for Rakuten only.
                $api_key = $this->getApiKey(1, $request->orderCompany);

                // Sending request to API. Passing api key, from, and to date to get orders list.
                $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key=' . $api_key->api_key . '&format=json&page=' . $request->pageActive . '&per_page=' . $request->length . '&created_from=' . $dateRange[0] . '&created_to=' . $dateRange[1] . $search . $status;

                // Getting order details
                if (!empty($urlGetOrders)) {
                    $orderDetails = $this->getUrlOrders($urlGetOrders, $request->orderCompany);
                }

                // Checking order details is empty or not
                if (!empty($orderDetails)) {

                    $data          = $orderDetails['data'];
                    $totalData     = (int) $orderDetails['totalData'];
                    $pages         = (int) $orderDetails['pages'];
                    $per_page      = $orderDetails['per_page'];
                    $totalFiltered = (int) $orderDetails['totalFiltered'];

                }
            }

            // Preparing array to send the response in JSON format to draw the data in datatable.
            $json_data = [
                'draw'            => (int) $params['draw'],
                'recordsTotal'    => $totalData,
                'recordsFiltered' => $totalFiltered,
                'pages'           => $pages,
                'per_page'        => $per_page,
                'data'            => $data
            ];

            return response()->json($json_data);
        } catch (\Exception $e) {
            return response()->json(['orderListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Get shop order details.
     *
     * @param  string  $urlGetOrders
     * @param  int  $companyId
     * @return \Illuminate\Http\Response
     */
    public function getUrlOrders($urlGetOrders, $companyId)
    {
        // Fetching data from API
        $jsonDecodedResults = $this->curl($urlGetOrders);

        // Checking API response is success or failure and count of total data.
        if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0')) {

            $totalData     = $jsonDecodedResults['result']['orders']['paging'][0]['total'];
            $pages         = $jsonDecodedResults['result']['orders']['paging'][0]['pages'];
            $per_page      = $jsonDecodedResults['result']['orders']['paging'][0]['per_page'];
            $totalFiltered = $totalData;

            foreach ($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {

                if (!empty($orderList['invoice_no'])) {
                    $downloadButton = '<a href="/admin/dashboard/order/list/download/' . $companyId . '/' . $orderList['order_no'] . '"><i class="fas fa-download"></i></a>';
                } else {
                    $downloadButton = '<span class="badge badge-secondary">No File</span>';
                }

                $nestedData['hash']    = '<input class="checked orderNoInput" type="checkbox" name="id[]" value="' . $orderList['order_no'] . '" />';
                $nestedData['order']   = '<h6>' . $orderList['order_no'] . '</h6><div>Invoice no: <span class="badge badge-secondary badge-pill">' . $orderList['invoice_no'] . '</span></div><div>Created on: <span class="badge badge-secondary badge-pill">' . date("d.m.y H:i:s", strtotime($orderList['created'])) . '</span></div>';
                $nestedData['status']  = $this->orderLabels($orderList['status']);
                $nestedData['actions'] = $downloadButton;
                $data[]                = $nestedData;
            }

            return compact('data', 'totalData', 'pages', 'per_page', 'totalFiltered');
        }
    }

    /**
     * Download invoice.
     *
     * @param  int  $companyId
     * @param  string  $orderNo
     * @return \Illuminate\Http\Response
     */
    public function download($companyId, $orderNo)
    {
        try {
            // Get api key from shops
            // 1 = Rakuten: Other shops like Amazone and eBay send invoices automatically. For Rakuten, we need to send invoices. 
            // The invoice sends functionality is for Rakuten only.
            $api_key    = $this->getApiKey(1, $companyId);

            // Sending request to API. Passing api key, and order number to get orders list.
            $getOrderInvoice = 'http://webservice.rakuten.de/merchants/orders/getOrderInvoice?key=' . $api_key->api_key . '&format=json&order_no=' . $orderNo;

            // Get order invoice
            if (!empty($getOrderInvoice)) {
                // Fetching data from API
                $jsonDecodedResults = $this->curl($getOrderInvoice);
            }

            // Checking the API response is success or failure.
            if ($jsonDecodedResults['result']['success'] === '1') {

                // URL src from API response
                // URL src doesn't have trasfer protocol. So added trasfer protocol in environment file manually.
                $fileSource = env('API_URL_TRANSFER_PROTOCOL') . $jsonDecodedResults['result']['invoice']['src'];

                $fileName = $jsonDecodedResults['result']['invoice']['filename']; // Here we get th Filename from API response.

                $headers = ['Content-Type: application/pdf'];

                $file_get_contents = file_get_contents($fileSource);

                // Path of directory and file
                $pathToDirectory = 'invoice/' . $companyId;

                $pathToFile = $pathToDirectory . '/' . $fileName;

                // If directory doesn't exists create a new one.
                if (!Storage::exists($pathToDirectory)) {
                    $createDirectory = Storage::makeDirectory($pathToDirectory, 0775);
                }

                // Checking data already exist or not
                if (Storage::exists($pathToFile)) {

                    Storage::delete($pathToFile); // Delete files from directory

                    file_put_contents(storage_path('app/' . $pathToFile), $file_get_contents); // Store content in to a file
                } 
                else {

                    file_put_contents(storage_path('app/' . $pathToFile), $file_get_contents); // Store content in to a file
                }

                return Storage::download($pathToFile, $fileName, $headers);
            }
        } catch (\Exception $e) {
            return response()->json(['orderListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * ZIP and Download all invoices within a date range 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadAllInvoices(Request $request)
    {
        try {
            $orderListArray = [];

            $companyId = $request->inputOrderCompanyId;

            // Get api key from shops
            // 1 = Rakuten: Other shops like Amazone and eBay send invoices automatically. For Rakuten, we need to send invoices. 
            // The invoice sends functionality is for Rakuten only.
            $api_key = $this->getApiKey(1, $companyId);

            // Checking if user checked checkbox to download all files.
            if ($request->allFilesChecked === 'on') {

                $dateRangeRequest = explode("-", $request->inputOrderDateRange);
                $orderListPages   = $request->orderListPages;
                $orderListPerPage = $request->orderListPerPage;
                $orderListTotal   = $request->orderListTotal;

                // For loop to fetch all order no and store data in to array.
                for ($i = 1; $i <= $orderListPages; $i++) {

                    // Sending request to API. Passing api key, from, and to date to get orders list.
                    $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key=' . $api_key->api_key . '&format=json&page=' . $i . '&created_from=' . $dateRangeRequest[0] . '&created_to=' . $dateRangeRequest[1];

                    // Fetching data from API
                    $jsonDecodedResults = $this->curl($urlGetOrders);

                    // Condition to checking API results
                    if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0')) {

                        foreach ($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {
                            $orderListArray[] = $orderList['order_no'];
                        }
                    }
                }

                $orderNoArray = $orderListArray;
            } 
            else {
                $orderNoArray = explode(',', $request->inputOrderNoArr);
            }

            // create new zip object
            $zip = new ZipArchive;

            // Define the file name. Give it a unique name to avoid overriding.
            $zipFileName = 'invoices_' . date("dmyHis") . '.zip';

            // Path of directory and file
            $pathToDirectory   = 'invoice/' . $companyId;

            // If directory doesn't exists create a new one.
            if (!Storage::exists($pathToDirectory)) {
                $createDirectory = Storage::makeDirectory($pathToDirectory, 0775);
            }

            // Create the ZIP file directly inside the desired folder. No need for a temporary file.
            $zip->open(storage_path('app/invoice/' . $companyId . '/' . $zipFileName), ZipArchive::CREATE);

            // Sending request to API. Passing api key, and order number to get orders list.
            foreach ($orderNoArray as $orderNo) {

                $getOrderInvoice = 'http://webservice.rakuten.de/merchants/orders/getOrderInvoice?key=' . $api_key->api_key . '&format=json&order_no=' . $orderNo;

                // Fetching data from API
                $jsonDecodedResults = $this->curl($getOrderInvoice);

                if ($jsonDecodedResults['result']['success'] === '1') {
                    // URL src from API response
                    // URL src doesn't have trasfer protocol. So manually added trasfer protocol in environment file.
                    $fileSource = env('API_URL_TRANSFER_PROTOCOL') . $jsonDecodedResults['result']['invoice']['src'];
                    $fileName = $jsonDecodedResults['result']['invoice']['filename']; // Filename from API response
                    $file_get_contents = file_get_contents($fileSource);

                    // Add it to the zip
                    $zip->addFromString($zipFileName . '/' . $fileName, $file_get_contents);
                }
            }

            // Close zip
            $zip->close();

            $filePath = 'invoice/' . $companyId . '/' . $zipFileName;

            $headers = [
                'Content-Type: application/octet-stream',
                'Content-Disposition: attachment; filename=' . $zipFileName,
                'Content-length: ' . filesize(storage_path('app/invoice/' . $companyId . '/' . $zipFileName)),
                'Pragma: no-cache',
                'Expires: 0'
            ];

            return Storage::download($filePath, $zipFileName, $headers);
        } catch (\Exception $e) {
            return response()->json(['orderListStatusMsg' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
