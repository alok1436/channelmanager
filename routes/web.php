<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\LoginController;
// use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\SettingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/',                                 'DashboardController@index')->name('home');
Route::get('/gls_csv',                          'Controller@gls_csv')->name('gls_csv');
Route::get('/checkwoocommerce',                 'OrderController@checkwoocommerce')->name('checkwoocommerce');
Route::get('amazon/orders',                     'OrderController@amazonOrders')->name('amazonOrders');
Route::get('order/mark/paid/{id}',              'OrderController@markPaid')->name('markPaid');
Route::get('/dashboard',                        'DashboardController@index')->name('dashboard');
Route::get('/fixWarning/{id}',                  'DashboardController@fixWarning')->name('fixWarning');

Route::get('/login',                            'LoginController@index')->name('login');
Route::post('/dologin',                         'LoginController@dologin')->name('dologin');
Route::get('/logout',                           'LoginController@logout')->name('logout');
Route::get('/passwordresetview',                'LoginController@passwordresetview')->name('passwordresetview');
Route::post('/passwordresetemail',              'LoginController@passwordresetemail')->name('passwordresetemail');
Route::post('/passwordreset',                   'LoginController@passwordreset')->name('passwordreset');

Route::get('/manufacturer',                     'SettingController@manufacturer')->name('manufacturer');
Route::get('/manufacturerDelete',               'SettingController@manufacturerDelete')->name('manufacturerDelete');
Route::get('/manufacturerUpdate',               'SettingController@manufacturerUpdate')->name('manufacturerUpdate');
Route::get('/manufacturerAddView',              'SettingController@manufacturerAddView')->name('manufacturerAddView');
Route::post('/manufacturerAdd',                 'SettingController@manufacturerAdd')->name('manufacturerAdd');
Route::get('/warehouseView',                    'SettingController@warehouseView')->name('warehouseView');
Route::get('/warehouseDelete',                  'SettingController@warehouseDelete')->name('warehouseDelete');
Route::get('/warehouseAddView',                 'SettingController@warehouseAddView')->name('warehouseAddView');
Route::post('/warehouseAdd',                    'SettingController@warehouseAdd')->name('warehouseAdd');
Route::get('/warehouseUpdate',                  'SettingController@warehouseUpdate')->name('warehouseUpdate');
Route::get('/channelView',                      'SettingController@channelView')->name('channelView');
Route::get('/channelUpdate',                    'SettingController@channelUpdate')->name('channelUpdate');
Route::get('/channeldelete',                    'SettingController@channeldelete')->name('channeldelete');
Route::get('/channelAddView',                   'SettingController@channelAddView')->name('channelAddView');
Route::get('/channelEditView',                  'SettingController@channelEditView')->name('channelEditView');
Route::post('/channelAdd',                      'SettingController@channelAdd')->name('channelAdd');
Route::post('/channelEdit',                     'SettingController@channelEdit')->name('channelEdit');
Route::get('/paymentView',                      'SettingController@paymentView')->name('paymentView');
Route::get('/paymentUpdate',                    'SettingController@paymentUpdate')->name('paymentUpdate');
Route::get('/paymentAddView',                   'SettingController@paymentAddView')->name('paymentAddView');
Route::get('/paymentDelete',                    'SettingController@paymentDelete')->name('paymentDelete');
Route::post('/paymentAdd',                      'SettingController@paymentAdd')->name('paymentAdd');
Route::get('/userView',                         'SettingController@userView')->name('userView');
Route::get('/userAddView',                      'SettingController@userAddView')->name('userAddView');
Route::get('/userEditView',                     'SettingController@userEditView')->name('userEditView');
Route::post('/userAdd',                         'SettingController@userAdd')->name('userAdd');
Route::get('/userDelete',                       'SettingController@userDelete')->name('userDelete');
Route::get('/platformView',                     'SettingController@platformView')->name('platformView');
Route::get('/platformDelete',                   'SettingController@platformDelete')->name('platformDelete');
Route::get('/platformUpdate',                   'SettingController@platformUpdate')->name('platformUpdate');
Route::get('/countryView',                      'SettingController@countryView')->name('countryView');
Route::get('/countryAddView',                   'SettingController@countryAddView')->name('countryAddView');
Route::post('/countryAdd',                      'SettingController@countryAdd')->name('countryAdd');
Route::get('/countryDelete',                    'SettingController@countryDelete')->name('countryDelete');
Route::get('/countryUpdate',                    'SettingController@countryUpdate')->name('countryUpdate');
Route::get('/currencyView',                     'SettingController@currencyView')->name('currencyView');
Route::get('/currencyAddView',                  'SettingController@currencyAddView')->name('currencyAddView');
Route::post('/currencyAdd',                     'SettingController@currencyAdd')->name('currencyAdd');
Route::get('/currencyDelete',                   'SettingController@currencyDelete')->name('currencyDelete');
Route::get('/currencyUpdate',                   'SettingController@currencyUpdate')->name('currencyUpdate');
Route::get('/companyView',                      'SettingController@companyView')->name('companyView');
Route::get('/companyAddView',                   'SettingController@companyAddView')->name('companyAddView');
Route::post('/companyAdd',                      'SettingController@companyAdd')->name('companyAdd');
Route::get('/companyDelete',                    'SettingController@companyDelete')->name('companyDelete');
Route::get('/companyUpdate',                    'SettingController@companyUpdate')->name('companyUpdate');
Route::get('/courierView',                      'SettingController@courierView')->name('courierView');
Route::get('/courierAddView',                   'SettingController@courierAddView')->name('courierAddView');
Route::post('/courierAdd',                      'SettingController@courierAdd')->name('courierAdd');
Route::get('/courierDelete',                    'SettingController@courierDelete')->name('courierDelete');
Route::get('/courierUpdate',                    'SettingController@courierUpdate')->name('courierUpdate');
Route::get('/categoryView',                     'SettingController@categoryView')->name('categoryView');
Route::get('/categoryAddView',                  'SettingController@categoryAddView')->name('categoryAddView');
Route::post('/categoryAdd',                     'SettingController@categoryAdd')->name('categoryAdd');
Route::get('/categoryDelete',                   'SettingController@categoryDelete')->name('categoryDelete');
Route::get('/categoryUpdate',                   'SettingController@categoryUpdate')->name('categoryUpdate');
Route::get('/subcategoryView',                  'SettingController@subcategoryView')->name('subcategoryView');
Route::get('/subcategoryAddView',               'SettingController@subcategoryAddView')->name('subcategoryAddView');
Route::post('/subcategoryAdd',                  'SettingController@subcategoryAdd')->name('subcategoryAdd');
Route::get('/subcategoryDelete',                'SettingController@subcategoryDelete')->name('subcategoryDelete');
Route::get('/subcategoryUpdate',                'SettingController@subcategoryUpdate')->name('subcategoryUpdate');
Route::get('/vendordepotView',                  'SettingController@vendordepotView')->name('vendordepotView');
Route::get('/vendordepotUpdate',                'SettingController@vendordepotUpdate')->name('vendordepotUpdate');
Route::get('/vendordepotAddView',               'SettingController@vendordepotAddView')->name('vendordepotAddView');
Route::post('/vendordepotAdd',                  'SettingController@vendordepotAdd')->name('vendordepotAdd');
Route::post('/sdaCourierEdit',                  'SettingController@sdaCourierEdit')->name('sdaCourierEdit');
Route::get('/channelCountry',                   'SettingController@channelCountryView')->name('channelCountryView');
Route::get('/channelCountryUpdate',             'SettingController@channelCountryUpdate')->name('channelCountryUpdate');
Route::get('/channelCountryAddView',            'SettingController@channelCountryAddView')->name('channelCountryAddView');
Route::post('/channelCountryAdd',               'SettingController@channelCountryAdd')->name('channelCountryAdd');
Route::get('/channelCountryDelete/{id}',        'SettingController@channelCountryDelete')->name('channelCountryDelete');
Route::post('/uploadCompanyLogo',               'SettingController@uploadCompanyLogo')->name('uploadCompanyLogo');
Route::get('/generalSettingView',               'SettingController@generalSettingView')->name('generalSettingView');
Route::post('/updateGeneralSetting',            'SettingController@updateGeneralSetting')->name('updateGeneralSetting');
Route::get('/exportQuantity',                  'InventoryController@exportQuantity')->name('exportQuantity');

