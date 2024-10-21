<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/update', [\App\Http\Controllers\AuthController::class, 'updateUser']);

Route::middleware('auth:sanctum')->group ( function()   {

    //Role Permission Start Here
    Route::group(['middleware' => 'CheckSuperAdmin'], function () {
    Route::post('/add-roleWithPermission',[\App\Http\Controllers\RolePermission\RolePermissionController::class, 'createRoleWithPermission']);
    Route::post('/update-roleWithPermission',[\App\Http\Controllers\RolePermission\RolePermissionController::class, 'updateRoleWithPermission']);
    Route::get('/allRole',[\App\Http\Controllers\RolePermission\RolePermissionController::class, 'allRole']);
    Route::get('/allPermission',[\App\Http\Controllers\RolePermission\RolePermissionController::class, 'allPermission']);
    Route::get('/view-all-users', [\App\Http\Controllers\AuthController::class, 'allUsers']);
    
    });
  //Role Permission End Here
   
    
    //2,3,4,5
    Route::prefix('customer')->group(function () {
        Route::post('/store', [\App\Http\Controllers\Customer\InformationController::class, 'store'])->middleware('checkPermission:customer.store');
        Route::get('/view', [\App\Http\Controllers\Customer\InformationController::class, 'view'])->middleware('checkPermission:customer.view');
        Route::post('/update', [\App\Http\Controllers\Customer\InformationController::class, 'update'])->middleware('checkPermission:customer.update');
        Route::get('/allAgent', [\App\Http\Controllers\Customer\InformationController::class, 'allAgentView'])->middleware('checkPermission:customer.view');
        Route::prefix('priceInformation')->group(function(){
            Route::post('/store', [\App\Http\Controllers\Customer\PriceInformationController::class, 'store']);
            Route::post('/update', [\App\Http\Controllers\Customer\PriceInformationController::class, 'update']);
            Route::get('/view', [\App\Http\Controllers\Customer\PriceInformationController::class, 'view']);
        });
        Route::prefix('paymentInformation')->group(function () {
            Route::post('/store', [\App\Http\Controllers\Customer\PaymentInformationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\Customer\PaymentInformationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\Customer\PaymentInformationController::class, 'update']);
            Route::get('/statementView/{id}', [\App\Http\Controllers\Customer\PaymentInformationController::class, 'statementView']);
        });
        Route::prefix('landAgreement')->group(function () {
            Route::post('/store', [\App\Http\Controllers\Customer\LandAgreementController::class, 'store']);
            Route::post('/update', [\App\Http\Controllers\Customer\LandAgreementController::class, 'update']);
            //5.1
            Route::get('/view', [\App\Http\Controllers\Customer\LandAgreementController::class, 'view']);
            // 5.2-5.5
            Route::get('/viewByDolilVariation/{id}', [\App\Http\Controllers\Customer\LandAgreementController::class, 'viewByDolilVariation']);
        });
    });

    //6
    Route::prefix('purchasedLandDetails')->group(function () {
        Route::get('/allList',[\App\Http\Controllers\PurchaseLandDetails\PurchasedLandAgreementController::class, 'allList']);
        Route::get('/viewByDolilVariation/{id}',[\App\Http\Controllers\PurchaseLandDetails\PurchasedLandAgreementController::class, 'viewByDolilVariation']);
    });

    //7
    Route::prefix('landSeller')->group(function () {
        Route::post('/store', [\App\Http\Controllers\LandSeller\InformationController::class, 'store']);
        Route::post('/update', [\App\Http\Controllers\LandSeller\InformationController::class, 'update']);
        Route::get('/view', [\App\Http\Controllers\LandSeller\InformationController::class, 'view']);
        Route::prefix('sellerAgreement')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandSeller\LandSellerAgreementController::class, 'store']);
            Route::post('/update', [\App\Http\Controllers\LandSeller\LandSellerAgreementController::class, 'update']);
            Route::get('/view', [\App\Http\Controllers\LandSeller\LandSellerAgreementController::class, 'view']);
        });
        Route::prefix('sellerPayment')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandSeller\LandSellerPaymentController::class, 'store']);
            Route::post('/update', [\App\Http\Controllers\LandSeller\LandSellerPaymentController::class, 'update']);
            Route::get('/view', [\App\Http\Controllers\LandSeller\LandSellerPaymentController::class, 'view']);
            Route::get('/singleView/{id}', [\App\Http\Controllers\LandSeller\LandSellerPaymentController::class, 'singleView']);
            Route::get('/paymentStatement', [\App\Http\Controllers\LandSeller\LandSellerPaymentController::class, 'viewPaymentStatement']);
        });
    });


   //10
 Route::prefix('agent')->group(function () {
    Route::post('/store', [\App\Http\Controllers\Agent\InformationController::class, 'store']);
    Route::get('/view', [\App\Http\Controllers\Agent\InformationController::class, 'view']);
    Route::post('/update', [\App\Http\Controllers\Agent\InformationController::class, 'update']);
    Route::get('/workList', [\App\Http\Controllers\Agent\InformationController::class, 'workList']);
    Route::post('/createPayment', [\App\Http\Controllers\Agent\AgentPaymentController::class, 'createPayment']);
    Route::post('/addPayment', [\App\Http\Controllers\Agent\AgentPaymentController::class, 'addPayment']);
    Route::post('/updatePayment', [\App\Http\Controllers\Agent\AgentPaymentController::class, 'updatePayment']);
    Route::get('/paymentStatement/{id}', [\App\Http\Controllers\Agent\AgentPaymentController::class, 'paymentStatement']);
});


    //11
    Route::prefix('landBroker')->group(function () {
        Route::post('/store', [\App\Http\Controllers\LandBroker\InformationController::class, 'store']);
        Route::get('/view', [\App\Http\Controllers\LandBroker\InformationController::class, 'view']);
        Route::post('/update', [\App\Http\Controllers\LandBroker\InformationController::class, 'update']);
        Route::get('/workList', [\App\Http\Controllers\LandBroker\InformationController::class, 'workList']);
        Route::post('/createPayment',[\App\Http\Controllers\LandBroker\LandBrokerPaymentController::class,'createPayment']);
        Route::post('/addPayment',[\App\Http\Controllers\LandBroker\LandBrokerPaymentController::class,'addPayment']);
        Route::post('/updatePayment', [\App\Http\Controllers\LandBroker\LandBrokerPaymentController::class, 'updatePayment']);
        Route::get('/paymentStatement', [\App\Http\Controllers\LandBroker\LandBrokerPaymentController::class, 'paymentStatement']);
    });

    //13
    Route::prefix('landInformationBank')->group(function () {
        Route::prefix('projectInformation')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandInformationBank\ProjectInformationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\LandInformationBank\ProjectInformationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\LandInformationBank\ProjectInformationController::class, 'update']);
        });
        Route::prefix('mouzaInformation')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandInformationBank\MouzaInformationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\LandInformationBank\MouzaInformationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\LandInformationBank\MouzaInformationController::class, 'update']);
        });
        Route::prefix('csDagKhatiyan')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandInformationBank\CSDagKhatiyanController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandInformationBank\CSDagKhatiyanController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandInformationBank\CSDagKhatiyanController::class, 'update']);
        });
        Route::prefix('saDagKhatiyan')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandInformationBank\SADagKhatiyanController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandInformationBank\SADagKhatiyanController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandInformationBank\SADagKhatiyanController::class, 'update']);
        });
        Route::prefix('rsDagKhatiyan')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandInformationBank\RSDagKhatiyanController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandInformationBank\RSDagKhatiyanController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandInformationBank\RSDagKhatiyanController::class, 'update']);
        });
        Route::prefix('bsDagKhatiyan')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandInformationBank\BSDagKhatiyanController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandInformationBank\BSDagKhatiyanController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandInformationBank\BSDagKhatiyanController::class, 'update']);
        });
    });

    //14
    Route::prefix('landMutationBank')->group(function () {
        Route::prefix('csMutationList')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandMutationBank\CSMutationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\LandMutationBank\CSMutationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\LandMutationBank\CSMutationController::class, 'update']);
        });
        Route::prefix('saMutationList')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandMutationBank\SAMutationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\LandMutationBank\SAMutationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\LandMutationBank\SAMutationController::class, 'update']);
        });
        Route::prefix('rsMutationList')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandMutationBank\RSMutationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\LandMutationBank\RSMutationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\LandMutationBank\RSMutationController::class, 'update']);
        });
        Route::prefix('bsMutationList')->group(function () {
            Route::post('/store', [\App\Http\Controllers\LandMutationBank\BSMutationController::class, 'store']);
            Route::get('/view', [\App\Http\Controllers\LandMutationBank\BSMutationController::class, 'view']);
            Route::post('/update', [\App\Http\Controllers\LandMutationBank\BSMutationController::class, 'update']);
        });
    });

    //15
    Route::prefix('landSubDeedBank')->group(function () {
        Route::prefix('csSubDeed')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandSubDeedBank\CSSubDeedController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandSubDeedBank\CSSubDeedController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandSubDeedBank\CSSubDeedController::class, 'update']);
        });
        Route::prefix('saSubDeed')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandSubDeedBank\SASubDeedController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandSubDeedBank\SASubDeedController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandSubDeedBank\SASubDeedController::class, 'update']);
        });
        Route::prefix('rsSubDeed')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandSubDeedBank\RSSubDeedController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandSubDeedBank\RSSubDeedController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandSubDeedBank\RSSubDeedController::class, 'update']);

        });
        Route::prefix('bsSubDeed')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandSubDeedBank\BSSubDeedController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandSubDeedBank\BSSubDeedController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandSubDeedBank\BSSubDeedController::class, 'update']);

        });
    });

    //16
    Route::prefix('landDisputeBank')->group(function () {
        Route::prefix('csDisputeList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandDisputeBank\CSDisputeController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandDisputeBank\CSDisputeController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandDisputeBank\CSDisputeController::class, 'update']);
        });
        Route::prefix('saDisputeList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandDisputeBank\SADisputeController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandDisputeBank\SADisputeController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandDisputeBank\SADisputeController::class, 'update']);
        });
        Route::prefix('rsDisputeList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandDisputeBank\RSDisputeController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandDisputeBank\RSDisputeController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandDisputeBank\RSDisputeController::class, 'update']);
        });
        Route::prefix('bsDisputeList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandDisputeBank\BSDisputeController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandDisputeBank\BSDisputeController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandDisputeBank\BSDisputeController::class, 'update']);
        });

    });

    //17
    Route::prefix('landMouzaMapBank')->group(function () {
        Route::prefix('csMouzaMapList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandMouzaMapBank\CSMouzaMapController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandMouzaMapBank\CSMouzaMapController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandMouzaMapBank\CSMouzaMapController::class, 'update']);
        });
        Route::prefix('saMouzaMapList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandMouzaMapBank\SAMouzaMapController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandMouzaMapBank\SAMouzaMapController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandMouzaMapBank\SAMouzaMapController::class, 'update']);
        });
        Route::prefix('rsMouzaMapList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandMouzaMapBank\RSMouzaMapController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandMouzaMapBank\RSMouzaMapController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandMouzaMapBank\RSMouzaMapController::class, 'update']);
        });
        Route::prefix('bsMouzaMapList')->group(function () {
            Route::post('/store',[\App\Http\Controllers\LandMouzaMapBank\BSMouzaMapController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\LandMouzaMapBank\BSMouzaMapController::class, 'view']);
            Route::post('/update',[\App\Http\Controllers\LandMouzaMapBank\BSMouzaMapController::class, 'update']);
        });

    });

    //18
    Route::prefix('plotBank')->group(function () {
        //18.1
        Route::post('/store',[\App\Http\Controllers\PlotBank\InformationController::class, 'store']);
        Route::post('/update',[\App\Http\Controllers\PlotBank\InformationController::class, 'update']);
        Route::get('/view',[\App\Http\Controllers\PlotBank\InformationController::class, 'view']);
        //18.2
        Route::get('/bookedPlots',[\App\Http\Controllers\PlotBank\InformationController::class, 'bookedPlots']);
        //18.3
        Route::get('/remainingPlots',[\App\Http\Controllers\PlotBank\InformationController::class, 'remainingPlots']);
        //18.4
        Route::get('/registryCompletePlots',[\App\Http\Controllers\PlotBank\InformationController::class, 'registryCompletePlots']);
        //18.5
        Route::prefix('mutationComplete')->group(function () {
            Route::post('/store',[\App\Http\Controllers\PlotBank\MutationController::class, 'store']);
            Route::get('/view',[\App\Http\Controllers\PlotBank\MutationController::class, 'view']);
        });
        //18.6
        Route::get('/paymentCompletePlots',[\App\Http\Controllers\PlotBank\InformationController::class, 'paymentCompletePlots']);
        //18.7

    });

    //Others
    Route::prefix('dolilVaritation')->group(function () {
        Route::get('/list',[\App\Http\Controllers\LandSeller\InformationController::class, 'dolilVaritation']);
    });
});

