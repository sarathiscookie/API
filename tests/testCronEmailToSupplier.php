// Replace this function with CronEmailToSupplier.php -> handle().

public function handle()
    {
        $test = '';
        $testOrders = [];
        $testApi = [];
        $testItem = [];
        $testModuleSettings = [];

        $shops = Shop::active()->get();

        //foreach ($shops as $shop) {
            // Sending request to API. Passing api key to get orders list.
            //$urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key=' . $shop->api_key . '&format=json&format=json&status=editable';
            $urlGetOrders = 'http://webservice.rakuten.de/merchants/orders/getOrders?key=' . 'd6872f58e1ac5c8af686562c6e882ba4' . '&format=json&format=json&status=editable';

            // Fetching data from API
            $jsonDecodedResults = $this->curl($urlGetOrders);

            // Checking API response is success or failure and count of total data.
            if (($jsonDecodedResults['result']['success'] === '1') && ($jsonDecodedResults['result']['orders']['paging'][0]['total'] != '0')) {

                /* Log::info('API Key'); // Testing api key.
                Log::info($shop->api_key); // Testing api key.
                Log::info('--------------'); // Testing api key. */

                // Loop for extracting each orders
                //foreach ($jsonDecodedResults['result']['orders']['order'] as $key => $orderList) {

                    /* Log::info('Order List'); // Testing order list.
                    Log::info($orderList); // Testing order list.
                    Log::info('-------------'); // Testing order list. */

                    // Loop for extracting each items in orders
                    //foreach($orderList['items']['item'] as $item) {
                    $items = [1534883040, 1534959990, 1534960600, 2681013465, 2637884280];
                    foreach($items as $item) {
                        //Log::info('Product Item', $item['product_id']); // Testing product item.
                        Log::info('Product Item'); // Testing product item.
                        Log::info($item); // Testing product item.
                        Log::info('-----------'); // Testing product item.

                        $test .= 'Product Item'; // Testing product item.
                        $test .= $item; // Testing product item.
                        $test .= '-----------'; // Testing product item.

                        // Fetching module settings matching with product id.
                        //$moduleSetting = ModuleSetting::byProductId($item['product_id']);
                        $moduleSetting = ModuleSetting::byProductId($item);

                        Log::info('Module Setting'); // Testing module setting
                        Log::info($moduleSetting); // Testing module setting
                        Log::info('---------------'); // Testing module setting

                        $test .= 'Module Setting'; // Testing module setting
                        $test .= $moduleSetting; // Testing module setting
                        $test .= '---------------'; // Testing module setting

                        // Conditions to send cron job email.
                        // 1: Module settins status must be active.
                        // 2: Supplier id must be filled.
                        // 3: Cron status must be not sent.
                        if( (!empty($moduleSetting)) && ($moduleSetting->user_supplier_id !== null) && ($moduleSetting->status === 1) && $moduleSetting->cron_status === 0) {
                            Log::info('Success');
                            Log::info($moduleSetting);
                            Log::info('--------------');

                            $test .= 'Success';
                            $test .= $moduleSetting;
                            $test .= '--------------';

                            // Fetching supplier details 
                            $supplier = User::supplier()->active()->find($moduleSetting->user_supplier_id);

                            if( !empty($supplier) ) {

                                Log::info('Supplier');
                                Log::info($supplier->email);
                                Log::info('Done...');
                                
                                $test .= 'Supplier';
                                $test .= $supplier->email;
                                $test .= 'Done...';

                                // Send email to supplier
                                Mail::send(new SendEmailToSupplier($supplier));
                            }
                            else {
                                Log::info('Supplier not active');
                                Log::info('Supplier Id: '.$supplier->id. 'Supplier Email: '.$supplier->email);
                                Log::info('Done...');

                                $test .= 'Supplier not active';
                                $test .= 'Supplier Id: '.$supplier->id. 'Supplier Email: '.$supplier->email;
                                $test .= 'Done...';
                            }

                            

                            // TODO: If condition is okay, then send cron to module settings supplier id where supplier is active.
                    
                            // TODO: If supplier is not active info should store in to the Log.

                        }

                    }

                //}
                
            }
        //}

        dd($test);
    
    }