Route::get('/productView',                      'ProductController@productView')->name('productView');
Route::get('/productAddView',                   'ProductController@productAddView')->name('productAddView');
Route::get('/productEditView',                  'ProductController@productEditView')->name('productEditView');
Route::post('/productEdit',                     'ProductController@productEdit')->name('productEdit');
Route::post('/productAdd',                      'ProductController@productAdd')->name('productAdd');
Route::get('/productDelete',                    'ProductController@productDelete')->name('productDelete');
Route::get('/productUpdate',                    'ProductController@productUpdate')->name('productUpdate');
Route::post('/productXlsImport',                'ProductController@productXlsImport')->name('productXlsImport');
Route::get('/productXlsExport',                 'ProductController@productXlsExport')->name('productXlsExport');
Route::get('/newproductDelete',                 'ProductController@newproductDelete')->name('newproductDelete');
Route::get('/getNewWoocommerceProducts',        'ProductController@getNewWoocommerceProducts')->name('getNewWoocommerceProducts');
Route::get('/offlineProductsView',              'ProductController@offlineProductsView')->name('offlineProductsView');
Route::post('/addBlackList',                    'ProductController@addBlackList')->name('addBlackList');
Route::get('/removeFromBlackList',              'ProductController@removeFromBlackList')->name('removeFromBlackList');
Route::get('/blacklistView',                    'ProductController@blacklistView')->name('blacklistView');
Route::post('/searchProduct',                   'ProductController@searchProduct')->name('searchProduct');
Route::get('ajax/prodcut/list',                 'ProductController@productlist')->name('ajax.productlist');

