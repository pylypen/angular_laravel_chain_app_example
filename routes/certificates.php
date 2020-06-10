<?php

Route::get('/test/{certificate_name?}', 'CertificatesController@test');

Route::get('/{certificate_name?}', 'CertificatesController@view');

