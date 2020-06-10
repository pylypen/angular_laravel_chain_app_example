<?php

$onlyAccess = [
    'index',
    'store',
    'show',
    'destroy'
];


/* Stripe Webhook Route */
Route::post('stripe/webhook', 'WebhookController@handleWebhook');

Route::get('/', function () {

    return view('welcome');

});
Route::post('auth/login', 'AuthController@login');

Route::group(['middleware' => 'jwt.auth'], function () use ($onlyAccess) {

    Route::get('auth/check', 'AuthController@check');
    
    Route::get('auth/user', 'AuthController@user');

    Route::post('auth/logout', 'AuthController@logout');

    Route::get('users/admins', 'UserController@admins');
    
    Route::resource('users', 'UserController', [
        'only' => $onlyAccess
    ]);
    Route::post('/users/update/{id}', 'UserController@update');
    Route::post('/users/updateAdmins/{id}', 'UserController@updateAdmins');
    Route::post('/users/storeAdmin', 'UserController@storeAdmin');

    
    Route::get('/organisations/all', 'OrganisationsController@all')->name('v1/organisations/all');
    Route::resource('organisations', 'OrganisationsController', [
        'only' => $onlyAccess
    ]);
    Route::post('/organisations/update/{id}', 'OrganisationsController@update')->name('v1/organisations/update');

    Route::get('/org_users/{org_id}','OrganisationsController@usersOrg');

    Route::post('users/confirm_code', 'UserController@generateConfirmCode');

    Route::post('/update_owner/{org_id}','OrganisationsController@updateOwner');

    Route::get('/get_users_csv', 'UserController@usersSnapshotCsv');
    Route::get('/get_org_csv', 'OrganisationsController@orgSnapshotCsv');

    Route::post('users/delete_user_organisation', 'UserController@deleteUserOrganisation');
    Route::post('users/set_organisation_setting', 'UserController@setOrganisationSetting');
    

});
Route::group(['middleware' => 'jwt.refresh'], function () {

    Route::get('auth/refresh', 'AuthController@refresh');

});
