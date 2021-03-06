<?php




/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
include ( "NexmoMessage.php" );
Route::get('/', function()
{
	return Redirect::to('/dashboard');
});

Route::group(array('prefix' => 'api/v1'), function() {

	Route::resource('crops', 'CropApiController');
	Route::get('/crops/{id}/fertilizers', 'CropApiController@getFertilizers');
	Route::get('/crops/{id}/fertilizers/{fertilizerid}', 'CropApiController@getFertilizer');
	Route::get('/crops/{id}/pests', 'CropApiController@getPests');
	Route::get('/crops/{id}/pests/{pestid}', 'CropApiController@getPest');

	Route::resource('fertilizers', 'FertilizerApiController');
	Route::get('/fertilizers/{id}/crops', 'FertilizerApiController@getCrops');
	Route::get('/fertilizers/{id}/crops/{cropid}', 'FertilizerApiController@getCrop');


	Route::resource('pests', 'PestApiController');
	Route::get('/pests/{id}/crops', 'PestApiController@getCrops');
	Route::get('/pests/{id}/crops/{cropid}', 'PestApiController@getCrop');

	Route::resource('animals', 'AnimalApiController');
	Route::get('/animals/{id}/tips', 'AnimalApiController@getTips');
	Route::get('/animals/{id}/tips/{tipid}', 'AnimalApiController@getTip');

	Route::resource('announcements', 'AnnouncementApiController');
	
});

Route::group(array('prefix' => 'dashboard', 'before' => 'login'),function ()
{
	Route::get('/', 'DashboardController@getIndex');
	Route::get('/help', 'DashboardController@getHelp');
	Route::get('/crops/new', 'DashboardController@getCropForm');
	Route::post('/crops', 'DashboardController@postCropForm');
	Route::get('/crops', 'DashboardController@getCropTable');
	Route::get('/crops/{id}/delete', 'DashboardController@deleteCrop');	
	Route::get('/crops/{id}/edit', 'DashboardController@editCrop');
	Route::get('/crops/{id}/pests', 'DashboardController@getCropsPests');
	Route::get('/crops/{id}/fertilizers', 'DashboardController@getCropsFertilizers');	
	Route::get('/crops/{id}/tips', 'DashboardController@getCropTips');
	Route::get('/crops/{id}/tips/new', 'DashboardController@getCropTipForm');
	Route::post('/crops/{id}/tips', 'DashboardController@postCropTipForm');

	Route::get('/fertilizers', 'DashboardController@getFertilizerTable');
	Route::get('/fertilizers/new', 'DashboardController@getFertilizerForm');
	Route::post('/fertilizers', 'DashboardController@postfertilizerForm');
	Route::get('/fertilizers/{id}/crops', 'DashboardController@getFertilizerCrops');

	Route::get('/pests/new', 'DashboardController@getPestForm');
	Route::get('/pests', 'DashboardController@getPestTable');
	Route::get('/pests/{id}/crops', 'DashboardController@getPestCrops');
	Route::post('/pests', 'DashboardController@postPestForm');

	Route::get('/livestock/new', 'DashboardController@getLivestockForm');
	Route::get('/livestock', 'DashboardController@getLivestockTable');
	Route::post('/livestock', 'DashboardController@postLivestockForm');
	Route::get('/livestock/{id}/tips', 'DashboardController@getLivestockTips');
	Route::get('/livestock/{id}/tips/new', 'DashboardController@getLivestockTipForm');
	Route::post('/livestock/{id}/tips', 'DashboardController@postLivestockTipForm');

	Route::get('/announcements/new', 'DashboardController@getAnnouncementForm');
	Route::post('/announcements', 'DashboardController@postAnnouncementForm');
	Route::get('/announcements', 'DashboardController@getAnnouncementTable');

	Route::get('/test', function ()
	{
		$sms = new NexmoMessage('a8ca5821', '3d21bce2');
		$info = $sms->sendText( '14438558961', 'MyApp', 'Hello!' );
		echo $sms->displayOverview($info);
	});

	Route::get('/questions', 'DashboardController@getQuestionTable');
	Route::get('/answer/{id}', 'DashboardController@getAnswerForm');
	Route::post('/questions/{id}', function($id){
		$question = Question::find($id);
		$answer = Input::get('answer');

		$sms = new NexmoMessage('a8ca5821', '3d21bce2');
		$info = $sms->sendText( $question->from, 'BALE',$answer );
		echo $sms->displayOverview($info);

		$question->delete();
	});
});