Route::get('/manufacturerorderView',            'SupplyController@manufacturerorderView')->name('manufacturerorderView');
Route::post('/confirmNewContainer',             'SupplyController@confirmNewContainer')->name('confirmNewContainer');
Route::get('/containerconfirmView',             'SupplyController@containerconfirmView')->name('containerconfirmView');
Route::get('/containerconfirmDelete',           'SupplyController@containerconfirmDelete')->name('containerconfirmDelete');
Route::post('/sendManufacturerOrder',           'SupplyController@sendManufacturerOrder')->name('sendManufacturerOrder');
Route::get('/warehouseTransferFirstView',       'SupplyController@warehouseTransferFirstView')->name('warehouseTransferFirstView');
Route::post('/warehouseTransferSecondView',     'SupplyController@warehouseTransferSecondView')->name('warehouseTransferSecondView');
Route::post('/confirmWarehouseTransfer',        'SupplyController@confirmWarehouseTransfer')->name('confirmWarehouseTransfer');
Route::get('/warehouseconfirmView',             'SupplyController@warehouseconfirmView')->name('warehouseconfirmView');
Route::post('/transferWarehouse',               'SupplyController@transferWarehouse')->name('transferWarehouse');
Route::get('/warehouseconfirmDel',              'SupplyController@warehouseconfirmDel')->name('warehouseconfirmDel');

Route::get('/modelView',                        'ModelController@modelView')->name('modelView');
Route::get('/modelshippingUpdate',              'ModelController@modelshippingUpdate')->name('modelshippingUpdate');
Route::get('/modelfeesUpdate',                  'ModelController@modelfeesUpdate')->name('modelfeesUpdate');
Route::get('/modelamazonshippingcostUpdate',    'ModelController@modelamazonshippingcostUpdate')->name('modelamazonshippingcostUpdate');
Route::get('/modelvatUpdate',                   'ModelController@modelvatUpdate')->name('modelvatUpdate');
Route::post('/addNewShippingModel',             'ModelController@addNewShippingModel')->name('addNewShippingModel');
Route::post('/addNewFeesModel',                 'ModelController@addNewFeesModel')->name('addNewFeesModel');
Route::post('/addNewVatModel',                  'ModelController@addNewVatModel')->name('addNewVatModel');
Route::post('/addNewAmazonShippingCostModel',   'ModelController@addNewAmazonShippingCostModel')->name('addNewAmazonShippingCostModel');

