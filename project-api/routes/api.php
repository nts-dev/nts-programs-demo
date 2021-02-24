<?php

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'prefix' => 'v1/auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::group(['prefix' => 'v1/user'], function () {
    Route::post('create', ['as' => 'user.create', 'uses' => 'UserController@store']);
    Route::get('list', ['as' => 'user.index', 'uses' => 'UserController@index']);
    Route::get('details/{id}', ['as' => 'user.details', 'uses' => 'UserController@show']);
    Route::post('update/{id}', ['as' => 'user.update', 'uses' => 'UserController@update']);
    Route::get('delete/{id}', ['as' => 'user.delete', 'uses' => 'UserController@destroy']);
    Route::post('projects/add/{id}', ['as' => 'user.add_project', 'uses' => 'UserController@addProjects']);
    Route::get('projects/get/{id}', ['as' => 'user.get_project', 'uses' => 'UserController@getProjects']);
});

Route::group(['prefix' => 'v1/role'], function () {
    Route::post('create', ['as' => 'role.create', 'uses' => 'RolesController@store']);
    Route::get('list', ['as' => 'role.index', 'uses' => 'RolesController@index']);
    Route::post('update/{id}', ['as' => 'role.update', 'uses' => 'RolesController@update']);
    Route::get('selectlist', ['as' => 'role.select_list', 'uses' => 'RolesController@getRolesList']);
});

Route::group(['prefix' => 'v1/project'], function () {
    Route::post('create', ['as' => 'project.create', 'uses' => 'ProjectController@store']);
    Route::get('list/{type}', ['as' => 'project.index', 'uses' => 'ProjectController@index']);
    Route::get('details/{id}', ['as' => 'project.details', 'uses' => 'ProjectController@show']);
    Route::post('update/{id}', ['as' => 'project.update', 'uses' => 'ProjectController@update']);
    Route::get('delete/{id}', ['as' => 'project.delete', 'uses' => 'ProjectController@destroy']);
    Route::post('addtype', ['as' => 'project.addtype', 'uses' => 'ProjectController@addType']);
});

Route::group(['prefix' => 'v1/document'], function () {
    Route::post('create', ['as' => 'document.create', 'uses' => 'DocumentController@store']);
    Route::get('list/{pId}', ['as' => 'document.index', 'uses' => 'DocumentController@index']);
    Route::get('details/{id}', ['as' => 'document.details', 'uses' => 'DocumentController@show']);
    Route::post('update/{id}', ['as' => 'document.update', 'uses' => 'DocumentController@update']);
    Route::post('delete/{id}', ['as' => 'document.delete', 'uses' => 'DocumentController@destroy']);
    Route::get('content/show/{id}', ['as' => 'document.showcontent', 'uses' => 'DocumentController@showContent']);
    Route::post('editcell', ['as' => 'document.updatecol', 'uses' => 'DocumentController@editCell']);
    Route::get('media/{id}', ['as' => 'document.media', 'uses' => 'DocumentController@getMedia']);
    Route::post('media', ['as' => 'document.media', 'uses' => 'DocumentController@addMedia']);
});

Route::group(['prefix' => 'v1/chapter'], function () {
    Route::post('create', ['as' => 'chapter.create', 'uses' => 'ChapterController@store']);
    Route::get('list/{pId}', ['as' => 'chapter.index', 'uses' => 'ChapterController@index']);
    Route::get('details/{id}', ['as' => 'chapter.details', 'uses' => 'ChapterController@show']);
    Route::post('update/{id}', ['as' => 'chapter.update', 'uses' => 'ChapterController@update']);
    Route::get('delete/{id}', ['as' => 'chapter.delete', 'uses' => 'ChapterController@destroy']);
    Route::get('content/show/{id}', ['as' => 'chapter.showcontent', 'uses' => 'ChapterController@showContent']);
    Route::post('content/update/{id}', ['as' => 'chapter.updatecontent', 'uses' => 'ChapterController@updateContent']);
    Route::post('editcell', ['as' => 'chapter.updatecol', 'uses' => 'ChapterController@editCell']);
    Route::get('media/{id}', ['as' => 'document.media', 'uses' => 'ChapterController@getMedia']);
    Route::post('media', ['as' => 'document.media', 'uses' => 'ChapterController@addMedia']);
});

Route::group(['prefix' => 'v1/event'], function () {
    Route::post('create', ['as' => 'event.create', 'uses' => 'EventController@store']);
    Route::get('list/{pId}/{type}/{id}', ['as' => 'event.index', 'uses' => 'EventController@index']);
    Route::get('details/{id}', ['as' => 'event.details', 'uses' => 'EventController@show']);
    Route::post('update/{id}', ['as' => 'event.update', 'uses' => 'EventController@update']);
    Route::get('delete/{id}', ['as' => 'event.delete', 'uses' => 'EventController@destroy']);
    Route::get('user/list', ['as' => 'event.userlist', 'uses' => 'EventController@getUserList']);
    Route::get('assigned/{event_id}', ['as' => 'event.assigned', 'uses' => 'EventController@getAssignedUsers']);
    Route::get('days/{event_id}', ['as' => 'event.days', 'uses' => 'EventController@getAssignedDays']);
    Route::post('add/user', ['as' => 'event.adduser', 'uses' => 'EventController@attachUser']);
    Route::post('editcell', ['as' => 'event.updatecol', 'uses' => 'EventController@editCell']);
    Route::get('generate/{event_id}', ['as' => 'event.generate', 'uses' => 'EventController@generateEvents']);
    Route::get('reoccurences/{event_id}', ['as' => 'event.reoccurences', 'uses' => 'EventController@getChildEvents']);
});