Route::get('/msgreply', function(){
	//ini_set('display_errors', 'On');

	$crop_prefix = 100;
	$pest_prefix = 200;
	$fertilizer_prefix = 300;
	$livestock_prefix = 400;
	$announcement_prefix = 500;
	$tip_prefix_crop = 600;
	$help_msg = "In the crops/animals section is where you will find a list of crops/animals accompanied by their code. The accouncement section is where the latest updates provided by your extension officers are posted.Lastly, the questions section is where you send any question of concern and an api will try to get back tou as soon as possible. Thank you for using BALE SMS.";	

	
	
	// include ("crop.php");
	// Declare new NexmoMessage.

	//$crops = Crop::all();
	//$livestocks = Livestock::all();
	//$info = $sms->sendText( '18768540368', 'MyApp', 'Hello!' );
	//echo $sms->displayOverview($info);
			
$sms = new NexmoMessage('a8ca5821', '3d21bce2');
	
	if($sms->inboundText())
	{
		$text = $sms->text;
		if(Session::has('question'))
		{
			
			$question = new Question;
			$question->content = $text;
			$question->from = $sms->from;
			$question->save();
			
			$reply = "Thank you for asking we'll get back to you as soon as possible :)";
			$sms->reply($reply);
		}
		elseif($text== 0)
		{
			$sms->reply($help_msg);
		}
		elseif($text== 400)
			$sms->reply("Send\n0 for help\n1 for Crops Section\n2 for Livestock Section\n3 for announcements section\n4 for questions section"); // predial larceny
		elseif($text==1)
		{
			$reply ="";
			$crops = Crop::all();
			$reply .="Send\n";
			foreach($crops as $crop)
			{
				$code = $crop_prefix.$crop->id;
				$reply .= ($code." for ".$crop->name."\n");
			}
			$sms->reply($reply);
		}
		elseif($text ==2)
		{
			$reply ="";
			$livestocks = Livestock::all();
			$reply .="Send\n";
			foreach($livestocks as $livestock)
			{
				$code = $livestock_prefix.$livestock->id;
				$reply .=($code." for ".$livestock->name."\n");
			}
			$sms->reply($reply);
		}
		elseif($text ==3)
		{
			$reply = "The latest announcements from your extension officers are:\n";
			$announcements = Announcement::all();
			foreach($announcements as $announcement)
			{
				$code = $announcement_prefix.$announcement->id;
				$reply .=($code." for ".$announcement->description."\n");
			}
			$sms->reply($reply);
		}
		elseif($text ==4)
		{
			$reply = "Please reply with any question you have and an extension officer will attempt to answer you as soon as possible as best as possible.";
			$sms->reply($reply);
			Session::flash('question',$sms->from);
		}
		elseif(substr($text,0, 3) == $tip_prefix_crop)
		{
			$text = (int) $text;
			$id = substr($text,3);
			$tip = Croptip::findOrFail($id);
			
			$sms->reply($tip->description." - ".$tip->content);
		}
		elseif(substr($text,0, 3) == $crop_prefix)
		{ 
			$text = (int) $text;
			if(strlen($text) >4)
				$id = substr($text,3,strlen($text)-4);
			else 
				$id = substr($text,3);
			
			try
			{
				$crop = Crop::findOrFail($id);
		
				if(substr($text,3) == $crop->id)
				{
					$code = $text;

					$option1 = $code."1 - Getting started"; 
					$option2 = $code."2 - Latest tips";
					$option3 = $code."3 - Recommended fertilizers";
					$option4 = $code."4 - Pests";
					$option5 = $code."5 - When to harvest";
					
					$reply = $option1."\n".$option2."\n".$option3."\n".$option4."\n".$option5;
					$reply1 = substr($reply,0,strlen($reply)/2);
					$reply2 = substr($reply,strlen($reply)/2,strlen($reply)/2);
					
					$sms->reply($reply);
					//$sms->sendText($sms->from,'Bale',$reply2);
				}
				elseif($id == $crop->id)
				{
					  $lastDigit = substr($text, strlen($text)-1);
					  
					  switch($lastDigit)
					  {
						  case 1:$sms->reply("Getting start with ".($crop->name).":\n".($crop->getting_started));
						  break;
					  
						  case 2:
						  $tips = $crop->croptips()->get();
						  $reply = "";
						  foreach($tips as $tip)
						  {
							  $code = $tip_prefix_crop.$tip->id;
							  $reply.= ($code." - ".$tip->description."\n");
						  }
						  $sms->reply("Some tips are:\n".$reply);
						  break;
					  
						  case 3:
						  $fertilizers = $crop->fertilizers()->get();
						  $reply = "";
						  foreach($fertilizers as $fertilizer)
						  {
							  $reply.= ($fertilizer->type."\n");
						  }
						  $sms->reply("The recommended fertilizers for ".$crop->name." are \n".$reply);
						  break;
						  case 4:
						  $pests = $crop->pests()->get();
						  $reply = "";
						  foreach($pests as $pest)
						  {
							  $code = $pest_prefix.$pest->id;
							  $reply.= ($code." - ".$pest->type."\n");
						  }
						  $sms->reply("The pests that normally affect ".$crop->name." are \n".$reply);
						  break;
						  case 5:$sms->reply("The recommended number of days to wait before harvesting ".$crop->name." are ".$crop->days_until_harvest);
						  break;
						  default: $sms->reply($lastDigit);
						  break;
					  }					    
				 }
			}	
			catch(Exception $e)
			{
				$sms->reply($e);
			}
		}
		elseif(substr($text,0, 3) == $livestock_prefix)
		{
			$text = (int) $text;
			strlen($text) == 4 ? $id = substr($text,3,strlen($text)-3):substr($text,3,strlen($text)-4);
			foreach($livestocks as $livestock)
			{
				if(substr($text,3) == $livestock->id)
				{
					$code = $text;
					$option1 = $code."1 - Getting started with ".$livestock->name;
					$option2 = $code."2 - Recommended feed for ".$livestock->name;
					$option3 = $code."3 - Tips for caring for ".$livestock->name;
					
					$sms->reply($option1."\n".$option2."\n".$option3);
				}
				elseif($id == $livestock->id)
				{
					  $lastDigit = substr($text, strlen($text)-1);
					  
					  switch($lastDigit)
					  {
						  case 1:$sms->reply("Getting started with ".$livestock->name.":\n".($livestock->getting_started));
						  break;
					  
						  case 2:$sms->reply("The recommended feed for ".$livestock->name." is ".($livestock->feed));
						  break;
					  
						  case 3:$sms->reply("Some recommended tips for ".$livestock->name." are\n".($livestock->care_methods));
						  break;
						  default: $sms->reply($lastDigit);
						  break;
					  }					    
				 }
			}
		}
		else
		{
			$sms->reply("Unknown code");
		}
	}
	echo "string";
});