Route::get('/orderView',                        'OrderController@orderView')->name('orderView');
//Route::get('/orderView2',                        'OrderController2@orderView2')->name('orderView');
Route::get('/getInvoiceForm',                      'OrderController@getInvoiceForm')->name('getInvoiceForm');
Route::get('/orderUpdate',                      'OrderController@orderUpdate')->name('orderUpdate');
Route::get('/orderDelete',                      'OrderController@orderDelete')->name('orderDelete');
Route::get('/orderAddView',                     'OrderController@orderAddView')->name('orderAddView');
Route::post('/orderAdd',                        'OrderController@orderAdd')->name('orderAdd');
Route::post('/importOrderFile',                 'OrderController@importOrderFile')->name('importOrderFile');
Route::get('/createCSV',                        'OrderController@createCSV')->name('createCSV');
Route::get('/sendToPlatform',                   'OrderController@sendToPlatform')->name('sendToPlatform');
Route::get('/send_to_platform',                 'OttoController@orderSendToPlatform')->name('order.send_to_platform');
Route::get('/printDocuments',                   'OrderController@printDocuments')->name('printDocuments');
Route::post('/addAmazonAddress',                'OrderController@addAmazonAddress')->name('addAmazonAddress');
Route::get('/getWoocommerceOrders',             'OrderController@getWoocommerceOrders')->name('getWoocommerceOrders');
Route::get('/createOrderInvoice',               'OrderController@createOrderInvoice')->name('createOrderInvoice');
Route::post('/editInvoiceData',                 'OrderController@editInvoiceData')->name('editInvoiceData');
Route::get('order/invoice/create/{id}',         'OrderController@orderInvoiceCreate')->name('orderInvoiceCreate');
Route::get('order/documents',         			'OrderController@reportDocuments')->name('order.documents');
Route::get('order/documents/download',         	'OrderController@reportDocumentsDownload')->name('order.documents.download');

Route::get('/soldweeklyView',                   'SoldweeklyController@soldweeklyView')->name('soldweeklyView');

Route::get('/finanzstatusView',                 'FinanzstatusController@index')->name('finanzstatusView');
Route::get('/finanzstatusUpdate',               'FinanzstatusController@finanzstatusUpdate')->name('finanzstatusUpdate');
Route::get('ajax/finanzstatus/get',              'FinanzstatusController@ajaxFinanzstatus')->name('ajax.finanzstatus');

Route::get('/FBAView',                          'FBAController@index')->name('FBAView');
Route::post('/importFBAFile',                   'FBAController@importFBAFile')->name('importFBAFile');
Route::get('/fbaUpdate',                        'FBAController@fbaUpdate')->name('fbaUpdate');
Route::get('/createExcel',                      'FBAController@createExcel')->name('createExcel');
Route::get('/FBAontheway',                      'FBAController@FBAontheway')->name('FBAontheway');
Route::get('/setArrived',                       'FBAController@setArrived')->name('setArrived');
Route::get('/removeShippedFBA',                 'FBAController@removeShippedFBA')->name('removeShippedFBA');
Route::get('/fbaShippedUpdate',                 'FBAController@fbaShippedUpdate')->name('fbaShippedUpdate');
Route::post('/integrityFBAFile',                'FBAController@integrityFBAFile')->name('integrityFBAFile');
Route::get('/fbadelete',                        'FBAController@fbadelete')->name('fbadelete');

Route::get('/priceView',                        'PriceController@index')->name('priceView');
Route::get('/price/delete/{id}',                'PriceController@deletePrice')->name('deletePrice');
Route::get('/price/update/{channelId}',         'PriceServiceController@index');
Route::get('/get_shipping_model_data',          'PriceController@get_shipping_model_data')->name('get_shipping_model_data');
Route::get('/get_fees_model_data',              'PriceController@get_fees_model_data')->name('get_fees_model_data');
Route::get('/get_vat_model_data',               'PriceController@get_vat_model_data')->name('get_vat_model_data');
Route::get('/get_auto_price',                   'PriceController@get_auto_price')->name('get_auto_price');
Route::get('/get_manual_price',                 'PriceController@get_manual_price')->name('get_manual_price');
Route::get('/priceUpdate',                      'PriceController@priceUpdate')->name('priceUpdate');
Route::post('/importPriceFile',                 'PriceController@importPriceFile')->name('importPriceFile');
Route::get('/createUploadFiles',                'PriceController@createUploadFiles')->name('createUploadFiles');
Route::post('/uploadShippingCost',              'PriceController@uploadShippingCost')->name('uploadShippingCost');
Route::get('/noneexistingproducts',             'PriceController@noneexistingproducts')->name('noneexistingproducts');
Route::get('/newProductUpdate',                 'PriceController@newProductUpdate')->name('newProductUpdate');
Route::get('/getWoocommercePriceandQuantity',   'PriceController@getWoocommercePriceandQuantity')->name('getWoocommercePriceandQuantity');
Route::get('/wcUpdateStoreData/{channelId}',    'PriceServiceController@wcUpdateStoreData')->name('wcUpdateStoreData');
Route::get('/wcUpdateStoreData/{channelId}',    'OttoController@wcUpdateStoreData')->name('wcUpdateStoreData');