Route::group(['prefix' => 'v1/file'], function () {
    Route::post('upload/{id}/{type}', ['as' => 'file.upload', 'uses' => 'FileController@store']);
    Route::get('list/{type}/{id}', ['as' => 'file.index', 'uses' => 'FileController@index']);
    Route::get('details/{id}', ['as' => 'file.details', 'uses' => 'FileController@show']);
    Route::post('update/{id}', ['as' => 'file.update', 'uses' => 'FileController@update']);
    Route::get('delete/{id}', ['as' => 'file.delete', 'uses' => 'FileController@destroy']);
});

Route::group(['prefix' => 'v1/media'], function () {
    Route::post('upload/{id}/{type}', ['as' => 'media.upload', 'uses' => 'MediaController@store']);
    Route::get('list/{pId}', ['as' => 'media.index', 'uses' => 'MediaController@index']);
    Route::get('details/{id}', ['as' => 'media.details', 'uses' => 'MediaController@show']);
    Route::post('update/{id}', ['as' => 'media.update', 'uses' => 'MediaController@update']);
    Route::get('delete/{id}', ['as' => 'media.delete', 'uses' => 'MediaController@destroy']);
});

Route::group(['prefix' => 'v1/course'], function () {
    Route::get('list', ['as' => 'courses.index', 'uses' => 'CourseController@fetchCourses']);
    Route::get('topics/{serverId}/{courseId}', ['as' => 'courses.index', 'uses' => 'CourseController@fetchTopics']);
    Route::get('lesson/{lessonId}', ['as' => 'courses.lesson', 'uses' => 'CourseController@fetchLessonPage']);
    Route::get('module/{moduleId}/server/{serverId}/lesson/{lessonId}', ['as' => 'courses.content', 'uses' => 'CourseController@fetchLessonPageContent']);
    Route::get('page/{moduleId}/server/{serverId}/course/{courseId}', ['as' => 'courses.page', 'uses' => 'CourseController@fetchPageContent']);

});


Route::group(['prefix' => 'v1/question'], function () {
    Route::post('create', ['as' => 'question.create', 'uses' => 'QuestionController@createQuestion']);
    Route::get('list/{courseId}/{pageId?}', ['as' => 'question.index', 'uses' => 'QuestionController@fetchQuestions']);
    Route::get('delete/{id}', ['as' => 'question.delete', 'uses' => 'QuestionController@deleteQuestion']);
    Route::post('update_cell', ['as' => 'question.update_cell', 'uses' => 'QuestionController@editQuestionCell']);
    Route::get('show/{id}', ['as' => 'question.show', 'uses' => 'QuestionController@showQuestion']);
    Route::post('update/{id}', ['as' => 'question.update', 'uses' => 'QuestionController@updateQuestion']);
});

Route::group(['prefix' => 'v1/choice'], function () {
    Route::post('create', ['as' => 'choice.create', 'uses' => 'ChoiceController@createChoice']);
    Route::get('list/{questionId}', ['as' => 'choice.index', 'uses' => 'ChoiceController@fetchChoices']);
    Route::get('delete/{id}', ['as' => 'choice.delete', 'uses' => 'ChoiceController@deleteChoice']);
    Route::post('update_cell', ['as' => 'choice.update_cell', 'uses' => 'ChoiceController@editChoiceCell']);
    Route::get('show/{id}', ['as' => 'choice.show', 'uses' => 'ChoiceController@showChoice']);
    Route::post('update/{id}', ['as' => 'choice.update', 'uses' => 'ChoiceController@updateChoice']);
});

Route::group(['prefix' => 'v1/topic'], function () {

    Route::get('question/delete/{id}', ['as' => 'topic.question.delete', 'uses' => 'QuestionController@deletePageQuestion']);
    Route::get('question/list/{pageId}', ['as' => 'topic.question.index', 'uses' => 'QuestionController@fetchPageQuestions']);
    Route::post('question/export', ['as' => 'topic.question.export', 'uses' => 'QuestionController@exportQuestions']);
    Route::post('question/link', ['as' => 'topic.question.link', 'uses' => 'QuestionController@linkQuestionToPage']);
});

Route::group(['prefix' => 'v1/server'], function () {
    Route::post('create', ['as' => 'server.create', 'uses' => 'ServerController@createServer']);
    Route::get('list', ['as' => 'server.index', 'uses' => 'ServerController@fetchServers']);
    Route::get('delete/{id}', ['as' => 'server.delete', 'uses' => 'ServerController@deleteServer']);
    Route::post('update_cell', ['as' => 'server.update_cell', 'uses' => 'ServerController@editServerCell']);
    Route::get('show/{id}', ['as' => 'server.show', 'uses' => 'ServerController@showServer']);
    Route::post('update/{id}', ['as' => 'server.update', 'uses' => 'ServerController@updateServer']);
});

Route::group(['prefix' => 'v1/schedule'], function () {
    Route::get('events', ['as' => 'schedule.index', 'uses' => 'EventController@fetchScheduleEvents']);
    Route::get('user/{id}', ['as' => 'schedule.user', 'uses' => 'EventController@fetchUserScheduleEvents']);
    Route::get('users', ['as' => 'schedule.users', 'uses' => 'EventController@fetchScheduleUsers']);
});



