<?php

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

$onlyAccess = [
    'index',
    'store',
    'show',
    'destroy'
];

Route::group(['prefix' => 'v1/', 'namespace' => 'v1'], function () use ($onlyAccess) {
    
    /* ********** */
    /* PUBLIC GROUP */
    
    Route::post('/login', 'Auth\Auth@login');

    Route::post('/confirm_code', 'Auth\ConfirmEmailController@update');
    Route::get('/confirm_code/{code}', 'Auth\ConfirmEmailController@getData');
    
    Route::post('/forgot_password', 'Auth\ForgotPasswordController@sendResetLinkEmail');

    Route::get('/forgot_password/question-by-email', 'Auth\ForgotPasswordController@getQuestionByEmail');

    Route::post('/reset_password', 'Auth\ResetPasswordController@reset');

    Route::group(['prefix' => 'system/'], function () {
        Route::get('/secret_questions', 'Settings\SystemController@secret_questions');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::post('before_store', 'Users\UserController@beforeStore');
    });

    Route::post('/organisation/store', 'Organisations\OrganisationController@store');

    /* ********** */
    /* AUTH GROUP */

    Route::group(['middleware' => ['jwt.auth']], function () use ($onlyAccess) {

        /* PEOPLE GROUP */
        
        Route::group(['prefix' => 'people', 'namespace' => 'People'], function () {

            Route::get('/list', 'PeopleController@list');

            Route::group(['middleware' => ['api.org_admin']], function () {
                Route::post('/toggle_admin/{user_id}', 'PeopleController@toggleAdmin');
                Route::post('/updateUser/{user_id}', 'PeopleController@updateUser');
                Route::post('/createUser', 'PeopleController@createUser');
                Route::post('/reinvite/{user_id}', 'PeopleController@reinvite');

                Route::delete('/deleteUser/{user_id}', 'PeopleController@deleteUser');
            });
        });

        /* SETTINGS GROUP */

        Route::group(['prefix' => 'settings', 'namespace' => 'Settings'], function () {

            Route::get('/config', 'SettingsController@getConfig');

            Route::get('/personal_info', 'SettingsController@getPersonalInfo');
            Route::post('/personal_info', 'SettingsController@updatePersonalInfo');

            Route::post('/change_password', 'SettingsController@updatePassword');

            Route::get('/account_settings', 'SettingsController@getAccountSettings');
            Route::post('/account_settings', 'SettingsController@updateAccountSettings');

            

            /* SETTINGS DEVELOPERS GROUP */
            Route::group(['middleware' => ['api.org_admin']], function () {
                Route::get('/get_developers_accs', 'SettingsController@getListDevAcc');
                Route::post('/add_developer_acc', 'SettingsController@addDevAcc');
                Route::post('/delete_developer_acc', 'SettingsController@deleteDevAcc');
            });
        });

        Route::group(['prefix' => 'settings', 'namespace' => 'Certificates'], function () {
            Route::get('/certificates', 'CertificatesController@getList');
        });
        

        /* SITES GROUP */

        Route::group(['prefix' => 'sites', 'namespace' => 'Sites'], function () {

            Route::get('/list', 'SiteController@index');
            Route::get('/settings_config/{id?}', 'SiteController@getSiteSettingsConfig');
            Route::get('/members_config/{id}', 'SiteController@getSiteMembersConfig');

            Route::post('/update/{id?}', 'SiteController@updateSiteSettings');
            Route::post('/update_members/{id}', 'SiteController@updateSiteMembers');

        });

        /* TEAMS GROUP */

        Route::group(['prefix' => 'teams', 'namespace' => 'Teams'], function () {

            Route::get('/list', 'TeamController@index');

            Route::get('/settings_config/{id?}', 'TeamController@getTeamSettingsConfig');
            Route::get('/members_config/{id}', 'TeamController@getTeamMembersConfig');

            Route::post('/renew_settings_config', 'TeamController@renewTeamSettingsConfig');

            Route::post('/update/{id?}', 'TeamController@updateTeamSettings');
            Route::post('/update_members/{id}', 'TeamController@updateTeamMembers');
        });

        /* COURSE GROUP */

        Route::group(['prefix' => 'courses', 'namespace' => 'Courses'], function () {

            Route::get('/list', 'CourseController@getList');
            Route::get('/managing_list', 'CourseController@getManagingList');
            Route::get('/grading/details/{id}', 'CourseController@getGrading');

            Route::get('/details/{id}', 'CourseController@getCourse');

            Route::get('/publish/{id}', 'CourseController@getPublishConfig');

            Route::post('/create', 'CourseController@createCourse');
            Route::post('/update_details/{id}', 'CourseController@updateDetails');
            Route::post('/update_order/{id}', 'CourseController@updateLessonsOrder');

            Route::post('/reset_thumbnail/{id}', 'CourseController@resetThumbnail');
            Route::post('/reset_featured_background/{id}', 'CourseController@resetFeaturedBackground');

            Route::get('/media_types_list/{lesson_id}','LessonMediaOrderController@media_types_list');
            Route::post('/save_media_order','LessonMediaOrderController@save_media_order');

            Route::delete('/{id}', 'CourseController@deleteCourse');

        });


        /* MARKETPLACE GROUP */

        Route::group(['prefix' => 'marketplace', 'namespace' => 'Courses'], function () {

            Route::get('/approval_settings/{course_id}', 'MarketplaceController@getMarketplaceApprovals');
            Route::get('/assigns/{course_id}', 'MarketplaceController@getMarketplaceAssigns');

            Route::get('/details/{course_id}', 'MarketplaceController@getStatsDetails');

            Route::post('/show_hide', 'MarketplaceController@showHide');
            Route::post('/request_review', 'MarketplaceController@requestReview');
            Route::post('/submit_review', 'MarketplaceController@submitReview');
            Route::post('/team_assign', 'MarketplaceController@wildcardAssign');
            Route::post('/personal_assign', 'MarketplaceController@personalAssign');

        });

        /* LESSONS GROUP */

        Route::group(['prefix' => 'lessons', 'namespace' => 'Courses'], function () {
            Route::get('/details/{id}', 'LessonController@getLesson');

            Route::post('/create', 'LessonController@createLesson');
            Route::post('/update_details/{id}', 'LessonController@updateDetails');
            Route::post('/upload_media/{id}', 'LessonController@uploadMedia');
            Route::post('/add_youtube_media/{id}', 'LessonController@addYoutubeMedia');
            Route::post('/update_progress/{id}', 'LessonController@updateLessonProgress');

            Route::delete('/{id}', 'LessonController@deleteLesson');
            Route::delete('/delete_youtube_media/{media_id}', 'LessonController@deleteYoutubeMedia');
            Route::delete('/delete_media/{media_id}', 'LessonController@deleteMedia');
        });


        /* LESSON COMMENTS GROUP */

        Route::group(['prefix' => 'lesson_comments', 'namespace' => 'Courses'], function () {
            Route::get('/list/{lesson_id}', 'LessonCommentsController@list');

            Route::post('/create', 'LessonCommentsController@create');
            Route::post('/update', 'LessonCommentsController@update');
            Route::delete('/{id}', 'LessonCommentsController@delete');

        });


        /* STATISTICS GROUP */

        Route::group(['prefix' => 'statistics', 'namespace' => 'Statistics'], function () {
            Route::get('/courses', 'StatisticsController@courses');
        });

        /* ********** */
        /* USER GROUP */

        Route::post('/logout ', 'Auth\Auth@logout');

        Route::resource('chat', 'Settings\ChatController', [
            'only' => ['index', 'store', 'show']
        ]);

        Route::group(['prefix' => 'user', 'namespace' => 'Users'], function () {
            Route::post('update', 'UserController@update');
            Route::get('courses_snapshot/{user_id}', 'vUserController@getCoursesSnapshot');
        });

        Route::resource('user', 'Users\UserController', [
            'only' => [
                'show',
                'destroy'
            ]
        ]);

        Route::get('/user/courses_snapshot/{user_id}', 'Users\UserController@getCoursesSnapshot');
        Route::get('/user/get_csv/{course_id}', 'Users\UserController@UsersSnapshotCsv');

        Route::resource('secret_question', 'Settings\SecretQuestionsController', [
            'only' => $onlyAccess
        ]);
        Route::post('/secret_question/update ', 'Settings\SecretQuestionsController@update');

        Route::resource('organisation', 'Organisations\OrganisationController', [
            'only' => [
                'index',
                'show'
            ]
        ]);
        Route::post('/organisation/update ', 'Organisations\OrganisationController@update');

        /* ********** */
        /* Certificates GROUP */

        Route::group(['prefix' => 'certificates', 'namespace' => 'Certificates'], function () {
            Route::post('/request', 'CertificatesController@create');
            
            Route::post('/email_request', 'CertificatesController@createByEmail');

        });

        /* ********** */
        /* Payment Subscriptions GROUP */

        Route::group(['prefix' => 'subscriptions', 'namespace' => 'Subscriptions'], function () {
            Route::get('check', 'SubscriptionsController@checkSubscription');

            Route::group(['middleware' => ['api.org_owner']], function () {
                Route::post('create', 'SubscriptionsController@subscribe');
                Route::get('cancel', 'SubscriptionsController@cancelSubscription');
                Route::get('list/plans', 'SubscriptionsController@listPlans');
            });
        });
    });


    //Route::post('/refresh', 'v1\Auth@refresh')->name('refresh');

});