Route::get('/createJill', function(){

	$user = new User;
	$user->name = "Jill Brown";
	$user->username = "JillB";
	$user->password = "Password";
	$user->phone = '2222222';
	$user->email = 'jlb@jlb.com';
	$user->save();

	return 'saved';
});

Route::get('/createChicken', function(){
	$livestock = new Livestock;

	$livestock->name = "Chicken";
	$livestock->price = "1000.00";
	$livestock->care_methods = "Keep out of cold areas";
	$livestock->feed = "Chicken Feed";

	$livestock->save();
});

/*

$user->name = "John Brown";
	$user->username = "JohnB";
	$user->password = "Password";
	$user->phone = '111111111';
	$user->email = 'jb@jb.com';


*/


Route::get('/login',function(){
	Session::flush();
	return View::make('login');
	});
	
Route::get('/logout',function(){
	Session::flush();
	});

Route::get('/showAll', function() {
	return User::all()->toJson();
});

Route::get('/home', function() {
	if(Session::has('user'))
	{
		$user = Session::get('user');
		return $user->name ." is logged in";
	}
	else
		return "nah";
});

Route::post('/login',function() {
	
	$data = Input::all();
	
	$number= $data['number'];
	$password= $data['password'];
	
	$users = User::all();
	foreach($users as $user)
	{
		if(strcmp($user->phone,$number)==0 && strcmp($user->password,$password)==0)
		{
			Session::put('user',$user);
			return Redirect::to('/dashboard');
		} else {
			return Redirect::to('/login');
		}
	}
});