Route::get('/vendorView',                       'VendorController@vendorView')->name('vendorView');
Route::post('/importVendorFile',                'VendorController@importVendorFile')->name('importVendorFile');
Route::get('/vendorUpdate',                     'VendorController@vendorUpdate')->name('vendorUpdate');
Route::get('/sendvendororder',                  'VendorController@sendvendororder')->name('sendvendororder');
Route::get('/vendorBlackList',                  'VendorController@vendorBlackList')->name('vendorBlackList');
Route::get('/vendorblacklistaddview',           'VendorController@vendorblacklistaddview')->name('vendorblacklistaddview');
Route::post('/vendorblacklistadd',              'VendorController@vendorblacklistadd')->name('vendorblacklistadd');
Route::get('/deletevendorblacklist/{idblacklist}',  'VendorController@deletevendorblacklist')->name('deletevendorblacklist');

Route::get('/trendView',                        'ForecastController@trendView')->name('trendView');
Route::get('/trendAddView',                     'ForecastController@trendAddView')->name('trendAddView');
Route::get('/trendUpdate',                      'ForecastController@trendUpdate')->name('trendUpdate');
Route::post('/trendAdd',                        'ForecastController@trendAdd')->name('trendAdd');
Route::get('/testAPI',                          'ForecastController@testAPI')->name('testAPI');
Route::get('/forcastOutput',                    'ForecastController@forcastOutput')->name('forcastOutput');
Route::get('/calculate',                        'ForecastController@calculate')->name('calculate');
Route::get('/caltrend',                         'ForecastController@caltrend')->name('caltrend');
Route::post('/sendCalculateManufacturerOrder',  'ForecastController@sendCalculateManufacturerOrder')->name('sendCalculateManufacturerOrder');

Route::get('/createKit',                        'KitController@index')->name('createKit');
Route::get('/bufferUpdate',                     'KitController@bufferUpdate')->name('bufferUpdate');
Route::get('/sendNewKit',                       'KitController@sendNewKit')->name('sendNewKit');

Route::get('/inventoryView',                    'InventoryController@inventoryView')->name('inventoryView');
Route::get('/inventoryView2',                   'InventoryController@inventoryView2')->name('inventoryView2');
Route::get('ajax/invertory/get',                'InventoryController@ajaxInventory')->name('ajax.inventory');
Route::get('otto/get/orders',                	'OttoController@getOrders')->name('otto.orders');
Route::get('otto/get/{type}',                	'OttoController@downloadAssets')->name('otto.assets');
Route::get('ottoUpdateStoreData/{channelId}',  'OttoController@updatePriceAndQuantity')->name('updatePriceAndQuantity');
Route::get('fba/quantity',                		'FBAController@getQuantity');

Route::get('ebay/connect/{id}',                	'EbayController@connect')->name('ebay.connect');
Route::get('ebay/callback',                		'EbayController@callback')->name('ebay.callback');
Route::get('ebay/downloadReport',   			'EbayController@downloadReport')->name('ebay.downloadReport');
Route::get('ebay/updatePrice/{channelId}',      'EbayController@updatePrice')->name('ebay.updatePrice');
Route::get('ebay/updateQuantity/{channelId}',   'EbayController@updateQuantity')->name('ebay.updateQuantity');
Route::get('order/sync',   						'CronController@test');


Route::get('woo/downloadReport',   			   'WooController@downloadReport')->name('woo.downloadReport');
Route::get('woo/updatePrice/{channelId}',      'WooController@updatePrice')->name('woo.updatePrice');
Route::get('woo/updateQuantity/{channelId}',   'WooController@updateQuantity')->name('woo.updateQuantity');
